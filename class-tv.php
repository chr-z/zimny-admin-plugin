<?php
/**
 * Zimny_Admin_Tv — Zimny TV 24/7: transmissão linear, programação e REST endpoint.
 *
 * Gera a programação linear 24/7 a partir de uma playlist do YouTube ou vídeos manuais.
 * Fornece o endpoint GET /wp-json/zimny/v1/live-tv para o app React Native.
 *
 * @package Zimny_Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Zimny_Admin_Tv {

    const OPTION_KEY     = 'zimny_tv_settings';
    const VIDEOS_KEY     = 'zimny_tv_videos';
    const CACHE_KEY      = 'zimny_tv_current_video';
    const CACHE_TTL      = 30;

    /**
     * Construtor: registra o endpoint REST e assets do admin.
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_action('admin_enqueue_scripts', array($this, 'admin_assets'));
    }

    /**
     * CSS/JS inline para as páginas de admin da TV.
     */
    public function admin_assets(string $hook): void {
        if (strpos($hook, 'zimny-tv') === false) return;
        wp_add_inline_style('wp-admin', '
            .zimny-tv-wrap { max-width: 960px; }
            .zimny-tv-wrap h1 { margin-bottom: 24px; }
            .zimny-tv-status { display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:4px;background:#f0f0f1;margin-bottom:20px; }
            .zimny-tv-status.online { background:#d4edda;color:#155724; }
            .zimny-tv-status.offline { background:#f8d7da;color:#721c24; }
            .zimny-tv-status .dot { width:10px;height:10px;border-radius:50%;display:inline-block; }
            .zimny-tv-status.online .dot { background:#28a745; }
            .zimny-tv-status.offline .dot { background:#dc3545; }
            .zimny-tv-video-row { display:flex;align-items:center;gap:12px;padding:12px;background:#fff;border:1px solid #ddd;margin-bottom:8px;border-radius:4px; }
            .zimny-tv-video-row .handle { cursor:grab;color:#999;font-size:18px; }
            .zimny-tv-video-row .thumb { width:80px;height:45px;background:#f0f0f1;border-radius:2px;overflow:hidden;flex-shrink:0; }
            .zimny-tv-video-row .thumb img { width:100%;height:100%;object-fit:cover; }
            .zimny-tv-video-row .info { flex:1;min-width:0; }
            .zimny-tv-video-row .info .title { font-weight:600;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
            .zimny-tv-video-row .info .meta { font-size:12px;color:#666; }
            .zimny-tv-video-row .actions { display:flex;gap:8px;flex-shrink:0; }
            .zimny-tv-field { margin-bottom:16px; }
            .zimny-tv-field label { display:block;font-weight:600;margin-bottom:4px; }
            .zimny-tv-field input[type="text"], .zimny-tv-field textarea { width:100%;max-width:480px; }
            .zimny-tv-field .description { color:#666;font-size:12px;margin-top:4px; }
            .zimny-tv-video-form { background:#f0f0f1;padding:16px;border-radius:4px;margin-bottom:20px; }
            .zimny-tv-video-form .fields { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
            .zimny-tv-video-form .field-full { grid-column:1/-1; }
            .zimny-tv-video-form .actions { margin-top:12px;display:flex;gap:8px; }
            #zimny-tv-video-list { margin-bottom:16px; }
            .zimny-tv-empty { text-align:center;padding:40px;color:#999; }
        ');
        // Garante que o frame de Media Library (wp.media) está disponível
        wp_enqueue_media();
    }

    /**
     * Registra a rota GET /wp-json/zimny/v1/live-tv
     */
    public function register_rest_routes(): void {
        register_rest_route('zimny/v1', '/live-tv', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_stream'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('zimny/v1', '/podcast', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_podcast'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'limit' => array(
                    'required'          => false,
                    'sanitize_callback' => 'absint',
                    'default'           => 50,
                ),
            ),
        ));
    }

    /**
     * Callback principal do endpoint /live-tv.
     *
     * Retorna o vídeo atual (now_playing) + grade de programação (schedule).
     * Usa cache de 30s via transients.
     */
    public function get_stream() {
        // Endpoint "ao vivo": a resposta NUNCA pode ser cacheada por página/CDN.
        // O app usa server_timestamp p/ posicionar o player; corpo congelado no
        // LiteSpeed/Hostinger fazia o app perseguir um relógio morto (loop de
        // vídeos/vinheta). Força no-cache em todas as camadas.
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true); // respeitado por LiteSpeed/W3TC etc.
        }
        nocache_headers(); // WP: Cache-Control: no-cache, must-revalidate
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        // Avisa o LiteSpeed Cache (quando presente) para não page-cachear.
        if (has_action('litespeed_control_set_nocache')) {
            do_action('litespeed_control_set_nocache', 'zimny-live-tv-endpoint');
        }

        // 0. `?fresh=1` força recálculo (ignora o transient de 30s). Usado pelo
        //    app na virada de programa/vinheta para sincronizar com o AO VIVO.
        $force_fresh = isset($_GET['fresh']) && $_GET['fresh'] === '1';

        // 1. Tenta cache
        if (!$force_fresh) {
            $cached = get_transient(self::CACHE_KEY);
            if ($cached !== false) {
                // Nunca serve um transient que aponte para um programa que já
                // terminou na grade real (ex: cache gerado nos últimos segundos
                // de um vídeo). Isso evita o "travamento no fim" que parece
                // falha/playlist para quem abre a TV no momento errado.
                $cached_program = $cached['now_playing'] ?? null;
                $cached_is_stale = false;
                if (is_array($cached_program)) {
                    $cached_duration = absint($cached_program['duration'] ?? 0);
                    if ($cached_duration > 0) {
                        $elapsed = max(0, time() - absint($cached_program['server_timestamp'] ?? time()));
                        $cached_pos = absint($cached_program['seek_to_seconds'] ?? 0) + $elapsed;
                        if ($cached_pos >= $cached_duration) {
                            $cached_is_stale = true;
                        }
                    }
                }
                if (!$cached_is_stale) {
                    return rest_ensure_response($cached);
                }
            }
        }

        $settings         = get_option(self::OPTION_KEY, array());
        $default_category = $settings['default_category'] ?? 'ZIMNY TV · TRANSMISSÃO 24/7';
        $videos           = get_option(self::VIDEOS_KEY, array());

        $items = array();

        // 2. Monta lista de itens: attachment_id → resolve URL, youtube_id → mantém
        foreach ($videos as $v) {
            $item = array(
                'video_id' => '',
                'title'    => $v['title'] ?? '',
                'category' => !empty($v['category']) ? $v['category'] : $default_category,
                'duration' => !empty($v['duration']) ? absint($v['duration']) : 1800,
                'video_url' => '',
                'thumbnail_url' => '',
            );

            // WordPress Media Library
            if (!empty($v['attachment_id'])) {
                $attach_id = absint($v['attachment_id']);
                $url = wp_get_attachment_url($attach_id);
                if ($url) {
                    $item['video_id']    = (string) $attach_id;
                    $item['video_url']   = $url;
                    $item['thumbnail_url'] = wp_get_attachment_image_url($attach_id, 'medium') ?: '';
                    // Se a duração não foi salva manualmente, tenta obter dos metadados
                    if (empty($v['duration'])) {
                        $metadata = wp_get_attachment_metadata($attach_id);
                        if (!empty($metadata['length'])) {
                            $item['duration'] = (int) $metadata['length'];
                        }
                    }
                    $items[] = $item;
                }
            }
            // YouTube fallback (legado)
            elseif (!empty($v['video_id'])) {
                $item['video_id'] = $v['video_id'];
                $items[] = $item;
            }
        }

        if (empty($items)) {
            return new WP_Error('no_videos', 'Nenhum vídeo encontrado na programação', array('status' => 404));
        }

        // 2.5. Intro entre programas (abertura automática a cada transição)
        $intro_id = absint($settings['intro_attachment_id'] ?? 0);
        if ($intro_id) {
            $intro_url = wp_get_attachment_url($intro_id);
            if ($intro_url) {
                $intro_meta  = wp_get_attachment_metadata($intro_id);
                $intro_title = !empty($settings['intro_title']) ? $settings['intro_title'] : 'ABERTURA ZIMNY TV';
                $intro_item  = array(
                    'video_id'      => (string) $intro_id,
                    'title'         => $intro_title,
                    'category'      => $default_category,
                    'duration'      => !empty($intro_meta['length']) ? (int) $intro_meta['length'] : 7,
                    'video_url'     => $intro_url,
                    'thumbnail_url' => wp_get_attachment_image_url($intro_id, 'medium') ?: '',
                    'is_intro'      => true,
                );
                $interleaved = array();
                foreach ($items as $it) {
                    $interleaved[] = $it;
                    $interleaved[] = $intro_item;
                }
                $items = $interleaved;
            }
        }

        // 3. Lógica de programação linear 24/7 com durações reais
        $total_videos = count($items);
        $current_time = time();
        $total_cycle  = array_sum(array_column($items, 'duration'));

        if ($total_cycle <= 0) {
            $total_cycle = $total_videos * 1800;
        }

        $cycle_pos = $current_time % $total_cycle;
        $accumulated = 0;
        $current_index = 0;

        foreach ($items as $i => $item) {
            $accumulated += $item['duration'];
            if ($cycle_pos < $accumulated) {
                $current_index = $i;
                break;
            }
        }

        // Calcula seek_to_seconds corretamente
        $seek_to = $cycle_pos;
        for ($i = 0; $i < $current_index; $i++) {
            $seek_to -= $items[$i]['duration'];
        }
        if ($seek_to < 0) {
            $seek_to = 0;
        }

        $current = $items[$current_index];

        // 4. Constrói guia de programação com timestamps epoch (UTC)
        $schedule = array();
        $time_offset = -$seek_to;
        for ($i = 0; $i < min(5, $total_videos); $i++) {
            $idx = ($current_index + $i) % $total_videos;
            $video_time = $current_time + $time_offset;
            $schedule[] = array(
                'video_id' => $items[$idx]['video_id'],
                'title'    => $items[$idx]['title'],
                'time'     => $video_time, // epoch timestamp (UTC) — app formata no fuso local
                'is_now'   => ($i === 0),
                'is_next'  => ($i === 1),
            );
            $time_offset += $items[$idx]['duration'];
        }

        // 5.5. Fila de próximos vídeos (programas + vinhetas) para o app encadear
        //      sem refetch a cada transição (sempre pré-carrega o próximo).
        $upcoming = array();
        $upcoming_limit = 16;
        for ($i = 1; $i <= $upcoming_limit; $i++) {
            $idx = ($current_index + $i) % $total_videos;
            $it  = $items[$idx];
            $upcoming[] = array(
                'video_id'      => $it['video_id'],
                'title'         => $it['title'],
                'category'      => $it['category'],
                'video_url'     => $it['video_url'] ?? '',
                'thumbnail_url' => $it['thumbnail_url'] ?? '',
                'duration'      => (int) $it['duration'],
                'is_intro'      => !empty($it['is_intro']),
            );
        }

        // 5. Próximo vídeo (para o app pré-carregar e fazer fade sem corte)
        $next_index = ($current_index + 1) % $total_videos;
        $next = $items[$next_index];

        // 6. Prepara resposta
        $result = array(
            'now_playing' => array(
                'video_id'         => $current['video_id'],
                'title'            => $current['title'],
                'category'         => $current['category'],
                'video_url'        => $current['video_url'] ?? '',
                'thumbnail_url'    => $current['thumbnail_url'] ?? '',
                'duration'         => (int) $current['duration'],
                'seek_to_seconds'  => (int) $seek_to,
                'server_timestamp' => $current_time,
            ),
            'next_playing' => array(
                'video_id'         => $next['video_id'],
                'title'            => $next['title'],
                'category'         => $next['category'],
                'video_url'        => $next['video_url'] ?? '',
                'thumbnail_url'    => $next['thumbnail_url'] ?? '',
                'duration'         => (int) $next['duration'],
                'seek_to_seconds'  => 0, // o próximo começa do zero quando o atual termina
                'server_timestamp' => $current_time,
            ),
            'upcoming' => $upcoming,
            'schedule' => $schedule,
        );

        set_transient(self::CACHE_KEY, $result, self::CACHE_TTL);
        return rest_ensure_response($result);
    }

    /**
     * Retorna a chave do YouTube sem expô-la ao aplicativo ou à interface.
     * Prioriza wp-config/ambiente e mantém compatibilidade com a opção já usada
     * pelo plugin Zimny TV 24/7 existente.
     */
    private function get_youtube_api_key(array $settings): string {
        if (defined('ZIMNY_YOUTUBE_API_KEY')) {
            return sanitize_text_field((string) ZIMNY_YOUTUBE_API_KEY);
        }

        $environment_key = getenv('ZIMNY_YOUTUBE_API_KEY');
        if (is_string($environment_key) && $environment_key !== '') {
            return sanitize_text_field($environment_key);
        }

        return sanitize_text_field((string) ($settings['api_key'] ?? ''));
    }

    /**
     * GET /wp-json/zimny/v1/podcast
     *
     * Proxy server-side para a playlist do YouTube. A chave nunca é enviada ao
     * app e a resposta fica em cache por cinco minutos.
     */
    public function get_podcast($request) {
        $settings    = get_option(self::OPTION_KEY, array());
        $api_key     = $this->get_youtube_api_key($settings);
        $playlist_id = sanitize_text_field((string) ($settings['playlist_id'] ?? 'PLVfeeCBKHOWQ2d8T5FnQ7-yohOHrkB6GL'));
        $limit       = min(50, max(1, absint($request->get_param('limit') ?: 50)));

        if ($api_key === '' || $playlist_id === '') {
            return new WP_Error(
                'zimny_youtube_not_configured',
                'YouTube não configurado no servidor.',
                array('status' => 503)
            );
        }

        $cache_key = 'zimny_podcast_' . md5($playlist_id . '|' . $limit);
        $cached    = get_transient($cache_key);
        if (is_array($cached)) {
            $response = rest_ensure_response($cached);
            $response->header('Cache-Control', 'public, max-age=300');
            return $response;
        }

        $videos     = array();
        $page_token = '';

        do {
            $query = array(
                'part'       => 'snippet',
                'playlistId' => $playlist_id,
                'maxResults' => 50,
                'key'        => $api_key,
            );
            if ($page_token !== '') {
                $query['pageToken'] = $page_token;
            }

            $url = add_query_arg($query, 'https://www.googleapis.com/youtube/v3/playlistItems');
            $youtube_response = wp_safe_remote_get($url, array('timeout' => 10));

            if (is_wp_error($youtube_response)) {
                return new WP_Error(
                    'zimny_youtube_unavailable',
                    'Não foi possível consultar o YouTube no momento.',
                    array('status' => 502)
                );
            }

            $status = wp_remote_retrieve_response_code($youtube_response);
            $body   = json_decode(wp_remote_retrieve_body($youtube_response), true);
            if ($status !== 200 || !is_array($body)) {
                return new WP_Error(
                    'zimny_youtube_error',
                    'O YouTube recusou a consulta do servidor.',
                    array('status' => 502)
                );
            }

            foreach (($body['items'] ?? array()) as $item) {
                $snippet    = is_array($item['snippet'] ?? null) ? $item['snippet'] : array();
                $resource   = is_array($snippet['resourceId'] ?? null) ? $snippet['resourceId'] : array();
                $video_id   = sanitize_text_field((string) ($resource['videoId'] ?? ''));
                $thumbnails = is_array($snippet['thumbnails'] ?? null) ? $snippet['thumbnails'] : array();
                $thumbnail  = '';

                foreach (array('maxres', 'high', 'medium', 'default') as $size) {
                    if (!empty($thumbnails[$size]['url'])) {
                        $thumbnail = esc_url_raw($thumbnails[$size]['url']);
                        break;
                    }
                }

                if ($video_id === '') {
                    continue;
                }

                $videos[] = array(
                    'id'           => sanitize_text_field((string) ($item['id'] ?? $video_id)),
                    'videoId'      => $video_id,
                    'title'        => sanitize_text_field((string) ($snippet['title'] ?? '')),
                    'description'  => sanitize_textarea_field((string) ($snippet['description'] ?? '')),
                    'thumbnail'    => $thumbnail,
                    'publishedAt'  => sanitize_text_field((string) ($snippet['publishedAt'] ?? '')),
                    'channelTitle' => sanitize_text_field((string) ($snippet['channelTitle'] ?? '')),
                    'video_url'    => 'https://www.youtube.com/watch?v=' . rawurlencode($video_id),
                );

                if (count($videos) >= $limit) {
                    break 2;
                }
            }

            $page_token = sanitize_text_field((string) ($body['nextPageToken'] ?? ''));
        } while ($page_token !== '' && count($videos) < $limit);

        usort($videos, function ($a, $b) {
            return strtotime($b['publishedAt'] ?? '') <=> strtotime($a['publishedAt'] ?? '');
        });

        set_transient($cache_key, $videos, 5 * MINUTE_IN_SECONDS);
        $response = rest_ensure_response($videos);
        $response->header('Cache-Control', 'public, max-age=300');
        return $response;
    }

    // ─── Admin: Página Principal (Dashboard) ─────────────────────────────────

    public function render_dashboard_page(): void {
        $settings = get_option(self::OPTION_KEY, array());
        $endpoint_url = rest_url('zimny/v1/live-tv');
        $response = wp_remote_get($endpoint_url);
        $is_online = !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;

        // Processa limpeza de cache
        if (isset($_POST['action']) && $_POST['action'] === 'clear_cache') {
            if (wp_verify_nonce($_POST['zimny_tv_nonce'] ?? '', 'zimny_tv_clear_cache')) {
                delete_transient(self::CACHE_KEY);
                echo '<div class="notice notice-success"><p>Cache limpo com sucesso!</p></div>';
            }
        }

        ?>
        <div class="wrap zimny-tv-wrap">
            <h1>Zimny TV 24/7</h1>
            <div class="zimny-tv-status <?php echo $is_online ? 'online' : 'offline'; ?>">
                <span class="dot"></span>
                <span><?php echo $is_online ? 'Transmissão ativa' : 'Transmissão indisponível'; ?></span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
                <div class="postbox">
                    <div class="postbox-header"><h2 class="hndle">Endpoint REST</h2></div>
                    <div class="inside">
                        <p><code>GET <?php echo esc_url($endpoint_url); ?></code></p>
                        <p class="description">Use este endpoint no aplicativo para buscar os dados da transmissão ao vivo.</p>
                    </div>
                </div>
                <div class="postbox">
                    <div class="postbox-header"><h2 class="hndle">Cache</h2></div>
                    <div class="inside">
                        <p>Cache: <strong><?php echo get_transient(self::CACHE_KEY) ? 'Ativo' : 'Vazio'; ?></strong> (expira a cada 30s)</p>
                        <form method="post" action="">
                            <?php wp_nonce_field('zimny_tv_clear_cache', 'zimny_tv_nonce'); ?>
                            <input type="hidden" name="action" value="clear_cache" />
                            <?php submit_button('Limpar Cache', 'secondary', 'submit', false); ?>
                        </form>
                    </div>
                </div>
            </div>
            <div class="postbox">
                <div class="postbox-header"><h2 class="hndle">Resumo da Programação</h2></div>
                <div class="inside">
                    <?php
                    $videos = get_option(self::VIDEOS_KEY, array());
                    $wp_count = 0;
                    $yt_count = 0;
                    foreach ($videos as $v) {
                        if (!empty($v['attachment_id'])) $wp_count++;
                        elseif (!empty($v['video_id'])) $yt_count++;
                    }
                    echo '<p>Vídeos na programação: <strong>' . count($videos) . '</strong></p>';
                    echo '<p>WordPress Media Library: <strong>' . $wp_count . '</strong></p>';
                    echo '<p>YouTube (legado): <strong>' . $yt_count . '</strong></p>';

                    if (!empty($videos)) {
                        $total = array_sum(array_column($videos, 'duration'));
                        echo '<p>Ciclo total: <strong>' . self::format_duration($total) . '</strong></p>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }

    // ─── Admin: Vídeos da Programação (WordPress Media Library) ─────────────

    public function render_videos_page(): void {
        // Garante que o frame de Media Library (wp.media) está carregado
        wp_enqueue_media();

        $settings = get_option(self::OPTION_KEY, array());
        $videos = get_option(self::VIDEOS_KEY, array());
        $default_category = $settings['default_category'] ?? 'ZIMNY TV · TRANSMISSÃO 24/7';

        // Processa formulários
        if (isset($_POST['action']) && wp_verify_nonce($_POST['_wpnonce'] ?? '', 'zimny_tv_save_videos')) {
            if ($_POST['action'] === 'add_videos') {
                // Suporta múltiplos vídeos via attachment_ids separados por vírgula
                $ids_input = sanitize_text_field($_POST['attachment_ids'] ?? '');
                $ids = array_filter(array_map('absint', explode(',', $ids_input)));
                $added = 0;
                foreach ($ids as $attach_id) {
                    if ($attach_id <= 0) continue;
                    // Evita duplicatas
                    $dup = false;
                    foreach ($videos as $existing) {
                        if (!empty($existing['attachment_id']) && (int)$existing['attachment_id'] === $attach_id) {
                            $dup = true;
                            break;
                        }
                    }
                    if ($dup) continue;

                    $post = get_post($attach_id);
                    $title = $post ? $post->post_title : 'Vídeo #' . $attach_id;
                    $metadata = wp_get_attachment_metadata($attach_id);
                    $duration = !empty($metadata['length']) ? (int) $metadata['length'] : 1800;

                    $videos[] = array(
                        'attachment_id' => $attach_id,
                        'title'         => $title,
                        'category'      => $default_category,
                        'duration'      => $duration,
                    );
                    $added++;
                }
                if ($added > 0) {
                    update_option(self::VIDEOS_KEY, $videos);
                    delete_transient(self::CACHE_KEY);
                    echo '<div class="notice notice-success"><p>' . $added . ' vídeo(s) adicionado(s)!</p></div>';
                } else {
                    echo '<div class="notice notice-info"><p>Nenhum vídeo novo adicionado (já existem ou IDs inválidos).</p></div>';
                }
            } elseif ($_POST['action'] === 'remove_video' && isset($_POST['index'])) {
                $index = absint($_POST['index']);
                if (isset($videos[$index])) {
                    array_splice($videos, $index, 1);
                    update_option(self::VIDEOS_KEY, $videos);
                    delete_transient(self::CACHE_KEY);
                    echo '<div class="notice notice-success"><p>Vídeo removido!</p></div>';
                }
            } elseif ($_POST['action'] === 'reorder_videos' && isset($_POST['order']) && $_POST['order'] !== '') {
                $order = array_map('absint', explode(',', $_POST['order']));
                $reordered = array();
                foreach ($order as $pos) {
                    if (isset($videos[$pos])) $reordered[] = $videos[$pos];
                }
                if (!empty($reordered)) {
                    update_option(self::VIDEOS_KEY, $reordered);
                    delete_transient(self::CACHE_KEY);
                    echo '<div class="notice notice-success"><p>Ordem atualizada!</p></div>';
                    $videos = $reordered;
                }
            }
        }

        ?>
        <div class="wrap zimny-tv-wrap">
            <h1>Vídeos da Programação</h1>
            <p class="description">Gerencie os vídeos que compõem a grade da Zimny TV 24/7. Selecione vídeos da <strong>Biblioteca de Mídia</strong> do WordPress.</p>

            <button type="button" class="button button-primary" onclick="openMediaLibrary()">+ Adicionar Vídeo(s) da Biblioteca</button>

            <!-- Formulário oculto para adicionar vídeos (preenchido via JS) -->
            <form method="post" action="" id="add-video-form" style="display:none;">
                <?php wp_nonce_field('zimny_tv_save_videos'); ?>
                <input type="hidden" name="action" value="add_videos" />
                <input type="hidden" name="attachment_ids" id="media-attachment-ids" value="" />
            </form>

            <!-- Lista de vídeos -->
            <div id="zimny-tv-video-list">
                <?php if (empty($videos)): ?>
                    <div class="zimny-tv-empty">
                        <p>Nenhum vídeo na programação.</p>
                        <p>Clique em <strong>"+ Adicionar Vídeo(s) da Biblioteca"</strong> para começar.</p>
                    </div>
                <?php else: ?>
                    <form method="post" action="" id="reorder-form">
                        <?php wp_nonce_field('zimny_tv_save_videos'); ?>
                        <input type="hidden" name="action" value="reorder_videos" />
                        <input type="hidden" name="order" id="video-order" value="" />
                    </form>
                    <?php foreach ($videos as $index => $video): ?>
                        <?php
                        $is_wp = !empty($video['attachment_id']);
                        $thumb_url = '';
                        $source_label = '';
                        if ($is_wp) {
                            $thumb_url = wp_get_attachment_image_url($video['attachment_id'], 'medium');
                            $source_label = 'WP Media';
                        } elseif (!empty($video['video_id'])) {
                            $thumb_url = 'https://img.youtube.com/vi/' . esc_attr($video['video_id']) . '/mqdefault.jpg';
                            $source_label = 'YouTube';
                        }
                        ?>
                        <div class="zimny-tv-video-row" data-index="<?php echo $index; ?>">
                            <span class="handle dashicons dashicons-menu"></span>
                            <div class="thumb">
                                <?php if ($thumb_url): ?>
                                <img src="<?php echo esc_url($thumb_url); ?>" alt="" onerror="this.parentElement.innerHTML='<div style=\"width:80px;height:45px;background:#eee;display:flex;align-items:center;justify-content:center;font-size:10px;color:#999\">Sem thumb</div>'" />
                                <?php else: ?>
                                <div style="width:80px;height:45px;background:#eee;display:flex;align-items:center;justify-content:center;font-size:10px;color:#999">Sem thumb</div>
                                <?php endif; ?>
                            </div>
                            <div class="info">
                                <div class="title"><?php echo esc_html($video['title']); ?></div>
                                <div class="meta">
                                    <?php if ($is_wp): ?>
                                    ID: <code><?php echo esc_html($video['attachment_id']); ?></code>
                                    <?php else: ?>
                                    YouTube: <code><?php echo esc_html($video['video_id']); ?></code>
                                    <?php endif; ?>
                                    | GC: <?php echo esc_html($video['category'] ?? '—'); ?>
                                    | ⏱ <?php echo self::format_duration($video['duration'] ?? 1800); ?>
                                    <span style="display:inline-block;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:600;background:#cce5ff;color:#004085;"><?php echo $source_label; ?></span>
                                </div>
                            </div>
                            <div class="actions">
                                <form method="post" action="" style="display:inline;">
                                    <?php wp_nonce_field('zimny_tv_save_videos'); ?>
                                    <input type="hidden" name="action" value="remove_video" />
                                    <input type="hidden" name="index" value="<?php echo $index; ?>" />
                                    <button type="submit" class="button button-small button-link-delete" onclick="return confirm('Remover este vídeo da programação?')">Remover</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (!empty($videos)): ?>
                <p><button type="button" class="button button-primary" onclick="saveOrder()">Salvar Ordem</button></p>
            <?php endif; ?>
        </div>

        <script>
        function openMediaLibrary() {
            if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
                alert('Erro: Biblioteca de mídia não disponível. Tente recarregar a página.');
                return;
            }
            var frame = wp.media({
                title: 'Selecionar Vídeos para Zimny TV',
                library: { type: 'video' },
                multiple: true,
                button: { text: 'Adicionar à Programação' }
            });
            frame.on('select', function() {
                var selections = frame.state().get('selection').toJSON();
                var ids = selections.map(function(a) { return a.id; }).join(',');
                document.getElementById('media-attachment-ids').value = ids;
                document.getElementById('add-video-form').submit();
            });
            frame.open();
        }
        function saveOrder() {
            const rows = document.querySelectorAll('.zimny-tv-video-row');
            document.getElementById('video-order').value = Array.from(rows).map(r => r.dataset.index).join(',');
            document.getElementById('reorder-form').submit();
        }
        let dragSrc = null;
        document.addEventListener('dragstart', e => { if (e.target.closest('.zimny-tv-video-row')) { dragSrc = e.target.closest('.zimny-tv-video-row'); e.dataTransfer.effectAllowed = 'move'; } });
        document.addEventListener('dragover', e => { if (e.target.closest('.zimny-tv-video-row')) e.preventDefault(); });
        document.addEventListener('drop', e => {
            const t = e.target.closest('.zimny-tv-video-row');
            if (dragSrc && t && dragSrc !== t) {
                const list = document.getElementById('zimny-tv-video-list');
                const children = Array.from(list.children);
                if (children.indexOf(dragSrc) < children.indexOf(t)) t.after(dragSrc); else t.before(dragSrc);
                document.querySelectorAll('.zimny-tv-video-row').forEach((r, i) => r.dataset.index = i);
            }
        });
        </script>
        <?php
    }

    // ─── Admin: Configurações (simplificada) ─────────────────────────────────

    public function render_settings_page(): void {
        $settings = get_option(self::OPTION_KEY, array());

        $intro_name = '';
        if (!empty($settings['intro_attachment_id'])) {
            $intro_name = get_the_title((int) $settings['intro_attachment_id']);
            if (empty($intro_name)) {
                $intro_name = 'Vídeo #' . (int) $settings['intro_attachment_id'];
            }
        }

        if (isset($_POST['action']) && $_POST['action'] === 'save_settings') {
            if (wp_verify_nonce($_POST['_wpnonce'] ?? '', 'zimny_tv_save_settings')) {
                $settings['default_category']    = sanitize_text_field($_POST['default_category'] ?? 'ZIMNY TV · TRANSMISSÃO 24/7');
                $settings['intro_attachment_id'] = absint($_POST['intro_attachment_id'] ?? 0);
                $settings['intro_title']         = sanitize_text_field($_POST['intro_title'] ?? 'ABERTURA ZIMNY TV');
                update_option(self::OPTION_KEY, $settings);
                delete_transient(self::CACHE_KEY);
                echo '<div class="notice notice-success"><p>Configurações salvas!</p></div>';
            }
        }

        ?>
        <div class="wrap zimny-tv-wrap">
            <h1>Configurações da Zimny TV</h1>
            <form method="post" action="">
                <?php wp_nonce_field('zimny_tv_save_settings'); ?>
                <input type="hidden" name="action" value="save_settings" />

                <div class="postbox">
                    <div class="postbox-header"><h2 class="hndle">Programação</h2></div>
                    <div class="inside">
                        <div class="zimny-tv-field">
                            <label for="default_category">Categoria padrão (Lower Third GC)</label>
                            <input type="text" name="default_category" id="default_category" value="<?php echo esc_attr($settings['default_category'] ?? 'ZIMNY TV · TRANSMISSÃO 24/7'); ?>" />
                            <p class="description">Texto exibido no Lower Third GC sobre o player de vídeo. Pode ser personalizado por vídeo na página "Vídeos da Programação".</p>
                        </div>

                        <div class="zimny-tv-field">
                            <label for="intro_attachment_id">Vídeo de abertura (Intro entre programas)</label>
                            <div style="display:flex;gap:8px;align-items:center;">
                                <input type="hidden" name="intro_attachment_id" id="intro_attachment_id" value="<?php echo esc_attr($settings['intro_attachment_id'] ?? ''); ?>" />
                                <input type="text" id="intro_attachment_name" value="<?php echo esc_attr($intro_name); ?>" readonly style="width:260px;background:#fff;" />
                                <button type="button" class="button" onclick="openIntroMediaLibrary()">Selecionar da Biblioteca</button>
                                <button type="button" class="button" onclick="clearIntro()">Remover</button>
                            </div>
                            <p class="description">Abertura exibida automaticamente entre um programa e outro na transmissão 24/7. O vídeo precisa estar na Media Library (envie o intro.mp4).</p>
                        </div>

                        <div class="zimny-tv-field">
                            <label for="intro_title">Título da abertura</label>
                            <input type="text" name="intro_title" id="intro_title" value="<?php echo esc_attr($settings['intro_title'] ?? 'ABERTURA ZIMNY TV'); ?>" />
                            <p class="description">Título exibido no app durante a abertura (barra de informações).</p>
                        </div>
                    </div>
                </div>

                <script>
                function openIntroMediaLibrary() {
                    if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
                        alert('Erro: Biblioteca de mídia não disponível. Tente recarregar a página.');
                        return;
                    }
                    var frame = wp.media({
                        title: 'Selecionar Vídeo de Abertura (Intro)',
                        library: { type: 'video' },
                        multiple: false,
                        button: { text: 'Usar como Abertura' }
                    });
                    frame.on('select', function() {
                        var att = frame.state().get('selection').first().toJSON();
                        document.getElementById('intro_attachment_id').value = att.id;
                        document.getElementById('intro_attachment_name').value = att.filename || att.title || ('Vídeo #' + att.id);
                    });
                    frame.open();
                }
                function clearIntro() {
                    document.getElementById('intro_attachment_id').value = '';
                    document.getElementById('intro_attachment_name').value = '';
                }
                </script>

                <?php submit_button('Salvar Configurações'); ?>
            </form>
        </div>
        <?php
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Formata duração em segundos para string legível (ex: "1h30m15s").
     */
    public static function format_duration(int $seconds): string {
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        if ($h > 0) {
            return "{$h}h{$m}m{$s}s";
        }
        if ($m > 0) {
            return "{$m}m{$s}s";
        }
        return "{$s}s";
    }
}
