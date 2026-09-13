<?php
/**
 * Zimny_Admin_Event_Order - Página "Ordenar Eventos" (drag-and-drop)
 *
 * @package Zimny_Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Zimny_Admin_Event_Order {

    public function render_event_order_page() {
        $event_order = get_option('zimny_admin_event_order', array());
        if (!is_array($event_order)) $event_order = array();

        $all_events = get_posts(array(
            'post_type'      => 'zimny_event',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));

        // Sort: events in saved order first, then by date
        if (!empty($event_order)) {
            $ordered = array();
            $unordered = array();
            foreach ($all_events as $ev) {
                $idx = array_search($ev->ID, $event_order);
                if ($idx !== false) {
                    $ordered[$idx] = $ev;
                } else {
                    $unordered[] = $ev;
                }
            }
            ksort($ordered);
            $all_events = array_merge($ordered, $unordered);
        }
        ?>
        <div class="wrap">
            <h1 style="display:flex; align-items:center; gap:10px;">
                <span class="dashicons dashicons-sort" style="font-size:32px; width:32px; height:32px;"></span>
                Ordenar Eventos
            </h1>
            <p>Arraste os eventos para definir a ordem de exibição na aba <strong>Eventos</strong> do app. O primeiro da lista aparece primeiro.</p>

            <div id="event-order-status" style="display:none; padding:10px 14px; border-radius:4px; margin-bottom:16px; font-weight:600;"></div>

            <div style="background:#fff; border:1px solid #dcdcde; padding:20px; border-radius:8px; max-width:800px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                <ul id="zimny-event-sortable" style="list-style:none; padding:0; margin:0;">
                    <?php foreach ($all_events as $ev):
                        $thumb = get_the_post_thumbnail_url($ev->ID, 'thumbnail');
                        $cat = get_post_meta($ev->ID, '_zimny_event_category', true);
                        $cat_label = $cat === 'producao' ? 'Produção' : ($cat === 'cobertura' ? 'Cobertura' : '');
                    ?>
                    <li data-post-id="<?php echo $ev->ID; ?>" style="display:flex; align-items:center; gap:12px; padding:12px 14px; margin-bottom:6px; background:#f0f0f1; border:1px solid #dcdcde; border-radius:6px; cursor:grab; transition:border-color 0.2s;">
                        <span class="dashicons dashicons-menu" style="color:#8c8f94; cursor:grab;"></span>
                        <div style="width:50px; height:50px; border-radius:4px; overflow:hidden; background:#fff; border:1px solid #dcdcde; flex-shrink:0;">
                            <?php if ($thumb): ?>
                                <img src="<?php echo esc_url($thumb); ?>" style="width:100%; height:100%; object-fit:cover;" />
                            <?php else: ?>
                                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center;">
                                    <span class="dashicons dashicons-calendar-alt" style="color:#8c8f94;"></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:600; font-size:14px;"><?php echo esc_html($ev->post_title); ?></div>
                            <div style="font-size:11px; color:#646970; margin-top:2px;">
                                ID: <?php echo $ev->ID; ?>
                                <?php if ($cat_label): ?>
                                · <span style="color:<?php echo $cat === 'producao' ? '#46b450' : '#2271b1'; ?>;"><?php echo $cat_label; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span style="font-size:11px; color:#8c8f94; background:#fff; padding:2px 8px; border-radius:10px; border:1px solid #dcdcde;">
                            #<?php echo isset($event_order) ? array_search($ev->ID, $event_order) !== false ? array_search($ev->ID, $event_order) + 1 : '-' : '-'; ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <div style="margin-top:20px; display:flex; gap:8px; align-items:center;">
                    <button type="button" class="button button-primary button-hero" onclick="saveEventOrder()">
                        💾 Salvar Ordem dos Eventos
                    </button>
                    <span id="event-order-spinner" style="display:none;" class="spinner"></span>
                </div>
            </div>
        </div>

        <style>
        #zimny-event-sortable li:hover { border-color:#2271b1; }
        #zimny-event-sortable li.dragging { opacity:0.5; border-style:dashed; background:#f0f6fc; }
        #zimny-event-sortable li.drag-over { border-color:#2271b1; border-style:dashed; background:#f0f6fc; }
        </style>

        <script>
        jQuery(document).ready(function($) {
            $("#zimny-event-sortable").sortable({
                handle: ".dashicons-menu",
                axis: "y",
                tolerance: "pointer",
                cursor: "grabbing",
                opacity: 0.6,
                placeholder: {
                    element: function() {
                        return $('<li style="border:2px dashed #2271b1; background:#f0f6fc; border-radius:6px; height:60px; margin-bottom:6px;"></li>');
                    },
                    update: function() {}
                },
                update: function() {
                    // Update position numbers
                    $("#zimny-event-sortable li").each(function(i) {
                        $(this).find('span:last').text('#' + (i + 1));
                    });
                }
            });
            $("#zimny-event-sortable").disableSelection();
        });

        async function saveEventOrder() {
            const statusEl = document.getElementById('event-order-status');
            const spinner = document.getElementById('event-order-spinner');
            spinner.style.display = 'inline-block';

            const order = [];
            document.querySelectorAll('#zimny-event-sortable li').forEach(function(li) {
                order.push(parseInt(li.dataset.postId));
            });

            try {
                const formData = new FormData();
                formData.append('action', 'zimny_save_event_order');
                formData.append('event_ids', JSON.stringify(order));
                formData.append('_wpnonce', '<?php echo wp_create_nonce('zimny_save_event_order'); ?>');

                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();

                statusEl.className = 'zimny-layout-status ' + (result.success ? 'success' : 'error');
                statusEl.textContent = result.success
                    ? '✅ Ordem salva com sucesso!'
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
        </script>
        <?php
    }
}