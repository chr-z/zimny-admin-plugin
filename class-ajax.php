<?php
/**
 * Zimny_Admin_Ajax - Handlers AJAX do painel
 *
 * @package Zimny_Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Zimny_Admin_Ajax {

    public function __construct() {
        add_action('wp_ajax_zimny_bulk_import_folder', array($this, 'handle_bulk_import'));
        add_action('wp_ajax_zimny_save_home_layout', array($this, 'handle_save_home_layout'));
        add_action('wp_ajax_zimny_save_splash_videos', array($this, 'handle_save_splash_videos'));
        add_action('wp_ajax_zimny_save_home_featured_videos', array($this, 'handle_save_home_featured_videos'));
        add_action('wp_ajax_zimny_save_home_events', array($this, 'handle_save_home_events'));
        add_action('wp_ajax_zimny_save_event_order', array($this, 'handle_save_event_order'));
        add_action('wp_ajax_zimny_save_marketing_plans', array($this, 'handle_save_marketing_plans'));
        add_action('wp_ajax_zimny_save_colunistas_config', array($this, 'handle_save_colunistas_config'));
        add_action('wp_ajax_zimny_save_ads', array($this, 'handle_save_ads'));
        add_action('wp_ajax_zimny_save_anuncie_cards', array($this, 'handle_save_anuncie_cards'));
        add_action('wp_ajax_zimny_get_videos', array($this, 'handle_get_zimny_videos'));
    }

    public function handle_save_home_layout() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Sem permissão.');
        }
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'zimny_save_home_layout')) {
            wp_send_json_error('Nonce inválido.');
        }

        $raw = isset($_POST['layout']) ? $_POST['layout'] : '[]';
        $layout = json_decode(wp_unslash($raw), true);

        if (!is_array($layout)) {
            wp_send_json_error('Formato de layout inválido.');
        }

        $sanitized = array();
        foreach ($layout as $item) {
            $sanitized[] = array(
                'type'          => sanitize_text_field($item['type'] ?? ''),
                'slug'          => sanitize_text_field($item['slug'] ?? ''),
                'term_id'       => absint($item['term_id'] ?? 0),
                'title'         => sanitize_text_field($item['title'] ?? ''),
                'visible'       => !empty($item['visible']),
                'show_title'    => !empty($item['show_title']),
                'padding_top'   => isset($item['padding_top']) ? absint($item['padding_top']) : 16,
                'padding_bottom' => isset($item['padding_bottom']) ? absint($item['padding_bottom']) : 8,
            );
        }

        update_option(Zimny_Admin::OPTION_LAYOUT, $sanitized, false);
        wp_send_json_success('Layout salvo.');
    }

    public function handle_save_splash_videos() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Sem permissão.');
        }
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'zimny_save_splash_videos')) {
            wp_send_json_error('Nonce inválido.');
        }

        $raw = isset($_POST['video_ids']) ? $_POST['video_ids'] : '[]';
        $ids = json_decode(wp_unslash($raw), true);

        if (!is_array($ids)) {
            wp_send_json_error('Formato inválido.');
        }

        $sanitized = array_map('absint', $ids);
        $sanitized = array_filter($sanitized, function ($id) { return $id > 0; });
        $sanitized = array_values($sanitized);

        update_option(Zimny_Admin::OPTION_SPLASH_VIDEOS, $sanitized, false);
        wp_send_json_success('Vídeos do Splash Player salvos.');
    }

    public function handle_save_home_featured_videos() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Sem permissão.');
        }
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'zimny_save_home_featured_videos')) {
            wp_send_json_error('Nonce inválido.');
        }

        $raw = isset($_POST['video_ids']) ? $_POST['video_ids'] : '[]';
        $ids = json_decode(wp_unslash($raw), true);

        if (!is_array($ids)) {
            wp_send_json_error('Formato inválido.');
        }

        $sanitized = array_map('absint', $ids);
        $sanitized = array_filter($sanitized, function ($id) { return $id > 0; });
        $sanitized = array_values($sanitized);

        update_option(Zimny_Admin::OPTION_HOME_FEATURED_VIDEOS, $sanitized, false);
        wp_send_json_success('Vídeos em destaque da Home salvos.');
    }

    public function handle_save_home_events() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Sem permissão.');
        }
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'zimny_save_home_events')) {
            wp_send_json_error('Nonce inválido.');
        }

        $raw = isset($_POST['event_ids']) ? $_POST['event_ids'] : '[]';
        $ids = json_decode(wp_unslash($raw), true);

        if (!is_array($ids)) {
            wp_send_json_error('Formato inválido.');
        }

        $sanitized = array_map('absint', $ids);
        $sanitized = array_filter($sanitized, function ($id) { return $id > 0; });
        $sanitized = array_values($sanitized);

        update_option(Zimny_Admin::OPTION_HOME_EVENTS, $sanitized, false);
        wp_send_json_success('Eventos em destaque da Home salvos.');
    }

    public function handle_save_event_order() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Sem permissão.');
        }
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'zimny_save_event_order')) {
            wp_send_json_error('Nonce inválido.');
        }

        $raw = isset($_POST['event_ids']) ? $_POST['event_ids'] : '[]';
        $ids = json_decode(wp_unslash($raw), true);

        if (!is_array($ids)) {
            wp_send_json_error('Formato inválido.');
        }

        $sanitized = array_map('absint', $ids);
        $sanitized = array_filter($sanitized, function ($id) { return $id > 0; });
        $sanitized = array_values($sanitized);

        update_option('zimny_admin_event_order', $sanitized, false);
        wp_send_json_success('Ordem dos eventos salva.');
    }

    public function handle_save_marketing_plans() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Sem permissão.');
        }
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'zimny_save_marketing_plans')) {
            wp_send_json_error('Nonce inválido.');
        }

        $raw = isset($_POST['plans']) ? $_POST['plans'] : '[]';
        $plans = json_decode(wp_unslash($raw), true);

        if (!is_array($plans)) {
            wp_send_json_error('Formato inválido.');
        }

        $sanitized = array();
        foreach ($plans as $plan) {
            $sanitized[] = array(
                'image_url' => esc_url_raw($plan['image_url'] ?? ''),
                'image_id'  => absint($plan['image_id'] ?? 0),
                'title'     => sanitize_text_field($plan['title'] ?? ''),
                'link'      => esc_url_raw($plan['link'] ?? ''),
            );
        }

        update_option(Zimny_Admin::OPTION_MARKETING_PLANS, $sanitized, false);
        wp_send_json_success('Planos de marketing salvos.');
    }

    public function handle_save_colunistas_config() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Sem permissão.');
        }
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'zimny_save_colunistas_config')) {
            wp_send_json_error('Nonce inválido.');
        }

        $raw = isset($_POST['config']) ? $_POST['config'] : '{}';
        $config = json_decode(wp_unslash($raw), true);

        if (!is_array($config)) {
            wp_send_json_error('Formato inválido.');
        }

        $day_assignments = array();
        if (isset($config['day_assignments']) && is_array($config['day_assignments'])) {
            foreach ($config['day_assignments'] as $day => $user_id) {
                $day = absint($day);
                $user_id = absint($user_id);
                if ($day >= 1 && $day <= 7 && $user_id > 0) {
                    $day_assignments[$day] = $user_id;
                }
            }
        }

        $column_names = array();
        if (isset($config['column_names']) && is_array($config['column_names'])) {
            foreach ($config['column_names'] as $user_id => $name) {
                $user_id = absint($user_id);
                $name = sanitize_text_field($name);
                if ($user_id > 0 && !empty($name)) {
                    $column_names[$user_id] = $name;
                }
            }
        }

        $column_colors = array();
        if (isset($config['column_colors']) && is_array($config['column_colors'])) {
            foreach ($config['column_colors'] as $user_id => $color) {
                $user_id = absint($user_id);
                $color = sanitize_text_field($color);
                if ($user_id > 0 && !empty($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
                    $column_colors[$user_id] = $color;
                }
            }
        }

        $instagram_handles = array();
        if (isset($config['instagram_handles']) && is_array($config['instagram_handles'])) {
            foreach ($config['instagram_handles'] as $user_id => $handle) {
                $user_id = absint($user_id);
                $handle = sanitize_text_field($handle);
                if ($user_id > 0 && !empty($handle)) {
                    $instagram_handles[$user_id] = $handle;
                }
            }
        }

        $sanitized = array(
            'day_assignments' => $day_assignments,
            'column_names' => $column_names,
            'column_colors' => $column_colors,
            'instagram_handles' => $instagram_handles,
        );

        update_option(Zimny_Admin::OPTION_COLUNISTAS_CONFIG, $sanitized, false);
        wp_send_json_success('Configuração dos colunistas salva.');
    }

    public function handle_save_ads() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Sem permissão.');
        }
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'zimny_save_ads')) {
            wp_send_json_error('Nonce inválido.');
        }

        $raw = isset($_POST['ads']) ? $_POST['ads'] : '[]';
        $ads = json_decode(wp_unslash($raw), true);

        if (!is_array($ads)) {
            wp_send_json_error('Formato inválido.');
        }

        $sanitized = array();
        foreach ($ads as $ad) {
            $sanitized[] = array(
                'logo_url'    => esc_url_raw($ad['logo_url'] ?? ''),
                'logo_id'     => absint($ad['logo_id'] ?? 0),
                'image_url'   => esc_url_raw($ad['image_url'] ?? ''),
                'image_id'    => absint($ad['image_id'] ?? 0),
                'title_pt'    => sanitize_text_field($ad['title_pt'] ?? $ad['title'] ?? ''),
                'title_en'    => sanitize_text_field($ad['title_en'] ?? ''),
                'title_es'    => sanitize_text_field($ad['title_es'] ?? ''),
                'description_pt' => sanitize_textarea_field($ad['description_pt'] ?? $ad['description'] ?? ''),
                'description_en' => sanitize_textarea_field($ad['description_en'] ?? ''),
                'description_es' => sanitize_textarea_field($ad['description_es'] ?? ''),
                'cta_text_pt'    => sanitize_text_field($ad['cta_text_pt'] ?? $ad['cta_text'] ?? ''),
                'cta_text_en'    => sanitize_text_field($ad['cta_text_en'] ?? ''),
                'cta_text_es'    => sanitize_text_field($ad['cta_text_es'] ?? ''),
                'link'        => esc_url_raw($ad['link'] ?? ''),
                'type'        => in_array($ad['type'] ?? '', array('banner', 'sponsored', 'custom', 'image_banner')) ? $ad['type'] : 'banner',
                'placement'   => in_array($ad['placement'] ?? '', array('home', 'article', 'both')) ? $ad['placement'] : 'both',
                'active'      => !empty($ad['active']),
                'impressions' => absint($ad['impressions'] ?? 0),
                'clicks'      => absint($ad['clicks'] ?? 0),
            );
        }

        update_option(Zimny_Admin::OPTION_ADS, $sanitized, false);
        wp_send_json_success('Publicidades salvas.');
    }

    public function handle_save_anuncie_cards() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Sem permissão.');
        }
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'zimny_save_anuncie_cards')) {
            wp_send_json_error('Nonce inválido.');
        }

        $raw = isset($_POST['cards']) ? $_POST['cards'] : '[]';
        $cards = json_decode(wp_unslash($raw), true);

        if (!is_array($cards)) {
            wp_send_json_error('Formato inválido.');
        }

        $sanitized = array();
        foreach ($cards as $card) {
            $variant = absint($card['variant'] ?? 1);
            if ($variant < 1 || $variant > 4) $variant = 1;

            $sanitized[] = array(
                'image_url'   => esc_url_raw($card['image_url'] ?? ''),
                'image_id'    => absint($card['image_id'] ?? 0),
                'variant'     => $variant,
                'title'       => sanitize_text_field($card['title'] ?? ''),
                'description' => sanitize_textarea_field($card['description'] ?? ''),
                'cta_text'    => sanitize_text_field($card['cta_text'] ?? ''),
                'cta_link'    => esc_url_raw($card['cta_link'] ?? ''),
            );
        }

        update_option(Zimny_Admin::OPTION_ANUNCIE_CARDS, $sanitized, false);
        wp_send_json_success('Cards salvos.');
    }

    public function handle_bulk_import() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Sem permissão de acesso.');
        }

        $folder_name = sanitize_text_field($_POST['folder_name']);
        $carousel_id = isset($_POST['carousel_id']) ? intval($_POST['carousel_id']) : 0;

        if (empty($_FILES['video_file']) || empty($_FILES['thumb_file'])) {
            wp_send_json_error('Arquivos ausentes no envio.');
        }

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        // Extrai a data YYYY-MM-DD se o nome da pasta começar com ela
        $post_date = current_time('mysql');
        if (preg_match('/^(\d{4}-\d{2}-\d{2})_/', $folder_name, $matches)) {
            $post_date = $matches[1] . ' ' . current_time('H:i:s');
        }

        $video_attachment_id = media_handle_upload('video_file', 0);
        if (is_wp_error($video_attachment_id)) {
            wp_send_json_error('Erro ao salvar vídeo: ' . $video_attachment_id->get_error_message());
        }
        $video_url = wp_get_attachment_url($video_attachment_id);

        $thumb_attachment_id = media_handle_upload('thumb_file', 0);
        if (is_wp_error($thumb_attachment_id)) {
            wp_send_json_error('Erro ao salvar thumbnail: ' . $thumb_attachment_id->get_error_message());
        }

        $post_title = 'Vídeo ' . $folder_name;
        $post_id = wp_insert_post(array(
            'post_title'    => $post_title,
            'post_type'     => 'zimny_video',
            'post_status'   => 'publish',
            'post_date'     => $post_date,
            'post_date_gmt' => get_gmt_from_date($post_date),
        ));

        if (is_wp_error($post_id)) {
            wp_send_json_error('Erro ao criar registro no banco de dados.');
        }

        set_post_thumbnail($post_id, $thumb_attachment_id);
        update_post_meta($post_id, '_zimny_video_file_url', $video_url);
        update_post_meta($post_id, '_zimny_video_attachment_id', $video_attachment_id);

        if ($carousel_id > 0) {
            wp_set_post_terms($post_id, array($carousel_id), 'video_carousel');
        }

        wp_send_json_success(array(
            'post_id'  => $post_id,
            'edit_url' => get_edit_post_link($post_id, ''),
        ));
    }

    // ── GET /wp-admin/admin-ajax.php?action=zimny_get_videos ─────────────────

    public function handle_get_zimny_videos() {
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Sem permissão.');
        }

        $query = new WP_Query(array(
            'post_type'      => 'zimny_video',
            'posts_per_page' => 200,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));

        $videos = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                $video_url = get_post_meta($post_id, '_zimny_video_file_url', true);
                $thumb_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
                if (!$thumb_url) {
                    $thumb_url = get_the_post_thumbnail_url($post_id, 'medium');
                }

                $videos[] = array(
                    'id'            => (string) $post_id,
                    'title'         => get_the_title(),
                    'video_url'     => $video_url ? $video_url : '',
                    'thumbnail_url' => $thumb_url ? $thumb_url : '',
                );
            }
            wp_reset_postdata();
        }

        wp_send_json_success(array('videos' => $videos));
    }
}