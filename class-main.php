<?php
/**
 * Zimny_Admin - Classe coordenadora principal
 *
 * Gerencia a inicialização de todos os módulos, constantes compartilhadas
 * e a migração de opções antigas.
 *
 * @package Zimny_Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Zimny_Admin {

    const OPTION_LAYOUT               = 'zimny_admin_home_layout';
    const OPTION_SPLASH_VIDEOS        = 'zimny_admin_splash_videos';
    const OPTION_HOME_FEATURED_VIDEOS = 'zimny_admin_home_featured_videos';
    const OPTION_HOME_EVENTS          = 'zimny_admin_home_events';
    const OPTION_MARKETING_PLANS      = 'zimny_admin_marketing_plans';
    const OPTION_ADS                  = 'zimny_admin_ads';
    const OPTION_COLUNISTAS_CONFIG    = 'zimny_admin_colunistas_config';
    const OPTION_ANUNCIE_CARDS        = 'zimny_admin_anuncie_cards';

    /**
     * Instância singleton.
     *
     * @var Zimny_Admin
     */
    private static $instance = null;

    /**
     * Referências dos submódulos para acesso entre classes.
     *
     * @var Zimny_Admin_Post_Types
     */
    public $post_types;

    /**
     * @var Zimny_Admin_Columns
     */
    public $columns;

    /**
     * @var Zimny_Admin_Menu
     */
    public $menu;

    /**
     * @var Zimny_Admin_Assets
     */
    public $assets;

    /**
     * @var Zimny_Admin_Home_Layout
     */
    public $home_layout;

    /**
     * @var Zimny_Admin_Event_Order
     */
    public $event_order;

    /**
     * @var Zimny_Admin_Bulk_Import
     */
    public $bulk_import;

    /**
     * @var Zimny_Admin_Ads
     */
    public $ads;

    /**
     * @var Zimny_Admin_Anuncie_Cards
     */
    public $anuncie_cards;

    /**
     * @var Zimny_Admin_Meta_Boxes
     */
    public $meta_boxes;

    /**
     * @var Zimny_Admin_Ajax
     */
    public $ajax;

    /**
     * @var Zimny_Admin_Tv
     */
    public $tv;

    /**
     * @var Zimny_Admin_Rest_Api
     */
    public $rest_api;

    /**
     * @var Zimny_Admin_Translator
     */
    public $translator;

    /**
     * Construtor: instancia todos os submódulos e registra hooks globais.
     */
    public function __construct() {
        if (null !== self::$instance) {
            return;
        }
        self::$instance = $this;

        // Instancia submódulos (ordem importa para dependências)
        $this->post_types   = new Zimny_Admin_Post_Types();
        $this->columns      = new Zimny_Admin_Columns();
        $this->menu         = new Zimny_Admin_Menu($this);
        $this->assets       = new Zimny_Admin_Assets($this);
        $this->home_layout  = new Zimny_Admin_Home_Layout();
        $this->event_order  = new Zimny_Admin_Event_Order();
        $this->bulk_import  = new Zimny_Admin_Bulk_Import();
        $this->ads          = new Zimny_Admin_Ads();
        $this->anuncie_cards = new Zimny_Admin_Anuncie_Cards();
        $this->meta_boxes   = new Zimny_Admin_Meta_Boxes();
        $this->ajax         = new Zimny_Admin_Ajax();
        $this->tv           = new Zimny_Admin_Tv();
        $this->push         = new Zimny_Admin_Push($this);
        $this->rest_api     = new Zimny_Admin_Rest_Api($this);
        $this->translator   = new Zimny_Admin_Translator($this);

        // Activation hook registrado no bootstrap (zimny-admin.php)
        // mas o método migrate_old_options está aqui.
    }

    /**
     * Retorna a instância singleton.
     *
     * @return Zimny_Admin
     */
    public static function get_instance() {
        return self::$instance;
    }

    /**
     * Migra opções antigas do Zimny Play para o novo prefixo.
     */
    public function migrate_old_options() {
        $old_new = array(
            'zimny_play_home_layout'          => self::OPTION_LAYOUT,
            'zimny_play_splash_videos'        => self::OPTION_SPLASH_VIDEOS,
            'zimny_play_home_featured_videos' => self::OPTION_HOME_FEATURED_VIDEOS,
        );
        foreach ($old_new as $old => $new) {
            $value = get_option($old, null);
            if ($value !== null) {
                update_option($new, $value, false);
            }
        }
    }
}