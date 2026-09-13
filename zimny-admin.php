<?php
/**
 * Plugin Name: Zimny Admin — Gerenciador Completo do App
 * Description: Gerencia vídeos, carrosséis, layout da Home, eventos, coberturas e splash player do aplicativo Zimny Magazine.
 * Version:     3.11.0
 * Author:      Zimny Magazine
 * Text Domain: zimny-admin
 *
 * CPTs:
 *   zimny_video     → "Zimny Play (Vídeos App)"
 *   zimny_event     → "Eventos Zimny"
 *   zimny_cobertura → "Coberturas"
 *
 * Taxonomy:
 *   video_carousel → "Carrosséis e Colunas (App)"
 *
 * Endpoints:
 *   GET /wp-json/zimny/v1/videos                 → Lista vídeos (filtro: ?carousel=slug&limit=N)
 *   GET /wp-json/zimny/v1/home-layout             → Layout ordenado da Home do app
 *   GET /wp-json/zimny/v1/splash-videos           → Vídeos do Splash Player (PiP)
 *   GET /wp-json/zimny/v1/home-featured-videos    → Vídeos em destaque da Home
 *   GET /wp-json/zimny/v1/events                  → Lista eventos (filtro: ?category=&home=true)
 *   GET /wp-json/zimny/v1/events/{id}/media       → Galeria de mídia de um evento
 *   GET /wp-json/zimny/v1/coberturas              → Lista coberturas (filtro: ?home=true&approved=true&limit=N)
 *   GET /wp-json/zimny/v1/coberturas/{id}/media   → Mídia de uma cobertura
 *   GET /wp-json/zimny/v1/podcast                 → Episódios do YouTube sem expor a chave
 */

if (!defined('ABSPATH')) {
    exit;
}

// ─── Backward compatibility: se o plugin antigo estiver ativo, desativa silenciosamente ───
if (class_exists('Zimny_Play_Manager')) {
    return;
}

// ─── Autoload: requires all modular files (mesma pasta do plugin) ───
$plugin_dir = plugin_dir_path(__FILE__);

$modules = array(
    'class-updater.php',   // Atualização automática via GitHub Releases
    'class-main.php',
    'class-cpt.php',
    'class-columns.php',
    'class-menu.php',
    'class-assets.php',
    'class-home-layout.php',
    'class-event-order.php',
    'class-bulk-import.php',
    'class-ads.php',
    'class-anuncie-cards.php',
    'class-meta-boxes.php',
    'class-ajax.php',
    'class-rest-api.php',
    'class-tv.php',         // Zimny TV 24/7 — transmissão linear + endpoint /live-tv
    'class-push.php',       // Notificações push do app — meta box editorial + Expo Push API
    'class-ig-feed.php',    // Feed Instagram @zimnymagazine — Graph API oficial + fallbacks (endpoint /instagram)
    'class-translator.php', // Tradução server-side (Google Translate API v2)
);

foreach ($modules as $module) {
    $path = $plugin_dir . $module;
    if (file_exists($path)) {
        require_once $path;
    }
}

// ─── Bootstrap: instancia o coordenador principal ───
$zimny_admin = new Zimny_Admin();

// ─── Migração das opções antigas na ativação do plugin ───
register_activation_hook(__FILE__, array($zimny_admin, 'migrate_old_options'));
