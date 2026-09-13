<?php
/**
 * Zimny_Admin_Rest_Api - Endpoints REST da API
 *
 * @package Zimny_Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Zimny_Admin_Rest_Api {

    /**
     * Referência ao coordenador principal.
     *
     * @var Zimny_Admin
     */
    private $admin;

    public function __construct($admin) {
        $this->admin = $admin;
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        // Aplica traduções aos posts da REST API padrão quando lang é informado
        add_filter('rest_prepare_post', array($this, 'apply_post_translation'), 10, 3);
    }

    /**
     * Filtro rest_prepare_post: substitui título/excerto/conteúdo
     * pela tradução salva quando o request traz X-Zimny-Lang: en|es.
     *
     * Usamos header (não query param) porque o GTranslate Free intercepta
     * `?lang=` na REST API padrão e quebra a resposta.
     */
    public function apply_post_translation($response, $post, $request) {
        // Tenta header X-Zimny-Lang primeiro, depois query param lang
        $lang = $request->get_header('X-Zimny-Lang');
        if (empty($lang)) {
            $lang = $request->get_param('lang');
        }
        if (!in_array($lang, array('en', 'es'), true)) {
            return $response;
        }

        $post_id = $post->ID;

        $title_en = get_post_meta($post_id, "_zimny_title_{$lang}", true);
        if ($title_en !== '') {
            $response->data['title']['rendered'] = $title_en;
        }

        $excerpt_en = get_post_meta($post_id, "_zimny_excerpt_{$lang}", true);
        if ($excerpt_en !== '' && isset($response->data['excerpt'])) {
            $response->data['excerpt']['rendered'] = $excerpt_en;
        }

        if (!empty($response->data['content'])) {
            $content_en = get_post_meta($post_id, "_zimny_content_{$lang}", true);
            if ($content_en !== '') {
                $response->data['content']['rendered'] = $content_en;
            }
        }

        return $response;
    }

    public function register_rest_routes() {
        // GET /wp-json/zimny/v1/videos
        register_rest_route('zimny/v1', '/videos', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_app_videos'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'carousel' => array(
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'limit' => array(
                    'required'          => false,
                    'sanitize_callback' => 'absint',
                    'default'           => 30,
                ),
            ),
        ));

        // GET /wp-json/zimny/v1/home-layout
        register_rest_route('zimny/v1', '/home-layout', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_home_layout'),
            'permission_callback' => '__return_true',
        ));

        // GET /wp-json/zimny/v1/splash-videos
        register_rest_route('zimny/v1', '/splash-videos', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_splash_videos'),
            'permission_callback' => '__return_true',
        ));

        // GET /wp-json/zimny/v1/home-featured-videos
        register_rest_route('zimny/v1', '/home-featured-videos', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_home_featured_videos'),
            'permission_callback' => '__return_true',
        ));

        // GET /wp-json/zimny/v1/events
        register_rest_route('zimny/v1', '/events', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_events'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'category' => array(
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'limit' => array(
                    'required'          => false,
                    'sanitize_callback' => 'absint',
                    'default'           => 50,
                ),
                'home' => array(
                    'required'          => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                    'default'           => false,
                ),
            ),
        ));

        // GET /wp-json/zimny/v1/events/{id}/media
        register_rest_route('zimny/v1', '/events/(?P<id>\d+)/media', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_event_media'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'id' => array(
                    'required'          => true,
                    'validate_callback' => function($param) { return is_numeric($param); },
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));

        // Compatibilidade com a nomenclatura antiga "galerias" no app.
        // O CPT zimny_event substituiu o conceito de galeria e mantém a mesma mídia.
        register_rest_route('zimny/v1', '/galleries', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_events'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'limit' => array(
                    'required'          => false,
                    'sanitize_callback' => 'absint',
                    'default'           => 50,
                ),
                'home' => array(
                    'required'          => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                    'default'           => false,
                ),
            ),
        ));

        register_rest_route('zimny/v1', '/galleries/(?P<id>\d+)/media', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_event_media'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'id' => array(
                    'required'          => true,
                    'validate_callback' => function($param) { return is_numeric($param); },
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));

        // GET /wp-json/zimny/v1/marketing-plans
        register_rest_route('zimny/v1', '/marketing-plans', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_marketing_plans'),
            'permission_callback' => '__return_true',
        ));

        // GET /wp-json/zimny/v1/ads — lista de anúncios (opcionalmente filtrada por placement)
        register_rest_route('zimny/v1', '/ads', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_ads'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'placement' => array(
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_text_field',
                    'default'           => '',
                ),
            ),
        ));

        // GET /wp-json/zimny/v1/ads/random — retorna um anúncio aleatório ativo por placement
        register_rest_route('zimny/v1', '/ads/random', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_random_ad'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'placement' => array(
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_text_field',
                    'default'           => 'both',
                ),
            ),
        ));

        // POST /wp-json/zimny/v1/ads/{id}/track-impression
        register_rest_route('zimny/v1', '/ads/(?P<id>ad-\d+)/track-impression', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'track_ad_impression'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'id' => array(
                    'required'          => true,
                    'validate_callback' => function($param) {
                        return is_string($param) && preg_match('/^ad-\d+$/', $param) === 1;
                    },
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));

        // POST /wp-json/zimny/v1/ads/{id}/track-click
        register_rest_route('zimny/v1', '/ads/(?P<id>ad-\d+)/track-click', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'track_ad_click'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'id' => array(
                    'required'          => true,
                    'validate_callback' => function($param) {
                        return is_string($param) && preg_match('/^ad-\d+$/', $param) === 1;
                    },
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));

        // GET /wp-json/zimny/v1/anuncie-cards
        register_rest_route('zimny/v1', '/anuncie-cards', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_anuncie_cards'),
            'permission_callback' => '__return_true',
        ));

        // GET /wp-json/zimny/v1/colunistas-config
        register_rest_route('zimny/v1', '/colunistas-config', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_colunistas_config'),
            'permission_callback' => '__return_true',
        ));

        // GET /wp-json/zimny/v1/posts/untranslated — posts que precisam de tradução
        register_rest_route('zimny/v1', '/posts/untranslated', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_untranslated_posts'),
            'permission_callback' => array($this, 'can_manage_translations'),
            'args'                => array(
                'limit' => array(
                    'required'          => false,
                    'sanitize_callback' => 'absint',
                    'default'           => 10,
                ),
                'after_id' => array(
                    'required'          => false,
                    'sanitize_callback' => 'absint',
                    'default'           => 0,
                ),
            ),
        ));

        // POST /wp-json/zimny/v1/posts/translations — salva traduções
        register_rest_route('zimny/v1', '/posts/translations', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'save_post_translations'),
            'permission_callback' => array($this, 'can_manage_translations'),
            'args'                => array(
                'translations' => array(
                    'required'          => true,
                    'sanitize_callback' => array($this, 'sanitize_translations'),
                ),
            ),
        ));

        // GET /wp-json/zimny/v1/coberturas
        register_rest_route('zimny/v1', '/coberturas', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_coberturas'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'home' => array(
                    'required'          => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                    'default'           => false,
                ),
                'approved' => array(
                    'required'          => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                    'default'           => true,
                ),
                'limit' => array(
                    'required'          => false,
                    'sanitize_callback' => 'absint',
                    'default'           => 50,
                ),
            ),
        ));

        // GET /wp-json/zimny/v1/coberturas/{id}/media
        register_rest_route('zimny/v1', '/coberturas/(?P<id>\d+)/media', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_cobertura_media'),
            'permission_callback' => '__return_true',
            'args'                => array(
                'id' => array(
                    'required'          => true,
                    'validate_callback' => function($param) { return is_numeric($param); },
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
    }

    // ── GET /wp-json/zimny/v1/videos ─────────────────────────────────────────

    public function get_app_videos($request) {
        $carousel_slug = $request->get_param('carousel');
        $limit = $request->get_param('limit') ? intval($request->get_param('limit')) : 30;

        $args = array(
            'post_type'      => 'zimny_video',
            'posts_per_page' => $limit,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        if (!empty($carousel_slug)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'video_carousel',
                    'field'    => 'slug',
                    'terms'    => $carousel_slug,
                ),
            );
        }

        $query = new WP_Query($args);
        $results = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                $post_order = (int) get_post_meta($post_id, '_zimny_video_order', true);
                $post_date  = get_the_date('U', $post_id);

                $video_url = get_post_meta($post_id, '_zimny_video_file_url', true);
                $thumb_url = get_the_post_thumbnail_url($post_id, 'large');

                $terms = get_the_terms($post_id, 'video_carousel');
                $carousels = array();
                if ($terms && !is_wp_error($terms)) {
                    foreach ($terms as $term) {
                        $carousels[] = array(
                            'id'   => $term->term_id,
                            'name' => $term->name,
                            'slug' => $term->slug,
                        );
                    }
                }

                $results[] = array(
                    'id'            => (string) $post_id,
                    'title'         => get_the_title(),
                    'video_url'     => $video_url ? $video_url : '',
                    'thumbnail_url' => $thumb_url ? $thumb_url : '',
                    'carousels'     => $carousels,
                    'order'         => $post_order,
                    '_sort_order'   => $post_order,
                    '_sort_date'    => $post_date,
                    'created_at'    => get_the_date('c'),
                );
            }
            wp_reset_postdata();

            usort($results, function ($a, $b) {
                if ($a['_sort_order'] !== $b['_sort_order']) {
                    return $a['_sort_order'] - $b['_sort_order'];
                }
                return $b['_sort_date'] - $a['_sort_date'];
            });

            $results = array_map(function ($item) {
                unset($item['_sort_order'], $item['_sort_date']);
                return $item;
            }, $results);
        }

        return rest_ensure_response($results);
    }

    // ── GET /wp-json/zimny/v1/home-layout ────────────────────────────────────

    public function get_home_layout($request) {
        $lang = $request->get_param('lang') ?: 'pt';
        if (!in_array($lang, array('pt', 'en', 'es'))) {
            $lang = 'pt';
        }

        $layout = $this->admin->home_layout->get_layout();

        // Translate section titles based on language
        $title_map = array(
            'pt' => array(
                'hero_banner'         => 'Hero Banner (Edição mais recente)',
                'acervo_carousel'     => 'Acervo (Edições anteriores)',
                'colunistas_carousel' => 'Colunistas',
                'ad_block'            => 'Bloco de Publicidade (Premium Ad)',
                'journal_section'     => 'Journal (Artigos recomendados)',
                'instagram_feed'      => 'Feed do Instagram',
                'home_featured_videos'=> 'Vídeos em Destaque (Home)',
                'events_carousel'     => 'Carrossel de Eventos',
                'events_more_button'  => 'Mais Eventos (botão)',
                'marketing_plans_carousel' => 'Planos de Marketing Digital',
                'colunista_dia'       => 'Coluna do Dia',
                'podcast_grid'        => 'Podcast (Grid 2×2)',
                'cobertura_grid'      => 'Cobertura (Grid 3×2)',
                'eventos_grid'        => 'Eventos (Grid 3×2)',
            ),
            'en' => array(
                'hero_banner'         => 'Hero Banner (Latest Edition)',
                'acervo_carousel'     => 'Collection (Past Editions)',
                'colunistas_carousel' => 'Columnists',
                'ad_block'            => 'Advertising Block (Premium Ad)',
                'journal_section'     => 'Journal (Recommended Articles)',
                'instagram_feed'      => 'Instagram Feed',
                'home_featured_videos'=> 'Featured Videos (Home)',
                'events_carousel'     => 'Events Carousel',
                'events_more_button'  => 'More Events (button)',
                'marketing_plans_carousel' => 'Digital Marketing Plans',
                'colunista_dia'       => 'Column of the Day',
                'podcast_grid'        => 'Podcast (Grid 2×2)',
                'cobertura_grid'      => 'Coverage (Grid 3×2)',
                'eventos_grid'        => 'Events (Grid 3×2)',
            ),
            'es' => array(
                'hero_banner'         => 'Hero Banner (Edición más reciente)',
                'acervo_carousel'     => 'Colección (Ediciones anteriores)',
                'colunistas_carousel' => 'Columnistas',
                'ad_block'            => 'Bloque de Publicidad (Premium Ad)',
                'journal_section'     => 'Journal (Artículos recomendados)',
                'instagram_feed'      => 'Feed de Instagram',
                'home_featured_videos'=> 'Videos Destacados (Home)',
                'events_carousel'     => 'Carrusel de Eventos',
                'events_more_button'  => 'Más Eventos (botón)',
                'marketing_plans_carousel' => 'Planes de Marketing Digital',
                'colunista_dia'       => 'Columna del Día',
                'podcast_grid'        => 'Podcast (Grid 2×2)',
                'cobertura_grid'      => 'Cobertura (Grid 3×2)',
                'eventos_grid'        => 'Eventos (Grid 3×2)',
            ),
        );

        // Apply translations to layout sections
        if ($lang !== 'pt' && !empty($title_map[$lang])) {
            foreach ($layout as &$section) {
                $slug = $section['slug'] ?? '';
                if (isset($title_map[$lang][$slug])) {
                    $section['title'] = $title_map[$lang][$slug];
                }
                // Also translate video carousel term names if present
                if (!empty($section['term_id'])) {
                    $term = get_term($section['term_id']);
                    if ($term && !is_wp_error($term)) {
                        $section['title'] = $term->name;
                    }
                }
            }
        }

        return rest_ensure_response($layout);
    }

    // ── GET /wp-json/zimny/v1/splash-videos ──────────────────────────────────

    public function get_splash_videos() {
        $splash_ids = get_option(Zimny_Admin::OPTION_SPLASH_VIDEOS, array());
        if (!is_array($splash_ids) || empty($splash_ids)) {
            // Fallback para opção antiga
            $splash_ids = get_option('zimny_play_splash_videos', array());
            if (!is_array($splash_ids) || empty($splash_ids)) {
                return rest_ensure_response(array());
            }
        }

        $videos = array();
        foreach ($splash_ids as $post_id) {
            $post = get_post($post_id);
            if (!$post || $post->post_type !== 'zimny_video' || $post->post_status !== 'publish') {
                continue;
            }

            $video_url = get_post_meta($post_id, '_zimny_video_file_url', true);
            $thumb_url = get_the_post_thumbnail_url($post_id, 'large');

            if (empty($video_url)) {
                continue;
            }

            $videos[] = array(
                'id'            => (string) $post_id,
                'video_url'     => esc_url($video_url),
                'thumbnail_url' => $thumb_url ? esc_url($thumb_url) : '',
            );
        }

        shuffle($videos);
        return rest_ensure_response($videos);
    }

    // ── GET /wp-json/zimny/v1/home-featured-videos ───────────────────────────

    public function get_home_featured_videos() {
        $featured_ids = get_option(Zimny_Admin::OPTION_HOME_FEATURED_VIDEOS, array());
        if (!is_array($featured_ids) || empty($featured_ids)) {
            $featured_ids = get_option('zimny_play_home_featured_videos', array());
            if (!is_array($featured_ids) || empty($featured_ids)) {
                return rest_ensure_response(array());
            }
        }

        $videos = array();
        foreach ($featured_ids as $post_id) {
            $post = get_post($post_id);
            if (!$post || $post->post_type !== 'zimny_video' || $post->post_status !== 'publish') {
                continue;
            }

            $video_url = get_post_meta($post_id, '_zimny_video_file_url', true);
            $thumb_url = get_the_post_thumbnail_url($post_id, 'large');
            $order     = (int) get_post_meta($post_id, '_zimny_video_order', true);

            if (empty($video_url)) {
                continue;
            }

            $videos[] = array(
                'id'            => (string) $post_id,
                'title'         => get_the_title($post),
                'video_url'     => esc_url($video_url),
                'thumbnail_url' => $thumb_url ? esc_url($thumb_url) : '',
                'order'         => $order,
                'created_at'    => $post->post_date,
            );
        }

        usort($videos, function ($a, $b) {
            if ($a['order'] !== $b['order']) {
                return $a['order'] - $b['order'];
            }
            return strcmp($b['created_at'], $a['created_at']);
        });

        return rest_ensure_response($videos);
    }

    // ── GET /wp-json/zimny/v1/events ─────────────────────────────────────────

    public function get_events($request) {
        $category = $request->get_param('category');
        $limit    = $request->get_param('limit') ? intval($request->get_param('limit')) : 50;
        $home     = $request->get_param('home');

        $args = array(
            'post_type'      => 'zimny_event',
            'posts_per_page' => $limit,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        if (!empty($category)) {
            $args['meta_query'] = array(
                array(
                    'key'   => '_zimny_event_category',
                    'value' => $category,
                ),
            );
        }

        if ($home) {
            $home_event_ids = get_option(Zimny_Admin::OPTION_HOME_EVENTS, array());
            if (!is_array($home_event_ids) || empty($home_event_ids)) {
                return rest_ensure_response(array());
            }
            $args['post__in'] = $home_event_ids;
            $args['orderby']  = 'post__in';
        }

        $query = new WP_Query($args);
        $results = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                $gallery_json = get_post_meta($post_id, '_zimny_event_gallery', true);
                $gallery = $gallery_json ? json_decode($gallery_json, true) : array();
                if (!is_array($gallery)) $gallery = array();

                // Find featured media
                $featured_media = null;
                foreach ($gallery as $item) {
                    if (!empty($item['is_featured'])) {
                        $featured_media = $item;
                        break;
                    }
                }
                // If no featured, use first item
                if (!$featured_media && !empty($gallery)) {
                    $featured_media = $gallery[0];
                }

                // Get thumbnail dimensions for dynamic sizing
                $thumb_id  = get_post_thumbnail_id($post_id);
                $thumb_src = $thumb_id ? wp_get_attachment_image_src($thumb_id, 'full') : null;
                $thumb_w   = $thumb_src ? $thumb_src[1] : 0;
                $thumb_h   = $thumb_src ? $thumb_src[2] : 0;

                $results[] = array(
                    'id'               => (string) $post_id,
                    'title'            => get_the_title(),
                    'content'          => get_the_content(),
                    'thumbnail_url'    => $thumb_src ? $thumb_src[0] : '',
                    'thumbnail_width'  => $thumb_w,
                    'thumbnail_height' => $thumb_h,
                    'category'       => get_post_meta($post_id, '_zimny_event_category', true) ?: 'producao',
                    'date'           => get_the_date('Y-m-d', $post_id),
                    'order'          => (int) get_post_meta($post_id, '_zimny_event_order', true),
                    'home_order'     => (int) get_post_meta($post_id, '_zimny_event_home_order', true),
                    'show_on_home'   => (bool) get_post_meta($post_id, '_zimny_event_show_on_home', true),
                    'featured_media' => $featured_media,
                    'media_count'    => count($gallery),
                    'created_at'     => get_the_date('c'),
                );
            }
            wp_reset_postdata();
        }

        // Sort by saved drag-and-drop order
        $event_order = get_option('zimny_admin_event_order', array());
        if (!empty($event_order) && is_array($event_order)) {
            $order_map = array_flip(array_map('intval', $event_order));

            usort($results, function ($a, $b) use ($order_map) {
                $a_id = (int) $a['id'];
                $b_id = (int) $b['id'];
                $a_pos = isset($order_map[$a_id]) ? $order_map[$a_id] : PHP_INT_MAX;
                $b_pos = isset($order_map[$b_id]) ? $order_map[$b_id] : PHP_INT_MAX;
                return $a_pos - $b_pos;
            });
        }

        return rest_ensure_response($results);
    }

    // ── GET /wp-json/zimny/v1/events/{id}/media ──────────────────────────────

    public function get_event_media($request) {
        $post_id = $request->get_param('id');
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'zimny_event' || $post->post_status !== 'publish') {
            return new WP_Error('not_found', 'Evento não encontrado.', array('status' => 404));
        }

        $gallery_json = get_post_meta($post_id, '_zimny_event_gallery', true);
        $gallery = $gallery_json ? json_decode($gallery_json, true) : array();

        if (!is_array($gallery)) {
            $gallery = array();
        }

        return rest_ensure_response($gallery);
    }

    // ── GET /wp-json/zimny/v1/marketing-plans ────────────────────────────────

    public function get_marketing_plans($request) {
        $lang = $request->get_param('lang') ?: 'pt';
        if (!in_array($lang, array('pt', 'en', 'es'))) {
            $lang = 'pt';
        }

        $plans = get_option(Zimny_Admin::OPTION_MARKETING_PLANS, array());
        if (!is_array($plans)) {
            $plans = array();
        }

        $result = array();
        foreach ($plans as $plan) {
            $image_url = $plan['image_url'] ?? '';
            $image_id  = absint($plan['image_id'] ?? 0);

            if (empty($image_url) && $image_id > 0) {
                $image_url = wp_get_attachment_image_url($image_id, 'large');
            }
            if (empty($image_url)) {
                continue;
            }

            $title_key = "title_{$lang}";
            $result[] = array(
                'id'        => 'plan-' . ($plan['id'] ?? count($result)),
                'image_url' => $image_url,
                'image_id'  => $image_id,
                'title'     => sanitize_text_field($plan[$title_key] ?? $plan['title_pt'] ?? $plan['title'] ?? ''),
                'link'      => esc_url_raw($plan['link'] ?? ''),
            );
        }

        return rest_ensure_response($result);
    }

    // ── GET /wp-json/zimny/v1/ads ────────────────────────────────────────────

    public function get_ads($request) {
        $placement = $request->get_param('placement') ?: '';
        $lang = $request->get_param('lang') ?: 'pt';
        // Sanitize lang
        if (!in_array($lang, array('pt', 'en', 'es'))) {
            $lang = 'pt';
        }
        $ads = get_option(Zimny_Admin::OPTION_ADS, array());
        if (!is_array($ads)) {
            $ads = array();
        }

        $result = array();
        foreach ($ads as $i => $ad) {
            // Quando um placement é informado, retorna apenas anúncios ativos
            // que correspondam ao placement (ou "both").
            if ($placement !== '') {
                if (empty($ad['active'])) continue;
                $ad_placement = $ad['placement'] ?? 'both';
                if ($ad_placement !== $placement && $ad_placement !== 'both') continue;
            }

            $logo_url  = $ad['logo_url'] ?? '';
            $logo_id   = absint($ad['logo_id'] ?? 0);
            $image_url = $ad['image_url'] ?? '';
            $image_id  = absint($ad['image_id'] ?? 0);

            if (empty($logo_url) && $logo_id > 0) {
                $logo_url = wp_get_attachment_image_url($logo_id, 'thumbnail');
            }
            if (empty($image_url) && $image_id > 0) {
                $image_url = wp_get_attachment_image_url($image_id, 'large');
            }

            // Resolve o texto no idioma solicitado, com fallback para PT
            $title_key = "title_{$lang}";
            $desc_key  = "description_{$lang}";
            $cta_key   = "cta_text_{$lang}";

            $result[] = array(
                // ID consistente com /ads/random e com o tracking (ad-{index}).
                'id'          => 'ad-' . $i,
                'logo_url'    => $logo_url ?: '',
                'logo_id'     => $logo_id,
                'image_url'   => $image_url ?: '',
                'image_id'    => $image_id,
                'title'       => sanitize_text_field($ad[$title_key] ?? $ad['title_pt'] ?? $ad['title'] ?? ''),
                'description' => sanitize_textarea_field($ad[$desc_key] ?? $ad['description_pt'] ?? $ad['description'] ?? ''),
                'cta_text'    => sanitize_text_field($ad[$cta_key] ?? $ad['cta_text_pt'] ?? $ad['cta_text'] ?? ''),
                'link'        => esc_url_raw($ad['link'] ?? ''),
                'type'        => in_array($ad['type'] ?? '', array('banner', 'sponsored', 'custom', 'image_banner')) ? $ad['type'] : 'banner',
                'placement'   => in_array($ad['placement'] ?? '', array('home', 'article', 'both')) ? $ad['placement'] : 'both',
                'active'      => !empty($ad['active']),
                'impressions' => absint($ad['impressions'] ?? 0),
                'clicks'      => absint($ad['clicks'] ?? 0),
            );
        }

        return rest_ensure_response($result);
    }

    // ── GET /wp-json/zimny/v1/ads/random ───────────────────────────────────────

    public function get_random_ad($request) {
        $placement = $request->get_param('placement') ?: 'both';
        $lang = $request->get_param('lang') ?: 'pt';
        if (!in_array($lang, array('pt', 'en', 'es'))) {
            $lang = 'pt';
        }
        $ads = get_option(Zimny_Admin::OPTION_ADS, array());
        if (!is_array($ads)) {
            $ads = array();
        }

        // Filter active ads matching the requested placement
        $eligible = array();
        foreach ($ads as $i => $ad) {
            if (empty($ad['active'])) continue;
            $ad_placement = $ad['placement'] ?? 'both';
            if ($ad_placement === $placement || $ad_placement === 'both') {
                $ad['_index'] = $i;
                $eligible[] = $ad;
            }
        }

        if (empty($eligible)) {
            return rest_ensure_response(null);
        }

        // Pick a random ad
        $ad = $eligible[array_rand($eligible)];
        $i = $ad['_index'];

        $logo_url  = $ad['logo_url'] ?? '';
        $logo_id   = absint($ad['logo_id'] ?? 0);
        $image_url = $ad['image_url'] ?? '';
        $image_id  = absint($ad['image_id'] ?? 0);

        if (empty($logo_url) && $logo_id > 0) {
            $logo_url = wp_get_attachment_image_url($logo_id, 'thumbnail');
        }
        if (empty($image_url) && $image_id > 0) {
            $image_url = wp_get_attachment_image_url($image_id, 'large');
        }

        $result = array(
            'id'          => 'ad-' . $i,
            'logo_url'    => $logo_url ?: '',
            'logo_id'     => $logo_id,
            'image_url'   => $image_url ?: '',
            'image_id'    => $image_id,
            'title'       => sanitize_text_field($ad["title_{$lang}"] ?? $ad['title_pt'] ?? $ad['title'] ?? ''),
            'description' => sanitize_textarea_field($ad["description_{$lang}"] ?? $ad['description_pt'] ?? $ad['description'] ?? ''),
            'cta_text'    => sanitize_text_field($ad["cta_text_{$lang}"] ?? $ad['cta_text_pt'] ?? $ad['cta_text'] ?? ''),
            'link'        => esc_url_raw($ad['link'] ?? ''),
            'type'        => in_array($ad['type'] ?? '', array('banner', 'sponsored', 'custom', 'image_banner')) ? $ad['type'] : 'banner',
            'placement'   => in_array($ad['placement'] ?? '', array('home', 'article', 'both')) ? $ad['placement'] : 'both',
            'active'      => true,
        );

        return rest_ensure_response($result);
    }

    // ── POST /wp-json/zimny/v1/ads/{id}/track-impression ──────────────────────

    /**
     * Evita que recarregamentos, robôs ou chamadas repetidas inflem as métricas.
     * O identificador não é persistido: apenas um hash temporário é armazenado.
     */
    private function is_ad_tracking_rate_limited($action, $index) {
        $remote_ip = isset($_SERVER['HTTP_CF_CONNECTING_IP'])
            ? wp_unslash($_SERVER['HTTP_CF_CONNECTING_IP'])
            : (isset($_SERVER['REMOTE_ADDR']) ? wp_unslash($_SERVER['REMOTE_ADDR']) : 'unknown');
        $user_agent = isset($_SERVER['HTTP_USER_AGENT'])
            ? wp_unslash($_SERVER['HTTP_USER_AGENT'])
            : 'unknown';

        $fingerprint = sanitize_text_field($remote_ip) . '|' . sanitize_text_field($user_agent);
        $key = 'zimny_ad_track_' . md5($action . '|' . absint($index) . '|' . $fingerprint);
        if (get_transient($key) !== false) {
            return true;
        }

        $window = $action === 'impression' ? 30 : 5;
        set_transient($key, 1, $window);
        return false;
    }

    public function track_ad_impression($request) {
        $ad_id = $request->get_param('id');
        $ads = get_option(Zimny_Admin::OPTION_ADS, array());
        if (!is_array($ads)) {
            return rest_ensure_response(array('success' => false, 'message' => 'No ads found'));
        }

        // Parse index from "ad-{index}" format
        $parts = explode('-', $ad_id);
        $index = isset($parts[1]) ? intval($parts[1]) : -1;

        if ($index < 0 || !isset($ads[$index])) {
            return rest_ensure_response(array('success' => false, 'message' => 'Ad not found'));
        }

        if (isset($ads[$index]['active']) && empty($ads[$index]['active'])) {
            return rest_ensure_response(array('success' => false, 'message' => 'Ad is inactive'));
        }

        if ($this->is_ad_tracking_rate_limited('impression', $index)) {
            return rest_ensure_response(array(
                'success'     => true,
                'counted'     => false,
                'impressions' => absint($ads[$index]['impressions'] ?? 0),
            ));
        }

        $ads[$index]['impressions'] = absint($ads[$index]['impressions'] ?? 0) + 1;
        update_option(Zimny_Admin::OPTION_ADS, $ads, false);

        return rest_ensure_response(array(
            'success'     => true,
            'counted'     => true,
            'impressions' => $ads[$index]['impressions'],
        ));
    }

    // ── POST /wp-json/zimny/v1/ads/{id}/track-click ───────────────────────────

    public function track_ad_click($request) {
        $ad_id = $request->get_param('id');
        $ads = get_option(Zimny_Admin::OPTION_ADS, array());
        if (!is_array($ads)) {
            return rest_ensure_response(array('success' => false, 'message' => 'No ads found'));
        }

        // Parse index from "ad-{index}" format
        $parts = explode('-', $ad_id);
        $index = isset($parts[1]) ? intval($parts[1]) : -1;

        if ($index < 0 || !isset($ads[$index])) {
            return rest_ensure_response(array('success' => false, 'message' => 'Ad not found'));
        }

        if (isset($ads[$index]['active']) && empty($ads[$index]['active'])) {
            return rest_ensure_response(array('success' => false, 'message' => 'Ad is inactive'));
        }

        if ($this->is_ad_tracking_rate_limited('click', $index)) {
            return rest_ensure_response(array(
                'success' => true,
                'counted' => false,
                'clicks'  => absint($ads[$index]['clicks'] ?? 0),
            ));
        }

        $ads[$index]['clicks'] = absint($ads[$index]['clicks'] ?? 0) + 1;
        update_option(Zimny_Admin::OPTION_ADS, $ads, false);

        return rest_ensure_response(array(
            'success' => true,
            'counted' => true,
            'clicks'  => $ads[$index]['clicks'],
        ));
    }

    // ── GET /wp-json/zimny/v1/anuncie-cards ───────────────────────────────────

    public function get_anuncie_cards($request) {
        $lang = $request->get_param('lang') ?: 'pt';
        if (!in_array($lang, array('pt', 'en', 'es'))) {
            $lang = 'pt';
        }

        $cards = get_option(Zimny_Admin::OPTION_ANUNCIE_CARDS, array());
        if (!is_array($cards)) {
            $cards = array();
        }

        $variant_labels = array(
            1 => 'Minimal Elegance',
            2 => 'Bordered Premium',
            3 => 'Split Content',
            4 => 'Full Bleed Bold',
        );

        $result = array();
        foreach ($cards as $i => $card) {
            $image_url = $card['image_url'] ?? '';
            $image_id  = absint($card['image_id'] ?? 0);
            $variant   = absint($card['variant'] ?? 1);
            if ($variant < 1 || $variant > 4) $variant = 1;

            if (empty($image_url) && $image_id > 0) {
                $image_url = wp_get_attachment_image_url($image_id, 'large');
            }

            $title_key = "title_{$lang}";
            $desc_key  = "description_{$lang}";
            $cta_key   = "cta_text_{$lang}";

            $result[] = array(
                'id'          => 'anuncie-' . $i,
                'image_url'   => $image_url ?: '',
                'image_id'    => $image_id,
                'variant'     => $variant,
                'variant_label' => $variant_labels[$variant] ?? 'Variant ' . $variant,
                'title'       => sanitize_text_field($card[$title_key] ?? $card['title_pt'] ?? $card['title'] ?? ''),
                'description' => sanitize_textarea_field($card[$desc_key] ?? $card['description_pt'] ?? $card['description'] ?? ''),
                'cta_text'    => sanitize_text_field($card[$cta_key] ?? $card['cta_text_pt'] ?? $card['cta_text'] ?? ''),
                'cta_link'    => esc_url_raw($card['cta_link'] ?? ''),
            );
        }

        return rest_ensure_response($result);
    }

    // ── GET /wp-json/zimny/v1/colunistas-config ──────────────────────────────

    public function get_colunistas_config($request) {
        $lang = $request->get_param('lang') ?: 'pt';
        if (!in_array($lang, array('pt', 'en', 'es'))) {
            $lang = 'pt';
        }

        $config = get_option(Zimny_Admin::OPTION_COLUNISTAS_CONFIG, array(
            'day_assignments' => array(),
            'column_names' => array(),
        ));
        if (!is_array($config)) {
            $config = array('day_assignments' => array(), 'column_names' => array());
        }

        $day_assignments = $config['day_assignments'] ?? array();
        $column_names = $config['column_names'] ?? array();

        // Calculate today's columnist
        $today_data = null;
        $current_day = (int) date('N'); // 1 (Monday) - 7 (Sunday)

        if (isset($day_assignments[$current_day])) {
            $today_user_id = absint($day_assignments[$current_day]);
            $today_user = get_userdata($today_user_id);

            if ($today_user && in_array('author', $today_user->roles, true)) {
                // Build colunista object (same structure as zimny-colunistas plugin)
                $image_id = (int) get_user_meta($today_user_id, 'colunista_image_id', true);
                $avatar_url = '';
                if ($image_id) {
                    $avatar_url = wp_get_attachment_image_url($image_id, 'medium');
                    if (!$avatar_url) $avatar_url = wp_get_attachment_image_url($image_id, 'large');
                    if (!$avatar_url) $avatar_url = wp_get_attachment_image_url($image_id, 'full');
                }
                if (empty($avatar_url)) {
                    $avatar_data = get_avatar_data($today_user_id, array('size' => 256));
                    $avatar_url = $avatar_data['url'] ?? '';
                    if (empty($avatar_url)) {
                        $name = urlencode($today_user->display_name);
                        $avatar_url = "https://ui-avatars.com/api/?name={$name}&background=1C1C1E&color=FFFFFF&size=256";
                    }
                }

                $bg_image_id = (int) get_user_meta($today_user_id, 'colunista_bg_image_id', true);
                $bg_image_url = '';
                if ($bg_image_id) {
                    $bg_image_url = wp_get_attachment_image_url($bg_image_id, 'large');
                    if (!$bg_image_url) $bg_image_url = wp_get_attachment_image_url($bg_image_id, 'full');
                }

                $profile_image_id = (int) get_user_meta($today_user_id, 'colunista_profile_image_id', true);
                $profile_image_url = '';
                if ($profile_image_id) {
                    $profile_image_url = wp_get_attachment_image_url($profile_image_id, 'large');
                    if (!$profile_image_url) $profile_image_url = wp_get_attachment_image_url($profile_image_id, 'full');
                }

                $clickable_meta = get_user_meta($today_user_id, 'colunista_clickable', true);
                $clickable = $clickable_meta === '' ? true : ($clickable_meta === '1');

                $today_data = array(
                    'day_number' => $current_day,
                    'colunista_id' => $today_user_id,
                    'colunista' => array(
                        'id'          => $today_user_id,
                        'name'        => $today_user->display_name,
                        'slug'        => $today_user->user_nicename,
                        'description' => get_user_meta($today_user_id, 'description', true) ?: '',
                        'avatar_url'       => $avatar_url,
                        'bg_image_url'     => $bg_image_url,
                        'profile_image_url' => $profile_image_url,
                        'order'       => (int) get_user_meta($today_user_id, 'colunista_order', true),
                        'visible'     => true,
                        'clickable'   => $clickable,
                        'post_count'  => (int) count_user_posts($today_user_id),
                    ),
                );
            }
        }

        $column_colors = $config['column_colors'] ?? array();
        $instagram_handles = $config['instagram_handles'] ?? array();

        // Resolve column names in the requested language
        $column_names_lang = array();
        foreach ($column_names as $day => $names) {
            if (is_array($names)) {
                // New format: names[$lang] = string
                $column_names_lang[$day] = $names[$lang] ?? $names['pt'] ?? '';
            } else {
                // Old format: single string (PT)
                $column_names_lang[$day] = $names;
            }
        }

        $response = array(
            'day_assignments' => $day_assignments,
            'column_names' => $column_names_lang,
            'column_colors' => $column_colors,
            'instagram_handles' => $instagram_handles,
            'today' => $today_data,
        );

        return rest_ensure_response($response);
    }

    // ── GET /wp-json/zimny/v1/coberturas ──────────────────────────────────────

    public function get_coberturas($request) {
        $home     = $request->get_param('home');
        $approved = $request->get_param('approved');
        $limit    = $request->get_param('limit') ? intval($request->get_param('limit')) : 50;

        $args = array(
            'post_type'      => 'zimny_cobertura',
            'posts_per_page' => $limit,
            'post_status'    => 'publish',
            'orderby'        => 'meta_value_num',
            'meta_key'       => '_zimny_cobertura_order',
            'order'          => 'ASC',
        );

        // Build meta query
        $meta_query = array();

        // Filter by approval status (default: only approved)
        if ($approved !== false) {
            $meta_query[] = array(
                'key'   => '_zimny_cobertura_approved',
                'value' => '1',
            );
        }

        if ($home) {
            $meta_query[] = array(
                'key'   => '_zimny_cobertura_show_on_home',
                'value' => '1',
            );
            // For home, order by home_order
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = '_zimny_cobertura_home_order';
        }

        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }

        $query = new WP_Query($args);
        $results = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                $media_json = get_post_meta($post_id, '_zimny_cobertura_media', true);
                $media = $media_json ? json_decode($media_json, true) : array();
                if (!is_array($media)) $media = array();

                // Find featured media for thumbnail
                $featured_media = null;
                foreach ($media as $item) {
                    if (!empty($item['is_featured'])) {
                        $featured_media = $item;
                        break;
                    }
                }
                // If no featured, use first item
                if (!$featured_media && !empty($media)) {
                    $featured_media = $media[0];
                }

                // Get WordPress featured image as fallback
                $thumb_id  = get_post_thumbnail_id($post_id);
                $thumb_src = $thumb_id ? wp_get_attachment_image_src($thumb_id, 'full') : null;
                $thumb_url = $thumb_src ? $thumb_src[0] : '';
                $thumb_w   = $thumb_src ? $thumb_src[1] : 0;
                $thumb_h   = $thumb_src ? $thumb_src[2] : 0;

                // Use featured media thumbnail if available
                $cobertura_thumb = $featured_media['thumbnail'] ?? $featured_media['url'] ?? $thumb_url;

                $results[] = array(
                    'id'                => (string) $post_id,
                    'title'             => get_the_title(),
                    'content'           => get_the_content(),
                    'excerpt'           => get_the_excerpt(),
                    'thumbnail_url'     => $cobertura_thumb,
                    'thumbnail_width'   => $thumb_w,
                    'thumbnail_height'  => $thumb_h,
                    'featured_media'    => $featured_media,
                    'media_count'       => count($media),
                    'show_on_home'      => (bool) get_post_meta($post_id, '_zimny_cobertura_show_on_home', true),
                    'home_order'        => (int) get_post_meta($post_id, '_zimny_cobertura_home_order', true),
                    'order'             => (int) get_post_meta($post_id, '_zimny_cobertura_order', true),
                    'is_approved'       => (bool) get_post_meta($post_id, '_zimny_cobertura_approved', true),
                    'date'              => get_the_date('Y-m-d', $post_id),
                    'created_at'        => get_the_date('c'),
                );
            }
            wp_reset_postdata();
        }

        return rest_ensure_response($results);
    }

    // ── GET /wp-json/zimny/v1/coberturas/{id}/media ───────────────────────────

    public function get_cobertura_media($request) {
        $post_id = $request->get_param('id');
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'zimny_cobertura' || $post->post_status !== 'publish') {
            return new WP_Error('not_found', 'Cobertura não encontrada.', array('status' => 404));
        }

        $media_json = get_post_meta($post_id, '_zimny_cobertura_media', true);
        $media = $media_json ? json_decode($media_json, true) : array();

        if (!is_array($media)) {
            $media = array();
        }

        return rest_ensure_response($media);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // TRADUÇÃO DE POSTS (Google Translate API no servidor)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * GET /wp-json/zimny/v1/posts/untranslated?limit=10&after_id=0
     *
     * Retorna posts publicados que ainda não possuem tradução completa (en E es).
     * Uso administrativo/servidor; o aplicativo apenas lê as traduções salvas.
     */
    public function get_untranslated_posts($request) {
        $limit   = absint($request->get_param('limit') ?: 10);
        $after_id = absint($request->get_param('after_id') ?: 0);
        if ($limit < 1) $limit = 10;
        if ($limit > 50) $limit = 50;

        $args = array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        );

        if ($after_id > 0) {
            $args['meta_query'] = array(
                array(
                    'key'     => '_zimny_translated',
                    'compare' => 'NOT EXISTS',
                ),
            );
            $args['date_query'] = null;
            // Continue from the last post ID (avoids re-processing same posts)
            $args['post__not_in'] = array($after_id);
        }

        $query = new WP_Query($args);
        $results = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                // Skip posts already fully translated (both en AND es)
                $has_en = get_post_meta($post_id, '_zimny_title_en', true) !== '';
                $has_es = get_post_meta($post_id, '_zimny_title_es', true) !== '';
                if ($has_en && $has_es) continue;

                $results[] = array(
                    'id'      => (string) $post_id,
                    'title'   => get_the_title(),
                    'excerpt' => wp_strip_all_tags(get_the_excerpt()),
                    'content' => wp_strip_all_tags(get_the_content()),
                );
            }
            wp_reset_postdata();
        }

        return rest_ensure_response(array(
            'posts' => $results,
            'total' => count($results),
        ));
    }

    /** Somente administradores podem ler a fila ou gravar traduções. */
    public function can_manage_translations() {
        return current_user_can('manage_options');
    }

    /**
     * Sanitize callback para o payload de traduções.
     */
    public function sanitize_translations($value) {
        if (!is_array($value)) {
            return array();
        }
        $clean = array();
        foreach ($value as $item) {
            if (!is_array($item) || empty($item['post_id'])) continue;
            $post_id = absint($item['post_id']);
            if ($post_id <= 0) continue;

            $clean[] = array(
                'post_id'         => $post_id,
                'title_en'        => sanitize_text_field($item['title_en'] ?? ''),
                'title_es'        => sanitize_text_field($item['title_es'] ?? ''),
                'excerpt_en'      => sanitize_textarea_field($item['excerpt_en'] ?? ''),
                'excerpt_es'      => sanitize_textarea_field($item['excerpt_es'] ?? ''),
                'content_en'      => wp_kses_post($item['content_en'] ?? ''),
                'content_es'      => wp_kses_post($item['content_es'] ?? ''),
            );
        }
        return $clean;
    }

    /**
     * POST /wp-json/zimny/v1/posts/translations
     *
     * Salva traduções administrativas em post meta.
     * Payload: { "translations": [ { post_id, title_en, title_es, excerpt_en, excerpt_es, content_en, content_es } ] }
     */
    public function save_post_translations($request) {
        $translations = $request->get_param('translations');
        if (!is_array($translations) || empty($translations)) {
            return new WP_Error('invalid_translations', 'Nenhuma tradução enviada.', array('status' => 400));
        }

        $saved = 0;
        foreach ($translations as $t) {
            $post_id = absint($t['post_id'] ?? 0);
            if ($post_id <= 0) continue;
            $post = get_post($post_id);
            if (!$post || $post->post_type !== 'post') continue;

            update_post_meta($post_id, '_zimny_title_en',    $t['title_en'] ?? '');
            update_post_meta($post_id, '_zimny_title_es',    $t['title_es'] ?? '');
            update_post_meta($post_id, '_zimny_excerpt_en',  $t['excerpt_en'] ?? '');
            update_post_meta($post_id, '_zimny_excerpt_es',  $t['excerpt_es'] ?? '');
            update_post_meta($post_id, '_zimny_content_en',  $t['content_en'] ?? '');
            update_post_meta($post_id, '_zimny_content_es',  $t['content_es'] ?? '');
            update_post_meta($post_id, '_zimny_translated',  time());
            $saved++;
        }

        return rest_ensure_response(array(
            'success' => true,
            'saved'   => $saved,
        ));
    }

    /**
     * POST /wp-json/zimny/v1/posts/translate-trigger
     *
     * Dispara a tradução server-side de N posts (EN + ES juntos).
     * A tradução usa a cota/configuração do servidor e é guardada no post meta.
     */
    public function trigger_translation($request) {
        $limit = absint($request->get_param('limit') ?: 5);
        $result = $this->admin->translator->translate_batch($limit);

        return rest_ensure_response(array(
            'success'    => true,
            'translated' => $result['translated'],
            'skipped'    => $result['skipped'],
            'remaining'  => $result['remaining'],
        ));
    }

    /**
     * Aplica a tradução de um post (título + excerto + conteúdo) conforme o idioma.
     * Fallback: retorna o conteúdo original em português.
     */
    public static function translate_post_fields(array $post_data, $post_id, $lang) {
        if (!in_array($lang, array('en', 'es'))) {
            return $post_data;
        }

        $title   = get_post_meta($post_id, "_zimny_title_{$lang}", true);
        $excerpt = get_post_meta($post_id, "_zimny_excerpt_{$lang}", true);
        $content = get_post_meta($post_id, "_zimny_content_{$lang}", true);

        if ($title !== '') {
            $post_data['title'] = $title;
        }
        if ($excerpt !== '' && isset($post_data['excerpt'])) {
            $post_data['excerpt'] = $excerpt;
        }
        if ($content !== '' && isset($post_data['content'])) {
            $post_data['content'] = $content;
        }

        return $post_data;
    }
}
