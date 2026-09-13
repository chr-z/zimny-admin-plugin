<?php
/**
 * Zimny_Admin_Updater - Atualização automática via GitHub Releases
 *
 * Integra o Plugin Update Checker (PUC) para que o plugin
 * seja atualizado diretamente da tela "Plugins → Atualizar"
 * do WordPress, sem necessidade de FTP manual.
 *
 * @package Zimny_Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Zimny_Admin_Updater {

    public function __construct() {
        try {
            $this->init();
        } catch (\Throwable $e) {
            if (function_exists('error_log')) {
                error_log('Zimny Admin Updater: ' . $e->getMessage());
            }
        }
    }

    private function init() {
        $puc_file = __DIR__ . '/plugin-update-checker/plugin-update-checker.php';

        if (!file_exists($puc_file)) {
            return;
        }

        require_once $puc_file;

        // ── Token de autenticação ──────────────────────────────────────────
        $token = '';
        if (defined('ZIMNY_GITHUB_TOKEN')) {
            $token = ZIMNY_GITHUB_TOKEN;
        }
        $token = apply_filters('zimny_github_token', $token);

        // ── Constrói o checker ─────────────────────────────────────────────
        // OBS: NÃO usamos enableReleaseAssets() porque o arquivo principal
        // zimny-admin.php AGORA ESTÁ NO REPOSITÓRIO. O PUC baixa o ZIP
        // gerado automaticamente pelo GitHub a partir da tag, que contém
        // todos os arquivos necessários na estrutura correta.
        $update_checker = \YahnisElsts\PluginUpdateChecker\v5p5\PucFactory::buildUpdateChecker(
            'https://github.com/chr-z/zimny-admin/',
            __DIR__ . '/zimny-admin.php',
            'zimny-admin/zimny-admin.php'
        );

        // ── Branch: o repositório usa 'main' como padrão ──────────────────
        $update_checker->setBranch('main');

        // ── Autenticação (necessário porque o repositório é privado) ──────
        if (!empty($token)) {
            $update_checker->getVcsApi()->setAuthentication($token);
        }
    }
}

new Zimny_Admin_Updater();