<?php
/**
 * Zimny_Admin_Meta_Boxes - Meta boxes de vídeos e eventos + save
 *
 * @package Zimny_Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Zimny_Admin_Meta_Boxes {

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
        add_action('save_post_zimny_video', array($this, 'save_video_meta_boxes'));
        add_action('save_post_zimny_event', array($this, 'save_event_meta_boxes'));
        add_action('save_post_zimny_cobertura', array($this, 'save_cobertura_meta_boxes'));
    }

    /**
     * CSS inline dos meta boxes de evento.
     */
    public function get_event_meta_box_css(): string {
        return '
        .zimny-event-meta-wrap { display:flex; gap:24px; flex-wrap:wrap; }
        .zimny-event-meta-wrap .field-group { flex:1; min-width:200px; }
        .zimny-event-meta-wrap label { font-weight:600; display:block; margin-bottom:4px; }
        .zimny-event-meta-wrap select, .zimny-event-meta-wrap input[type="text"], .zimny-event-meta-wrap input[type="number"] { width:100%; padding:6px; }
        .zimny-gallery-items { display:flex; flex-direction:column; gap:8px; margin-top:12px; }
        .zimny-gallery-item {
            display:flex; align-items:center; gap:12px;
            background:#f0f0f1; border:1px solid #dcdcde;
            padding:10px 12px; border-radius:6px;
            cursor:move; transition:all 0.2s;
        }
        .zimny-gallery-item:hover { border-color:#2271b1; }
        .zimny-gallery-item .gallery-thumb { width:60px; height:60px; border-radius:4px; overflow:hidden; background:#fff; flex-shrink:0; }
        .zimny-gallery-item .gallery-thumb img { width:100%; height:100%; object-fit:cover; }
        .zimny-gallery-item .gallery-info { flex:1; min-width:0; }
        .zimny-gallery-item .gallery-info .gallery-title { font-weight:600; font-size:13px; }
        .zimny-gallery-item .gallery-info .gallery-meta { font-size:11px; color:#646970; margin-top:2px; }
        .zimny-gallery-item .gallery-actions { display:flex; gap:4px; flex-shrink:0; }
        .zimny-gallery-item .gallery-actions button {
            width:32px; height:32px; border-radius:50%; border:1px solid #dcdcde;
            background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center;
            font-size:14px; transition:all 0.2s;
        }
        .zimny-gallery-item .gallery-actions button:hover { border-color:#2271b1; background:#f0f6fc; }
        .zimny-gallery-item .gallery-actions button.featured { background:#f5a623; border-color:#f5a623; color:#fff; }
        .zimny-gallery-item .gallery-actions button.cobertura-on { background:#2271b1; border-color:#2271b1; color:#fff; }
        .zimny-gallery-item .gallery-actions button.remove:hover { border-color:#d63638; color:#d63638; background:#fcf0f1; }
        .zimny-gallery-empty { padding:20px; text-align:center; color:#8c8f94; border:2px dashed #dcdcde; border-radius:6px; }
        ';
    }

    public function register_meta_boxes() {
        // Meta box do vídeo (legado)
        add_meta_box(
            'zimny_video_details',
            '🎬 Configurações do Vídeo (Zimny Admin)',
            array($this, 'render_video_meta_box_content'),
            'zimny_video',
            'normal',
            'high'
        );

        // Meta box do evento
        add_meta_box(
            'zimny_event_details',
            '📋 Configurações do Evento',
            array($this, 'render_event_meta_box_content'),
            'zimny_event',
            'normal',
            'high'
        );

        // Meta box da galeria do evento
        add_meta_box(
            'zimny_event_gallery',
            '🖼️ Galeria de Mídia do Evento',
            array($this, 'render_event_gallery_meta_box'),
            'zimny_event',
            'normal',
            'high'
        );

        // Meta box da cobertura (CPT zimny_cobertura)
        add_meta_box(
            'zimny_cobertura_media',
            '🖼️ Mídia da Cobertura',
            array($this, 'render_cobertura_media_meta_box'),
            'zimny_cobertura',
            'normal',
            'high'
        );

        // Meta box de configurações da cobertura
        add_meta_box(
            'zimny_cobertura_settings',
            '⚙️ Configurações da Cobertura',
            array($this, 'render_cobertura_settings_meta_box'),
            'zimny_cobertura',
            'side',
            'default'
        );
    }

    // ── Meta Box: Vídeo ──────────────────────────────────────────────────────

    public function render_video_meta_box_content($post) {
        wp_nonce_field('zimny_save_video_meta', 'zimny_video_nonce');

        $video_url    = get_post_meta($post->ID, '_zimny_video_file_url', true);
        $order        = get_post_meta($post->ID, '_zimny_video_order', true);
        $thumb_id     = get_post_thumbnail_id($post->ID);
        $thumb_url    = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'medium') : '';
        $video_attach_id = get_post_meta($post->ID, '_zimny_video_attachment_id', true);
        $video_thumb  = $video_attach_id ? wp_get_attachment_image_url($video_attach_id, 'medium') : '';
        if ($order === '') $order = 0;
        ?>
        <div style="display:flex; gap:24px; flex-wrap:wrap;">
            <div style="flex-shrink:0; width:200px;">
                <label><strong>Thumbnail (Destaque)</strong></label>
                <div style="margin-top:6px; width:200px; height:356px; background:#f0f0f1; border-radius:6px; overflow:hidden; border:1px solid #dcdcde; display:flex; align-items:center; justify-content:center;">
                    <?php if ($thumb_url): ?>
                        <img src="<?php echo esc_url($thumb_url); ?>" style="width:100%; height:100%; object-fit:cover;" alt="Thumbnail preview" />
                    <?php else: ?>
                        <span style="color:#8c8f94; font-size:12px; text-align:center; padding:8px;">Nenhuma thumbnail definida<br><small>Use a "Imagem destacada"</small></span>
                    <?php endif; ?>
                </div>
            </div>

            <div style="flex:1; min-width:280px;">
                <p>
                    <label><strong>🎬 URL do Arquivo MP4:</strong></label><br>
                    <input type="text" name="zimny_video_file_url" value="<?php echo esc_attr($video_url); ?>" style="width:100%; font-size:14px; padding:6px; margin-top:4px;" placeholder="https://zimnymagazine.com/wp-content/uploads/.../video.mp4">
                </p>

                <?php if ($video_thumb): ?>
                <div style="margin-bottom:12px; padding:8px 12px; background:#f0f6fc; border-radius:4px; border-left:4px solid #2271b1;">
                    <span style="font-size:12px; color:#2271b1; font-weight:600;">📹 Vídeo importado</span>
                    <div style="margin-top:4px; display:flex; gap:8px; align-items:center;">
                        <img src="<?php echo esc_url($video_thumb); ?>" style="width:60px; height:60px; object-fit:cover; border-radius:4px;" />
                        <span style="font-size:11px; color:#646970; word-break:break-all;"><?php echo esc_html(basename($video_url ?: '')); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <p>
                    <label><strong>🔢 Ordem de Exibição no Carrossel:</strong></label><br>
                    <input type="number" name="zimny_video_order" value="<?php echo esc_attr($order); ?>" style="width:120px; font-size:14px; padding:6px; margin-top:4px;">
                </p>

                <p>
                    <label><strong>📂 Carrosséis vinculados:</strong></label><br>
                    <?php
                    $terms = get_the_terms($post->ID, 'video_carousel');
                    if ($terms && !is_wp_error($terms)):
                        foreach ($terms as $term):
                            echo '<span style="display:inline-block; background:#f0f6fc; color:#2271b1; padding:2px 10px; border-radius:3px; font-size:12px; margin:2px;">' . esc_html($term->name) . '</span>';
                        endforeach;
                    else:
                        echo '<span style="color:#8c8f94; font-size:12px;">Nenhum carrossel vinculado.</span>';
                    endif;
                    ?>
                </p>
            </div>
        </div>
        <?php
    }

    // ── Meta Box: Evento (Configurações) ─────────────────────────────────────

    public function render_event_meta_box_content($post) {
        wp_nonce_field('zimny_save_event_meta', 'zimny_event_nonce');

        $category = get_post_meta($post->ID, '_zimny_event_category', true);
        $order = get_post_meta($post->ID, '_zimny_event_order', true);
        $show_on_home = get_post_meta($post->ID, '_zimny_event_show_on_home', true);
        $home_order = get_post_meta($post->ID, '_zimny_event_home_order', true);
        if ($order === '') $order = 0;
        if ($home_order === '') $home_order = 0;
        ?>
        <div class="zimny-event-meta-wrap">
            <div class="field-group">
                <label for="zimny_event_category">Categoria do Evento</label>
                <select name="zimny_event_category" id="zimny_event_category">
                    <option value="producao" <?php selected($category, 'producao'); ?>>Produção Zimny</option>
                    <option value="cobertura" <?php selected($category, 'cobertura'); ?>>Cobertura</option>
                </select>
            </div>

            <div class="field-group">
                <label for="zimny_event_order">Ordem de Exibição</label>
                <input type="number" name="zimny_event_order" id="zimny_event_order" value="<?php echo esc_attr($order); ?>" min="0" step="1" />
                <p class="description">Menor número aparece primeiro na lista de eventos.</p>
            </div>

            <div class="field-group" style="min-width:100%;">
                <label>
                    <input type="checkbox" name="zimny_event_show_on_home" value="1" <?php checked($show_on_home, '1'); ?> />
                    Mostrar no carrossel de eventos da Home
                </label>
            </div>

            <div class="field-group" style="min-width:200px;">
                <label for="zimny_event_home_order">Ordem no Carrossel da Home</label>
                <input type="number" name="zimny_event_home_order" id="zimny_event_home_order" value="<?php echo esc_attr($home_order); ?>" min="0" step="1" />
                <p class="description">Usado apenas se "Mostrar na Home" estiver marcado.</p>
            </div>
        </div>
        <?php
    }

    // ── Meta Box: Galeria do Evento ──────────────────────────────────────────

    public function render_event_gallery_meta_box($post) {
        wp_nonce_field('zimny_save_event_gallery', 'zimny_event_gallery_nonce');

        $gallery_json = get_post_meta($post->ID, '_zimny_event_gallery', true);
        $gallery = $gallery_json ? json_decode($gallery_json, true) : array();
        if (!is_array($gallery)) $gallery = array();
        ?>
        <div id="zimny-gallery-wrap">
            <p>
                <button type="button" class="button button-primary" onclick="openMediaLibrary()">
                    📁 Adicionar Mídia da Biblioteca
                </button>
                <button type="button" class="button" onclick="addVideoUrl()">
                    🔗 Adicionar URL de Vídeo
                </button>
            </p>

            <div id="zimny-gallery-items" class="zimny-gallery-items">
                <?php if (empty($gallery)): ?>
                    <div class="zimny-gallery-empty">
                        <span class="dashicons dashicons-format-image" style="font-size:40px; width:40px; height:40px; color:#dcdcde;"></span>
                        <p style="margin:8px 0 0;">Nenhuma mídia na galeria. Clique em "Adicionar Mídia" para começar.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($gallery as $i => $item): ?>
                    <div class="zimny-gallery-item" data-index="<?php echo $i; ?>">
                        <div class="gallery-thumb">
                            <img src="<?php echo esc_url($item['thumbnail'] ?? $item['url']); ?>" alt="" />
                        </div>
                        <div class="gallery-info">
                            <div class="gallery-title"><?php echo esc_html($item['title'] ?? 'Sem título'); ?></div>
                            <div class="gallery-meta">
                                <?php echo strtoupper($item['type'] ?? 'photo'); ?>
                                · <?php echo $item['orientation'] ?? 'landscape'; ?>
                                <?php if (!empty($item['is_featured'])): ?>
                                · <strong style="color:#f5a623;">★ EM DESTAQUE</strong>
                                <?php endif; ?>
                                <?php if (!empty($item['is_cobertura'])): ?>
                                · <strong style="color:#2271b1;">🎥 COBERTURA</strong>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="gallery-actions">
                            <button type="button" class="<?php echo !empty($item['is_featured']) ? 'featured' : ''; ?>" onclick="toggleFeatured(this)" title="Marcar como destaque">
                                ★
                            </button>
                            <button type="button" class="cobertura <?php echo !empty($item['is_cobertura']) ? 'cobertura-on' : ''; ?>" onclick="toggleCobertura(this)" title="Marcar como cobertura (aparece no grid de cobertura do app)">
                                🎥
                            </button>
                            <button type="button" onclick="moveGalleryItem(this, -1)" title="Mover para cima" <?php echo $i === 0 ? 'disabled style="opacity:0.3;"' : ''; ?>>
                                ↑
                            </button>
                            <button type="button" onclick="moveGalleryItem(this, 1)" title="Mover para baixo" <?php echo $i === count($gallery) - 1 ? 'disabled style="opacity:0.3;"' : ''; ?>>
                                ↓
                            </button>
                            <button type="button" class="remove" onclick="removeGalleryItem(this)" title="Remover">
                                ✕
                            </button>
                        </div>
                        <input type="hidden" name="zimny_gallery_data[]" value='<?php echo esc_attr(json_encode($item)); ?>' />
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <p class="description" style="margin-top:8px;">
                Arraste os itens para reordenar. O item marcado como <strong>★ Em Destaque</strong> aparece como hero no topo da galeria do app. Itens marcados como <strong>🎥 Cobertura</strong> entram na seção "Cobertura" do app, exibida em grid 3 colunas.
            </p>
        </div>

        <script>
        let galleryMediaFrame = null;

        function openMediaLibrary() {
            if (galleryMediaFrame) {
                galleryMediaFrame.open();
                return;
            }

            galleryMediaFrame = wp.media({
                title: 'Selecionar Mídia para a Galeria',
                button: { text: 'Adicionar à Galeria' },
                multiple: true,
                library: { type: ['image', 'video'] }
            });

            galleryMediaFrame.on('select', function() {
                const attachments = galleryMediaFrame.state().get('selection').toJSON();
                attachments.forEach(function(att) {
                    const isImage = att.type === 'image';
                    const orientation = att.width && att.height
                        ? (att.width > att.height ? 'landscape' : (att.height > att.width ? 'portrait' : 'square'))
                        : 'landscape';

                    const item = {
                        id: 'media_' + att.id,
                        type: isImage ? 'photo' : 'video',
                        url: att.url,
                        thumbnail: att.sizes?.thumbnail?.url || att.url,
                        title: att.title || 'Sem título',
                        orientation: orientation,
                        is_featured: false,
                        is_cobertura: false,
                        width: att.width || 0,
                        height: att.height || 0,
                    };

                    addGalleryItem(item);
                });
            });

            galleryMediaFrame.open();
        }

        function addVideoUrl() {
            const url = prompt('Insira a URL do vídeo (YouTube, Vimeo, ou MP4):');
            if (!url) return;

            const title = prompt('Título do vídeo:', 'Vídeo');
            if (title === null) return;

            const orientation = prompt('Orientação (portrait/landscape/square):', 'landscape') || 'landscape';

            const item = {
                id: 'video_' + Date.now(),
                type: 'video',
                url: url,
                thumbnail: '',
                title: title || 'Vídeo',
                orientation: orientation,
                is_featured: false,
                is_cobertura: false,
                width: orientation === 'portrait' ? 720 : 1280,
                height: orientation === 'portrait' ? 1280 : 720,
            };

            addGalleryItem(item);
        }

        function addGalleryItem(item) {
            const container = document.getElementById('zimny-gallery-items');
            const emptyMsg = container.querySelector('.zimny-gallery-empty');
            if (emptyMsg) emptyMsg.remove();

            const div = document.createElement('div');
            div.className = 'zimny-gallery-item';
            div.dataset.index = container.children.length;
            div.innerHTML = `
                <div class="gallery-thumb">
                    <img src="${item.thumbnail || item.url}" alt="" onerror="this.src='<?php echo admin_url('images/media-button-image.svg'); ?>';" />
                </div>
                <div class="gallery-info">
                    <div class="gallery-title">${item.title}</div>
                    <div class="gallery-meta">${item.type.toUpperCase()} · ${item.orientation}${item.is_featured ? ' · <strong style="color:#f5a623;">★ EM DESTAQUE</strong>' : ''}${item.is_cobertura ? ' · <strong style="color:#2271b1;">🎥 COBERTURA</strong>' : ''}</div>
                </div>
                <div class="gallery-actions">
                    <button type="button" class="${item.is_featured ? 'featured' : ''}" onclick="toggleFeatured(this)" title="Marcar como destaque">★</button>
                    <button type="button" class="cobertura ${item.is_cobertura ? 'cobertura-on' : ''}" onclick="toggleCobertura(this)" title="Marcar como cobertura (grid 3 colunas no app)">🎥</button>
                    <button type="button" onclick="moveGalleryItem(this, -1)" title="Mover para cima" disabled style="opacity:0.3;">↑</button>
                    <button type="button" onclick="moveGalleryItem(this, 1)" title="Mover para baixo">↓</button>
                    <button type="button" class="remove" onclick="removeGalleryItem(this)" title="Remover">✕</button>
                </div>
                <input type="hidden" name="zimny_gallery_data[]" value='${JSON.stringify(item)}' />
            `;
            container.appendChild(div);
            updateGalleryIndices();
        }

        function toggleFeatured(btn) {
            const item = btn.closest('.zimny-gallery-item');
            // Remove featured de todos os outros
            item.closest('.zimny-gallery-items').querySelectorAll('.gallery-actions button.featured').forEach(function(b) {
                b.classList.remove('featured');
            });
            // Toggle no clicado
            btn.classList.toggle('featured');

            // Atualiza o hidden input
            updateGalleryHiddenInputs();
        }

        function toggleCobertura(btn) {
            btn.classList.toggle('cobertura-on');
            // Atualiza o hidden input
            updateGalleryHiddenInputs();
        }

        function moveGalleryItem(btn, direction) {
            const item = btn.closest('.zimny-gallery-item');
            const container = item.closest('.zimny-gallery-items');
            const items = container.querySelectorAll('.zimny-gallery-item');
            const index = Array.from(items).indexOf(item);
            const newIndex = index + direction;

            if (newIndex < 0 || newIndex >= items.length) return;

            if (direction < 0) {
                container.insertBefore(item, items[newIndex]);
            } else {
                container.insertBefore(item, items[newIndex].nextSibling);
            }

            updateGalleryIndices();
            updateGalleryHiddenInputs();
        }

        function removeGalleryItem(btn) {
            if (!confirm('Remover este item da galeria?')) return;
            const item = btn.closest('.zimny-gallery-item');
            item.remove();
            updateGalleryIndices();

            // Se vazio, mostra mensagem
            const container = document.getElementById('zimny-gallery-items');
            if (container.querySelectorAll('.zimny-gallery-item').length === 0) {
                container.innerHTML = '<div class="zimny-gallery-empty"><span class="dashicons dashicons-format-image" style="font-size:40px; width:40px; height:40px; color:#dcdcde;"></span><p style="margin:8px 0 0;">Nenhuma mídia na galeria.</p></div>';
            }
        }

        function updateGalleryIndices() {
            const container = document.getElementById('zimny-gallery-items');
            container.querySelectorAll('.zimny-gallery-item').forEach(function(item, i) {
                item.dataset.index = i;
                const buttons = item.querySelectorAll('.gallery-actions button');
                // buttons order: ★ featured, 🎥 cobertura, ↑ up, ↓ down, ✕ remove
                const upBtn = buttons[2];
                const downBtn = buttons[3];
                const total = container.querySelectorAll('.zimny-gallery-item').length;
                if (upBtn) {
                    upBtn.disabled = i === 0;
                    upBtn.style.opacity = i === 0 ? '0.3' : '1';
                }
                if (downBtn) {
                    downBtn.disabled = i === total - 1;
                    downBtn.style.opacity = i === total - 1 ? '0.3' : '1';
                }
            });
        }

        function updateGalleryHiddenInputs() {
            const container = document.getElementById('zimny-gallery-items');
            container.querySelectorAll('.zimny-gallery-item').forEach(function(item) {
                const isFeatured = item.querySelector('.gallery-actions button.featured') !== null;
                const isCobertura = item.querySelector('.gallery-actions button.cobertura.cobertura-on') !== null;

                const existingInput = item.querySelector('input[name="zimny_gallery_data[]"]');
                if (existingInput) {
                    const data = JSON.parse(existingInput.value);
                    data.is_featured = isFeatured;
                    data.is_cobertura = isCobertura;
                    existingInput.value = JSON.stringify(data);
                }
            });
        }

        // Enable drag-and-drop reordering
        jQuery(document).ready(function($) {
            $("#zimny-gallery-items").sortable({
                items: ".zimny-gallery-item",
                handle: ".gallery-thumb",
                placeholder: "zimny-gallery-item drag-over",
                axis: "y",
                tolerance: "pointer",
                cursor: "grabbing",
                opacity: 0.6,
                update: function() {
                    updateGalleryIndices();
                    updateGalleryHiddenInputs();
                }
            });
        });
        </script>
        <?php
    }

    // ── Meta Box: Cobertura (Configurações) ──────────────────────────────────

    public function render_cobertura_settings_meta_box($post) {
        wp_nonce_field('zimny_save_cobertura_meta', 'zimny_cobertura_nonce');

        $show_on_home  = get_post_meta($post->ID, '_zimny_cobertura_show_on_home', true);
        $home_order    = get_post_meta($post->ID, '_zimny_cobertura_home_order', true);
        $cobertura_order = get_post_meta($post->ID, '_zimny_cobertura_order', true);
        $is_approved   = get_post_meta($post->ID, '_zimny_cobertura_approved', true);
        if ($home_order === '') $home_order = 0;
        if ($cobertura_order === '') $cobertura_order = 0;
        ?>
        <p>
            <label>
                <input type="checkbox" name="zimny_cobertura_show_on_home" value="1" <?php checked($show_on_home, '1'); ?> />
                <strong>Mostrar no grid da Home</strong>
            </label>
        </p>
        <p>
            <label><strong>Ordem na Home:</strong></label><br>
            <input type="number" name="zimny_cobertura_home_order" value="<?php echo esc_attr($home_order); ?>" min="0" step="1" style="width:100%;" />
        </p>
        <p>
            <label><strong>Ordem de Exibição:</strong></label><br>
            <input type="number" name="zimny_cobertura_order" value="<?php echo esc_attr($cobertura_order); ?>" min="0" step="1" style="width:100%;" />
            <span style="font-size:11px; color:#646970;">Menor número aparece primeiro.</span>
        </p>
        <p style="border-top:1px solid #dcdcde; padding-top:12px; margin-top:12px;">
            <label>
                <input type="checkbox" name="zimny_cobertura_approved" value="1" <?php checked($is_approved, '1'); ?> />
                <strong style="color:#46b450;">✅ Aprovado para exibição no App</strong>
            </label>
            <span style="display:block; font-size:11px; color:#646970; margin-top:4px;">
                Apenas coberturas aprovadas aparecem na tela "Cobertura de Eventos" do aplicativo.
            </span>
        </p>
        <?php
    }

    // ── Meta Box: Cobertura (Mídia) ──────────────────────────────────────────

    public function render_cobertura_media_meta_box($post) {
        wp_nonce_field('zimny_save_cobertura_media', 'zimny_cobertura_media_nonce');

        $media_json = get_post_meta($post->ID, '_zimny_cobertura_media', true);
        $media = $media_json ? json_decode($media_json, true) : array();
        if (!is_array($media)) $media = array();
        ?>
        <div id="zimny-cobertura-media-wrap">
            <p>
                <button type="button" class="button button-primary" onclick="openCoberturaMediaLibrary()">
                    📁 Adicionar Mídia da Biblioteca
                </button>
                <button type="button" class="button" onclick="addCoberturaVideoUrl()">
                    🔗 Adicionar URL de Vídeo
                </button>
                <button type="button" class="button" onclick="openZimnyVideoPicker()">
                    🎬 Selecionar Vídeos do Zimny
                </button>
            </p>

            <div id="zimny-cobertura-media-items" class="zimny-gallery-items">
                <?php if (empty($media)): ?>
                    <div class="zimny-gallery-empty">
                        <span class="dashicons dashicons-camera" style="font-size:40px; width:40px; height:40px; color:#dcdcde;"></span>
                        <p style="margin:8px 0 0;">Nenhuma mídia na cobertura. Clique em "Adicionar Mídia" para começar.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($media as $i => $item): ?>
                    <div class="zimny-gallery-item" data-index="<?php echo $i; ?>">
                        <div class="gallery-thumb">
                            <img src="<?php echo esc_url($item['thumbnail'] ?? $item['url']); ?>" alt="" />
                        </div>
                        <div class="gallery-info">
                            <div class="gallery-title"><?php echo esc_html($item['title'] ?? 'Sem título'); ?></div>
                            <div class="gallery-meta">
                                <?php echo strtoupper($item['type'] ?? 'photo'); ?>
                                · <?php echo $item['orientation'] ?? 'landscape'; ?>
                                <?php if (!empty($item['is_featured'])): ?>
                                · <strong style="color:#f5a623;">★ EM DESTAQUE</strong>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="gallery-actions">
                            <button type="button" class="<?php echo !empty($item['is_featured']) ? 'featured' : ''; ?>" onclick="toggleCoberturaFeatured(this)" title="Marcar como destaque">
                                ★
                            </button>
                            <button type="button" onclick="moveCoberturaMediaItem(this, -1)" title="Mover para cima" <?php echo $i === 0 ? 'disabled style="opacity:0.3;"' : ''; ?>>
                                ↑
                            </button>
                            <button type="button" onclick="moveCoberturaMediaItem(this, 1)" title="Mover para baixo" <?php echo $i === count($media) - 1 ? 'disabled style="opacity:0.3;"' : ''; ?>>
                                ↓
                            </button>
                            <button type="button" class="remove" onclick="removeCoberturaMediaItem(this)" title="Remover">
                                ✕
                            </button>
                        </div>
                        <input type="hidden" name="zimny_cobertura_media_data[]" value='<?php echo esc_attr(json_encode($item)); ?>' />
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <p class="description" style="margin-top:8px;">
                Arraste os itens para reordenar. O item marcado como <strong>★ Em Destaque</strong> aparece como capa da cobertura no app.
                Para vídeos do YouTube, a thumbnail é extraída automaticamente.
            </p>
        </div>

        <script>
        let coberturaMediaFrame = null;

        function openCoberturaMediaLibrary() {
            if (coberturaMediaFrame) {
                coberturaMediaFrame.open();
                return;
            }

            coberturaMediaFrame = wp.media({
                title: 'Selecionar Mídia para a Cobertura',
                button: { text: 'Adicionar à Cobertura' },
                multiple: true,
                library: { type: ['image', 'video'] }
            });

            coberturaMediaFrame.on('select', function() {
                const attachments = coberturaMediaFrame.state().get('selection').toJSON();
                attachments.forEach(function(att) {
                    const isImage = att.type === 'image';
                    const orientation = att.width && att.height
                        ? (att.width > att.height ? 'landscape' : (att.height > att.width ? 'portrait' : 'square'))
                        : 'landscape';

                    const item = {
                        id: 'media_' + att.id,
                        type: isImage ? 'photo' : 'video',
                        url: att.url,
                        thumbnail: att.sizes?.thumbnail?.url || att.url,
                        title: att.title || 'Sem título',
                        orientation: orientation,
                        is_featured: false,
                        width: att.width || 0,
                        height: att.height || 0,
                    };

                    addCoberturaMediaItem(item);
                });
            });

            coberturaMediaFrame.open();
        }

        /**
         * Extrai thumbnail do YouTube automaticamente a partir da URL.
         */
        function extractYoutubeThumbnail(url) {
            const patterns = [
                /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/,
                /youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/,
            ];
            for (const pattern of patterns) {
                const match = url.match(pattern);
                if (match) {
                    return 'https://img.youtube.com/vi/' + match[1] + '/hqdefault.jpg';
                }
            }
            return '';
        }

        /**
         * Extrai thumbnail do Vimeo via oEmbed (requer fetch).
         */
        async function extractVimeoThumbnail(url) {
            const match = url.match(/vimeo\.com\/(\d+)/);
            if (!match) return '';
            try {
                const resp = await fetch('https://vimeo.com/api/v2/video/' + match[1] + '.json');
                const data = await resp.json();
                return data[0]?.thumbnail_large || data[0]?.thumbnail_medium || '';
            } catch (e) {
                return '';
            }
        }

        async function addCoberturaVideoUrl() {
            const url = prompt('Insira a URL do vídeo (YouTube, Vimeo, ou MP4):');
            if (!url) return;

            const title = prompt('Título do vídeo:', 'Vídeo');
            if (title === null) return;

            const orientation = prompt('Orientação (portrait/landscape/square):', 'landscape') || 'landscape';

            // Auto-detect thumbnail
            let thumbnail = extractYoutubeThumbnail(url);
            if (!thumbnail) {
                thumbnail = await extractVimeoThumbnail(url);
            }

            const item = {
                id: 'video_' + Date.now(),
                type: 'video',
                url: url,
                thumbnail: thumbnail,
                title: title || 'Vídeo',
                orientation: orientation,
                is_featured: false,
                width: orientation === 'portrait' ? 720 : 1280,
                height: orientation === 'portrait' ? 1280 : 720,
            };

            addCoberturaMediaItem(item);
        }

        function addCoberturaMediaItem(item) {
            const container = document.getElementById('zimny-cobertura-media-items');
            const emptyMsg = container.querySelector('.zimny-gallery-empty');
            if (emptyMsg) emptyMsg.remove();

            const div = document.createElement('div');
            div.className = 'zimny-gallery-item';
            div.dataset.index = container.children.length;
            div.innerHTML = `
                <div class="gallery-thumb">
                    <img src="${item.thumbnail || item.url}" alt="" onerror="this.src='<?php echo admin_url('images/media-button-image.svg'); ?>';" />
                </div>
                <div class="gallery-info">
                    <div class="gallery-title">${item.title}</div>
                    <div class="gallery-meta">${item.type.toUpperCase()} · ${item.orientation}${item.is_featured ? ' · <strong style="color:#f5a623;">★ EM DESTAQUE</strong>' : ''}</div>
                </div>
                <div class="gallery-actions">
                    <button type="button" class="${item.is_featured ? 'featured' : ''}" onclick="toggleCoberturaFeatured(this)" title="Marcar como destaque">★</button>
                    <button type="button" onclick="moveCoberturaMediaItem(this, -1)" title="Mover para cima" disabled style="opacity:0.3;">↑</button>
                    <button type="button" onclick="moveCoberturaMediaItem(this, 1)" title="Mover para baixo">↓</button>
                    <button type="button" class="remove" onclick="removeCoberturaMediaItem(this)" title="Remover">✕</button>
                </div>
                <input type="hidden" name="zimny_cobertura_media_data[]" value='${JSON.stringify(item)}' />
            `;
            container.appendChild(div);
            updateCoberturaMediaIndices();
        }

        function toggleCoberturaFeatured(btn) {
            const item = btn.closest('.zimny-gallery-item');
            // Remove featured de todos os outros
            item.closest('#zimny-cobertura-media-items').querySelectorAll('.gallery-actions button.featured').forEach(function(b) {
                b.classList.remove('featured');
            });
            // Toggle no clicado
            btn.classList.toggle('featured');

            // Atualiza o hidden input
            updateCoberturaMediaHiddenInputs();
        }

        function moveCoberturaMediaItem(btn, direction) {
            const item = btn.closest('.zimny-gallery-item');
            const container = item.closest('#zimny-cobertura-media-items');
            const items = container.querySelectorAll('.zimny-gallery-item');
            const index = Array.from(items).indexOf(item);
            const newIndex = index + direction;

            if (newIndex < 0 || newIndex >= items.length) return;

            if (direction < 0) {
                container.insertBefore(item, items[newIndex]);
            } else {
                container.insertBefore(item, items[newIndex].nextSibling);
            }

            updateCoberturaMediaIndices();
            updateCoberturaMediaHiddenInputs();
        }

        function removeCoberturaMediaItem(btn) {
            if (!confirm('Remover este item da cobertura?')) return;
            const item = btn.closest('.zimny-gallery-item');
            item.remove();
            updateCoberturaMediaIndices();

            // Se vazio, mostra mensagem
            const container = document.getElementById('zimny-cobertura-media-items');
            if (container.querySelectorAll('.zimny-gallery-item').length === 0) {
                container.innerHTML = '<div class="zimny-gallery-empty"><span class="dashicons dashicons-camera" style="font-size:40px; width:40px; height:40px; color:#dcdcde;"></span><p style="margin:8px 0 0;">Nenhuma mídia na cobertura.</p></div>';
            }
        }

        function updateCoberturaMediaIndices() {
            const container = document.getElementById('zimny-cobertura-media-items');
            container.querySelectorAll('.zimny-gallery-item').forEach(function(item, i) {
                item.dataset.index = i;
                const buttons = item.querySelectorAll('.gallery-actions button');
                // buttons order: ★ featured, ↑ up, ↓ down, ✕ remove
                const upBtn = buttons[1];
                const downBtn = buttons[2];
                const total = container.querySelectorAll('.zimny-gallery-item').length;
                if (upBtn) {
                    upBtn.disabled = i === 0;
                    upBtn.style.opacity = i === 0 ? '0.3' : '1';
                }
                if (downBtn) {
                    downBtn.disabled = i === total - 1;
                    downBtn.style.opacity = i === total - 1 ? '0.3' : '1';
                }
            });
        }

        function updateCoberturaMediaHiddenInputs() {
            const container = document.getElementById('zimny-cobertura-media-items');
            container.querySelectorAll('.zimny-gallery-item').forEach(function(item) {
                const isFeatured = item.querySelector('.gallery-actions button.featured') !== null;

                const existingInput = item.querySelector('input[name="zimny_cobertura_media_data[]"]');
                if (existingInput) {
                    const data = JSON.parse(existingInput.value);
                    data.is_featured = isFeatured;
                    existingInput.value = JSON.stringify(data);
                }
            });
        }

        // Enable drag-and-drop reordering
        jQuery(document).ready(function($) {
            $("#zimny-cobertura-media-items").sortable({
                items: ".zimny-gallery-item",
                handle: ".gallery-thumb",
                placeholder: "zimny-gallery-item drag-over",
                axis: "y",
                tolerance: "pointer",
                cursor: "grabbing",
                opacity: 0.6,
                update: function() {
                    updateCoberturaMediaIndices();
                    updateCoberturaMediaHiddenInputs();
                }
            });
        });

        /* ── Zimny Video Picker ──────────────────────────────────────────── */

        function openZimnyVideoPicker() {
            // Remove existing modal if any
            const old = document.getElementById('zimny-video-picker-overlay');
            if (old) old.remove();

            const overlay = document.createElement('div');
            overlay.id = 'zimny-video-picker-overlay';
            overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:99999;display:flex;align-items:center;justify-content:center;';

            const modal = document.createElement('div');
            modal.style.cssText = 'background:#fff;border-radius:10px;width:90%;max-width:720px;max-height:85vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 10px 40px rgba(0,0,0,0.4);';

            modal.innerHTML = `
                <div style="padding:16px 20px;border-bottom:1px solid #dcdcde;display:flex;align-items:center;justify-content:space-between;">
                    <h2 style="margin:0;font-size:16px;">🎬 Selecionar Vídeo do Zimny</h2>
                    <button type="button" onclick="closeZimnyVideoPicker()" style="background:none;border:none;font-size:22px;cursor:pointer;color:#666;">&times;</button>
                </div>
                <div id="zimny-video-grid" style="padding:16px;overflow-y:auto;display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:12px;min-height:200px;">
                    <div style="grid-column:1/-1;text-align:center;padding:40px;color:#999;">Carregando vídeos...</div>
                </div>
            `;

            overlay.appendChild(modal);
            document.body.appendChild(overlay);

            // Fetch videos via AJAX
            jQuery.post(ajaxurl, { action: 'zimny_get_videos' }, function(resp) {
                const grid = document.getElementById('zimny-video-grid');
                if (!resp.success || !resp.data.videos.length) {
                    grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:#999;">Nenhum vídeo encontrado. Importe vídeos pelo "Importador em Lote".</div>';
                    return;
                }
                grid.innerHTML = '';
                resp.data.videos.forEach(function(video) {
                    const card = document.createElement('div');
                    card.style.cssText = 'cursor:pointer;border-radius:6px;overflow:hidden;border:2px solid #e0e0e0;transition:border-color 0.2s;background:#fafafa;';
                    card.onmouseenter = function() { card.style.borderColor = '#2271b1'; };
                    card.onmouseleave = function() { card.style.borderColor = '#e0e0e0'; };
                    card.onclick = function() { pickZimnyVideo(video); };
                    card.innerHTML = `
                        <div style="aspect-ratio:16/9;background:#1c1c1e;overflow:hidden;">
                            <img src="${video.thumbnail_url || ''}" alt="" style="width:100%;height:100%;object-fit:cover;" onerror="this.src='<?php echo admin_url('images/media-button-image.svg'); ?>';" />
                        </div>
                        <div style="padding:6px 8px;font-size:11px;font-weight:600;color:#333;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${video.title}</div>
                    `;
                    grid.appendChild(card);
                });
            }).fail(function() {
                const grid = document.getElementById('zimny-video-grid');
                grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:#d63638;">Erro ao carregar vídeos.</div>';
            });
        }

        function closeZimnyVideoPicker() {
            const el = document.getElementById('zimny-video-picker-overlay');
            if (el) el.remove();
        }

        function pickZimnyVideo(video) {
            const item = {
                id: 'zimny_video_' + video.id,
                type: 'video',
                url: video.video_url,
                thumbnail: video.thumbnail_url,
                title: video.title,
                orientation: 'landscape',
                is_featured: false,
                width: 0,
                height: 0,
            };
            addCoberturaMediaItem(item);
            closeZimnyVideoPicker();
        }
        </script>
        <?php
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // SAVE META BOXES
    // ═══════════════════════════════════════════════════════════════════════════

    public function save_video_meta_boxes($post_id) {
        if (!isset($_POST['zimny_video_nonce']) || !wp_verify_nonce($_POST['zimny_video_nonce'], 'zimny_save_video_meta')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        if (isset($_POST['zimny_video_file_url'])) {
            update_post_meta($post_id, '_zimny_video_file_url', esc_url_raw($_POST['zimny_video_file_url']));
        }
        if (isset($_POST['zimny_video_order'])) {
            update_post_meta($post_id, '_zimny_video_order', intval($_POST['zimny_video_order']));
        }
    }

    public function save_event_meta_boxes($post_id) {
        if (!isset($_POST['zimny_event_nonce']) || !wp_verify_nonce($_POST['zimny_event_nonce'], 'zimny_save_event_meta')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        if (isset($_POST['zimny_event_category'])) {
            update_post_meta($post_id, '_zimny_event_category', sanitize_text_field($_POST['zimny_event_category']));
        }
        if (isset($_POST['zimny_event_order'])) {
            update_post_meta($post_id, '_zimny_event_order', intval($_POST['zimny_event_order']));
        }
        $show_on_home = isset($_POST['zimny_event_show_on_home']) ? '1' : '0';
        update_post_meta($post_id, '_zimny_event_show_on_home', $show_on_home);
        if (isset($_POST['zimny_event_home_order'])) {
            update_post_meta($post_id, '_zimny_event_home_order', intval($_POST['zimny_event_home_order']));
        }

        // Save gallery
        if (isset($_POST['zimny_gallery_data'])) {
            $gallery = array();
            foreach ($_POST['zimny_gallery_data'] as $json) {
                $item = json_decode(wp_unslash($json), true);
                if (is_array($item)) {
                    $gallery[] = $item;
                }
            }
            update_post_meta($post_id, '_zimny_event_gallery', wp_json_encode($gallery));
        }
    }

    // ── Save: Cobertura ──────────────────────────────────────────────────────

    public function save_cobertura_meta_boxes($post_id) {
        // Save cobertura settings (side meta box)
        if (isset($_POST['zimny_cobertura_nonce']) && wp_verify_nonce($_POST['zimny_cobertura_nonce'], 'zimny_save_cobertura_meta')) {
            if (!defined('DOING_AUTOSAVE') || !DOING_AUTOSAVE) {
                if (current_user_can('edit_post', $post_id)) {
                    $show_on_home = isset($_POST['zimny_cobertura_show_on_home']) ? '1' : '0';
                    update_post_meta($post_id, '_zimny_cobertura_show_on_home', $show_on_home);
                    if (isset($_POST['zimny_cobertura_home_order'])) {
                        update_post_meta($post_id, '_zimny_cobertura_home_order', intval($_POST['zimny_cobertura_home_order']));
                    }
                    if (isset($_POST['zimny_cobertura_order'])) {
                        update_post_meta($post_id, '_zimny_cobertura_order', intval($_POST['zimny_cobertura_order']));
                    }
                    // Approval status
                    $is_approved = isset($_POST['zimny_cobertura_approved']) ? '1' : '0';
                    update_post_meta($post_id, '_zimny_cobertura_approved', $is_approved);
                }
            }
        }

        // Save cobertura media
        if (isset($_POST['zimny_cobertura_media_nonce']) && wp_verify_nonce($_POST['zimny_cobertura_media_nonce'], 'zimny_save_cobertura_media')) {
            if (!defined('DOING_AUTOSAVE') || !DOING_AUTOSAVE) {
                if (current_user_can('edit_post', $post_id)) {
                    if (isset($_POST['zimny_cobertura_media_data'])) {
                        $media = array();
                        foreach ($_POST['zimny_cobertura_media_data'] as $json) {
                            $item = json_decode(wp_unslash($json), true);
                            if (is_array($item)) {
                                $media[] = $item;
                            }
                        }
                        update_post_meta($post_id, '_zimny_cobertura_media', wp_json_encode($media));
                    }
                }
            }
        }
    }
}