<?php
/**
 * Zimny_Admin_Push — Notificações push do app Zimny (Expo Notifications)
 *
 * Fluxo editorial:
 *  - Meta box no editor de posts: editor escolhe NÃO ENVIAR / ENVIAR AUTOMÁTICO /
 *    ENVIAR COM TEXTO PRÓPRIO, escreve título (65) + descrição (110) com limite
 *    rígido e vê preview estilo lockscreen. Simples para editores leigos.
 *  - Ao publicar (inclui posts agendados publicados via cron), dispara push
 *    para todos os devices registrados via API do Expo (FCM/APNs).
 *  - Trilíngue automático: quem usa o app em EN/ES recebe o aviso traduzido
 *    (Google Translate — class-translator.php), sem trabalho extra do editor.
 *  - Anti-duplicação: meta `_zimny_push_sent` impede reenvio em update/republicação.
 *  - Página admin: kill switch global, stats, log dos últimos envios, envio de teste.
 *
 * Endpoints REST (namespace zimny/v1):
 *  - POST /push/register  {token, platform, app_version, language}  → registra/atualiza device
 *  - POST /push/disable   {token}                          → opt-out do device
 *  - GET  /push/config                                     → {enabled} global
 *
 * Trilíngue (pt/en/es): o app envia a língua da UI no register; no envio,
 * cada device recebe o título/corpo no idioma dele — o texto pt é traduzido
 * automaticamente para en/es (class-translator.php) com fallback pt.
 *
 * @package Zimny_Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Zimny_Admin_Push {

    /** API de envio do Expo (grátis, roteia para FCM/APNs). */
    const EXPO_API_URL = 'https://exp.host/--/api/v2/push/send';

    /** Limites que Android/iOS exibem sem cortar. */
    const TITLE_LIMIT = 65;
    const BODY_LIMIT  = 110;

    /** Versão do schema da tabela de devices (para migração self-healing). */
    const DB_VERSION = '2';

    /** Idiomas suportados nas notificações ('pt' é o fallback padrão). */
    const LANGS = array('pt', 'en', 'es');
    const DEFAULT_LANG = 'pt';

    /** Meta keys. */
    const META_MODE   = '_zimny_push_mode';   // off | curated | auto
    const META_TITLE  = '_zimny_push_title';  // pt (padrão — o editor escreve 1x)
    const META_BODY   = '_zimny_push_body';
    const META_SENT   = '_zimny_push_sent';   // timestamp do último envio

    /**
     * Referência ao coordenador principal.
     *
     * @var Zimny_Admin
     */
    private $admin;

    public function __construct($admin) {
        $this->admin = $admin;

        // Migração da tabela de devices (self-healing em deploy).
        add_action('admin_init', array($this, 'maybe_migrate_db'));

        // Meta box + save (apenas posts — notícias e colunas dos editores).
        add_action('add_meta_boxes', array($this, 'register_meta_box'));
        add_action('save_post_post', array($this, 'save_push_meta'), 10, 2);

        // Disparo no publish (cobre agendados publicados via cron).
        add_action('transition_post_status', array($this, 'on_publish'), 10, 3);

        // REST (namespace zimny/v1).
        add_action('rest_api_init', array($this, 'register_rest_routes'));

        // Página admin sob o menu do Zimny Admin.
        add_action('admin_menu', array($this, 'register_admin_page'));

        // Assets do meta box (JS/CSS inline só no editor).
        add_action('admin_enqueue_scripts', array($this, 'enqueue_editor_assets'));
    }

    // ═════════════════════════════════════════════════════════════════
    //  BANCO DE DADOS — devices registrados
    // ═════════════════════════════════════════════════════════════════

    public function maybe_migrate_db() {
        if (get_option('zimny_push_db_version') === self::DB_VERSION) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'zimny_push_devices';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            expo_token VARCHAR(255) NOT NULL,
            platform VARCHAR(10) NOT NULL DEFAULT 'unknown',
            app_version VARCHAR(20) NOT NULL DEFAULT '',
            language VARCHAR(10) NOT NULL DEFAULT 'pt',
            active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            last_seen DATETIME NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY expo_token (expo_token),
            KEY active_idx (active),
            KEY language_idx (language)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        update_option('zimny_push_db_version', self::DB_VERSION);
    }

    private function devices_table() {
        global $wpdb;
        return $wpdb->prefix . 'zimny_push_devices';
    }

    // ═════════════════════════════════════════════════════════════════
    //  META BOX (editor de posts)
    // ═════════════════════════════════════════════════════════════════

    public function register_meta_box() {
        add_meta_box(
            'zimny_push_notification',
            '🔔 Notificação Push (App Zimny)',
            array($this, 'render_meta_box'),
            'post',
            'side',
            'high'
        );
    }

    public function render_meta_box($post) {
        wp_nonce_field('zimny_push_save', 'zimny_push_nonce');

        $mode  = get_post_meta($post->ID, self::META_MODE, true);
        if ($mode === '') {
            $mode = 'off';
        }
        $title = get_post_meta($post->ID, self::META_TITLE, true);
        $body  = get_post_meta($post->ID, self::META_BODY, true);
        $sent  = get_post_meta($post->ID, self::META_SENT, true);
        ?>
        <div class="zimny-push-box" data-title-limit="<?php echo esc_attr(self::TITLE_LIMIT); ?>" data-body-limit="<?php echo esc_attr(self::BODY_LIMIT); ?>">

            <?php if ($sent): ?>
                <p class="zimny-push-sent-info" style="margin:0 0 10px; padding:8px 10px; background:#fff8e5; border-left:4px solid #f5a623; font-size:12px;">
                    ✅ Push enviado em <strong><?php echo esc_html(mysql2date('d/m/Y H:i', date('Y-m-d H:i:s', (int) $sent))); ?></strong>.
                    Publicar de novo <em>não</em> reenvia (anti-spam).
                </p>
            <?php endif; ?>

            <p style="margin:0 0 8px;"><strong>Quando este post for publicado, o que fazer?</strong></p>

            <p style="margin:0 0 6px;">
                <label>
                    <input type="radio" name="zimny_push_mode" value="off" <?php checked($mode, 'off'); ?>>
                    <strong>Não enviar aviso</strong>
                </label>
            </p>
            <p style="margin:0 0 6px;">
                <label>
                    <input type="radio" name="zimny_push_mode" value="auto" <?php checked($mode, 'auto'); ?>>
                    <strong>Enviar automático</strong> — usa o título e o resumo do post
                </label>
            </p>
            <p style="margin:0 0 12px;">
                <label>
                    <input type="radio" name="zimny_push_mode" value="curated" <?php checked($mode, 'curated'); ?>>
                    <strong>Enviar com texto próprio</strong> — você escreve abaixo
                </label>
            </p>

            <div class="zimny-push-fields" style="<?php echo $mode === 'curated' ? '' : 'display:none;'; ?>">
                <p style="margin:0 0 4px;">
                    <label for="zimny_push_title"><strong>Título do aviso</strong> <span style="color:#646970; font-weight:400;">(máximo <?php echo (int) self::TITLE_LIMIT; ?> letras)</span></label>
                    <input type="text" id="zimny_push_title" name="zimny_push_title"
                        value="<?php echo esc_attr($title); ?>"
                        maxlength="<?php echo esc_attr(self::TITLE_LIMIT); ?>"
                        data-count="<?php echo esc_attr(self::TITLE_LIMIT); ?>"
                        style="width:100%;" placeholder="ex.: Zimny lança nova edição digital">
                </p>
                <p class="zimny-push-counter" style="margin:0 0 10px; font-size:11px; color:#646970; text-align:right;">0/<?php echo (int) self::TITLE_LIMIT; ?></p>

                <p style="margin:0 0 4px;">
                    <label for="zimny_push_body"><strong>Descrição</strong> <span style="color:#646970; font-weight:400;">(máximo <?php echo (int) self::BODY_LIMIT; ?> letras)</span></label>
                    <textarea id="zimny_push_body" name="zimny_push_body" rows="3"
                        maxlength="<?php echo esc_attr(self::BODY_LIMIT); ?>"
                        data-count="<?php echo esc_attr(self::BODY_LIMIT); ?>"
                        style="width:100%;" placeholder="Resumo que aparece na tela do celular"><?php echo esc_textarea($body); ?></textarea>
                </p>
                <p class="zimny-push-counter" style="margin:0 0 6px; font-size:11px; color:#646970; text-align:right;">0/<?php echo (int) self::BODY_LIMIT; ?></p>
                <p style="margin:0 0 4px; font-size:11px; color:#646970;">Deixe em branco para usar o título e o resumo do post.</p>
            </div>

            <p style="margin:4px 0 8px; font-size:11px; color:#646970; border-top:1px solid #eee; padding-top:8px;">
                🌎 Quem usa o app em inglês ou espanhol recebe o aviso traduzido automaticamente. Você não precisa escrever em outro idioma.
            </p>
            

            <details class="zimny-push-preview-wrap" style="margin-bottom:8px;">
                <summary style="cursor:pointer; font-weight:600;">👁 Preview no celular</summary>
                <div class="zimny-push-preview" style="margin-top:10px;">
                    <div style="background:#000; border-radius:14px; padding:12px 14px; margin-bottom:8px; border:1px solid #333;">
                        <div style="font-size:9px; color:#888; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">📱 Android</div>
                        <div style="font-size:11px; color:#aaa; display:flex; align-items:center; gap:4px; margin-bottom:4px;">
                            <span style="display:inline-block; width:10px; height:10px; background:#fff; border-radius:2px;"></span> ZIMNY · agora
                        </div>
                        <div class="pv-title" style="font-size:13px; font-weight:700; color:#fff; line-height:1.3;">
                            <?php echo esc_html($title ? $title : 'Título da notificação'); ?>
                        </div>
                        <div class="pv-body" style="font-size:12px; color:#ccc; line-height:1.35;">
                            <?php echo esc_html($body ? $body : 'Descrição curta da notificação.'); ?>
                        </div>
                    </div>
                    <div style="background:linear-gradient(135deg,#1c1c1e,#2c2c2e); border-radius:14px; padding:12px 14px; border:1px solid #3a3a3c;">
                        <div style="font-size:9px; color:#888; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">🍎 iOS</div>
                        <div style="display:flex; gap:8px; align-items:flex-start;">
                            <span style="display:inline-block; width:20px; height:20px; background:#fff; border-radius:5px; flex-shrink:0;"></span>
                            <div>
                                <div class="pv-title" style="font-size:13px; font-weight:700; color:#fff; line-height:1.3;">
                                    <?php echo esc_html($title ? $title : 'Título da notificação'); ?>
                                </div>
                                <div class="pv-body" style="font-size:12px; color:#ccc; line-height:1.35;">
                                    <?php echo esc_html($body ? $body : 'Descrição curta da notificação.'); ?>
                                </div>
                                <div style="font-size:10px; color:#888; margin-top:2px;">agora</div>
                            </div>
                        </div>
                    </div>
                </div>
            </details>

            <?php if ($sent): ?>
                <p style="margin:8px 0 0;">
                    <label>
                        <input type="checkbox" name="zimny_push_resend" value="1">
                        Forçar reenvio (ignora anti-duplicação)
                    </label>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    public function enqueue_editor_assets($hook) {
        if (!in_array($hook, array('post.php', 'post-new.php'), true)) {
            return;
        }
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'post') {
            return;
        }
        // JS inline: contadores + preview ao vivo + toggle de campos.
        wp_register_script('zimny-push-editor', false, array(), '1.0.0', true);
        wp_enqueue_script('zimny-push-editor');
        wp_add_inline_script('zimny-push-editor', "
(function () {
    var box = document.querySelector('.zimny-push-box');
    if (!box) return;
    var tLimit = parseInt(box.dataset.titleLimit, 10);
    var bLimit = parseInt(box.dataset.bodyLimit, 10);
    var title = document.getElementById('zimny_push_title');
    var body  = document.getElementById('zimny_push_body');
    var fields = box.querySelector('.zimny-push-fields');

    function syncCounters() {
        box.querySelectorAll('input[data-count], textarea[data-count]').forEach(function (el) {
            var field = el.closest('.zimny-push-field');
            var c = field ? field.querySelector('.zimny-push-counter') : null;
            if (c) c.textContent = el.value.length + '/' + el.dataset.count;
        });
    }
    function syncPreview() {
        var pvs = box.querySelectorAll('.pv-title');
        var pvsb = box.querySelectorAll('.pv-body');
        pvs.forEach(function (el) { el.textContent = title.value || 'Título da notificação'; });
        pvsb.forEach(function (el) { el.textContent = body.value || 'Descrição curta da notificação.'; });
    }
    function syncFields(modeEl) {
        if (fields) fields.style.display = modeEl.value === 'curated' ? '' : 'none';
    }

    box.querySelectorAll('input[data-count], textarea[data-count]').forEach(function (el) {
        el.addEventListener('input', function () { syncCounters(); syncPreview(); });
    });
    box.querySelectorAll('input[name=\"zimny_push_mode\"]').forEach(function (r) {
        r.addEventListener('change', function () { syncFields(r); });
    });
    syncCounters(); syncPreview();
})();
        ");
    }

    public function save_push_meta($post_id, $post) {
        // Permissões / revisões / autosave / nonce.
        if (!isset($_POST['zimny_push_nonce']) || !wp_verify_nonce(sanitize_key($_POST['zimny_push_nonce']), 'zimny_push_save')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $mode = isset($_POST['zimny_push_mode']) ? sanitize_key($_POST['zimny_push_mode']) : 'off';
        if (!in_array($mode, array('off', 'curated', 'auto'), true)) {
            $mode = 'off';
        }

        $title = isset($_POST['zimny_push_title']) ? mb_substr(wp_strip_all_tags(wp_unslash($_POST['zimny_push_title'])), 0, self::TITLE_LIMIT) : '';
        $body  = isset($_POST['zimny_push_body']) ? mb_substr(wp_strip_all_tags(wp_unslash($_POST['zimny_push_body'])), 0, self::BODY_LIMIT) : '';

        update_post_meta($post_id, self::META_MODE, $mode);
        update_post_meta($post_id, self::META_TITLE, $title);
        update_post_meta($post_id, self::META_BODY, $body);

        // Reenvio forçado (consome uma vez só).
        if (!empty($_POST['zimny_push_resend'])) {
            update_post_meta($post_id, '_zimny_push_resend', 1);
        } else {
            delete_post_meta($post_id, '_zimny_push_resend');
        }
    }

    // ═════════════════════════════════════════════════════════════════
    //  DISPARO NO PUBLISH
    // ═════════════════════════════════════════════════════════════════

    public function on_publish($new_status, $old_status, $post) {
        if ($new_status !== 'publish' || $old_status === 'publish') {
            return;
        }
        if (!$post || $post->post_type !== 'post') {
            return;
        }

        $mode = get_post_meta($post->ID, self::META_MODE, true);
        if ($mode === '') {
            $mode = 'off';
        }
        if ($mode === 'off') {
            return;
        }

        // Kill switch global.
        if (get_option('zimny_push_enabled', '1') !== '1') {
            $this->log(array('time' => time(), 'post_id' => $post->ID, 'title' => $post->post_title, 'status' => 'skipped_disabled'));
            return;
        }

        // Anti-duplicação (a menos que o editor tenha marcado reenvio forçado
        // — que é limpo no save via meta temporária).
        $force = get_post_meta($post->ID, '_zimny_push_resend', true);
        if (get_post_meta($post->ID, self::META_SENT, true) && !$force) {
            $this->log(array('time' => time(), 'post_id' => $post->ID, 'title' => $post->post_title, 'status' => 'skipped_duplicate'));
            return;
        }

        // Texto padrão (pt) conforme o modo — o editor escreve UMA vez.
        $curated = ($mode === 'curated');
        if ($curated) {
            $title = get_post_meta($post->ID, self::META_TITLE, true);
            $body  = get_post_meta($post->ID, self::META_BODY, true);
            if ($title === '') {
                $title = html_entity_decode(get_the_title($post), ENT_QUOTES, 'UTF-8');
            }
            if ($body === '') {
                $body = $this->auto_excerpt($post);
            }
        } else { // auto — título do post + resumo
            $title = html_entity_decode(get_the_title($post), ENT_QUOTES, 'UTF-8');
            $body  = $this->auto_excerpt($post);
        }

        $lang_messages = array(
            'pt' => array(
                'title' => mb_substr($title, 0, self::TITLE_LIMIT),
                'body'  => mb_substr($body, 0, self::BODY_LIMIT),
            ),
        );

        // EN/ES para os devices que usam o app em inglês/espanhol:
        //  - modo AUTO: usa as traduções do post já salvas pelo tradutor
        //    (0 custo de cota) quando existem — o aviso é o título/resumo do post.
        //  - demais casos: traduz o par na hora via Google Translate; em qualquer
        //    falha (sem chave/cota/erro) o device cai no texto em pt. O push
        //    NUNCA pode quebrar por causa de tradução.
        $en = null;
        $es = null;
        if (!$curated) {
            $title_en = (string) get_post_meta($post->ID, '_zimny_title_en', true);
            $excerpt_en = (string) get_post_meta($post->ID, '_zimny_excerpt_en', true);
            $title_es = (string) get_post_meta($post->ID, '_zimny_title_es', true);
            $excerpt_es = (string) get_post_meta($post->ID, '_zimny_excerpt_es', true);
            if ($title_en !== '' && $excerpt_en !== '') {
                $en = array(
                    'title' => mb_substr($title_en, 0, self::TITLE_LIMIT),
                    'body'  => mb_substr($excerpt_en, 0, self::BODY_LIMIT),
                );
            }
            if ($title_es !== '' && $excerpt_es !== '') {
                $es = array(
                    'title' => mb_substr($title_es, 0, self::TITLE_LIMIT),
                    'body'  => mb_substr($excerpt_es, 0, self::BODY_LIMIT),
                );
            }
        }
        if ($en === null || $es === null) {
            $translator = isset($this->admin->translator) ? $this->admin->translator : null;
            if ($translator !== null && method_exists($translator, 'translate_push_texts')) {
                $tr = $translator->translate_push_texts($lang_messages['pt']['title'], $lang_messages['pt']['body']);
                if ($en === null && !empty($tr['en'])) {
                    $en = array(
                        'title' => mb_substr($tr['en']['title'], 0, self::TITLE_LIMIT),
                        'body'  => mb_substr($tr['en']['body'], 0, self::BODY_LIMIT),
                    );
                }
                if ($es === null && !empty($tr['es'])) {
                    $es = array(
                        'title' => mb_substr($tr['es']['title'], 0, self::TITLE_LIMIT),
                        'body'  => mb_substr($tr['es']['body'], 0, self::BODY_LIMIT),
                    );
                }
            }
        }
        if ($en !== null) {
            $lang_messages['en'] = $en;
        }
        if ($es !== null) {
            $lang_messages['es'] = $es;
        }

        // Deep link do app: abre o post direto.
        $data = array(
            'type'   => 'new_post',
            'postId' => (int) $post->ID,
            'slug'   => $post->post_name,
        );

        $result = $this->send_to_all($lang_messages, $data);

        update_post_meta($post->ID, self::META_SENT, time());
        delete_post_meta($post->ID, '_zimny_push_resend');

        $this->log(array(
            'time'    => time(),
            'post_id' => $post->ID,
            'title'   => $lang_messages['pt']['title'],
            'mode'    => $mode,
            'langs'   => implode(',', array_keys($lang_messages)),
            'status'  => $result['ok'] > 0 ? 'sent' : 'failed',
            'ok'      => $result['ok'],
            'errors'  => $result['errors'],
            'devices' => $result['devices'],
        ));
    }

    private function auto_excerpt($post) {
        $excerpt = $post->post_excerpt;
        if ($excerpt === '') {
            $excerpt = wp_strip_all_tags($post->post_content);
        }
        $excerpt = html_entity_decode($excerpt, ENT_QUOTES, 'UTF-8');
        $excerpt = preg_replace('/\s+/', ' ', trim($excerpt));
        return mb_substr($excerpt, 0, self::BODY_LIMIT);
    }

    /**
     * Envia para todos os devices ativos (chunks de 100 pela API do Expo),
     * escolhendo título/corpo conforme o idioma registrado do device.
     *
     * @param array $lang_messages {pt: {title, body}, en?: {...}, es?: {...}} — en/es opcionais;
     *                              device sem o idioma (ou idioma ausente) recebe 'pt'.
     * @param array $data          payload extra (deep link etc).
     * @param string|null $single_token Envio de teste para um token (usa 'pt').
     *
     * @return array {ok: int, errors: int, devices: int, details: []}
     */
    public function send_to_all($lang_messages, $data = array(), $single_token = null) {
        global $wpdb;

        $table = $this->devices_table();
        if ($single_token) {
            $rows = array(array('expo_token' => $single_token, 'language' => self::DEFAULT_LANG));
        } else {
            $rows = $wpdb->get_results("SELECT expo_token, language FROM {$table} WHERE active = 1", ARRAY_A);
        }
        $devices = count($rows);

        // Fallback garantido: se não veio texto pt, usa o primeiro idioma disponível.
        if (empty($lang_messages[self::DEFAULT_LANG])) {
            $lang_messages[self::DEFAULT_LANG] = reset($lang_messages);
        }

        $ok = 0;
        $errors = 0;
        $details = array();

        $chunks = array_chunk($rows, 100);
        foreach ($chunks as $chunk) {
            $messages = array();
            foreach ($chunk as $row) {
                $lang = isset($row['language']) && in_array($row['language'], self::LANGS, true) ? $row['language'] : self::DEFAULT_LANG;
                $pair = isset($lang_messages[$lang]) ? $lang_messages[$lang] : $lang_messages[self::DEFAULT_LANG];
                $messages[] = array(
                    'to'        => $row['expo_token'],
                    'title'     => isset($pair['title']) ? $pair['title'] : '',
                    'body'      => isset($pair['body']) ? $pair['body'] : '',
                    'data'      => $data,
                    'sound'     => 'default',
                    'channelId' => 'zimny-news',
                    'priority'  => 'high',
                );
            }

            $response = wp_remote_post(self::EXPO_API_URL, array(
                'timeout' => 20,
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ),
                'body'    => wp_json_encode($messages),
            ));

            if (is_wp_error($response)) {
                $errors += count($chunk);
                $details[] = 'http_error: ' . $response->get_error_message();
                continue;
            }

            $code = (int) wp_remote_retrieve_response_code($response);
            $json = json_decode(wp_remote_retrieve_body($response), true);

            if ($code !== 200 || empty($json['data'])) {
                $errors += count($chunk);
                $details[] = 'api_error: HTTP ' . $code . ' ' . substr(wp_remote_retrieve_body($response), 0, 200);
                continue;
            }

            foreach ($json['data'] as $i => $ticket) {
                if (($ticket['status'] ?? '') === 'ok') {
                    $ok++;
                } else {
                    $errors++;
                    $msg = $ticket['message'] ?? 'unknown';
                    $err = $ticket['details']['error'] ?? '';
                    $details[] = $err . ': ' . $msg;
                    // Token inválido → desativa para não sujar futuros envios.
                    if ($err === 'DeviceNotRegistered' && isset($chunk[$i]['expo_token'])) {
                        $this->deactivate_token($chunk[$i]['expo_token']);
                    }
                }
            }
        }

        return array('ok' => $ok, 'errors' => $errors, 'devices' => $devices, 'details' => $details);
    }

    private function deactivate_token($token) {
        global $wpdb;
        $wpdb->update(
            $this->devices_table(),
            array('active' => 0),
            array('expo_token' => $token),
            array('%d'),
            array('%s')
        );
    }

    private function log($entry) {
        $log = get_option('zimny_push_log', array());
        array_unshift($log, $entry);
        update_option('zimny_push_log', array_slice($log, 0, 100), false);
    }

    // ═════════════════════════════════════════════════════════════════
    //  REST — registro de devices (app)
    // ═════════════════════════════════════════════════════════════════

    public function register_rest_routes() {
        register_rest_route('zimny/v1', '/push/register', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'rest_register_device'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('zimny/v1', '/push/disable', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'rest_disable_device'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('zimny/v1', '/push/config', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'rest_get_config'),
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * POST /zimny/v1/push/register  {token, platform, app_version, language}
     */
    public function rest_register_device($request) {
        $token = sanitize_text_field($request->get_param('token'));
        $platform = sanitize_key($request->get_param('platform'));
        $app_version = sanitize_text_field($request->get_param('app_version'));
        $language = sanitize_key($request->get_param('language'));
        if (!in_array($language, self::LANGS, true)) {
            $language = self::DEFAULT_LANG;
        }

        if (strpos($token, 'ExponentPushToken[') !== 0 || strlen($token) > 255) {
            return new WP_Error('invalid_token', 'Token Expo inválido', array('status' => 400));
        }
        if (!in_array($platform, array('ios', 'android'), true)) {
            $platform = 'unknown';
        }

        global $wpdb;
        $table = $this->devices_table();
        $now = current_time('mysql');

        // Upsert por token (device único, token pode rotar → atualiza).
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE expo_token = %s", $token));
        if ($exists) {
            $wpdb->update(
                $table,
                array('platform' => $platform, 'app_version' => $app_version, 'language' => $language, 'active' => 1, 'last_seen' => $now),
                array('expo_token' => $token),
                array('%s', '%s', '%s', '%d', '%s'),
                array('%s')
            );
        } else {
            $wpdb->insert(
                $table,
                array(
                    'expo_token'  => $token,
                    'platform'    => $platform,
                    'app_version' => $app_version,
                    'language'    => $language,
                    'active'      => 1,
                    'created_at'  => $now,
                    'last_seen'   => $now,
                ),
                array('%s', '%s', '%s', '%s', '%d', '%s', '%s')
            );
        }

        return rest_ensure_response(array(
            'ok'                    => true,
            'notificationsEnabled'  => get_option('zimny_push_enabled', '1') === '1',
        ));
    }

    /**
     * POST /zimny/v1/push/disable  {token}
     */
    public function rest_disable_device($request) {
        $token = sanitize_text_field($request->get_param('token'));
        if (strpos($token, 'ExponentPushToken[') !== 0) {
            return new WP_Error('invalid_token', 'Token Expo inválido', array('status' => 400));
        }
        $this->deactivate_token($token);
        return rest_ensure_response(array('ok' => true));
    }

    /**
     * GET /zimny/v1/push/config
     */
    public function rest_get_config() {
        return rest_ensure_response(array(
            'enabled'          => get_option('zimny_push_enabled', '1') === '1',
            'channelId'        => 'zimny-news',
            'titleLimit'       => self::TITLE_LIMIT,
            'bodyLimit'        => self::BODY_LIMIT,
        ));
    }

    // ═════════════════════════════════════════════════════════════════
    //  PÁGINA ADMIN
    // ═════════════════════════════════════════════════════════════════

    public function register_admin_page() {
        add_submenu_page(
            'edit.php?post_type=zimny_video',
            'Notificações Push',
            '🔔 Notificações Push',
            'manage_options',
            'zimny-push',
            array($this, 'render_admin_page')
        );
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Ações.
        if (isset($_POST['zimny_push_action'])) {
            check_admin_referer('zimny_push_admin');

            if ($_POST['zimny_push_action'] === 'toggle_enabled') {
                $new = get_option('zimny_push_enabled', '1') === '1' ? '0' : '1';
                update_option('zimny_push_enabled', $new);
                echo '<div class="notice notice-success is-dismissible"><p>' .
                    ($new === '1' ? '✅ Push ATIVADO.' : '🛑 Push DESATIVADO (kill switch global).') .
                    '</p></div>';
            }

            if ($_POST['zimny_push_action'] === 'test_send') {
                $token = sanitize_text_field($_POST['test_token'] ?? '');
                if (strpos($token, 'ExponentPushToken[') === 0) {
                    // Teste trilíngue: o token recebe o texto do idioma dele.
                    $res = $this->send_to_all(array(
                        'pt' => array('title' => '🧪 Teste ZIMNY', 'body' => 'Notificação de teste — se você viu isso, o push está funcionando!'),
                        'en' => array('title' => '🧪 ZIMNY Test', 'body' => 'Test notification — if you saw this, push is working!'),
                        'es' => array('title' => '🧪 Prueba ZIMNY', 'body' => 'Notificación de prueba — ¡si viste esto, el push funciona!'),
                    ), array('type' => 'test'), $token);
                    echo '<div class="notice notice-' . ($res['ok'] > 0 ? 'success' : 'error') . ' is-dismissible"><p>' .
                        'Envio de teste: ' . (int) $res['ok'] . ' ok, ' . (int) $res['errors'] . ' erros.' .
                        ($res['details'] ? '<br><code>' . esc_html(implode(' | ', array_slice($res['details'], 0, 3))) . '</code>' : '') .
                        '</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>Token inválido (deve começar com ExponentPushToken[).</p></div>';
                }
            }
        }

        global $wpdb;
        $table = $this->devices_table();
        $enabled = get_option('zimny_push_enabled', '1') === '1';
        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        $active = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE active = 1");
        $by_platform = $wpdb->get_results("SELECT platform, COUNT(*) AS n FROM {$table} WHERE active = 1 GROUP BY platform", ARRAY_A);
        $by_language = $wpdb->get_results("SELECT language, COUNT(*) AS n FROM {$table} WHERE active = 1 GROUP BY language", ARRAY_A);
        $log = get_option('zimny_push_log', array());
        ?>
        <div class="wrap">
            <h1>🔔 Notificações Push — App Zimny</h1>

            <div style="display:flex; gap:16px; margin:16px 0; flex-wrap:wrap;">
                <div style="background:#fff; border:1px solid #dcdcde; border-radius:8px; padding:16px 24px; min-width:140px;">
                    <div style="font-size:28px; font-weight:700;"><?php echo (int) $active; ?></div>
                    <div style="color:#646970;">devices ativos</div>
                </div>
                <div style="background:#fff; border:1px solid #dcdcde; border-radius:8px; padding:16px 24px; min-width:140px;">
                    <div style="font-size:28px; font-weight:700;"><?php echo (int) $total; ?></div>
                    <div style="color:#646970;">devices totais</div>
                </div>
                <div style="background:#fff; border:1px solid #dcdcde; border-radius:8px; padding:16px 24px; min-width:140px;">
                    <div style="font-size:28px; font-weight:700;">
                        <?php
                        $counts = array();
                        foreach ((array) $by_platform as $row) {
                            $counts[$row['platform']] = (int) $row['n'];
                        }
                        echo esc_html(($counts['android'] ?? 0) . ' A · ' . ($counts['ios'] ?? 0) . ' iOS');
                        ?>
                    </div>
                    <div style="color:#646970;">por plataforma</div>
                </div>
                <div style="background:#fff; border:1px solid #dcdcde; border-radius:8px; padding:16px 24px; min-width:140px;">
                    <div style="font-size:16px; font-weight:700;">
                        <?php
                        $langs = array('pt' => 0, 'en' => 0, 'es' => 0);
                        foreach ((array) $by_language as $row) {
                            $langs[$row['language']] = (int) $row['n'];
                        }
                        echo esc_html('PT ' . $langs['pt'] . ' · EN ' . $langs['en'] . ' · ES ' . $langs['es']);
                        ?>
                    </div>
                    <div style="color:#646970;">por idioma</div>
                </div>
            </div>

            <form method="post" style="margin:16px 0;">
                <?php wp_nonce_field('zimny_push_admin'); ?>
                <input type="hidden" name="zimny_push_action" value="toggle_enabled">
                <button type="submit" class="button button-large <?php echo $enabled ? 'button-danger' : 'button-primary'; ?>"
                    style="<?php echo $enabled ? 'background:#d63638; color:#fff; border-color:#d63638;' : ''; ?>">
                    <?php echo $enabled ? '🛑 DESATIVAR push globalmente (kill switch)' : '▶️ ATIVAR push globalmente'; ?>
                </button>
                <span style="margin-left:12px; color:#646970;">
                    Status: <strong><?php echo $enabled ? 'ATIVO' : 'DESATIVADO'; ?></strong>
                </span>
            </form>

            <h2>Envio de teste</h2>
            <form method="post" style="margin:12px 0; max-width:640px;">
                <?php wp_nonce_field('zimny_push_admin'); ?>
                <input type="hidden" name="zimny_push_action" value="test_send">
                <input type="text" name="test_token" placeholder="ExponentPushToken[xxxxxxxx...]" style="width:70%;" required>
                <button type="submit" class="button">Enviar teste</button>
                <p style="color:#646970; font-size:12px;">
                    Pegue o token no app: Configurações → Notificações → estado do registro mostra o token.
                </p>
            </form>

            <h2>Últimos envios</h2>
            <table class="widefat striped" style="max-width:860px;">
                <thead>
                    <tr>
                        <th>Data</th><th>Post</th><th>Modo</th><th>Status</th><th>OK</th><th>Erros</th><th>Devices</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($log)): ?>
                        <tr><td colspan="7">Nenhum envio ainda.</td></tr>
                    <?php else: foreach ($log as $e): ?>
                        <tr>
                            <td><?php echo esc_html(wp_date('d/m/Y H:i', (int) $e['time'])); ?></td>
                            <td>
                                <?php if (!empty($e['post_id'])): ?>
                                    <a href="<?php echo esc_url(get_edit_post_link((int) $e['post_id'])); ?>"><?php echo esc_html(mb_substr($e['title'] ?? ('#' . $e['post_id']), 0, 48)); ?></a>
                                <?php else: ?>
                                    <?php echo esc_html($e['title'] ?? '—'); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($e['mode'] ?? '—'); ?></td>
                            <td><?php echo esc_html($e['status'] ?? '—'); ?></td>
                            <td><?php echo (int) ($e['ok'] ?? 0); ?></td>
                            <td><?php echo (int) ($e['errors'] ?? 0); ?></td>
                            <td><?php echo (int) ($e['devices'] ?? 0); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
