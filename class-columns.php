<?php
/**
 * Zimny_Admin_Columns - Colunas personalizadas e bulk edit
 *
 * @package Zimny_Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Zimny_Admin_Columns {

    public function __construct() {
        // Custom columns na lista de vídeos
        add_filter('manage_zimny_video_posts_columns', array($this, 'add_video_list_columns'));
        add_action('manage_zimny_video_posts_custom_column', array($this, 'render_video_list_columns'), 10, 2);

        // Custom columns na lista de eventos
        add_filter('manage_zimny_event_posts_columns', array($this, 'add_event_list_columns'));
        add_action('manage_zimny_event_posts_custom_column', array($this, 'render_event_list_columns'), 10, 2);

        // Bulk edit: checkbox para limpar carrosséis
        add_action('bulk_edit_custom_box', array($this, 'render_bulk_edit_clear_carousel'), 10, 2);
        add_action('admin_footer', array($this, 'render_bulk_edit_js'));

        // Custom columns na lista de coberturas
        add_filter('manage_zimny_cobertura_posts_columns', array($this, 'add_cobertura_list_columns'));
        add_action('manage_zimny_cobertura_posts_custom_column', array($this, 'render_cobertura_list_columns'), 10, 2);

        // AJAX: limpar carrosséis em lote
        add_action('wp_ajax_zimny_bulk_clear_carousel', array($this, 'handle_bulk_clear_carousel'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // BULK EDIT: LIMPAR CARROSSÉIS
    // ═══════════════════════════════════════════════════════════════════════════

    public function render_bulk_edit_clear_carousel($column_name, $post_type) {
        if ($post_type !== 'zimny_video' || $column_name !== 'taxonomy-video_carousel') {
            return;
        }
        ?>
        <fieldset class="inline-edit-col-right" style="margin-top:12px;">
            <div class="inline-edit-col">
                <label class="alignleft" style="display:flex; align-items:center; gap:6px;">
                    <input type="checkbox" id="zimny_clear_carousel_bulk" value="1" />
                    <span class="checkbox-title">🗑️ Remover de todos os carrosséis</span>
                </label>
                <p class="description" style="margin:4px 0 0 24px; color:#d63638; font-size:11px;">
                    Marque para remover ESTES vídeos de TODOS os carrosséis.
                </p>
            </div>
        </fieldset>
        <?php
    }

    public function render_bulk_edit_js() {
        global $pagenow;
        if ($pagenow !== 'edit.php' || (get_query_var('post_type') !== 'zimny_video' && get_query_var('post_type') !== 'zimny_event')) {
            return;
        }
        ?>
        <script>
        jQuery(document).ready(function($) {
            $(document).on('click', '#bulk_edit', function() {
                var clearChecked = $('#zimny_clear_carousel_bulk').is(':checked');
                if (!clearChecked) return;

                var postIds = [];
                $('tbody th.check-column input[type="checkbox"]:checked').each(function() {
                    var id = $(this).val();
                    if (id) postIds.push(id);
                });

                if (postIds.length === 0) return;

                postIds.forEach(function(postId) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'zimny_bulk_clear_carousel',
                            post_id: postId,
                            _wpnonce: '<?php echo wp_create_nonce('zimny_bulk_clear_carousel'); ?>'
                        }
                    });
                });
            });
        });
        </script>
        <?php
    }

    public function handle_bulk_clear_carousel() {
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Sem permissão.');
        }
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'zimny_bulk_clear_carousel')) {
            wp_send_json_error('Nonce inválido.');
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        if (!$post_id) {
            wp_send_json_error('ID inválido.');
        }

        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'zimny_video') {
            wp_send_json_error('Post inválido.');
        }

        wp_set_post_terms($post_id, array(), 'video_carousel');
        wp_send_json_success(array(
            'post_id' => $post_id,
            'message' => 'Carrosséis removidos com sucesso.',
        ));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // CUSTOM COLUMNS
    // ═══════════════════════════════════════════════════════════════════════════

    // ── Vídeos ────────────────────────────────────────────────────────────────

    public function add_video_list_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $label) {
            if ($key === 'cb') {
                $new_columns[$key] = $label;
                $new_columns['zimny_thumb'] = '<span class="dashicons dashicons-format-image" style="font-size:16px; width:16px; height:16px;" title="Thumbnail"></span>';
            } elseif ($key === 'title') {
                $new_columns[$key] = 'Título / Vídeo';
            } elseif ($key === 'taxonomy-video_carousel') {
                $new_columns[$key] = 'Carrosséis';
            } elseif ($key === 'date') {
                $new_columns['zimny_order'] = 'Ordem';
                $new_columns[$key] = $label;
            } else {
                $new_columns[$key] = $label;
            }
        }
        return $new_columns;
    }

    public function render_video_list_columns($column, $post_id) {
        switch ($column) {
            case 'zimny_thumb':
                $thumb_id  = get_post_thumbnail_id($post_id);
                $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'thumbnail') : '';
                $video_url = get_post_meta($post_id, '_zimny_video_file_url', true);
                ?>
                <div style="display:flex; gap:10px; align-items:center; padding:4px 0;">
                    <div style="width:54px; height:96px; border-radius:4px; overflow:hidden; background:#f0f0f1; border:1px solid #dcdcde; flex-shrink:0;">
                        <?php if ($thumb_url): ?>
                            <img src="<?php echo esc_url($thumb_url); ?>" style="width:100%; height:100%; object-fit:cover;" alt="" />
                        <?php else: ?>
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center;">
                                <span class="dashicons dashicons-format-video" style="font-size:20px; width:20px; height:20px; color:#8c8f94;"></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:11px; color:#646970; line-height:1.4; min-width:0;">
                        <?php if ($video_url): ?>
                            <span style="display:inline-block; background:#e7f4e8; color:#46b450; padding:0 6px; border-radius:2px; font-size:10px; font-weight:600;">MP4</span>
                            <span style="display:block; margin-top:2px; word-break:break-all; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                <?php echo esc_html(basename($video_url)); ?>
                            </span>
                        <?php else: ?>
                            <span style="color:#d63638; font-size:10px;">Sem vídeo</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
                break;

            case 'zimny_order':
                $order = get_post_meta($post_id, '_zimny_video_order', true);
                echo '<span style="font-size:16px; font-weight:700; color:#2271b1;">' . esc_html($order ?: '0') . '</span>';
                break;
        }
    }

    // ── Eventos ───────────────────────────────────────────────────────────────

    public function add_event_list_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $label) {
            if ($key === 'cb') {
                $new_columns[$key] = $label;
                $new_columns['event_thumb'] = '<span class="dashicons dashicons-format-image" style="font-size:16px; width:16px; height:16px;" title="Thumb"></span>';
            } elseif ($key === 'title') {
                $new_columns[$key] = 'Evento';
            } elseif ($key === 'date') {
                $new_columns['event_category'] = 'Categoria';
                $new_columns['event_media_count'] = 'Mídias';
                $new_columns['event_home'] = 'Home';
                $new_columns[$key] = 'Criado em';
            } else {
                $new_columns[$key] = $label;
            }
        }
        return $new_columns;
    }

    public function render_event_list_columns($column, $post_id) {
        switch ($column) {
            case 'event_thumb':
                $thumb_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
                ?>
                <div style="width:54px; height:54px; border-radius:4px; overflow:hidden; background:#f0f0f1; border:1px solid #dcdcde;">
                    <?php if ($thumb_url): ?>
                        <img src="<?php echo esc_url($thumb_url); ?>" style="width:100%; height:100%; object-fit:cover;" alt="" />
                    <?php else: ?>
                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center;">
                            <span class="dashicons dashicons-calendar-alt" style="font-size:20px; width:20px; height:20px; color:#8c8f94;"></span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
                break;

            case 'event_category':
                $cat = get_post_meta($post_id, '_zimny_event_category', true);
                if ($cat === 'producao') {
                    echo '<span style="display:inline-block; background:#e7f4e8; color:#46b450; padding:2px 8px; border-radius:3px; font-size:11px; font-weight:600;">Produção Zimny</span>';
                } elseif ($cat === 'cobertura') {
                    echo '<span style="display:inline-block; background:#f0f6fc; color:#2271b1; padding:2px 8px; border-radius:3px; font-size:11px; font-weight:600;">Cobertura</span>';
                } else {
                    echo '<span style="color:#8c8f94; font-size:11px;">—</span>';
                }
                break;

            case 'event_media_count':
                $gallery = get_post_meta($post_id, '_zimny_event_gallery', true);
                $items = $gallery ? json_decode($gallery, true) : array();
                $count = is_array($items) ? count($items) : 0;
                echo '<span style="font-size:14px; font-weight:600; color:#2271b1;">' . $count . '</span>';
                break;

            case 'event_home':
                $show = get_post_meta($post_id, '_zimny_event_show_on_home', true);
                echo $show
                    ? '<span class="dashicons dashicons-yes" style="color:#46b450; font-size:20px;"></span>'
                    : '<span class="dashicons dashicons-no-alt" style="color:#8c8f94; font-size:20px;"></span>';
                break;
        }
    }

    // ── Coberturas ────────────────────────────────────────────────────────────

    public function add_cobertura_list_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $label) {
            if ($key === 'cb') {
                $new_columns[$key] = $label;
                $new_columns['cobertura_thumb'] = '<span class="dashicons dashicons-camera" style="font-size:16px; width:16px; height:16px;" title="Thumb"></span>';
            } elseif ($key === 'title') {
                $new_columns[$key] = 'Cobertura';
            } elseif ($key === 'date') {
                $new_columns['cobertura_media_count'] = 'Mídias';
                $new_columns['cobertura_approved'] = 'Aprovado';
                $new_columns['cobertura_home'] = 'Home';
                $new_columns[$key] = 'Criado em';
            } else {
                $new_columns[$key] = $label;
            }
        }
        return $new_columns;
    }

    public function render_cobertura_list_columns($column, $post_id) {
        switch ($column) {
            case 'cobertura_thumb':
                $media_json = get_post_meta($post_id, '_zimny_cobertura_media', true);
                $media = $media_json ? json_decode($media_json, true) : array();
                $thumb_url = '';
                if (is_array($media) && !empty($media)) {
                    $featured = null;
                    foreach ($media as $item) {
                        if (!empty($item['is_featured'])) {
                            $featured = $item;
                            break;
                        }
                    }
                    if (!$featured) $featured = $media[0];
                    $thumb_url = $featured['thumbnail'] ?? $featured['url'] ?? '';
                }
                if (empty($thumb_url)) {
                    $thumb_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
                }
                ?>
                <div style="width:54px; height:54px; border-radius:4px; overflow:hidden; background:#f0f0f1; border:1px solid #dcdcde;">
                    <?php if ($thumb_url): ?>
                        <img src="<?php echo esc_url($thumb_url); ?>" style="width:100%; height:100%; object-fit:cover;" alt="" />
                    <?php else: ?>
                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center;">
                            <span class="dashicons dashicons-camera" style="font-size:20px; width:20px; height:20px; color:#8c8f94;"></span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
                break;

            case 'cobertura_media_count':
                $media_json = get_post_meta($post_id, '_zimny_cobertura_media', true);
                $items = $media_json ? json_decode($media_json, true) : array();
                $count = is_array($items) ? count($items) : 0;
                echo '<span style="font-size:14px; font-weight:600; color:#2271b1;">' . $count . '</span>';
                break;

            case 'cobertura_approved':
                $approved = get_post_meta($post_id, '_zimny_cobertura_approved', true);
                echo $approved
                    ? '<span class="dashicons dashicons-yes" style="color:#46b450; font-size:20px;" title="Aprovado"></span>'
                    : '<span class="dashicons dashicons-no-alt" style="color:#d63638; font-size:20px;" title="Não aprovado"></span>';
                break;

            case 'cobertura_home':
                $show = get_post_meta($post_id, '_zimny_cobertura_show_on_home', true);
                echo $show
                    ? '<span class="dashicons dashicons-yes" style="color:#46b450; font-size:20px;"></span>'
                    : '<span class="dashicons dashicons-no-alt" style="color:#8c8f94; font-size:20px;"></span>';
                break;
        }
    }
}