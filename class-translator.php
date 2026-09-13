<?php
/**
 * Zimny_Admin_Translator - Tradução server-side dos posts (Google Translate API v2)
 *
 * Traduz os posts UMA VEZ no servidor e salva em post meta, de forma que
 * TODOS os dispositivos leiam as mesmas traduções — sem gastar a cota
 * por dispositivo (o que esgotaria os 500K chars/mês do free tier).
 *
 * Recursos:
 *  - WP-Cron automático (traduz N posts por execução)
 *  - Botão manual "Traduzir agora" no admin
 *  - Endpoint REST para gatilho manual
 *  - Controle de cota mensal (500K chars, free tier)
 *  - Traduz cada post em EN + ES juntos (cota distribuída uniformemente)
 *
 * @package Zimny_Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Zimny_Admin_Translator {

    const OPTION_API_KEY        = 'zimny_google_translate_key';
    const OPTION_QUOTA          = 'zimny_translate_quota';
    const OPTION_QUOTA_MONTH    = 'zimny_translate_quota_month';
    const OPTION_LAST_RUN       = 'zimny_translate_last_run';
    const CRON_HOOK             = 'zimny_translate_posts_cron';

    /** Free tier: 500.000 caracteres/mês */
    const MONTHLY_LIMIT = 500000;
    /** Para 10K antes do limite para segurança */
    const SAFETY_MARGIN = 10000;
    /** Traduz este número de posts por execução do cron */
    const BATCH_SIZE = 5;

    /** @var Zimny_Admin */
    private $admin;

    public function __construct($admin) {
        $this->admin = $admin;

        // Agenda o WP-Cron
        add_action(self::CRON_HOOK, array($this, 'cron_translate_batch'));
        add_action('admin_init', array($this, 'maybe_schedule_cron'));
        add_action('admin_init', array($this, 'handle_manual_trigger'));
    }

    // ─── Cron ──────────────────────────────────────────────────────────────

    public function maybe_schedule_cron(): void {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'hourly', self::CRON_HOOK);
        }
    }

    public function cron_translate_batch(): void {
        $this->translate_batch(self::BATCH_SIZE);
    }

    // ─── Cota mensal ───────────────────────────────────────────────────────

    private function get_quota_used(): int {
        $month = gmdate('Y-m');
        $saved_month = get_option(self::OPTION_QUOTA_MONTH, '');
        $used = absint(get_option(self::OPTION_QUOTA, 0));

        if ($saved_month !== $month) {
            update_option(self::OPTION_QUOTA_MONTH, $month);
            update_option(self::OPTION_QUOTA, 0);
            return 0;
        }
        return $used;
    }

    private function add_quota(int $chars): void {
        $used = $this->get_quota_used();
        update_option(self::OPTION_QUOTA, $used + $chars);
    }

    public function get_remaining_quota(): int {
        return max(0, self::MONTHLY_LIMIT - $this->get_quota_used());
    }

    // ─── Google Translate API v2 (free tier) ───────────────────────────────

    private function is_api_key_managed(): bool {
        if (defined('ZIMNY_GOOGLE_TRANSLATE_API_KEY')) {
            return true;
        }
        $environment_key = getenv('ZIMNY_GOOGLE_TRANSLATE_API_KEY');
        return is_string($environment_key) && trim($environment_key) !== '';
    }

    private function get_api_key(): string {
        if (defined('ZIMNY_GOOGLE_TRANSLATE_API_KEY')) {
            return sanitize_text_field((string) ZIMNY_GOOGLE_TRANSLATE_API_KEY);
        }
        $environment_key = getenv('ZIMNY_GOOGLE_TRANSLATE_API_KEY');
        if (is_string($environment_key) && trim($environment_key) !== '') {
            return sanitize_text_field($environment_key);
        }
        $key = get_option(self::OPTION_API_KEY, '');
        return is_string($key) ? trim($key) : '';
    }

    /**
     * Traduz um texto de pt → en ou es.
     * Retorna o texto original em caso de erro (fallback).
     */
    private function translate_text(string $text, string $target): string {
        if (trim($text) === '') return $text;
        $key = $this->get_api_key();
        if ($key === '') return $text; // chave não configurada

        $remaining = $this->get_remaining_quota();
        if ($remaining <= self::SAFETY_MARGIN) return $text; // cota esgotada

        $url = 'https://translation.googleapis.com/language/translate/v2?key=' . rawurlencode($key);
        $response = wp_remote_post($url, array(
            'timeout' => 30,
            'headers' => array('Content-Type' => 'application/json'),
            'body'    => wp_json_encode(array(
                'q'      => $text,
                'source' => 'pt',
                'target' => $target,
                'format' => 'text',
            )),
        ));

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return $text;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $translated = $body['data']['translations'][0]['translatedText'] ?? $text;

        // Conta a cota (baseada nos caracteres de entrada)
        $this->add_quota(strlen($text));

        return $translated;
    }

    // ─── Tradução em lote ──────────────────────────────────────────────────

    /**
     * Traduz o par título/corpo de uma NOTIFICAÇÃO PUSH (pt → en e es).
     *
     * Uma chamada por idioma (a API v2 aceita array em 'q'). Em qualquer
     * falha (sem chave, sem cota, erro HTTP) devolve o par como null para o
     * chamador cair no fallback pt — o push NUNCA pode quebrar por tradução.
     *
     * @return array{en: ?array{title:string,body:string}, es: ?array{title:string,body:string}}
     */
    public function translate_push_texts(string $title, string $body): array {
        $result = array('en' => null, 'es' => null);
        if (trim($title) === '' && trim($body) === '') {
            return $result;
        }
        $key = $this->get_api_key();
        if ($key === '') {
            return $result;
        }
        $url = 'https://translation.googleapis.com/language/translate/v2?key=' . rawurlencode($key);
        foreach (array('en', 'es') as $target) {
            if ($this->get_remaining_quota() <= self::SAFETY_MARGIN) {
                continue; // cota esgotada — fallback pt
            }
            $response = wp_remote_post($url, array(
                'timeout' => 15,
                'headers' => array('Content-Type' => 'application/json'),
                'body'    => wp_json_encode(array(
                    'q'      => array($title, $body),
                    'source' => 'pt',
                    'target' => $target,
                    'format' => 'text',
                )),
            ));
            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
                continue;
            }
            $resp = json_decode(wp_remote_retrieve_body($response), true);
            $tr = $resp['data']['translations'] ?? null;
            if (!is_array($tr) || count($tr) < 2) {
                continue;
            }
            $this->add_quota(strlen($title) + strlen($body));
            $result[$target] = array(
                'title' => isset($tr[0]['translatedText']) ? (string) $tr[0]['translatedText'] : $title,
                'body'  => isset($tr[1]['translatedText']) ? (string) $tr[1]['translatedText'] : $body,
            );
        }
        return $result;
    }

    /**
     * Traduz até $limit posts não traduzidos (em EN + ES juntos) e salva no meta.
     *
     * Ordem: do MAIS RECENTE ao MAIS ANTIGO, para que os posts novos
     * sejam traduzidos primeiro. Cota é preservada para posts futuros.
     *
     * @return array{translated:int, skipped:int, remaining:int, total_untranslated:int}
     */
    public function translate_batch(int $limit = 5): array {
        // Cota insuficiente para pelo menos 1 post? Aborta.
        if ($this->get_remaining_quota() <= self::SAFETY_MARGIN) {
            return array('translated' => 0, 'skipped' => 0, 'remaining' => 0, 'total_untranslated' => $this->count_untranslated_posts());
        }
        if ($limit < 1) $limit = 5;
        if ($limit > 50) $limit = 50;

        $args = array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => 'date',
            'order'          => 'DESC', // Mais recente primeiro
            'no_found_rows'  => true,
            'meta_query'     => array(
                'relation' => 'OR',
                array(
                    'key'     => '_zimny_title_en',
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key'     => '_zimny_title_es',
                    'compare' => 'NOT EXISTS',
                ),
            ),
        );

        $query = new WP_Query($args);
        $translated = 0;
        $skipped = 0;

        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            // Já traduzido em ambos? Skip.
            $has_en = get_post_meta($post_id, '_zimny_title_en', true) !== '';
            $has_es = get_post_meta($post_id, '_zimny_title_es', true) !== '';
            if ($has_en && $has_es) {
                $skipped++;
                continue;
            }

            $title   = get_the_title();
            $excerpt = wp_strip_all_tags(get_the_excerpt());
            $content = wp_strip_all_tags(get_the_content());

            // Estima o custo: (título+excerto+conteúdo) × 2 idiomas
            $chars_per_lang = strlen($title) + strlen($excerpt) + strlen($content);
            if ($this->get_remaining_quota() - ($chars_per_lang * 2) < 0) {
                // Não há cota suficiente para AMBOS os idiomas — para o batch.
                break;
            }

            // Traduz EN + ES juntos (distribui a cota uniformemente)
            $title_en   = $this->translate_text($title, 'en');
            $title_es   = $this->translate_text($title, 'es');
            $excerpt_en = $this->translate_text($excerpt, 'en');
            $excerpt_es = $this->translate_text($excerpt, 'es');
            $content_en = $this->translate_text($content, 'en');
            $content_es = $this->translate_text($content, 'es');

            update_post_meta($post_id, '_zimny_title_en',   $title_en);
            update_post_meta($post_id, '_zimny_title_es',   $title_es);
            update_post_meta($post_id, '_zimny_excerpt_en', $excerpt_en);
            update_post_meta($post_id, '_zimny_excerpt_es', $excerpt_es);
            update_post_meta($post_id, '_zimny_content_en', $content_en);
            update_post_meta($post_id, '_zimny_content_es', $content_es);
            update_post_meta($post_id, '_zimny_translated', time());

            $translated++;
        }
        wp_reset_postdata();

        update_option(self::OPTION_LAST_RUN, time());

        return array(
            'translated'         => $translated,
            'skipped'            => $skipped,
            'remaining'          => $this->get_remaining_quota(),
            'total_untranslated' => $this->count_untranslated_posts(),
        );
    }

    // ─── Gatilho manual (admin) ────────────────────────────────────────────

    public function handle_manual_trigger(): void {
        if (!current_user_can('manage_options')) return;
        if (!isset($_POST['zimny_translate_trigger'])) return;
        check_admin_referer('zimny_translate_trigger');

        // Pega o batch_size do formulário (padrão 5)
        $batch_size = isset($_POST['zimny_batch_size']) ? absint($_POST['zimny_batch_size']) : 5;
        if ($batch_size < 1) $batch_size = 5;
        if ($batch_size > 50) $batch_size = 50;

        $result = $this->translate_batch($batch_size);

        // Mensagem de feedback
        add_action('admin_notices', function () use ($result) {
            $cls = $result['translated'] > 0 ? 'notice-success' : 'notice-warning';
            echo '<div class="notice ' . $cls . ' is-dismissible"><p>';
            echo esc_html(
                sprintf(
                    'Tradução concluída: %d post(s) traduzido(s), %d ignorado(s), %d ainda sem tradução. Cota restante: %s caracteres.',
                    $result['translated'],
                    $result['skipped'],
                    $result['total_untranslated'],
                    number_format($result['remaining'])
                )
            );
            echo '</p></div>';
        });
    }

    // ─── Admin page (chamada pelo menu) ────────────────────────────────────

    public function render_translator_page(): void {
        if (isset($_POST['zimny_translate_key']) && current_user_can('manage_options')) {
            check_admin_referer('zimny_save_translate_key');
            if ($this->is_api_key_managed()) {
                echo '<div class="notice notice-warning is-dismissible"><p>A chave é gerenciada com segurança pelo servidor e não pode ser substituída nesta tela.</p></div>';
            } else {
                $new_key = sanitize_text_field(wp_unslash($_POST['zimny_translate_key']));
                if ($new_key !== '') {
                    update_option(self::OPTION_API_KEY, $new_key, false);
                    echo '<div class="notice notice-success is-dismissible"><p>Chave da API salva com sucesso.</p></div>';
                }
            }
        }

        $api_key = $this->get_api_key();
        $api_key_is_managed = $this->is_api_key_managed();
        $used = $this->get_quota_used();
        $remaining = $this->get_remaining_quota();
        $last_run = get_option(self::OPTION_LAST_RUN, 0);
        $untranslated = $this->count_untranslated_posts();
        $translated_total = $this->count_translated_posts();
        $total_posts = $untranslated + $translated_total;
        ?>
        <div class="wrap" style="max-width: 900px;">
            <h1>🌐 Traduções de Posts (Google Translate)</h1>
            <p style="color:#646970;">
                Ordem: do <strong>mais recente</strong> ao mais antigo.
                A cota de 500K chars/mês é compartilhada por todos os dispositivos.
                Tradução automática via WP-Cron a cada hora.
            </p>

            <div style="display:flex; gap:20px; flex-wrap:wrap; margin:20px 0;">
                <div style="flex:1; min-width:200px; background:#fff; border:1px solid #dcdcde; border-radius:10px; padding:18px;">
                    <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.5px; color:#8c8f94; font-weight:700;">Posts traduzidos</div>
                    <div style="font-size:26px; font-weight:700; margin-top:4px; color:#2271b1;"><?php echo esc_html(number_format($translated_total)); ?></div>
                    <div style="font-size:12px; color:#646970;">de <?php echo esc_html(number_format($total_posts)); ?> publicados</div>
                </div>
                <div style="flex:1; min-width:200px; background:#fff; border:1px solid #dcdcde; border-radius:10px; padding:18px;">
                    <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.5px; color:#8c8f94; font-weight:700;">Aguardando tradução</div>
                    <div style="font-size:26px; font-weight:700; margin-top:4px; <?php echo $untranslated > 0 ? 'color:#d63638;' : 'color:#46b450;'; ?>"><?php echo esc_html(number_format($untranslated)); ?></div>
                    <div style="font-size:12px; color:#646970;">posts</div>
                </div>
                <div style="flex:1; min-width:200px; background:#fff; border:1px solid #dcdcde; border-radius:10px; padding:18px;">
                    <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.5px; color:#8c8f94; font-weight:700;">Cota usada / mês</div>
                    <div style="font-size:26px; font-weight:700; margin-top:4px;"><?php echo esc_html(number_format($used)); ?></div>
                    <div style="font-size:12px; color:#646970;">de <?php echo esc_html(number_format(self::MONTHLY_LIMIT)); ?> caracteres</div>
                </div>
                <div style="flex:1; min-width:200px; background:#fff; border:1px solid #dcdcde; border-radius:10px; padding:18px;">
                    <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.5px; color:#8c8f94; font-weight:700;">Cota restante</div>
                    <div style="font-size:26px; font-weight:700; margin-top:4px; color:#46b450;"><?php echo esc_html(number_format($remaining)); ?></div>
                    <div style="font-size:12px; color:#646970;">caracteres</div>
                </div>
            </div>

            <?php if ($last_run): ?>
                <p style="color:#646970;">Última execução: <?php echo esc_html(date_i18n('d/m/Y H:i', $last_run)); ?></p>
            <?php endif; ?>

            <hr />

            <!-- Chave da API -->
            <h2>🔑 Chave da Google Cloud Translation API</h2>
            <p style="color:#646970;">
                Habilite a <strong>Cloud Translation API</strong> no Google Cloud Console e cole a chave aqui.
                O free tier oferece <strong>500.000 caracteres/mês grátis</strong> (API v2).
            </p>
            <form method="post">
                <?php wp_nonce_field('zimny_save_translate_key'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="zimny_translate_key">API Key</label></th>
                        <td>
                            <input type="password" id="zimny_translate_key" name="zimny_translate_key"
                                   value="" class="regular-text" autocomplete="new-password"
                                   placeholder="<?php echo esc_attr($api_key !== '' ? 'Chave configurada — digite somente para substituir' : 'AIza...'); ?>"
                                   style="width:100%; max-width:500px;" <?php disabled($api_key_is_managed); ?> />
                            <p class="description">
                                <?php if ($api_key_is_managed): ?>
                                    Chave configurada com segurança no servidor.
                                <?php else: ?>
                                    Onde conseguir: <code>console.cloud.google.com → APIs & Serviços → Habilitar "Cloud Translation API" → Credenciais → Criar chave de API</code>
                                <?php endif; ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <p><button type="submit" class="button button-primary">Salvar chave</button></p>
            </form>

            <hr />

            <!-- Tradução manual -->
            <h2>⚡ Traduzir agora</h2>
            <p style="color:#646970;">
                Traduz do <strong>mais recente</strong> ao mais antigo (EN + ES juntos).
                Defina quantos posts traduzir por lote. A cota é compartilhada —
                não traduza tudo de uma vez para sobrar para posts futuros do mês.
            </p>
            <form method="post">
                <?php wp_nonce_field('zimny_translate_trigger'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="zimny_batch_size">Posts por lote</label></th>
                        <td>
                            <input type="number" id="zimny_batch_size" name="zimny_batch_size"
                                   value="5" min="1" max="50" class="small-text"
                                   style="width: 80px;" />
                            <p class="description">Quantos posts traduzir nesta execução (máx. 50).</p>
                        </td>
                    </tr>
                </table>
                <p>
                    <button type="submit" name="zimny_translate_trigger" value="1" class="button button-primary">
                        ▶️ Traduzir lote
                    </button>
                </p>
            </form>
        </div>
        <?php
    }

    private function count_untranslated_posts(): int {
        $query = new WP_Query(array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => false,
            'meta_query'     => array(
                'relation' => 'OR',
                array('key' => '_zimny_title_en', 'compare' => 'NOT EXISTS'),
                array('key' => '_zimny_title_es', 'compare' => 'NOT EXISTS'),
            ),
        ));
        return (int) $query->found_posts;
    }

    private function count_translated_posts(): int {
        $query = new WP_Query(array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => false,
            'meta_query'     => array(
                array('key' => '_zimny_title_en', 'compare' => 'EXISTS'),
                array('key' => '_zimny_title_es', 'compare' => 'EXISTS'),
            ),
        ));
        return (int) $query->found_posts;
    }
}
