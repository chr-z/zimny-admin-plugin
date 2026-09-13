> **Sobre este repositório** — cópia pública do plugin que roda em produção no
> site da revista: gestão de vídeos, carrosséis, layout da home, eventos,
> publicidade, push e tradução PT/EN/ES. Credenciais, dados editoriais e
> configuração do servidor ficam fora; o histórico foi resetado. Deploy real e
> histórico completo seguem no repositório privado.

# Zimny Admin — Gerenciador Completo do App

Plugin WordPress que gerencia vídeos, carrosséis, layout da Home, eventos, galerias e splash player do aplicativo Zimny Magazine.

**Repositório**: https://github.com/chr-z/zimny-admin-plugin

## Estrutura modular (padrão WordPress)

O plugin é instalado em `wp-content/plugins/zimny-admin/` e o arquivo principal fica **dentro** da pasta do plugin:

```
zimny-admin/                ← Pasta do plugin no WordPress
├── zimny-admin.php         → Arquivo principal do plugin (bootstrap + header)
├── class-main.php          → Coordenador principal (constantes + migration)
├── class-updater.php       → Atualização automática via GitHub Releases
├── class-cpt.php           → CPTs e Taxonomy
├── class-columns.php       → Colunas personalizadas + bulk edit
├── class-menu.php          → Submenus do painel
├── class-assets.php        → Assets (CSS/JS inline)
├── class-home-layout.php   → Página "Layout da Home"
├── class-event-order.php   → Página "Ordenar Eventos"
├── class-bulk-import.php   → Página "Importador em Lote"
├── class-ads.php           → Página "Gerenciar Publicidades"
├── class-anuncie-cards.php → Página "Anuncie Conosco Cards"
├── class-meta-boxes.php    → Meta boxes de vídeo/evento
├── class-ajax.php          → Handlers AJAX
├── class-rest-api.php      → Endpoints REST
├── plugin-update-checker/  → Biblioteca Plugin Update Checker (PUC)
├── build-release.ps1       → Script de automação de release
├── README.md
└── .gitignore
```

## Atualização automática (como funciona)

O plugin usa o [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) v5.5 apontando para o repositório privado `chr-z/zimny-admin`. A cada release publicada, o WordPress detecta a nova versão e oferece a atualização na tela **Plugins → Atualizar**.

## Publicando uma nova versão (passo a passo)

Automático — execute o script no **Prompt do PowerShell**:

```powershell
cd d:\ZIMNY\MobileApp\wordpress\plugins\zimny-admin
.\build-release.ps1 -Version 3.1.1
```

O script:
1. Atualiza a `Version` no header de `zimny-admin.php`.
2. Commita e cria a tag `v3.1.1`.
3. Empurra (push) para o GitHub.
4. Gera o ZIP de distribuição (sem a pasta `.git`).
5. Cria a Release no GitHub com o ZIP anexado.

Manual — passo a passo completo:

1. **Edite** a `Version` em `zimny-admin.php` (linha 5).
2. **Commit e tag**:
   ```powershell
   cd d:\ZIMNY\MobileApp\wordpress\plugins\zimny-admin
   git add zimny-admin.php
   git commit -m "chore: bump version to 3.1.1"
   git tag v3.1.1
   git push origin main
   git push origin v3.1.1
   ```
3. **Gere o ZIP** (a pasta inteira, sem `.git`):
   ```powershell
   powershell -Command "$stage = \"$Env:TEMP\zimny-stage\"; New-Item -ItemType Directory -Path $stage -Force | Out-Null; Get-ChildItem -Exclude '.git' | ForEach-Object { Copy-Item $_.FullName $stage -Recurse }; Compress-Archive -Path \"$stage*\" -DestinationPath \"$Env:TEMP\zimny-admin-3.1.1.zip\" -Force; Remove-Item $stage -Recurse -Force"
   ```
4. **Crie a Release**:
   ```powershell
   gh release create v3.1.1 "$Env:TEMP\zimny-admin-3.1.1.zip" --repo chr-z/zimny-admin --title "Zimny Admin v3.1.1" --notes ""
   ```

## Configuração no WordPress (para repositório privado)

Como o repositório é **privado**, o Plugin Update Checker precisa de um **token do GitHub** para baixar a atualização.

### 1. Gerar um token no GitHub

1. Acesse https://github.com/settings/tokens
2. Clique **Generate new token (classic)**
3. Dê um nome (ex: `zimny-admin-wordpress`)
4. Marque o escopo **`repo`** (acesso total a repositórios privados)
5. Clique **Generate token**
6. Copie o token (começa com `ghp_`)

### 2. Adicionar o token ao WordPress

**Opção A — Constante no `wp-config.php`** (recomendada):

```php
define('ZIMNY_GITHUB_TOKEN', 'ghp_xxxxxxxxxxxxxxxxxxxx');
```

**Opção B — Filtro no `functions.php` do tema**:

```php
add_filter('zimny_github_token', function() {
    return 'ghp_xxxxxxxxxxxxxxxxxxxx';
});
```

### 3. Verificar atualização

1. No WordPress, vá em **Plugins → Plugins Instalados**
2. O plugin "Zimny Admin" deve aparecer na lista
3. Se houver uma nova versão, aparecerá a mensagem "Nova versão disponível"
4. Clique em **Atualizar** para baixar e instalar automaticamente

## Requisitos

- WordPress 6.0+
- PHP 7.4+
- Conexão HTTPS do servidor com GitHub (para baixar as releases)