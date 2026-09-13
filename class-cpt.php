<?php
/**
 * Zimny_Admin_Post_Types - Registra CPTs e Taxonomy
 *
 * @package Zimny_Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Zimny_Admin_Post_Types {

    public function __construct() {
        add_action('init', array($this, 'register_cpt_and_taxonomies'));
    }

    /**
     * Registra os CPTs (zimny_video, zimny_event, zimny_cobertura) e a taxonomy (video_carousel).
     */
    public function register_cpt_and_taxonomies() {
        // ── CPT: Vídeos (legado) ─────────────────────────────────────────────
        register_post_type('zimny_video', array(
            'labels' => array(
                'name'               => 'Zimny Play (Vídeos App)',
                'singular_name'      => 'Vídeo',
                'add_new_item'       => 'Adicionar Novo Vídeo',
                'edit_item'          => 'Editar Vídeo',
                'new_item'           => 'Novo Vídeo',
                'view_item'          => 'Ver Vídeo',
                'search_items'       => 'Buscar Vídeos',
                'not_found'          => 'Nenhum vídeo encontrado',
                'menu_name'          => 'Zimny Admin',
            ),
            'public'             => true,
            'has_archive'        => true,
            'show_in_rest'       => true,
            'menu_icon'          => 'dashicons-admin-generic',
            'supports'           => array('title', 'thumbnail'),
            'show_in_menu'       => true,
            'menu_position'      => 25,
        ));

        // ── CPT: Eventos ─────────────────────────────────────────────────────
        register_post_type('zimny_event', array(
            'labels' => array(
                'name'               => 'Eventos',
                'singular_name'      => 'Evento',
                'add_new_item'       => 'Adicionar Novo Evento',
                'edit_item'          => 'Editar Evento',
                'new_item'           => 'Novo Evento',
                'view_item'          => 'Ver Evento',
                'search_items'       => 'Buscar Eventos',
                'not_found'          => 'Nenhum evento encontrado',
                'not_found_in_trash' => 'Nenhum evento na lixeira',
                'all_items'          => 'Todos os Eventos',
                'menu_name'          => 'Eventos',
            ),
            'public'             => true,
            'has_archive'        => true,
            'show_in_rest'       => true,
            'menu_icon'          => 'dashicons-calendar-alt',
            'supports'           => array('title', 'thumbnail', 'editor'),
            'show_in_menu'       => 'edit.php?post_type=zimny_video',
            'menu_position'      => 1,
        ));

        // ── CPT: Coberturas (substitui o conceito de "galeria") ──────────────
        register_post_type('zimny_cobertura', array(
            'labels' => array(
                'name'               => 'Coberturas',
                'singular_name'      => 'Cobertura',
                'add_new_item'       => 'Adicionar Nova Cobertura',
                'edit_item'          => 'Editar Cobertura',
                'new_item'           => 'Nova Cobertura',
                'view_item'          => 'Ver Cobertura',
                'search_items'       => 'Buscar Coberturas',
                'not_found'          => 'Nenhuma cobertura encontrada',
                'not_found_in_trash' => 'Nenhuma cobertura na lixeira',
                'all_items'          => 'Todas as Coberturas',
                'menu_name'          => 'Coberturas',
            ),
            'public'             => true,
            'has_archive'        => true,
            'show_in_rest'       => true,
            'menu_icon'          => 'dashicons-camera',
            'supports'           => array('title', 'thumbnail', 'editor', 'excerpt'),
            'show_in_menu'       => 'edit.php?post_type=zimny_video',
            'menu_position'      => 2,
        ));

        // ── Taxonomy: Carrosséis de Vídeo ────────────────────────────────────
        register_taxonomy('video_carousel', 'zimny_video', array(
            'labels' => array(
                'name'              => 'Carrosséis e Colunas (App)',
                'singular_name'     => 'Carrossel',
                'search_items'      => 'Buscar Carrosséis',
                'all_items'         => 'Todos os Carrosséis',
                'edit_item'         => 'Editar Carrossel',
                'update_item'       => 'Atualizar Carrossel',
                'add_new_item'      => 'Adicionar Novo Carrossel',
                'new_item_name'     => 'Nome do Novo Carrossel',
                'menu_name'         => 'Carrosséis',
            ),
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => array('slug' => 'video-carousel'),
        ));
    }
}