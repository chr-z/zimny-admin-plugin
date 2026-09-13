<?php
/**
 * Zimny_Admin_Menu - Registra os submenus do painel
 *
 * @package Zimny_Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Zimny_Admin_Menu {

    /**
     * Referência ao coordenador principal.
     *
     * @var Zimny_Admin
     */
    private $admin;

    public function __construct($admin) {
        $this->admin = $admin;
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu() {
        // Bulk Import
        add_submenu_page(
            'edit.php?post_type=zimny_video',
            'Importador em Lote',
            '⚡ Importar em Lote',
            'manage_options',
            'zimny-bulk-import',
            array($this->admin->bulk_import, 'render_bulk_import_page')
        );

        // Home Layout Manager
        add_submenu_page(
            'edit.php?post_type=zimny_video',
            'Layout da Home',
            '🏠 Layout da Home',
            'manage_options',
            'zimny-home-layout',
            array($this->admin->home_layout, 'render_home_layout_page')
        );

        // Event Order (submenu under Zimny Admin)
        add_submenu_page(
            'edit.php?post_type=zimny_video',
            'Ordenar Eventos',
            '🔀 Ordenar Eventos',
            'manage_options',
            'zimny-event-order',
            array($this->admin->event_order, 'render_event_order_page')
        );

        // Ads Manager
        add_submenu_page(
            'edit.php?post_type=zimny_video',
            'Gerenciar Publicidades',
            '📢 Publicidades',
            'manage_options',
            'zimny-ads',
            array($this->admin->ads, 'render_ads_page')
        );

        // Anuncie Conosco Cards
        add_submenu_page(
            'edit.php?post_type=zimny_video',
            'Anuncie Conosco',
            '💼 Anuncie Conosco',
            'manage_options',
            'zimny-anuncie-cards',
            array($this->admin->anuncie_cards, 'render_anuncie_cards_page')
        );

        // Translator (Traduções de Posts)
        add_submenu_page(
            'edit.php?post_type=zimny_video',
            'Traduções de Posts',
            '🌐 Traduções',
            'manage_options',
            'zimny-translator',
            array($this->admin->translator, 'render_translator_page')
        );

        // Ads Reports
        add_submenu_page(
            'edit.php?post_type=zimny_video',
            'Relatórios de Publicidade',
            '📊 Relatórios Ads',
            'manage_options',
            'zimny-ads-reports',
            array($this->admin->ads, 'render_ads_reports_page')
        );

        // ── Zimny TV 24/7 ─────────────────────────────────────────────────

        add_submenu_page(
            'edit.php?post_type=zimny_video',
            'Zimny TV 24/7 — Painel',
            '📺 Zimny TV',
            'manage_options',
            'zimny-tv',
            array($this->admin->tv, 'render_dashboard_page')
        );

        add_submenu_page(
            'edit.php?post_type=zimny_video',
            'Vídeos da Programação',
            '📺 Vídeos TV',
            'manage_options',
            'zimny-tv-videos',
            array($this->admin->tv, 'render_videos_page')
        );

        add_submenu_page(
            'edit.php?post_type=zimny_video',
            'Configurações da Zimny TV',
            '📺 Config. TV',
            'manage_options',
            'zimny-tv-settings',
            array($this->admin->tv, 'render_settings_page')
        );
    }
}