<?php
/**
 * Zimny_Admin_Assets - Enfileira assets do admin (CSS/JS inline)
 *
 * @package Zimny_Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Zimny_Admin_Assets {

    /**
     * Referência ao coordenador principal.
     *
     * @var Zimny_Admin
     */
    private $admin;

    public function __construct($admin) {
        $this->admin = $admin;
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'zimny-bulk-import') !== false) {
            wp_enqueue_style('zimny-admin-css', false);
        }
        if (strpos($hook, 'zimny-home-layout') !== false) {
            wp_enqueue_style('wp-admin');
            wp_enqueue_style('dashicons');
            wp_enqueue_media();
            wp_enqueue_script('jquery-ui-sortable');
            wp_add_inline_style('wp-admin', $this->admin->home_layout->get_home_layout_css());
            wp_add_inline_script('media-editor', '
                jQuery(document).ready(function($) {
                    var _marketingCurrentItem = null;

                    $(document).on("click", ".change-plan-image", function(e) {
                        e.preventDefault();
                        var btn = this;
                        _marketingCurrentItem = btn.closest(".marketing-plan-item");

                        var frame = wp.media({
                            title: "Selecionar imagem do plano de marketing",
                            button: { text: "Usar esta imagem" },
                            multiple: false,
                            library: { type: "image" }
                        });

                        frame.on("select", function() {
                            var attachment = frame.state().get("selection").first().toJSON();
                            var item = _marketingCurrentItem;
                            if (!item) return;

                            var urlInput = item.querySelector(".plan-image-url");
                            var idInput = item.querySelector(".plan-image-id");
                            if (urlInput) urlInput.value = attachment.url;
                            if (idInput) idInput.value = attachment.id;

                            var thumbDiv = item.children[1];
                            if (thumbDiv) {
                                thumbDiv.innerHTML = "<img src=\"" + attachment.url + "\" style=\"width:100%;height:100%;object-fit:cover;\" />";
                            }
                        });

                        frame.open();
                    });
                });
            ');
        }
        // Event order page assets
        if (strpos($hook, 'zimny-event-order') !== false) {
            wp_enqueue_script('jquery-ui-sortable');
        }

        // Ads page assets
        if (strpos($hook, 'zimny-ads') !== false) {
            wp_enqueue_media();
            wp_enqueue_style('wp-admin');
            wp_enqueue_style('dashicons');
            wp_enqueue_script('jquery-ui-sortable');
            wp_add_inline_style('wp-admin', $this->admin->ads->get_ads_css());
            wp_add_inline_script('media-editor', '
                jQuery(document).ready(function($) {
                    var _adsCurrentItem = null;

                    $(document).on("click", ".change-ad-image", function(e) {
                        e.preventDefault();
                        var btn = this;
                        _adsCurrentItem = btn.closest(".zimny-ad-item");

                        var frame = wp.media({
                            title: "Selecionar imagem da publicidade",
                            button: { text: "Usar esta imagem" },
                            multiple: false,
                            library: { type: "image" }
                        });

                        frame.on("select", function() {
                            var attachment = frame.state().get("selection").first().toJSON();
                            var item = _adsCurrentItem;
                            if (!item) return;

                            var urlInput = item.querySelector(".ad-image-url");
                            var idInput = item.querySelector(".ad-image-id");
                            if (urlInput) urlInput.value = attachment.url;
                            if (idInput) idInput.value = attachment.id;

                            var thumbDiv = item.querySelector(".ad-thumb");
                            if (thumbDiv) {
                                thumbDiv.innerHTML = "<img src=\"" + attachment.url + "\" style=\"width:100%;height:100%;object-fit:cover;\" />";
                            }
                        });

                        frame.open();
                    });
                });
            ');
        }

        // Anuncie Conosco page assets
        if (strpos($hook, 'zimny-anuncie-cards') !== false) {
            wp_enqueue_media();
            wp_enqueue_style('wp-admin');
            wp_enqueue_style('dashicons');
            wp_enqueue_script('jquery-ui-sortable');
            wp_add_inline_style('wp-admin', $this->admin->anuncie_cards->get_anuncie_cards_css());
            wp_add_inline_script('media-editor', '
                jQuery(document).ready(function($) {
                    var _anuncieCurrentItem = null;

                    $(document).on("click", ".change-anuncie-image", function(e) {
                        e.preventDefault();
                        var btn = this;
                        _anuncieCurrentItem = btn.closest(".zimny-anuncie-item");

                        var frame = wp.media({
                            title: "Selecionar imagem do card",
                            button: { text: "Usar esta imagem" },
                            multiple: false,
                            library: { type: "image" }
                        });

                        frame.on("select", function() {
                            var attachment = frame.state().get("selection").first().toJSON();
                            var item = _anuncieCurrentItem;
                            if (!item) return;

                            var urlInput = item.querySelector(".anuncie-image-url");
                            var idInput = item.querySelector(".anuncie-image-id");
                            if (urlInput) urlInput.value = attachment.url;
                            if (idInput) idInput.value = attachment.id;

                            var thumbDiv = item.querySelector(".preview-img");
                            if (thumbDiv) {
                                thumbDiv.innerHTML = "<img src=\"" + attachment.url + "\" style=\"width:100%;height:100%;object-fit:cover;\" />";
                            }
                        });

                        frame.open();
                    });
                });
            ');
        }

        // Event meta box assets
        global $post_type;
        if ($post_type === 'zimny_event') {
            wp_enqueue_media();
            wp_add_inline_style('wp-admin', $this->admin->meta_boxes->get_event_meta_box_css());
        }
    }
}