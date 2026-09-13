<?php
/**
 * Zimny_Admin_IG_Feed - Feed do Instagram @zimnymagazine com API oficial
 *
 * Substitui o plugin isolado zimny-instagram-feed:
 *   - Fonte primária: Instagram Graph API (System User token, não expira).
 *   - Fallbacks: Instagram público, página do perfil e RSSHub (como antes).
 *   - Mantém o MESMO option/cache e o MESMO endpoint REST do plugin antigo,
 *     então o app iOS/Android continua funcionando sem build novo.
 *
 * Endpoint: GET /wp-json/zimny/v1/instagram
 * Cache:    wp_options 'zimny_instagram_feed' (TTL 30 min)
 *
 * @package Zimny_Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Zimny_Admin_IG_Feed {

    const CACHE_KEY        = 'zimny_instagram_feed';
    const LOG_KEY          = 'zimny_ig_debug_log';
    const TOKEN_OPT        = 'zimny_ig_graph_token';
    const IGID_OPT         = 'zimny_ig_graph_igid';
    const LAST_ATTEMPT     = 'zimny_ig_public_last_attempt';
    const TTL              = 1800; // 30 min
    const COOLDOWN         = 1800; // 30 min entre tentativas públicas
    const GRAPH_API_VER    = 'v26.0';
    const GRAPH_API_BASE   = 'https://graph.facebook.com';

    public function __construct() {
        add_filter('cron_schedules', array($this, 'register_cron_schedule'));
        add_action('rest_api_init', array($this, 'register_rest_route'));
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_post_zimny_ig_save_settings', array($this, 'handle_save_settings'));
        add_action('admin_post_zimny_ig_force_refresh', array($this, 'handle_force_refresh'));
        add_action('admin_post_zimny_ig_clear_logs', array($this, 'handle_clear_logs'));
        // Cron a cada 30 min (reaproveita schedule existente se houver)
        add_action('zimny_instagram_fetch', array($this, 'refresh_cache'));
        if (!wp_next_scheduled('zimny_instagram_fetch')) {
            add_action('init', function () {
                if (!wp_next_scheduled('zimny_instagram_fetch')) {
                    wp_schedule_event(time(), 'zimny_30min', 'zimny_instagram_fetch');
                }
            });
        }
    }

    public function register_cron_schedule(array $schedules) {
        $schedules['zimny_30min'] = array(
            'interval' => self::TTL,
            'display'  => __('A cada 30 minutos', 'zimny-admin'),
        );
        return $schedules;
    }

    // ─── Credenciais ────────────────────────────────────────────────────────

    private function token() {
        if (defined('ZIMNY_IG_GRAPH_TOKEN')) {
            return trim((string) ZIMNY_IG_GRAPH_TOKEN);
        }
        return trim((string) get_option(self::TOKEN_OPT, ''));
    }

    private function ig_id() {
        if (defined('ZIMNY_IG_GRAPH_IG_ID')) {
            return trim((string) ZIMNY_IG_GRAPH_IG_ID);
        }
        return trim((string) get_option(self::IGID_OPT, ''));
    }

    // ─── Log ────────────────────────────────────────────────────────────────

    private function log($message) {
        $logs = get_option(self::LOG_KEY, array());
        if (!is_array($logs)) {
            $logs = array();
        }
        $logs[] = array('time' => gmdate('H:i:s'), 'message' => $message);
        $logs = array_slice($logs, -20);
        update_option(self::LOG_KEY, $logs, false);
    }

    // ─── Fetch e cache (idêntico ao contrato do app) ────────────────────────

    public function refresh_cache() {
        $cache = get_option(self::CACHE_KEY, null);
        update_option(self::LAST_ATTEMPT, time(), false);

        $posts = $this->fetch_from_graph_api();
        $source = 'Graph API';

        if (empty($posts)) {
            $posts = $this->fetch_from_public_instagram();
            $source = 'Instagram público';
        }
        if (empty($posts)) {
            $this->log('API pública indisponível; tentando página do perfil');
            $posts = $this->scrape_profile();
            $source = 'perfil público';
        }
        if (empty($posts)) {
            $this->log('Perfil indisponível; tentando RSSHub gratuito');
            $posts = $this->fetch_from_rsshub();
            $source = 'RSSHub';
        }

        if (!empty($posts)) {
            usort($posts, function ($a, $b) {
                return strcmp((string) ($b['timestamp'] ?? ''), (string) ($a['timestamp'] ?? ''));
            });
            $posts = array_slice($posts, 0, 6);
            $data = array(
                'posts'     => $posts,
                'cached_at' => gmdate('Y-m-d\TH:i:s\Z'),
                'source'    => $source,
            );
            update_option(self::CACHE_KEY, $data, false);
            $this->log(sprintf('%s: cache atualizado com %d posts', $source, count($posts)));
            return $data;
        }

        if (is_array($cache) && !empty($cache['posts'])) {
            $this->log('Fontes gratuitas indisponíveis; mantendo último cache válido');
            return $cache;
        }

        $this->log('Sem cache inicial; aguardando próxima tentativa');
        return array('posts' => array(), 'cached_at' => gmdate('Y-m-d\TH:i:s\Z'), 'source' => 'vazio');
    }

    // ─── Fonte 1: Graph API oficial ─────────────────────────────────────────

    private function fetch_from_graph_api() {
        $token = $this->token();
        $ig_id = $this->ig_id();
        if ($token === '' || $ig_id === '') {
            $this->log('Graph API: credenciais não configuradas (Settings → Instagram Feed)');
            return array();
        }

        $url = add_query_arg(array(
            'fields'       => 'id,caption,media_url,thumbnail_url,permalink,timestamp,media_type',
            'limit'        => '15',
            'access_token' => $token,
        ), sprintf('%s/%s/%s/media', self::GRAPH_API_BASE, self::GRAPH_API_VER, rawurlencode($ig_id)));

        $response = wp_remote_get($url, array('timeout' => 20));
        if (is_wp_error($response)) {
            $this->log('Graph API: ' . $response->get_error_message());
            return array();
        }
        $code = (int) wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            $this->log('Graph API: HTTP ' . $code);
            return array();
        }

        $payload = json_decode(wp_remote_retrieve_body($response), true);
        $items = (is_array($payload) && isset($payload['data']) && is_array($payload['data']))
            ? $payload['data']
            : array();
        if (empty($items)) {
            $this->log('Graph API: resposta sem posts');
            return array();
        }

        $posts = array();
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $permalink = isset($item['permalink']) ? esc_url_raw((string) $item['permalink'], array('https')) : '';
            if ($permalink === '') {
                continue;
            }
            $shortcode = '';
            if (preg_match('#/p/([A-Za-z0-9_-]+)/?#', $permalink, $m)
                || preg_match('#/reel/([A-Za-z0-9_-]+)/?#', $permalink, $m)
                || preg_match('#/tv/([A-Za-z0-9_-]+)/?#', $permalink, $m)) {
                $shortcode = $m[1];
            }

            $image_url = isset($item['thumbnail_url']) && $item['thumbnail_url'] !== ''
                ? (string) $item['thumbnail_url']
                : (isset($item['media_url']) ? (string) $item['media_url'] : '');

            // CAROUSEL_ALBUM normalmente não traz media no nó pai — busca 1º filho.
            if ($image_url === '' && isset($item['media_type']) && $item['media_type'] === 'CAROUSEL_ALBUM') {
                $image_url = $this->graph_first_child_media((string) $item['id'], $token);
            }

            // Último recurso: rota estável derivada do shortcode (o REST final já converte).
            if ($image_url === '' && $shortcode !== '') {
                $image_url = sprintf('https://www.instagram.com/p/%s/media/?size=l', $shortcode);
            }
            if ($image_url === '') {
                continue;
            }

            $timestamp = gmdate('Y-m-d\TH:i:s\Z');
            if (!empty($item['timestamp'])) {
                $parsed = strtotime((string) $item['timestamp']);
                if ($parsed !== false) {
                    $timestamp = gmdate('Y-m-d\TH:i:s\Z', $parsed);
                }
            }

            $caption = isset($item['caption']) ? (string) $item['caption'] : '';
            $posts[] = array(
                'id'          => isset($item['id']) ? (string) $item['id'] : $shortcode,
                'image_url'   => esc_url_raw($image_url, array('https')),
                'caption'     => mb_substr($caption, 0, 150),
                'likes_count' => 0,
                'permalink'   => $permalink,
                'timestamp'   => $timestamp,
            );

            if (count($posts) >= 12) {
                break;
            }
        }

        if (!empty($posts)) {
            $this->log(sprintf('Graph API: %d posts oficiais carregados', count($posts)));
        }
        return $posts;
    }

    private function graph_first_child_media($media_id, $token) {
        $url = add_query_arg(array(
            'fields'       => 'thumbnail_url,media_url',
            'limit'        => '1',
            'access_token' => $token,
        ), sprintf('%s/%s/%s/children', self::GRAPH_API_BASE, self::GRAPH_API_VER, rawurlencode($media_id)));
        $response = wp_remote_get($url, array('timeout' => 12));
        if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
            return '';
        }
        $payload = json_decode(wp_remote_retrieve_body($response), true);
        $children = (is_array($payload) && isset($payload['data']) && is_array($payload['data'])) ? $payload['data'] : array();
        if (empty($children) || !is_array($children[0])) {
            return '';
        }
        $child = $children[0];
        return !empty($child['thumbnail_url']) ? (string) $child['thumbnail_url'] : (string) ($child['media_url'] ?? '');
    }

    // ─── Fallbacks gratuitos (portados do plugin antigo) ────────────────────

    private function fetch_from_public_instagram() {
        $url = sprintf(
            'https://www.instagram.com/api/v1/users/web_profile_info/?username=%s',
            rawurlencode('zimnymagazine')
        );
        $response = wp_safe_remote_get($url, array(
            'timeout' => 20,
            'headers' => array(
                'Accept'          => 'application/json',
                'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
                'User-Agent'      => 'Mozilla/5.0 (Linux; Android 13) AppleWebKit/537.36 Chrome/125.0.0.0 Mobile Safari/537.36',
                'X-IG-App-ID'     => '936619743392459',
            ),
        ));
        if (is_wp_error($response)) {
            $this->log('Instagram público: ' . $response->get_error_message());
            return array();
        }
        $code = (int) wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            $this->log('Instagram público: HTTP ' . $code);
            return array();
        }
        $payload = json_decode(wp_remote_retrieve_body($response), true);
        $edges = $payload['data']['user']['edge_owner_to_timeline_media']['edges'] ?? array();
        if (!is_array($edges) || empty($edges)) {
            $this->log('Instagram público: resposta sem posts');
            return array();
        }
        $posts = array();
        foreach ($edges as $edge) {
            $node = is_array($edge) && isset($edge['node']) && is_array($edge['node']) ? $edge['node'] : null;
            if (!$node) {
                continue;
            }
            $post = $this->map_public_node($node);
            if ($post !== null) {
                $posts[] = $post;
            }
            if (count($posts) >= 12) {
                break;
            }
        }
        return $posts;
    }

    private function map_public_node(array $node) {
        $shortcode = isset($node['shortcode']) ? (string) $node['shortcode'] : '';
        if ($shortcode === '' || !preg_match('/^[A-Za-z0-9_-]+$/', $shortcode)) {
            return null;
        }
        $kind = !empty($node['is_video']) ? 'reel' : 'p';
        $permalink = sprintf('https://www.instagram.com/%s/%s/', $kind, $shortcode);
        $ts = isset($node['taken_at_timestamp']) ? (int) $node['taken_at_timestamp'] : time();
        $caption = $node['edge_media_to_caption']['edges'][0]['node']['text'] ?? ($node['caption'] ?? '');
        return array(
            'id'          => isset($node['id']) ? (string) $node['id'] : $shortcode,
            'image_url'   => !empty($node['display_url']) ? esc_url_raw((string) $node['display_url'], array('https')) : sprintf('https://www.instagram.com/p/%s/media/?size=l', $shortcode),
            'caption'     => mb_substr((string) $caption, 0, 150),
            'likes_count' => 0,
            'permalink'   => $permalink,
            'timestamp'   => gmdate('Y-m-d\TH:i:s\Z', $ts),
        );
    }

    private function scrape_profile() {
        $url = sprintf('https://www.instagram.com/%s/', rawurlencode('zimnymagazine'));
        $response = wp_safe_remote_get($url, array(
            'timeout' => 15,
            'headers' => array(
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/125.0.0.0 Safari/537.36',
                'Accept'          => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
            ),
        ));
        if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
            return array();
        }
        $html = wp_remote_retrieve_body($response);
        $posts = $this->parse_embedded_json($html);
        if (empty($posts)) {
            $posts = $this->parse_next_data($html);
        }
        return $posts;
    }

    private function parse_embedded_json($html) {
        if (!preg_match_all('#<script[^>]+type=["\']application/json["\'][^>]*>(.*?)</script>#is', $html, $blocks)) {
            return array();
        }
        $posts = array();
        foreach ($blocks[1] as $block) {
            $json = json_decode(html_entity_decode(trim($block), ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
            if (!is_array($json)) {
                continue;
            }
            $feed = $this->dig($json, array('entry_data', 'ProfilePage', 0, 'graphql', 'user', 'edge_owner_to_timeline_media', 'edges'));
            if (is_array($feed)) {
                foreach ($feed as $edge) {
                    if (isset($edge['node']) && is_array($edge['node'])) {
                        $p = $this->map_public_node($edge['node']);
                        if ($p) {
                            $posts[] = $p;
                        }
                    }
                    if (count($posts) >= 12) {
                        break 2;
                    }
                }
            }
        }
        return $posts;
    }

    private function parse_next_data($html) {
        if (!preg_match('#<script[^>]+id=["\']__NEXT_DATA__["\'][^>]*>(.*?)</script>#is', $html, $m)) {
            return array();
        }
        $json = json_decode(html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
        if (!is_array($json)) {
            return array();
        }
        $items = $this->dig($json, array('props', 'pageProps', '__APOLLO_STATE__'));
        $posts = array();
        if (is_array($items)) {
            foreach ($items as $value) {
                if (!is_array($value) || empty($value['__typename'])) {
                    continue;
                }
                if (isset($value['shortcode']) && (isset($value['display_url']) || isset($value['display_resources']))) {
                    $p = $this->map_public_node($value);
                    if ($p) {
                        $posts[] = $p;
                    }
                }
                if (count($posts) >= 12) {
                    break;
                }
            }
        }
        return $posts;
    }

    private function dig(array $arr, array $keys) {
        $cur = $arr;
        foreach ($keys as $k) {
            if (is_array($cur) && array_key_exists($k, $cur)) {
                $cur = $cur[$k];
            } elseif (is_array($cur) && is_int($k) && array_key_exists($k, $cur)) {
                $cur = $cur[$k];
            } else {
                return null;
            }
        }
        return $cur;
    }

    private function fetch_from_rsshub() {
        $instances = array('https://rsshub.app', 'https://rsshub.rssforever.com', 'https://rsshub.pseudoyu.com');
        foreach ($instances as $base) {
            $url = sprintf('%s/instagram/user/%s', rtrim($base, '/'), 'zimnymagazine');
            $response = wp_safe_remote_get($url, array(
                'timeout' => 12,
                'headers' => array(
                    'Accept'     => 'application/rss+xml, application/xml, text/xml',
                    'User-Agent' => 'Mozilla/5.0 (compatible; ZimnyMedia/1.0; +https://zimnymagazine.com)',
                ),
            ));
            if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
                continue;
            }
            $body = wp_remote_retrieve_body($response);
            if (!preg_match_all('#<item\b[^>]*>(.*?)</item>#is', $body, $items)) {
                continue;
            }
            $posts = array();
            foreach ($items[1] as $item_xml) {
                if (!preg_match('#https://(?:www\.)?instagram\.com/(p|reel|tv)/([A-Za-z0-9_-]+)#i', $item_xml, $link)) {
                    continue;
                }
                $permalink = sprintf('https://www.instagram.com/%s/%s/', strtolower($link[1]), $link[2]);
                $timestamp = gmdate('Y-m-d\TH:i:s\Z');
                if (preg_match('#<pubDate[^>]*>(.*?)</pubDate>#is', $item_xml, $date_match)) {
                    $parsed = strtotime(html_entity_decode(strip_tags($date_match[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    if ($parsed !== false) {
                        $timestamp = gmdate('Y-m-d\TH:i:s\Z', $parsed);
                    }
                }
                $caption = '';
                if (preg_match('#<title[^>]*>(.*?)</title>#is', $item_xml, $title_match)) {
                    $caption = trim(html_entity_decode(strip_tags($title_match[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                }
                $posts[] = array(
                    'id'          => $link[2],
                    'image_url'   => sprintf('https://www.instagram.com/p/%s/media/?size=l', $link[2]),
                    'caption'     => mb_substr($caption, 0, 150),
                    'likes_count' => 0,
                    'permalink'   => $permalink,
                    'timestamp'   => $timestamp,
                );
                if (count($posts) >= 12) {
                    break;
                }
            }
            if (!empty($posts)) {
                return $posts;
            }
        }
        return array();
    }

    // ─── REST endpoint (mesmo contrato do app) ──────────────────────────────

    public function register_rest_route() {
        register_rest_route('zimny/v1', '/instagram', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'rest_endpoint'),
            'permission_callback' => '__return_true',
        ));
    }

    public function rest_endpoint() {
        $data = get_option(self::CACHE_KEY, null);
        $ts = is_array($data) && !empty($data['cached_at']) ? strtotime((string) $data['cached_at']) : false;
        $stale = $ts === false || (time() - $ts) >= self::TTL;
        $last = (int) get_option(self::LAST_ATTEMPT, 0);
        $can_retry = $last === 0 || (time() - $last) >= self::COOLDOWN;

        if ($data === null || !is_array($data) || ($stale && $can_retry)) {
            $data = $this->refresh_cache();
        }
        $data = $this->prepare_rest_data(is_array($data) ? $data : array());

        $response = new WP_REST_Response($data, 200);
        $response->set_headers(array(
            'X-Zimny-Cache'      => 'hit',
            'X-Zimny-Image-URLs' => 'stable',
            'Cache-Control'      => sprintf('public, max-age=%d', self::TTL),
        ));
        return $response;
    }

    private function prepare_rest_data(array $data) {
        if (empty($data['posts']) || !is_array($data['posts'])) {
            return $data;
        }
        foreach ($data['posts'] as &$post) {
            if (!is_array($post)) {
                continue;
            }
            $permalink = isset($post['permalink']) ? esc_url_raw((string) $post['permalink'], array('https')) : '';
            $stable = '';
            if ($permalink !== '' && preg_match('#^https://(?:www\.)?instagram\.com/(p|reel|tv)/([A-Za-z0-9_-]+)/?#i', $permalink, $mm)) {
                $stable = sprintf('https://www.instagram.com/p/%s/media/?size=l', $mm[2]);
            }
            if ($stable === '') {
                continue;
            }
            $source_url = isset($post['image_url']) ? esc_url_raw((string) $post['image_url'], array('https')) : '';
            if ($source_url !== '' && $source_url !== $stable) {
                $post['source_image_url'] = $source_url;
            }
            $post['image_url'] = $stable;
        }
        unset($post);
        return $data;
    }

    // ─── Admin: página de configuração ──────────────────────────────────────

    public function register_menu() {
        add_submenu_page(
            'edit.php?post_type=zimny_video',
            'Feed Instagram',
            '📸 Feed Instagram',
            'manage_options',
            'zimny-ig-feed',
            array($this, 'render_settings_page')
        );
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $data = get_option(self::CACHE_KEY, array());
        $posts = is_array($data) && !empty($data['posts']) ? count($data['posts']) : 0;
        $cached = is_array($data) && !empty($data['cached_at']) ? $data['cached_at'] : '—';
        $source = is_array($data) && !empty($data['source']) ? $data['source'] : '—';
        $token_set = $this->token() !== '';
        $igid = $this->ig_id();
        ?>
        <div class="wrap">
            <h1>Feed Instagram — @zimnymagazine</h1>
            <p>API oficial (Graph) primeiro; fallbacks gratuitos; cache de 30 min. Endpoint: <code><?php echo esc_html(rest_url('zimny/v1/instagram')); ?></code></p>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="zimny_ig_save_settings" />
                <?php wp_nonce_field('zimny_ig_save_settings'); ?>
                <h2>Credenciais da API oficial</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">System User token (Meta)</th>
                        <td>
                            <input type="password" name="zimny_ig_graph_token" class="regular-text" autocomplete="off"
                                   value="<?php echo $token_set ? '••••••••••••' : ''; ?>" />
                            <p class="description">Token do usuário do sistema (não expira). <?php echo $token_set ? 'Configurado — deixe vazio para manter.' : 'Ainda não configurado.'; ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">ID da conta do Instagram</th>
                        <td>
                            <input type="text" name="zimny_ig_graph_igid" class="regular-text" autocomplete="off" value="<?php echo esc_attr($igid); ?>" />
                            <p class="description">Ex.: 17841465187886962</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Salvar credenciais'); ?>
            </form>

            <h2>Status do cache</h2>
            <p><?php echo esc_html($posts); ?> posts | fonte: <strong><?php echo esc_html($source); ?></strong> | atualizado: <?php echo esc_html($cached); ?> UTC</p>
            <p>
                <a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=zimny_ig_force_refresh'), 'zimny_ig_force_refresh')); ?>">🔄 Forçar atualização agora</a>
            </p>

            <h2>Log de debug</h2>
            <div style="background:#1d2327;color:#c3c4c7;padding:12px 16px;border-radius:6px;font-family:monospace;font-size:12px;line-height:1.6;max-height:300px;overflow-y:auto;">
                <?php
                $logs = get_option(self::LOG_KEY, array());
                if (empty($logs) || !is_array($logs)) {
                    echo '<span style="color:#8c8f94;">Nenhum log ainda.</span>';
                } else {
                    foreach (array_reverse($logs) as $log) {
                        echo '<div>[' . esc_html($log['time'] ?? '') . '] ' . esc_html($log['message'] ?? '') . '</div>';
                    }
                }
                ?>
            </div>
            <p><a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=zimny_ig_clear_logs'), 'zimny_ig_clear_logs')); ?>">🗑️ Limpar logs</a></p>
        </div>
        <?php
    }

    public function handle_save_settings() {
        if (!current_user_can('manage_options')) {
            wp_die('Sem permissão.');
        }
        check_admin_referer('zimny_ig_save_settings');
        if (isset($_POST['zimny_ig_graph_token']) && $_POST['zimny_ig_graph_token'] !== '' && $_POST['zimny_ig_graph_token'] !== '••••••••••••') {
            update_option(self::TOKEN_OPT, sanitize_text_field(wp_unslash($_POST['zimny_ig_graph_token'])), false);
        }
        if (isset($_POST['zimny_ig_graph_igid'])) {
            update_option(self::IGID_OPT, sanitize_text_field(wp_unslash($_POST['zimny_ig_graph_igid'])), false);
        }
        wp_redirect(add_query_arg('saved', '1', admin_url('edit.php?post_type=zimny_video&page=zimny-ig-feed')));
        exit;
    }

    public function handle_force_refresh() {
        if (!current_user_can('manage_options')) {
            wp_die('Sem permissão.');
        }
        check_admin_referer('zimny_ig_force_refresh');
        $this->refresh_cache();
        wp_redirect(add_query_arg('refreshed', '1', admin_url('edit.php?post_type=zimny_video&page=zimny-ig-feed')));
        exit;
    }

    public function handle_clear_logs() {
        if (!current_user_can('manage_options')) {
            wp_die('Sem permissão.');
        }
        check_admin_referer('zimny_ig_clear_logs');
        delete_option(self::LOG_KEY);
        wp_redirect(add_query_arg('cleared', '1', admin_url('edit.php?post_type=zimny_video&page=zimny-ig-feed')));
        exit;
    }
}

new Zimny_Admin_IG_Feed();
