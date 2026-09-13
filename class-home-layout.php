<?php
/**
 * Zimny_Admin_Home_Layout - Página "Layout da Home" do painel
 *
 * Contém o construtor visual do layout, seções nativas, splash player,
 * vídeos em destaque, eventos em destaque, planos de marketing e
 * configuração dos colunistas.
 *
 * @package Zimny_Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Zimny_Admin_Home_Layout {

    /**
     * Retorna as seções nativas (built-in) disponíveis.
     */
    public function get_built_in_sections(): array {
        return array(
            'hero_banner' => array(
                'type'        => 'hero_banner',
                'slug'        => 'hero_banner',
                'title'       => 'Hero Banner (Edição mais recente)',
                'description' => 'Banner principal com a capa da edição mais recente.',
                'icon'        => 'dashicons-format-image',
            ),
            'acervo_carousel' => array(
                'type'        => 'acervo_carousel',
                'slug'        => 'acervo_carousel',
                'title'       => 'Acervo (Edições anteriores)',
                'description' => 'Carrossel horizontal com as edições anteriores.',
                'icon'        => 'dashicons-book-alt',
            ),
            'colunistas_carousel' => array(
                'type'        => 'colunistas_carousel',
                'slug'        => 'colunistas_carousel',
                'title'       => 'Colunistas',
                'description' => 'Carrossel de fotos dos colunistas da Zimny.',
                'icon'        => 'dashicons-groups',
            ),
            'ad_block' => array(
                'type'        => 'ad_block',
                'slug'        => 'ad_block',
                'title'       => 'Bloco de Publicidade (Premium Ad)',
                'description' => 'Espaço para anúncio patrocinado premium.',
                'icon'        => 'dashicons-megaphone',
            ),
            'journal_section' => array(
                'type'        => 'journal_section',
                'slug'        => 'journal_section',
                'title'       => 'Journal (Artigos recomendados)',
                'description' => 'Seção de artigos recomendados do magazine.',
                'icon'        => 'dashicons-welcome-write-blog',
            ),
            'instagram_feed' => array(
                'type'        => 'instagram_feed',
                'slug'        => 'instagram_feed',
                'title'       => 'Feed do Instagram',
                'description' => 'Últimos posts do perfil @zimnymagazine.',
                'icon'        => 'dashicons-instagram',
            ),
            'home_featured_videos' => array(
                'type'        => 'home_featured_videos',
                'slug'        => 'home_featured_videos',
                'title'       => 'Vídeos em Destaque (Home)',
                'description' => 'Carrossel com os vídeos selecionados manualmente como "Destaque da Home".',
                'icon'        => 'dashicons-star-filled',
            ),
            'events_carousel' => array(
                'type'        => 'events_carousel',
                'slug'        => 'events_carousel',
                'title'       => 'Carrossel de Eventos',
                'description' => 'Carrossel horizontal com thumbs dos eventos selecionados para a Home.',
                'icon'        => 'dashicons-calendar-alt',
            ),
            'events_more_button' => array(
                'type'        => 'events_more_button',
                'slug'        => 'events_more_button',
                'title'       => 'Mais Eventos (botão)',
                'description' => 'Botão "MAIS EVENTOS" que leva para a lista completa de eventos.',
                'icon'        => 'dashicons-arrow-right-alt2',
            ),
            'marketing_plans_carousel' => array(
                'type'        => 'marketing_plans_carousel',
                'slug'        => 'marketing_plans_carousel',
                'title'       => 'Planos de Marketing Digital',
                'description' => 'Carrossel 1:1 com imagens dos planos de marketing digital. Gerencie as imagens na seção "Planos de Marketing" abaixo.',
                'icon'        => 'dashicons-chart-area',
            ),
            'colunista_dia' => array(
                'type'        => 'colunista_dia',
                'slug'        => 'colunista_dia',
                'title'       => 'Coluna do Dia',
                'description' => 'Colunista em destaque baseado no dia da semana. Configure os dias na seção "Configuração dos Colunistas" abaixo.',
                'icon'        => 'dashicons-star-filled',
            ),
            'anuncie_card_v1' => array(
                'type'        => 'anuncie_card_v1',
                'slug'        => 'anuncie_card_v1',
                'title'       => '◆ Anuncie Conosco — Minimal Elegance',
                'description' => 'Variante 1: Card clean com serif, imagem com sombra e CTA com underline animado.',
                'icon'        => 'dashicons-megaphone',
            ),
            'anuncie_card_v2' => array(
                'type'        => 'anuncie_card_v2',
                'slug'        => 'anuncie_card_v2',
                'title'       => '◇ Anuncie Conosco — Bordered Premium',
                'description' => 'Variante 2: Card com borda dupla pulsante, imagem circular e seta animada no CTA.',
                'icon'        => 'dashicons-megaphone',
            ),
            'anuncie_card_v3' => array(
                'type'        => 'anuncie_card_v3',
                'slug'        => 'anuncie_card_v3',
                'title'       => '◈ Anuncie Conosco — Split Content',
                'description' => 'Variante 3: Card com layout dividido, barra decorativa e CTA com efeito shine.',
                'icon'        => 'dashicons-megaphone',
            ),
            'anuncie_card_v4' => array(
                'type'        => 'anuncie_card_v4',
                'slug'        => 'anuncie_card_v4',
                'title'       => '⬟ Anuncie Conosco — Full Bleed Bold',
                'description' => 'Variante 4: Card full-width com gradiente, imagem de fundo e CTA com glow pulsante.',
                'icon'        => 'dashicons-megaphone',
            ),
            'podcast_grid' => array(
                'type'        => 'podcast_grid',
                'slug'        => 'podcast_grid',
                'title'       => 'Podcast (Grid 2×2)',
                'description' => 'Grid 2×2 com os 4 podcasts mais recentes (Zimny Play).',
                'icon'        => 'dashicons-microphone',
            ),
            'cobertura_grid' => array(
                'type'        => 'cobertura_grid',
                'slug'        => 'cobertura_grid',
                'title'       => 'Cobertura (Grid 3×2)',
                'description' => 'Grid 3×2 com as coberturas de eventos aprovadas.',
                'icon'        => 'dashicons-camera',
            ),
            'eventos_grid' => array(
                'type'        => 'eventos_grid',
                'slug'        => 'eventos_grid',
                'title'       => 'Eventos (Grid 3×2)',
                'description' => 'Grid 3×2 com os eventos de produção mais recentes.',
                'icon'        => 'dashicons-calendar-alt',
            ),
        );
    }

    /**
     * Monta o layout padrão (seções nativas + carrosséis).
     */
    public function get_default_layout(): array {
        $layout = array();

        $built_in = $this->get_built_in_sections();
        foreach ($built_in as $slug => $section) {
            // ad_block is added manually via the dedicated button — not in the default layout
            if ($slug === 'ad_block') continue;
            $layout[] = array(
                'type'    => $section['type'],
                'slug'    => $section['slug'],
                'title'   => $section['title'],
                'visible' => true,
            );
        }

        $terms = get_terms(array(
            'taxonomy'   => 'video_carousel',
            'hide_empty' => false,
        ));
        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $layout[] = array(
                    'type'    => 'video_carousel',
                    'slug'    => $term->slug,
                    'term_id' => $term->term_id,
                    'title'   => $term->name,
                    'visible' => true,
                );
            }
        }

        return $layout;
    }

    /**
     * Retorna o layout salvo (ou o padrão).
     */
    public function get_layout(): array {
        $saved = get_option(Zimny_Admin::OPTION_LAYOUT, null);
        if ($saved !== null && is_array($saved) && !empty($saved)) {
            return $saved;
        }
        // Fallback para opção antiga
        $old = get_option('zimny_play_home_layout', null);
        if ($old !== null && is_array($old) && !empty($old)) {
            return $old;
        }
        return $this->get_default_layout();
    }

    /**
     * CSS inline da página de layout.
     */
    public function get_home_layout_css(): string {
        return '
        .zimny-layout-wrap { max-width: 960px; }
        .zimny-layout-wrap h1 { display:flex; align-items:center; gap:10px; margin-bottom:24px; }
        .zimny-layout-sections { margin-top:20px; }
        .zimny-layout-section {
            display:flex; align-items:center; gap:12px;
            background:#fff; border:1px solid #dcdcde;
            padding:14px 16px; margin-bottom:8px;
            border-radius:6px; cursor:grab;
            transition:box-shadow 0.2s, border-color 0.2s;
            user-select:none;
        }
        .zimny-layout-section:hover { border-color:#2271b1; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
        .zimny-layout-section.dragging { opacity:0.5; border-style:dashed; }
        .zimny-layout-section.drag-over { border-color:#2271b1; border-style:dashed; background:#f0f6fc; }
        .zimny-layout-section .handle { color:#8c8f94; font-size:20px; cursor:grab; flex-shrink:0; }
        .zimny-layout-section .icon { color:#2271b1; font-size:24px; width:24px; height:24px; flex-shrink:0; }
        .zimny-layout-section .info { flex:1; min-width:0; }
        .zimny-layout-section .info .title-input { font-weight:600; font-size:14px; width:100%; padding:4px 6px; border:1px solid #dcdcde; border-radius:4px; background:#fff; }
        .zimny-layout-section .info .title-input:focus { border-color:#2271b1; box-shadow:0 0 0 1px #2271b1; outline:none; }
        .zimny-layout-section .info .desc { color:#646970; font-size:12px; margin-top:2px; }
        .zimny-layout-section .info .badge {
            display:inline-block; padding:1px 8px; border-radius:3px;
            font-size:10px; font-weight:600; text-transform:uppercase;
            margin-top:4px;
        }
        .zimny-layout-section .info .badge.built-in { background:#e7f4e8; color:#46b450; }
        .zimny-layout-section .info .badge.carousel { background:#f0f6fc; color:#2271b1; }
        .zimny-layout-section .info .badge.ad { background:#fcf9e8; color:#996800; }
        .zimny-layout-section .info .badge.events { background:#f0e6ff; color:#7b2ff7; }
        .zimny-layout-section .info .badge.marketing { background:#fef5e7; color:#e67e22; }
        .zimny-layout-section .info .show-title-label { display:inline-flex; align-items:center; gap:4px; margin-top:4px; font-size:11px; color:#646970; cursor:pointer; user-select:none; }
        .zimny-layout-section .info .show-title-label input[type="checkbox"] { margin:0; }
        .zimny-layout-section .info .padding-controls { display:flex; gap:8px; margin-top:4px; }
        .zimny-layout-section .info .padding-controls label { display:inline-flex; align-items:center; gap:2px; font-size:10px; color:#646970; }
        .zimny-layout-section .info .padding-controls input[type="number"] { width:50px; padding:2px 4px; border:1px solid #dcdcde; border-radius:3px; font-size:11px; text-align:center; }
        .zimny-layout-section .info .padding-controls input[type="number"]:focus { border-color:#2271b1; outline:none; }
        .zimny-layout-section .visibility-toggle {
            flex-shrink:0; width:36px; height:36px; border-radius:50%;
            border:1px solid #dcdcde; background:#fff;
            display:flex; align-items:center; justify-content:center;
            cursor:pointer; font-size:18px; transition:all 0.2s;
        }
        .zimny-layout-section .visibility-toggle.visible { border-color:#46b450; color:#46b450; background:#e7f4e8; }
        .zimny-layout-section .visibility-toggle.hidden { border-color:#d63638; color:#d63638; background:#fcf0f1; }
        .zimny-layout-section .remove-btn {
            flex-shrink:0; width:36px; height:36px; border-radius:50%;
            border:1px solid #dcdcde; background:#fff;
            display:flex; align-items:center; justify-content:center;
            cursor:pointer; font-size:16px; color:#d63638; transition:all 0.2s;
        }
        .zimny-layout-section .remove-btn:hover { background:#fcf0f1; border-color:#d63638; }
        .zimny-layout-actions { margin-top:24px; display:flex; gap:12px; align-items:center; }
        .zimny-layout-add { margin-top:24px; padding:20px; background:#f0f0f1; border-radius:8px; }
        .zimny-layout-add h3 { margin-top:0; margin-bottom:12px; }
        .zimny-layout-add select { font-size:14px; padding:6px; min-width:300px; }
        .zimny-layout-add .button { margin-left:8px; }
        .zimny-layout-status { display:none; padding:10px 14px; border-radius:4px; margin-bottom:16px; font-weight:600; }
        .zimny-layout-status.success { display:block; background:#e7f4e8; border-left:4px solid #46b450; color:#2c5f2d; }
        .zimny-layout-status.error { display:block; background:#fcf0f1; border-left:4px solid #d63638; color:#8a2424; }
        ';
    }

    public function render_home_layout_page() {
        $layout = $this->get_layout();
        $built_in = $this->get_built_in_sections();

        $all_carousels = get_terms(array(
            'taxonomy'   => 'video_carousel',
            'hide_empty' => false,
        ));

        $layout_carousel_ids = array();
        foreach ($layout as $item) {
            if ($item['type'] === 'video_carousel' && !empty($item['term_id'])) {
                $layout_carousel_ids[] = $item['term_id'];
            }
        }
        ?>
        <div class="wrap zimny-layout-wrap">
            <h1>
                <span class="dashicons dashicons-layout" style="font-size:32px; width:32px; height:32px;"></span>
                Layout da Home do App
            </h1>
            <p>Arraste e solte as seções abaixo para definir a ordem de exibição na Home do aplicativo Zimny Magazine.</p>

            <div id="layout-status" class="zimny-layout-status"></div>

            <div style="background:#fff; border:1px solid #dcdcde; padding:20px; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                <div id="zimny-layout-list" class="zimny-layout-sections">
                    <?php foreach ($layout as $index => $item):
                        $type = $item['type'] ?? '';
                        $slug = $item['slug'] ?? '';
                        $title = $item['title'] ?? $slug;
                        $visible = !empty($item['visible']);
                        $show_title = !isset($item['show_title']) || !empty($item['show_title']);
                        $term_id = $item['term_id'] ?? 0;

                        $badge = 'built-in';
                        $icon = 'dashicons-layout';
                        if ($type === 'video_carousel') {
                            $badge = 'carousel';
                            $icon = 'dashicons-playlist-video';
                        } elseif ($type === 'ad_block') {
                            $badge = 'ad';
                            $icon = 'dashicons-megaphone';
                        } elseif ($type === 'events_carousel') {
                            $badge = 'events';
                            $icon = 'dashicons-calendar-alt';
                        } elseif (in_array($type, array('anuncie_card_v1', 'anuncie_card_v2', 'anuncie_card_v3', 'anuncie_card_v4'))) {
                            $badge = 'ad';
                            $icon = 'dashicons-megaphone';
                        } elseif (isset($built_in[$type])) {
                            $icon = $built_in[$type]['icon'];
                        }
                    ?>
                    <div class="zimny-layout-section" draggable="true" data-index="<?php echo esc_attr($index); ?>" data-type="<?php echo esc_attr($type); ?>" data-slug="<?php echo esc_attr($slug); ?>" data-term-id="<?php echo esc_attr($term_id); ?>">
                        <span class="handle dashicons dashicons-menu"></span>
                        <span class="icon dashicons <?php echo esc_attr($icon); ?>"></span>
                        <div class="info">
                            <input type="text" class="title-input" value="<?php echo esc_attr($title); ?>" placeholder="Título da seção" />
                            <div class="desc">
                                <?php if ($type === 'video_carousel'): ?>
                                    Carrossel de vídeos · <code><?php echo esc_html($slug); ?></code>
                                <?php elseif (isset($built_in[$type])): ?>
                                    <?php echo esc_html($built_in[$type]['description']); ?>
                                <?php else: ?>
                                    Seção personalizada
                                <?php endif; ?>
                            </div>
                            <span class="badge <?php echo esc_attr($badge); ?>">
                                <?php
                                echo $type === 'video_carousel' ? 'Carrossel' : ($type === 'ad_block' ? 'Publicidade' : ($type === 'events_carousel' ? 'Eventos' : ($type === 'marketing_plans_carousel' ? 'Planos de Marketing' : ($type === 'podcast_grid' ? 'Podcast' : ($type === 'cobertura_grid' ? 'Cobertura' : ($type === 'eventos_grid' ? 'Eventos' : (in_array($type, array('anuncie_card_v1', 'anuncie_card_v2', 'anuncie_card_v3', 'anuncie_card_v4')) ? 'Anuncie Conosco' : 'Nativo')))))));
                                ?>
                            </span>
                            <label class="show-title-label" title="Mostrar título na Home">
                                <input type="checkbox" class="show-title-check" <?php echo $show_title ? 'checked' : ''; ?> />
                                <span>Título</span>
                            </label>
                            <div class="padding-controls">
                                <label title="Espaçamento superior (px)">
                                    <span>↑</span>
                                    <input type="number" class="padding-top-input" min="0" max="120" step="1" value="<?php echo isset($item['padding_top']) ? esc_attr($item['padding_top']) : 16; ?>" />
                                </label>
                                <label title="Espaçamento inferior (px)">
                                    <span>↓</span>
                                    <input type="number" class="padding-bottom-input" min="0" max="120" step="1" value="<?php echo isset($item['padding_bottom']) ? esc_attr($item['padding_bottom']) : 8; ?>" />
                                </label>
                            </div>
                        </div>
                        <button type="button" class="visibility-toggle <?php echo $visible ? 'visible' : 'hidden'; ?>" onclick="toggleVisibility(this)" title="Mostrar/Ocultar">
                            <span class="dashicons <?php echo $visible ? 'dashicons-visibility' : 'dashicons-hidden'; ?>"></span>
                        </button>
                        <button type="button" class="remove-btn" onclick="removeSection(this)" title="Remover deste layout">
                            <span class="dashicons dashicons-no-alt"></span>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="zimny-layout-actions">
                    <button type="button" class="button button-primary button-hero" onclick="saveLayout()">
                        💾 Salvar Ordem da Home
                    </button>
                    <span id="save-spinner" style="display:none;" class="spinner"></span>
                </div>
            </div>

            <!-- Adicionar seções ao layout -->
            <div style="display:flex; gap:16px; flex-wrap:wrap; margin-top:24px;">
                <!-- Adicionar Bloco de Publicidade (ilimitado) -->
                <div class="zimny-layout-add" style="flex:0 0 auto; min-width:200px; text-align:center;">
                    <h3 style="margin-top:0;">📢 Publicidade</h3>
                    <p style="font-size:12px; color:#646970;">Adicione quantos blocos de publicidade quiser</p>
                    <button type="button" class="button button-primary" onclick="addAdBlockToLayout()" style="font-size:16px; padding:12px 24px; display:inline-flex; align-items:center; gap:6px;">
                        <span class="dashicons dashicons-plus-alt2" style="font-size:18px; width:18px; height:18px;"></span>
                        Adicionar Bloco de Publicidade
                    </button>
                </div>

                <!-- Adicionar seção nativa (built-in) -->
                <div class="zimny-layout-add" style="flex:1; min-width:280px;">
                    <h3>➕ Adicionar Seção Nativa</h3>
                    <p>Adicione seções padrão como Eventos, Colunistas, etc.:</p>
                    <select id="add-builtin-select">
                        <option value="">-- Selecione uma seção --</option>
                        <?php
                        $layout_slugs = array_column($layout, 'slug');
                        foreach ($built_in as $slug => $section):
                            if ($slug === 'ad_block') continue; // ad_block has its own button
                            if (in_array($slug, $layout_slugs)) continue;
                        ?>
                        <option value="<?php echo esc_attr($slug); ?>" data-title="<?php echo esc_attr($section['title']); ?>" data-icon="<?php echo esc_attr($section['icon']); ?>">
                            <?php echo esc_html($section['title']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="button" onclick="addBuiltInSection()">Adicionar</button>
                </div>

                <!-- Adicionar carrossel de vídeo -->
                <div class="zimny-layout-add" style="flex:1; min-width:280px;">
                    <h3>➕ Adicionar Carrossel de Vídeo</h3>
                    <p>Escolha um carrossel existente para adicionar à Home:</p>
                    <select id="add-carousel-select">
                        <option value="">-- Selecione um carrossel --</option>
                        <?php if (!empty($all_carousels) && !is_wp_error($all_carousels)): ?>
                            <?php foreach ($all_carousels as $term):
                                $already_in_layout = in_array($term->term_id, $layout_carousel_ids);
                            ?>
                            <option value="<?php echo esc_attr($term->term_id); ?>" data-slug="<?php echo esc_attr($term->slug); ?>" data-name="<?php echo esc_attr($term->name); ?>" <?php echo $already_in_layout ? 'disabled' : ''; ?>>
                                <?php echo esc_html($term->name); ?> (<?php echo esc_html($term->slug); ?>) <?php echo $already_in_layout ? '— já no layout' : ''; ?>
                            </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <button type="button" class="button" onclick="addCarouselToLayout()">Adicionar</button>
                </div>
            </div>

            <!-- Visual Video Browser -->
            <div style="margin-top:32px; background:#fff; border:1px solid #dcdcde; padding:20px; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                <h2 style="margin-top:0; display:flex; align-items:center; gap:8px;">
                    <span class="dashicons dashicons-format-video"></span>
                    Visualizador de Vídeos por Carrossel
                </h2>

                <?php
                $all_terms = get_terms(array(
                    'taxonomy'   => 'video_carousel',
                    'hide_empty' => false,
                ));

                if (!empty($all_terms) && !is_wp_error($all_terms)):
                    foreach ($all_terms as $term):
                        $term_videos = get_posts(array(
                            'post_type'      => 'zimny_video',
                            'posts_per_page' => -1,
                            'post_status'    => 'publish',
                            'tax_query'      => array(
                                array(
                                    'taxonomy' => 'video_carousel',
                                    'field'    => 'term_id',
                                    'terms'    => $term->term_id,
                                ),
                            ),
                            'orderby'        => 'date',
                            'order'          => 'DESC',
                        ));

                        if (empty($term_videos)) continue;
                ?>
                <div style="margin-top:20px; padding:16px; background:#f0f6fc; border-radius:6px;">
                    <h3 style="margin:0 0 4px 0; display:flex; align-items:center; gap:8px;">
                        <span class="dashicons dashicons-playlist-video" style="color:#2271b1;"></span>
                        <?php echo esc_html($term->name); ?>
                        <code style="font-size:11px; color:#646970;"><?php echo esc_html($term->slug); ?></code>
                        <span style="font-size:12px; color:#646970; font-weight:normal;">(<?php echo count($term_videos); ?> vídeos)</span>
                    </h3>
                    <div style="display:flex; flex-wrap:wrap; gap:12px; margin-top:12px;">
                        <?php foreach ($term_videos as $vid):
                            $vid_thumb = get_the_post_thumbnail_url($vid->ID, 'thumbnail');
                            $vid_url   = get_post_meta($vid->ID, '_zimny_video_file_url', true);
                            $vid_order = get_post_meta($vid->ID, '_zimny_video_order', true);
                        ?>
                        <div style="width:160px; background:#fff; border:1px solid #dcdcde; border-radius:6px; overflow:hidden;">
                            <div style="width:160px; height:284px; background:#f0f0f1; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                                <?php if ($vid_thumb): ?>
                                    <img src="<?php echo esc_url($vid_thumb); ?>" style="width:100%; height:100%; object-fit:cover;" alt="<?php echo esc_attr($vid->post_title); ?>" />
                                <?php else: ?>
                                    <span class="dashicons dashicons-format-video" style="font-size:40px; width:40px; height:40px; color:#8c8f94;"></span>
                                <?php endif; ?>
                            </div>
                            <div style="padding:8px 10px;">
                                <div style="font-size:12px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?php echo esc_attr($vid->post_title); ?>">
                                    <?php echo esc_html($vid->post_title); ?>
                                </div>
                                <div style="font-size:10px; color:#646970; margin-top:2px;">
                                    Ordem: <strong><?php echo esc_html($vid_order ?: '0'); ?></strong>
                                    <?php if ($vid_url): ?>
                                        · <a href="<?php echo esc_url($vid_url); ?>" target="_blank" style="text-decoration:none;">MP4</a>
                                    <?php endif; ?>
                                    · <a href="<?php echo get_edit_post_link($vid->ID); ?>" target="_blank" style="text-decoration:none;">Editar</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php
                    endforeach;
                else:
                    echo '<p style="color:#8c8f94;">Nenhum carrossel ou vídeo encontrado.</p>';
                endif;
                ?>
            </div>

            <!-- Splash Player -->
            <div style="margin-top:32px; background:#fff; border:1px solid #dcdcde; padding:20px; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                <h2 style="margin-top:0; display:flex; align-items:center; gap:8px;">
                    <span class="dashicons dashicons-video-alt3"></span>
                    Splash Player (PiP na Home)
                </h2>
                <p>Selecione os vídeos que serão tocados automaticamente em uma janela flutuante (PiP) no canto inferior esquerdo do app.</p>

                <div id="splash-status" class="zimny-layout-status"></div>

                <?php
                $splash_ids = get_option(Zimny_Admin::OPTION_SPLASH_VIDEOS, array());
                if (!is_array($splash_ids)) $splash_ids = array();
                if (empty($splash_ids)) {
                    $splash_ids = get_option('zimny_play_splash_videos', array());
                    if (!is_array($splash_ids)) $splash_ids = array();
                }

                $all_videos = get_posts(array(
                    'post_type'      => 'zimny_video',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                ));

                if (!empty($all_videos)):
                ?>
                <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:16px; max-height:400px; overflow-y:auto; padding:4px;">
                    <?php foreach ($all_videos as $vid):
                        $vid_thumb = get_the_post_thumbnail_url($vid->ID, 'thumbnail');
                        $checked = in_array($vid->ID, $splash_ids) ? 'checked' : '';
                    ?>
                    <label style="width:120px; background:#f0f0f1; border:2px solid <?php echo $checked ? '#2271b1' : '#dcdcde'; ?>; border-radius:6px; overflow:hidden; cursor:pointer; transition:border-color 0.2s; opacity:<?php echo $checked ? '1' : '0.7'; ?>;" class="splash-video-label" data-post-id="<?php echo esc_attr($vid->ID); ?>">
                        <div style="width:120px; height:213px; background:#f0f0f1; display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative;">
                            <?php if ($vid_thumb): ?>
                                <img src="<?php echo esc_url($vid_thumb); ?>" style="width:100%; height:100%; object-fit:cover;" />
                            <?php else: ?>
                                <span class="dashicons dashicons-format-video" style="font-size:30px; width:30px; height:30px; color:#8c8f94;"></span>
                            <?php endif; ?>
                            <div style="position:absolute; top:4px; right:4px; width:20px; height:20px; background:<?php echo $checked ? '#2271b1' : '#fff'; ?>; border:2px solid <?php echo $checked ? '#2271b1' : '#8c8f94'; ?>; border-radius:3px; display:flex; align-items:center; justify-content:center;">
                                <?php if ($checked): ?>
                                    <span class="dashicons dashicons-yes" style="font-size:14px; width:14px; height:14px; color:#fff;"></span>
                                <?php endif; ?>
                            </div>
                            <input type="checkbox" class="splash-video-checkbox" value="<?php echo esc_attr($vid->ID); ?>" <?php echo $checked; ?> style="position:absolute; opacity:0; width:0; height:0;" />
                        </div>
                        <div style="padding:6px 8px; font-size:11px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; text-align:center;">
                            <?php echo esc_html($vid->post_title); ?>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p style="color:#8c8f94;">Nenhum vídeo encontrado.</p>
                <?php endif; ?>

                <div style="margin-top:16px; display:flex; gap:8px; align-items:center;">
                    <button type="button" class="button button-primary" onclick="saveSplashVideos()">
                        💾 Salvar Vídeos do Splash Player
                    </button>
                    <button type="button" class="button" onclick="toggleAllSplashVideos(true)">Selecionar Todos</button>
                    <button type="button" class="button" onclick="toggleAllSplashVideos(false)">Limpar</button>
                    <span id="splash-spinner" style="display:none;" class="spinner"></span>
                </div>
            </div>

            <!-- Home Featured Videos -->
            <div style="margin-top:32px; background:#fff; border:1px solid #dcdcde; padding:20px; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                <h2 style="margin-top:0; display:flex; align-items:center; gap:8px;">
                    <span class="dashicons dashicons-star-filled" style="color:#f5a623;"></span>
                    Vídeos em Destaque da Home
                </h2>

                <div id="home-featured-status" class="zimny-layout-status"></div>

                <div style="margin-bottom:16px; display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                    <label style="font-weight:600; font-size:13px;">Filtrar por carrossel:</label>
                    <select id="home-featured-filter" onchange="filterHomeFeaturedVideos()" style="font-size:13px; padding:4px; min-width:200px;">
                        <option value="">— Todos os carrosséis —</option>
                        <?php
                        $filter_terms = get_terms(array('taxonomy' => 'video_carousel', 'hide_empty' => false));
                        if (!empty($filter_terms) && !is_wp_error($filter_terms)):
                            foreach ($filter_terms as $ft):
                                echo '<option value="' . esc_attr($ft->slug) . '">' . esc_html($ft->name) . ' (' . esc_html($ft->slug) . ')</option>';
                            endforeach;
                        endif;
                        ?>
                    </select>
                    <span id="home-featured-count" style="font-size:12px; color:#646970; background:#f0f0f1; padding:2px 10px; border-radius:10px;"></span>
                </div>

                <?php
                $home_featured_ids = get_option(Zimny_Admin::OPTION_HOME_FEATURED_VIDEOS, array());
                if (!is_array($home_featured_ids)) $home_featured_ids = array();
                if (empty($home_featured_ids)) {
                    $home_featured_ids = get_option('zimny_play_home_featured_videos', array());
                    if (!is_array($home_featured_ids)) $home_featured_ids = array();
                }

                $all_home_videos = get_posts(array(
                    'post_type'      => 'zimny_video',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'orderby'        => 'meta_value_num',
                    'meta_key'       => '_zimny_video_order',
                    'order'          => 'ASC',
                ));

                if (!empty($all_home_videos)):
                ?>
                <div id="home-featured-grid" style="display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; max-height:500px; overflow-y:auto; padding:4px;">
                    <?php foreach ($all_home_videos as $vid):
                        $vid_thumb  = get_the_post_thumbnail_url($vid->ID, 'thumbnail');
                        $vid_order  = get_post_meta($vid->ID, '_zimny_video_order', true);
                        $vid_carousels = wp_get_post_terms($vid->ID, 'video_carousel', array('fields' => 'slugs'));
                        $checked    = in_array($vid->ID, $home_featured_ids) ? 'checked' : '';
                        $carousel_badge = !empty($vid_carousels) ? esc_html(implode(', ', $vid_carousels)) : 'sem carrossel';
                    ?>
                    <label class="home-featured-label" data-post-id="<?php echo esc_attr($vid->ID); ?>" data-carousels="<?php echo esc_attr(implode(',', $vid_carousels)); ?>" style="width:120px; background:#f0f0f1; border:2px solid <?php echo $checked ? '#f5a623' : '#dcdcde'; ?>; border-radius:6px; overflow:hidden; cursor:pointer; transition:border-color 0.2s; opacity:<?php echo $checked ? '1' : '0.7'; ?>;">
                        <div style="width:120px; height:213px; background:#f0f0f1; display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative;">
                            <?php if ($vid_thumb): ?>
                                <img src="<?php echo esc_url($vid_thumb); ?>" style="width:100%; height:100%; object-fit:cover;" />
                            <?php else: ?>
                                <span class="dashicons dashicons-format-video" style="font-size:30px; width:30px; height:30px; color:#8c8f94;"></span>
                            <?php endif; ?>
                            <div style="position:absolute; top:4px; right:4px; width:20px; height:20px; background:<?php echo $checked ? '#f5a623' : '#fff'; ?>; border:2px solid <?php echo $checked ? '#f5a623' : '#8c8f94'; ?>; border-radius:3px; display:flex; align-items:center; justify-content:center;">
                                <?php if ($checked): ?>
                                    <span class="dashicons dashicons-yes" style="font-size:14px; width:14px; height:14px; color:#fff;"></span>
                                <?php endif; ?>
                            </div>
                            <input type="checkbox" class="home-featured-checkbox" value="<?php echo esc_attr($vid->ID); ?>" <?php echo $checked; ?> style="position:absolute; opacity:0; width:0; height:0;" />
                            <div style="position:absolute; bottom:4px; left:4px; background:rgba(0,0,0,0.7); color:#fff; font-size:9px; font-weight:700; padding:1px 6px; border-radius:3px;">
                                <?php echo esc_html($vid_order ?: '0'); ?>
                            </div>
                        </div>
                        <div style="padding:6px 8px;">
                            <div style="font-size:10px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?php echo esc_attr($vid->post_title); ?>">
                                <?php echo esc_html($vid->post_title); ?>
                            </div>
                            <div style="font-size:8px; color:#8c8f94; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                <?php echo $carousel_badge; ?>
                            </div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p style="color:#8c8f94;">Nenhum vídeo encontrado.</p>
                <?php endif; ?>

                <div style="margin-top:16px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <button type="button" class="button button-primary" onclick="saveHomeFeaturedVideos()">
                        💾 Salvar Vídeos em Destaque
                    </button>
                    <button type="button" class="button" onclick="toggleAllHomeFeatured(true)">Selecionar Todos</button>
                    <button type="button" class="button" onclick="toggleAllHomeFeatured(false)">Limpar</button>
                    <span id="home-featured-spinner" style="display:none;" class="spinner"></span>
                </div>
            </div>

            <!-- Home Events Carousel Selector -->
            <div style="margin-top:32px; background:#fff; border:1px solid #dcdcde; padding:20px; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                <h2 style="margin-top:0; display:flex; align-items:center; gap:8px;">
                    <span class="dashicons dashicons-calendar-alt" style="color:#7b2ff7;"></span>
                    Eventos em Destaque na Home
                </h2>
                <p>Selecione os eventos que aparecerão no carrossel <strong>"Carrossel de Eventos"</strong> da Home. A ordem segue o campo "Ordem na Home" de cada evento.</p>

                <div id="home-events-status" class="zimny-layout-status"></div>

                <?php
                $home_event_ids = get_option(Zimny_Admin::OPTION_HOME_EVENTS, array());
                if (!is_array($home_event_ids)) $home_event_ids = array();

                $all_events = get_posts(array(
                    'post_type'      => 'zimny_event',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'orderby'        => 'meta_value_num',
                    'meta_key'       => '_zimny_event_order',
                    'order'          => 'ASC',
                ));

                if (!empty($all_events)):
                ?>
                <div id="home-events-grid" style="display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; max-height:500px; overflow-y:auto; padding:4px;">
                    <?php foreach ($all_events as $ev):
                        $ev_thumb  = get_the_post_thumbnail_url($ev->ID, 'thumbnail');
                        $ev_cat    = get_post_meta($ev->ID, '_zimny_event_category', true);
                        $ev_order  = get_post_meta($ev->ID, '_zimny_event_home_order', true);
                        $checked   = in_array($ev->ID, $home_event_ids) ? 'checked' : '';
                        $cat_label = $ev_cat === 'producao' ? 'Produção' : ($ev_cat === 'cobertura' ? 'Cobertura' : '');
                    ?>
                    <label class="home-event-label" data-post-id="<?php echo esc_attr($ev->ID); ?>" style="width:140px; background:#f0f0f1; border:2px solid <?php echo $checked ? '#7b2ff7' : '#dcdcde'; ?>; border-radius:6px; overflow:hidden; cursor:pointer; transition:border-color 0.2s; opacity:<?php echo $checked ? '1' : '0.7'; ?>;">
                        <div style="width:140px; height:140px; background:#f0f0f1; display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative;">
                            <?php if ($ev_thumb): ?>
                                <img src="<?php echo esc_url($ev_thumb); ?>" style="width:100%; height:100%; object-fit:cover;" />
                            <?php else: ?>
                                <span class="dashicons dashicons-calendar-alt" style="font-size:40px; width:40px; height:40px; color:#8c8f94;"></span>
                            <?php endif; ?>
                            <div style="position:absolute; top:4px; right:4px; width:20px; height:20px; background:<?php echo $checked ? '#7b2ff7' : '#fff'; ?>; border:2px solid <?php echo $checked ? '#7b2ff7' : '#8c8f94'; ?>; border-radius:3px; display:flex; align-items:center; justify-content:center;">
                                <?php if ($checked): ?>
                                    <span class="dashicons dashicons-yes" style="font-size:14px; width:14px; height:14px; color:#fff;"></span>
                                <?php endif; ?>
                            </div>
                            <input type="checkbox" class="home-event-checkbox" value="<?php echo esc_attr($ev->ID); ?>" <?php echo $checked; ?> style="position:absolute; opacity:0; width:0; height:0;" />
                            <?php if ($cat_label): ?>
                            <div style="position:absolute; bottom:4px; left:4px; background:rgba(0,0,0,0.7); color:#fff; font-size:8px; font-weight:700; padding:2px 6px; border-radius:3px; text-transform:uppercase; letter-spacing:0.5px;">
                                <?php echo $cat_label; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div style="padding:6px 8px;">
                            <div style="font-size:10px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?php echo esc_attr($ev->post_title); ?>">
                                <?php echo esc_html($ev->post_title); ?>
                            </div>
                            <div style="font-size:8px; color:#8c8f94; margin-top:2px;">
                                Ordem na Home: <strong><?php echo esc_html($ev_order ?: '0'); ?></strong>
                            </div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p style="color:#8c8f94;">Nenhum evento encontrado. Crie eventos primeiro.</p>
                <?php endif; ?>

                <div style="margin-top:16px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <button type="button" class="button button-primary" onclick="saveHomeEvents()">
                        💾 Salvar Eventos em Destaque
                    </button>
                    <button type="button" class="button" onclick="toggleAllHomeEvents(true)">Selecionar Todos</button>
                    <button type="button" class="button" onclick="toggleAllHomeEvents(false)">Limpar</button>
                    <span id="home-events-spinner" style="display:none;" class="spinner"></span>
                </div>
            </div>

            <!-- Marketing Plans Carousel -->
            <div style="margin-top:32px; background:#fff; border:1px solid #dcdcde; padding:20px; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                <h2 style="margin-top:0; display:flex; align-items:center; gap:8px;">
                    <span class="dashicons dashicons-chart-area" style="color:#e67e22;"></span>
                    Planos de Marketing Digital
                </h2>
                <p>Gerencie as imagens (proporção ~1:1) que aparecerão no carrossel de planos de marketing na Home. Arraste para reordenar.</p>

                <div id="marketing-plans-status" class="zimny-layout-status"></div>

                <div id="marketing-plans-list" style="display:flex; flex-direction:column; gap:8px; margin-top:12px; min-height:60px; padding:8px; background:#f0f0f1; border:2px dashed #dcdcde; border-radius:6px;">
                    <?php
                    $marketing_plans = get_option(Zimny_Admin::OPTION_MARKETING_PLANS, array());
                    if (!is_array($marketing_plans)) $marketing_plans = array();

                    if (!empty($marketing_plans)):
                        foreach ($marketing_plans as $i => $plan):
                            $img_url = $plan['image_url'] ?? '';
                            $img_id  = $plan['image_id'] ?? 0;
                            $title   = $plan['title'] ?? '';
                            $link    = $plan['link'] ?? '';
                            $thumb   = $img_id ? wp_get_attachment_image_url($img_id, 'medium') : $img_url;
                    ?>
                    <div class="marketing-plan-item" data-index="<?php echo esc_attr($i); ?>" style="display:flex; align-items:center; gap:12px; background:#fff; border:1px solid #dcdcde; padding:10px 12px; border-radius:6px; cursor:move; transition:all 0.2s;">
                        <span class="handle dashicons dashicons-menu" style="color:#8c8f94; font-size:20px; cursor:grab; flex-shrink:0;"></span>
                        <div style="width:80px; height:80px; border-radius:4px; overflow:hidden; background:#f0f0f1; flex-shrink:0; border:1px solid #e0e0e0;">
                            <?php if ($thumb): ?>
                                <img src="<?php echo esc_url($thumb); ?>" style="width:100%; height:100%; object-fit:cover;" />
                            <?php else: ?>
                                <span class="dashicons dashicons-format-image" style="font-size:30px; width:80px; height:80px; display:flex; align-items:center; justify-content:center; color:#8c8f94;"></span>
                            <?php endif; ?>
                        </div>
                        <div style="flex:1; min-width:0; display:flex; flex-direction:column; gap:4px;">
                            <input type="text" class="plan-title-input" value="<?php echo esc_attr($title); ?>" placeholder="Título do plano (opcional)" style="font-weight:600; font-size:13px; width:100%; padding:4px 6px; border:1px solid #dcdcde; border-radius:4px;" />
                            <input type="text" class="plan-link-input" value="<?php echo esc_attr($link); ?>" placeholder="Link de destino (opcional)" style="font-size:12px; color:#646970; width:100%; padding:3px 6px; border:1px solid #dcdcde; border-radius:4px;" />
                            <input type="hidden" class="plan-image-url" value="<?php echo esc_attr($img_url); ?>" />
                            <input type="hidden" class="plan-image-id" value="<?php echo esc_attr($img_id); ?>" />
                        </div>
                        <button type="button" class="button button-small change-plan-image" title="Trocar imagem" style="flex-shrink:0;">🖼️</button>
                        <button type="button" class="button button-small remove-plan-item" title="Remover" style="flex-shrink:0; color:#d63638;">✕</button>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div style="margin-top:12px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <button type="button" class="button" id="add-marketing-plan-btn" style="display:flex; align-items:center; gap:4px;">
                        <span class="dashicons dashicons-plus-alt2" style="font-size:16px; width:16px; height:16px;"></span> Adicionar Imagem
                    </button>
                    <button type="button" class="button button-primary" onclick="saveMarketingPlans()">
                        💾 Salvar Planos de Marketing
                    </button>
                    <span id="marketing-plans-spinner" style="display:none;" class="spinner"></span>
                </div>
            </div>

            <!-- Colunistas Config -->
            <div style="margin-top:32px; background:#fff; border:1px solid #dcdcde; padding:20px; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                <h2 style="margin-top:0; display:flex; align-items:center; gap:8px;">
                    <span class="dashicons dashicons-groups" style="color:#7b2ff7;"></span>
                    🖊️ Configuração dos Colunistas
                </h2>
                <p>Configure os nomes das colunas e o dia da semana de cada colunista para a seção <strong>"Coluna do Dia"</strong> na Home.</p>

                <div id="colunistas-config-status" class="zimny-layout-status"></div>

                <?php
                $colunistas_config = get_option(Zimny_Admin::OPTION_COLUNISTAS_CONFIG, array(
                    'day_assignments' => array(),
                    'column_names' => array(),
                    'column_colors' => array(),
                    'instagram_handles' => array(),
                ));
                if (!is_array($colunistas_config)) $colunistas_config = array();
                $day_assignments = $colunistas_config['day_assignments'] ?? array();
                $column_names = $colunistas_config['column_names'] ?? array();
                $column_colors = $colunistas_config['column_colors'] ?? array();
                $instagram_handles = $colunistas_config['instagram_handles'] ?? array();

                $colunistas_users = get_users(array(
                    'role__in' => array('author'),
                    'orderby'  => 'display_name',
                    'order'    => 'ASC',
                ));

                $dias_semana = array(
                    1 => 'Segunda-feira',
                    2 => 'Terça-feira',
                    3 => 'Quarta-feira',
                    4 => 'Quinta-feira',
                    5 => 'Sexta-feira',
                    6 => 'Sábado',
                    7 => 'Domingo',
                );
                ?>

                <div id="colunistas-config-list" style="margin-top:16px;">
                    <?php if (!empty($colunistas_users)): ?>
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse; font-size:13px;">
                            <thead>
                                <tr style="background:#f0f0f1; text-align:left;">
                                    <th style="padding:10px 12px; border-bottom:2px solid #dcdcde;">Colunista</th>
                                    <th style="padding:10px 12px; border-bottom:2px solid #dcdcde;">Nome da Coluna</th>
                                    <th style="padding:10px 12px; border-bottom:2px solid #dcdcde;">Cor do Título</th>
                                    <th style="padding:10px 12px; border-bottom:2px solid #dcdcde;">Instagram</th>
                                    <th style="padding:10px 12px; border-bottom:2px solid #dcdcde;">Dia da Semana</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($colunistas_users as $col_user):
                                    $uid = $col_user->ID;
                                    $visible_meta = get_user_meta($uid, 'colunista_visible', true);
                                    $is_visible = $visible_meta === '' ? '1' : $visible_meta;
                                    $avatar = get_avatar_url($uid, array('size' => 32));
                                    $current_day = array_search($uid, $day_assignments) !== false ? array_search($uid, $day_assignments) : 0;
                                    $col_name = $column_names[$uid] ?? $col_user->display_name;
                                    $col_color = $column_colors[$uid] ?? '#8E8E93';
                                ?>
                                <tr style="border-bottom:1px solid #f0f0f1; <?php echo $is_visible !== '1' ? 'opacity:0.5;' : ''; ?>">
                                    <td style="padding:10px 12px; display:flex; align-items:center; gap:8px;">
                                        <?php if ($avatar): ?>
                                            <img src="<?php echo esc_url($avatar); ?>" style="width:32px; height:32px; border-radius:50%; object-fit:cover;" />
                                        <?php else: ?>
                                            <span class="dashicons dashicons-admin-users" style="font-size:32px; width:32px; height:32px; color:#8c8f94;"></span>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?php echo esc_html($col_user->display_name); ?></strong>
                                            <span style="font-size:11px; color:#646970; display:block;">@<?php echo esc_html($col_user->user_nicename); ?></span>
                                        </div>
                                        <?php if ($is_visible !== '1'): ?>
                                            <span style="font-size:10px; background:#fcf0f1; color:#d63638; padding:1px 6px; border-radius:3px;">Oculto</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:10px 12px;">
                                        <input type="text" class="colunista-name-input" data-user-id="<?php echo esc_attr($uid); ?>"
                                               value="<?php echo esc_attr($col_name); ?>"
                                               placeholder="Nome da coluna"
                                               style="width:100%; min-width:160px; padding:6px 8px; border:1px solid #dcdcde; border-radius:4px; font-size:13px;" />
                                    </td>
                                    <td style="padding:10px 12px;">
                                        <div style="display:flex; align-items:center; gap:6px;">
                                            <span class="colunista-color-swatch" data-user-id="<?php echo esc_attr($uid); ?>"
                                                  style="display:inline-block; width:28px; height:28px; border-radius:4px; background:<?php echo esc_attr($col_color); ?>; border:1px solid #dcdcde; flex-shrink:0;"></span>
                                            <input type="text" class="colunista-color-hex" data-user-id="<?php echo esc_attr($uid); ?>"
                                                   value="<?php echo esc_attr($col_color); ?>"
                                                   placeholder="#RRGGBB"
                                                   style="width:90px; padding:6px 8px; border:1px solid #dcdcde; border-radius:4px; font-size:12px; font-family:monospace; text-transform:uppercase;" />
                                        </div>
                                    </td>
                                    <td style="padding:10px 12px;">
                                        <input type="text" class="colunista-instagram-input" data-user-id="<?php echo esc_attr($uid); ?>"
                                               value="<?php echo esc_attr($instagram_handles[$uid] ?? $col_user->user_nicename); ?>"
                                               placeholder="@instagram"
                                               style="width:100%; min-width:120px; padding:6px 8px; border:1px solid #dcdcde; border-radius:4px; font-size:13px;" />
                                    </td>
                                    <td style="padding:10px 12px;">
                                        <select class="colunista-day-select" data-user-id="<?php echo esc_attr($uid); ?>"
                                                style="width:100%; min-width:140px; padding:6px 8px; border:1px solid #dcdcde; border-radius:4px; font-size:13px;">
                                            <option value="0">— Nenhum —</option>
                                            <?php foreach ($dias_semana as $num => $label):
                                                $disabled = in_array($num, $day_assignments) && $day_assignments[$num] != $uid ? 'disabled' : '';
                                            ?>
                                            <option value="<?php echo $num; ?>" <?php echo $current_day == $num ? 'selected' : ''; ?> <?php echo $disabled; ?>>
                                                <?php echo $label; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <p style="color:#8c8f94;">Nenhum colunista (author) encontrado. Crie usuários com perfil "Author" primeiro.</p>
                    <?php endif; ?>
                </div>

                <div style="margin-top:16px; display:flex; gap:8px; align-items:center;">
                    <button type="button" class="button button-primary" onclick="saveColunistasConfig()">
                        💾 Salvar Configuração dos Colunistas
                    </button>
                    <span id="colunistas-config-spinner" style="display:none;" class="spinner"></span>
                </div>
            </div>
        </div>

        <script>
        // ─── Splash Player ──────────────────────────────────────────────────
        document.querySelectorAll('.splash-video-label').forEach(function(label) {
            label.addEventListener('click', function(e) {
                e.preventDefault();
                const checkbox = this.querySelector('.splash-video-checkbox');
                checkbox.checked = !checkbox.checked;
                this.style.borderColor = checkbox.checked ? '#2271b1' : '#dcdcde';
                this.style.opacity = checkbox.checked ? '1' : '0.7';
                const checkHolder = this.querySelector('div:first-child div:nth-child(2)');
                if (checkbox.checked) {
                    checkHolder.style.background = '#2271b1';
                    checkHolder.style.borderColor = '#2271b1';
                    checkHolder.innerHTML = '<span class="dashicons dashicons-yes" style="font-size:14px;width:14px;height:14px;color:#fff;"></span>';
                } else {
                    checkHolder.style.background = '#fff';
                    checkHolder.style.borderColor = '#8c8f94';
                    checkHolder.innerHTML = '';
                }
            });
        });

        function toggleAllSplashVideos(select) {
            document.querySelectorAll('.splash-video-checkbox').forEach(function(cb) {
                cb.checked = select;
                const label = cb.closest('.splash-video-label');
                if (label) {
                    label.style.borderColor = select ? '#2271b1' : '#dcdcde';
                    label.style.opacity = select ? '1' : '0.7';
                    const checkHolder = label.querySelector('div:first-child div:nth-child(2)');
                    if (select) {
                        checkHolder.style.background = '#2271b1';
                        checkHolder.style.borderColor = '#2271b1';
                        checkHolder.innerHTML = '<span class="dashicons dashicons-yes" style="font-size:14px;width:14px;height:14px;color:#fff;"></span>';
                    } else {
                        checkHolder.style.background = '#fff';
                        checkHolder.style.borderColor = '#8c8f94';
                        checkHolder.innerHTML = '';
                    }
                }
            });
        }

        async function saveSplashVideos() {
            const statusEl = document.getElementById('splash-status');
            const spinner = document.getElementById('splash-spinner');
            spinner.style.display = 'inline-block';

            const checked = [];
            document.querySelectorAll('.splash-video-checkbox:checked').forEach(function(cb) {
                checked.push(parseInt(cb.value));
            });

            try {
                const formData = new FormData();
                formData.append('action', 'zimny_save_splash_videos');
                formData.append('video_ids', JSON.stringify(checked));
                formData.append('_wpnonce', '<?php echo wp_create_nonce('zimny_save_splash_videos'); ?>');

                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();

                statusEl.className = 'zimny-layout-status ' + (result.success ? 'success' : 'error');
                statusEl.textContent = result.success
                    ? '✅ ' + checked.length + ' vídeo(s) salvo(s) para o Splash Player!'
                    : '❌ Erro: ' + result.data;
                statusEl.style.display = 'block';
                setTimeout(function() { statusEl.style.display = 'none'; }, 4000);
            } catch (err) {
                statusEl.className = 'zimny-layout-status error';
                statusEl.textContent = '❌ Erro de conexão ao salvar.';
                statusEl.style.display = 'block';
            }
            spinner.style.display = 'none';
        }

        // ─── Home Featured Videos ───────────────────────────────────────────
        document.querySelectorAll('.home-featured-label').forEach(function(label) {
            label.addEventListener('click', function(e) {
                e.preventDefault();
                const checkbox = this.querySelector('.home-featured-checkbox');
                checkbox.checked = !checkbox.checked;
                this.style.borderColor = checkbox.checked ? '#f5a623' : '#dcdcde';
                this.style.opacity = checkbox.checked ? '1' : '0.7';
                const checkHolder = this.querySelector('div:first-child div:nth-child(2)');
                if (checkbox.checked) {
                    checkHolder.style.background = '#f5a623';
                    checkHolder.style.borderColor = '#f5a623';
                    checkHolder.innerHTML = '<span class="dashicons dashicons-yes" style="font-size:14px;width:14px;height:14px;color:#fff;"></span>';
                } else {
                    checkHolder.style.background = '#fff';
                    checkHolder.style.borderColor = '#8c8f94';
                    checkHolder.innerHTML = '';
                }
                updateHomeFeaturedCount();
            });
        });

        function updateHomeFeaturedCount() {
            const total = document.querySelectorAll('.home-featured-checkbox:checked').length;
            const el = document.getElementById('home-featured-count');
            if (el) el.textContent = total + ' selecionado(s)';
        }
        updateHomeFeaturedCount();

        function filterHomeFeaturedVideos() {
            const filter = document.getElementById('home-featured-filter').value;
            document.querySelectorAll('.home-featured-label').forEach(function(label) {
                const carousels = label.dataset.carousels || '';
                label.style.display = (!filter || carousels.includes(filter)) ? '' : 'none';
            });
        }

        function toggleAllHomeFeatured(select) {
            document.querySelectorAll('.home-featured-label').forEach(function(label) {
                if (label.style.display === 'none') return;
                const checkbox = label.querySelector('.home-featured-checkbox');
                checkbox.checked = select;
                label.style.borderColor = select ? '#f5a623' : '#dcdcde';
                label.style.opacity = select ? '1' : '0.7';
                const checkHolder = label.querySelector('div:first-child div:nth-child(2)');
                if (select) {
                    checkHolder.style.background = '#f5a623';
                    checkHolder.style.borderColor = '#f5a623';
                    checkHolder.innerHTML = '<span class="dashicons dashicons-yes" style="font-size:14px;width:14px;height:14px;color:#fff;"></span>';
                } else {
                    checkHolder.style.background = '#fff';
                    checkHolder.style.borderColor = '#8c8f94';
                    checkHolder.innerHTML = '';
                }
            });
            updateHomeFeaturedCount();
        }

        async function saveHomeFeaturedVideos() {
            const statusEl = document.getElementById('home-featured-status');
            const spinner = document.getElementById('home-featured-spinner');
            spinner.style.display = 'inline-block';

            const checked = [];
            document.querySelectorAll('.home-featured-checkbox:checked').forEach(function(cb) {
                checked.push(parseInt(cb.value));
            });

            try {
                const formData = new FormData();
                formData.append('action', 'zimny_save_home_featured_videos');
                formData.append('video_ids', JSON.stringify(checked));
                formData.append('_wpnonce', '<?php echo wp_create_nonce('zimny_save_home_featured_videos'); ?>');

                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();

                statusEl.className = 'zimny-layout-status ' + (result.success ? 'success' : 'error');
                statusEl.textContent = result.success
                    ? '✅ ' + checked.length + ' vídeo(s) em destaque salvos!'
                    : '❌ Erro: ' + result.data;
                statusEl.style.display = 'block';
                setTimeout(function() { statusEl.style.display = 'none'; }, 4000);
            } catch (err) {
                statusEl.className = 'zimny-layout-status error';
                statusEl.textContent = '❌ Erro de conexão ao salvar.';
                statusEl.style.display = 'block';
            }
            spinner.style.display = 'none';
        }

        // ─── Home Events ────────────────────────────────────────────────────
        document.querySelectorAll('.home-event-label').forEach(function(label) {
            label.addEventListener('click', function(e) {
                e.preventDefault();
                const checkbox = this.querySelector('.home-event-checkbox');
                checkbox.checked = !checkbox.checked;
                this.style.borderColor = checkbox.checked ? '#7b2ff7' : '#dcdcde';
                this.style.opacity = checkbox.checked ? '1' : '0.7';
                const checkHolder = this.querySelector('div:first-child div:nth-child(2)');
                if (checkbox.checked) {
                    checkHolder.style.background = '#7b2ff7';
                    checkHolder.style.borderColor = '#7b2ff7';
                    checkHolder.innerHTML = '<span class="dashicons dashicons-yes" style="font-size:14px;width:14px;height:14px;color:#fff;"></span>';
                } else {
                    checkHolder.style.background = '#fff';
                    checkHolder.style.borderColor = '#8c8f94';
                    checkHolder.innerHTML = '';
                }
            });
        });

        function toggleAllHomeEvents(select) {
            document.querySelectorAll('.home-event-checkbox').forEach(function(cb) {
                cb.checked = select;
                const label = cb.closest('.home-event-label');
                if (label) {
                    label.style.borderColor = select ? '#7b2ff7' : '#dcdcde';
                    label.style.opacity = select ? '1' : '0.7';
                    const checkHolder = label.querySelector('div:first-child div:nth-child(2)');
                    if (select) {
                        checkHolder.style.background = '#7b2ff7';
                        checkHolder.style.borderColor = '#7b2ff7';
                        checkHolder.innerHTML = '<span class="dashicons dashicons-yes" style="font-size:14px;width:14px;height:14px;color:#fff;"></span>';
                    } else {
                        checkHolder.style.background = '#fff';
                        checkHolder.style.borderColor = '#8c8f94';
                        checkHolder.innerHTML = '';
                    }
                }
            });
        }

        async function saveHomeEvents() {
            const statusEl = document.getElementById('home-events-status');
            const spinner = document.getElementById('home-events-spinner');
            spinner.style.display = 'inline-block';

            const checked = [];
            document.querySelectorAll('.home-event-checkbox:checked').forEach(function(cb) {
                checked.push(parseInt(cb.value));
            });

            try {
                const formData = new FormData();
                formData.append('action', 'zimny_save_home_events');
                formData.append('event_ids', JSON.stringify(checked));
                formData.append('_wpnonce', '<?php echo wp_create_nonce('zimny_save_home_events'); ?>');

                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();

                statusEl.className = 'zimny-layout-status ' + (result.success ? 'success' : 'error');
                statusEl.textContent = result.success
                    ? '✅ ' + checked.length + ' evento(s) em destaque na Home salvos!'
                    : '❌ Erro: ' + result.data;
                statusEl.style.display = 'block';
                setTimeout(function() { statusEl.style.display = 'none'; }, 4000);
            } catch (err) {
                statusEl.className = 'zimny-layout-status error';
                statusEl.textContent = '❌ Erro de conexão ao salvar.';
                statusEl.style.display = 'block';
            }
            spinner.style.display = 'none';
        }

        // ─── Layout ─────────────────────────────────────────────────────────
        jQuery(document).ready(function($) {
            $("#zimny-layout-list").sortable({
                handle: ".handle",
                placeholder: "zimny-layout-section drag-over",
                axis: "y",
                tolerance: "pointer",
                cursor: "grabbing",
                opacity: 0.6,
                update: function() {
                    $("#zimny-layout-list .zimny-layout-section").each(function(i) {
                        $(this).attr("data-index", i);
                    });
                }
            });
            $("#zimny-layout-list").disableSelection();
        });

        function toggleVisibility(btn) {
            const isVisible = btn.classList.contains('visible');
            btn.classList.toggle('visible');
            btn.classList.toggle('hidden');
            btn.querySelector('.dashicons').className = 'dashicons ' + (isVisible ? 'dashicons-visibility' : 'dashicons-hidden');
        }

        function removeSection(btn) {
            if (confirm('Remover esta seção do layout da Home?')) {
                btn.closest('.zimny-layout-section').remove();
            }
        }

        function addBuiltInSection() {
            const select = document.getElementById('add-builtin-select');
            const option = select.options[select.selectedIndex];
            if (!option || !option.value) {
                alert('Selecione uma seção para adicionar.');
                return;
            }

            const slug = option.value;
            const title = option.dataset.title;
            const icon = option.dataset.icon || 'dashicons-layout';

            // Map slug to badge/desc
            const sectionInfo = {
                'events_carousel': { badge: 'Eventos', desc: 'Carrossel de eventos na Home', badgeClass: 'events' },
                'marketing_plans_carousel': { badge: 'Planos', desc: 'Carrossel 1:1 de planos de marketing digital', badgeClass: 'marketing' },
                'colunista_dia': { badge: 'Colunista', desc: 'Colunista em destaque baseado no dia da semana', badgeClass: 'built-in' },
                'anuncie_card_v1': { badge: 'Anuncie V1', desc: '◆ Minimal Elegance — Card clean com serif e underline', badgeClass: 'ad' },
                'anuncie_card_v2': { badge: 'Anuncie V2', desc: '◇ Bordered Premium — Card com borda pulsante e seta', badgeClass: 'ad' },
                'anuncie_card_v3': { badge: 'Anuncie V3', desc: '◈ Split Content — Card dividido com barra e shine', badgeClass: 'ad' },
                'anuncie_card_v4': { badge: 'Anuncie V4', desc: '⬟ Full Bleed Bold — Card full-width com glow', badgeClass: 'ad' },
                'podcast_grid': { badge: 'Podcast', desc: 'Grid 2×2 com os 4 podcasts mais recentes', badgeClass: 'built-in' },
                'gallery_grid': { badge: 'Galeria', desc: 'Grid 3×2 com as galerias mais recentes', badgeClass: 'events' },
                'eventos_grid': { badge: 'Eventos', desc: 'Grid 3×2 com os eventos de produção mais recentes', badgeClass: 'events' },
            };
            const info = sectionInfo[slug] || { badge: 'Nativo', desc: 'Seção nativa do app', badgeClass: 'built-in' };

            const list = document.getElementById('zimny-layout-list');
            const div = document.createElement('div');
            div.className = 'zimny-layout-section';
            div.setAttribute('draggable', 'true');
            div.dataset.type = slug;
            div.dataset.slug = slug;
            div.dataset.termId = '0';
            div.innerHTML = `
                <span class="handle dashicons dashicons-menu"></span>
                <span class="icon dashicons ${icon}"></span>
                <div class="info">
                    <input type="text" class="title-input" value="${title}" placeholder="Título da seção" />
                    <div class="desc">${info.desc}</div>
                    <span class="badge ${info.badgeClass}">${info.badge}</span>
                    <label class="show-title-label" title="Mostrar título na Home">
                        <input type="checkbox" class="show-title-check" checked />
                        <span>Título</span>
                    </label>
                    <div class="padding-controls">
                        <label title="Espaçamento superior (px)"><span>↑</span><input type="number" class="padding-top-input" min="0" max="120" step="1" value="16" /></label>
                        <label title="Espaçamento inferior (px)"><span>↓</span><input type="number" class="padding-bottom-input" min="0" max="120" step="1" value="8" /></label>
                    </div>
                </div>
                <button type="button" class="visibility-toggle visible" onclick="toggleVisibility(this)" title="Mostrar/Ocultar">
                    <span class="dashicons dashicons-visibility"></span>
                </button>
                <button type="button" class="remove-btn" onclick="removeSection(this)" title="Remover deste layout">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            `;
            list.appendChild(div);

            // Disable option
            option.disabled = true;
            select.value = '';

            // Re-index
            document.querySelectorAll('.zimny-layout-section').forEach(function(el, i) {
                el.dataset.index = i;
            });
        }

        function addAdBlockToLayout() {
            const list = document.getElementById('zimny-layout-list');
            const div = document.createElement('div');
            div.className = 'zimny-layout-section';
            div.setAttribute('draggable', 'true');
            div.dataset.type = 'ad_block';
            div.dataset.slug = 'ad_block';
            div.dataset.termId = '0';
            div.innerHTML = `
                <span class="handle dashicons dashicons-menu"></span>
                <span class="icon dashicons dashicons-megaphone"></span>
                <div class="info">
                    <input type="text" class="title-input" value="Bloco de Publicidade" placeholder="Título da seção" />
                    <div class="desc">Espaço para anúncio patrocinado premium</div>
                    <span class="badge ad">Publicidade</span>
                    <label class="show-title-label" title="Mostrar título na Home">
                        <input type="checkbox" class="show-title-check" checked />
                        <span>Título</span>
                    </label>
                    <div class="padding-controls">
                        <label title="Espaçamento superior (px)"><span>↑</span><input type="number" class="padding-top-input" min="0" max="120" step="1" value="16" /></label>
                        <label title="Espaçamento inferior (px)"><span>↓</span><input type="number" class="padding-bottom-input" min="0" max="120" step="1" value="8" /></label>
                    </div>
                </div>
                <button type="button" class="visibility-toggle visible" onclick="toggleVisibility(this)" title="Mostrar/Ocultar">
                    <span class="dashicons dashicons-visibility"></span>
                </button>
                <button type="button" class="remove-btn" onclick="removeSection(this)" title="Remover deste layout">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            `;
            list.appendChild(div);

            document.querySelectorAll('.zimny-layout-section').forEach(function(el, i) {
                el.dataset.index = i;
            });
        }

        function addCarouselToLayout() {
            const select = document.getElementById('add-carousel-select');
            const option = select.options[select.selectedIndex];
            if (!option || !option.value) {
                alert('Selecione um carrossel para adicionar.');
                return;
            }

            const termId = option.value;
            const slug = option.dataset.slug;
            const name = option.dataset.name;

            const list = document.getElementById('zimny-layout-list');
            const div = document.createElement('div');
            div.className = 'zimny-layout-section';
            div.setAttribute('draggable', 'true');
            div.dataset.type = 'video_carousel';
            div.dataset.slug = slug;
            div.dataset.termId = termId;
            div.innerHTML = `
                <span class="handle dashicons dashicons-menu"></span>
                <span class="icon dashicons dashicons-playlist-video"></span>
                <div class="info">
                    <input type="text" class="title-input" value="${name}" placeholder="Título da seção" />
                    <div class="desc">Carrossel de vídeos · <code>${slug}</code></div>
                    <span class="badge carousel">Carrossel</span>
                    <label class="show-title-label" title="Mostrar título na Home">
                        <input type="checkbox" class="show-title-check" checked />
                        <span>Título</span>
                    </label>
                    <div class="padding-controls">
                        <label title="Espaçamento superior (px)"><span>↑</span><input type="number" class="padding-top-input" min="0" max="120" step="1" value="16" /></label>
                        <label title="Espaçamento inferior (px)"><span>↓</span><input type="number" class="padding-bottom-input" min="0" max="120" step="1" value="8" /></label>
                    </div>
                </div>
                <button type="button" class="visibility-toggle visible" onclick="toggleVisibility(this)" title="Mostrar/Ocultar">
                    <span class="dashicons dashicons-visibility"></span>
                </button>
                <button type="button" class="remove-btn" onclick="removeSection(this)" title="Remover deste layout">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            `;
            list.appendChild(div);

            option.disabled = true;
            select.value = '';

            document.querySelectorAll('.zimny-layout-section').forEach(function(el, i) {
                el.dataset.index = i;
            });
        }

        async function saveLayout() {
            const statusEl = document.getElementById('layout-status');
            const spinner = document.getElementById('save-spinner');
            spinner.style.display = 'inline-block';

            const sections = document.querySelectorAll('.zimny-layout-section');
            const layout = [];

            sections.forEach(function(section) {
                const type = section.dataset.type;
                const slug = section.dataset.slug;
                const termId = section.dataset.termId ? parseInt(section.dataset.termId) : 0;
                const visible = section.querySelector('.visibility-toggle').classList.contains('visible');
                const titleInput = section.querySelector('.title-input');
                const title = titleInput ? titleInput.value.trim() : section.querySelector('.title').textContent.trim();
                const showTitleCheck = section.querySelector('.show-title-check');
                const show_title = showTitleCheck ? showTitleCheck.checked : true;
                const paddingTopInput = section.querySelector('.padding-top-input');
                const paddingBottomInput = section.querySelector('.padding-bottom-input');
                const padding_top = paddingTopInput ? parseInt(paddingTopInput.value) || 32 : 32;
                const padding_bottom = paddingBottomInput ? parseInt(paddingBottomInput.value) || 8 : 8;

                layout.push({
                    type: type,
                    slug: slug,
                    term_id: termId,
                    title: title,
                    visible: visible,
                    show_title: show_title,
                    padding_top: padding_top,
                    padding_bottom: padding_bottom,
                });
            });

            try {
                const formData = new FormData();
                formData.append('action', 'zimny_save_home_layout');
                formData.append('layout', JSON.stringify(layout));
                formData.append('_wpnonce', '<?php echo wp_create_nonce('zimny_save_home_layout'); ?>');

                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();

                statusEl.className = 'zimny-layout-status ' + (result.success ? 'success' : 'error');
                statusEl.textContent = result.success ? '✅ Layout salvo com sucesso!' : '❌ Erro: ' + result.data;
                statusEl.style.display = 'block';
                setTimeout(function() { statusEl.style.display = 'none'; }, 4000);
            } catch (err) {
                statusEl.className = 'zimny-layout-status error';
                statusEl.textContent = '❌ Erro de conexão ao salvar.';
                statusEl.style.display = 'block';
            }
            spinner.style.display = 'none';
        }

        // ─── Marketing Plans ─────────────────────────────────────────────────
        jQuery(document).ready(function($) {
            $("#marketing-plans-list").sortable({
                handle: ".handle",
                axis: "y",
                tolerance: "pointer",
                cursor: "grabbing",
                opacity: 0.6,
                items: ".marketing-plan-item",
                placeholder: "marketing-plan-item",
                forcePlaceholderSize: true,
            });
        });

        // Add a new empty marketing plan item
        document.getElementById('add-marketing-plan-btn').addEventListener('click', function() {
            const list = document.getElementById('marketing-plans-list');
            const div = document.createElement('div');
            div.className = 'marketing-plan-item';
            div.style.cssText = 'display:flex; align-items:center; gap:12px; background:#fff; border:1px solid #dcdcde; padding:10px 12px; border-radius:6px; cursor:move; transition:all 0.2s;';
            div.innerHTML = `
                <span class="handle dashicons dashicons-menu" style="color:#8c8f94; font-size:20px; cursor:grab; flex-shrink:0;"></span>
                <div style="width:80px; height:80px; border-radius:4px; overflow:hidden; background:#f0f0f1; flex-shrink:0; border:1px solid #e0e0e0; display:flex; align-items:center; justify-content:center;">
                    <span class="dashicons dashicons-format-image" style="font-size:30px; width:80px; height:80px; display:flex; align-items:center; justify-content:center; color:#8c8f94;"></span>
                </div>
                <div style="flex:1; min-width:0; display:flex; flex-direction:column; gap:4px;">
                    <input type="text" class="plan-title-input" value="" placeholder="Título do plano (opcional)" style="font-weight:600; font-size:13px; width:100%; padding:4px 6px; border:1px solid #dcdcde; border-radius:4px;" />
                    <input type="text" class="plan-link-input" value="" placeholder="Link de destino (opcional)" style="font-size:12px; color:#646970; width:100%; padding:3px 6px; border:1px solid #dcdcde; border-radius:4px;" />
                    <input type="hidden" class="plan-image-url" value="" />
                    <input type="hidden" class="plan-image-id" value="0" />
                </div>
                <button type="button" class="button button-small change-plan-image" title="Selecionar imagem" style="flex-shrink:0;">🖼️</button>
                <button type="button" class="button button-small remove-plan-item" title="Remover" style="flex-shrink:0; color:#d63638;">✕</button>
            `;
            list.appendChild(div);
            // Trigger click on the image button to open media uploader
            setTimeout(function() {
                var imgBtn = div.querySelector('.change-plan-image');
                if (imgBtn) imgBtn.click();
            }, 100);
        });

        // Delegate click events for dynamically added buttons
        document.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.remove-plan-item');
            if (removeBtn) {
                e.preventDefault();
                removePlanItem(removeBtn);
            }
        });

        function removePlanItem(btn) {
            if (confirm('Remover este plano do carrossel?')) {
                const item = btn.closest('.marketing-plan-item');
                if (item) item.remove();
            }
        }

        async function saveMarketingPlans() {
            const statusEl = document.getElementById('marketing-plans-status');
            const spinner = document.getElementById('marketing-plans-spinner');
            spinner.style.display = 'inline-block';

            const items = document.querySelectorAll('.marketing-plan-item');
            const plans = [];

            items.forEach(function(item) {
                const imageUrl = item.querySelector('.plan-image-url').value;
                const imageId = parseInt(item.querySelector('.plan-image-id').value) || 0;
                const title = item.querySelector('.plan-title-input').value.trim();
                const link = item.querySelector('.plan-link-input').value.trim();

                if (imageUrl) {
                    plans.push({
                        image_url: imageUrl,
                        image_id: imageId,
                        title: title,
                        link: link,
                    });
                }
            });

            try {
                const formData = new FormData();
                formData.append('action', 'zimny_save_marketing_plans');
                formData.append('plans', JSON.stringify(plans));
                formData.append('_wpnonce', '<?php echo wp_create_nonce('zimny_save_marketing_plans'); ?>');

                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();

                statusEl.className = 'zimny-layout-status ' + (result.success ? 'success' : 'error');
                statusEl.textContent = result.success
                    ? '✅ ' + plans.length + ' plano(s) de marketing salvo(s)!'
                    : '❌ Erro: ' + result.data;
                statusEl.style.display = 'block';
                setTimeout(function() { statusEl.style.display = 'none'; }, 4000);
            } catch (err) {
                statusEl.className = 'zimny-layout-status error';
                statusEl.textContent = '❌ Erro de conexão ao salvar.';
                statusEl.style.display = 'block';
            }
            spinner.style.display = 'none';
        }

        // ─── Colunistas Config ────────────────────────────────────────────────
        async function saveColunistasConfig() {
            const statusEl = document.getElementById('colunistas-config-status');
            const spinner = document.getElementById('colunistas-config-spinner');
            spinner.style.display = 'inline-block';

            const dayAssignments = {};
            const columnNames = {};
            const columnColors = {};
            const instagramHandles = {};

            document.querySelectorAll('.colunista-day-select').forEach(function(select) {
                const userId = parseInt(select.dataset.userId);
                const day = parseInt(select.value);
                if (day > 0) {
                    dayAssignments[day] = userId;
                }
            });

            document.querySelectorAll('.colunista-name-input').forEach(function(input) {
                const userId = parseInt(input.dataset.userId);
                const name = input.value.trim();
                if (name) {
                    columnNames[userId] = name;
                }
            });

            document.querySelectorAll('.colunista-color-hex').forEach(function(input) {
                const userId = parseInt(input.dataset.userId);
                const color = input.value.trim();
                if (color) {
                    columnColors[userId] = color;
                }
            });

            document.querySelectorAll('.colunista-instagram-input').forEach(function(input) {
                const userId = parseInt(input.dataset.userId);
                const handle = input.value.trim().replace(/^@/, '');
                if (handle) {
                    instagramHandles[userId] = handle;
                }
            });

            try {
                const formData = new FormData();
                formData.append('action', 'zimny_save_colunistas_config');
                formData.append('config', JSON.stringify({
                    day_assignments: dayAssignments,
                    column_names: columnNames,
                    column_colors: columnColors,
                    instagram_handles: instagramHandles,
                }));
                formData.append('_wpnonce', '<?php echo wp_create_nonce('zimny_save_colunistas_config'); ?>');

                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();

                statusEl.className = 'zimny-layout-status ' + (result.success ? 'success' : 'error');
                statusEl.textContent = result.success
                    ? '✅ Configuração salva com sucesso!'
                    : '❌ Erro: ' + result.data;
                statusEl.style.display = 'block';
                setTimeout(function() { statusEl.style.display = 'none'; }, 4000);
            } catch (err) {
                statusEl.className = 'zimny-layout-status error';
                statusEl.textContent = '❌ Erro de conexão ao salvar.';
                statusEl.style.display = 'block';
            }
            spinner.style.display = 'none';
        }

        // ─── Color swatch sync ────────────────────────────────────────────────
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('colunista-color-hex')) {
                const td = e.target.closest('td');
                const swatch = td ? td.querySelector('.colunista-color-swatch') : null;
                if (swatch && /^#[0-9a-fA-F]{6}$/i.test(e.target.value)) {
                    swatch.style.background = e.target.value;
                }
            }
        });
        </script>
        <?php
    }
}