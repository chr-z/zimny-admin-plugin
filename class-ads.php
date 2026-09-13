<?php
/**
 * Zimny_Admin_Ads - Página "Gerenciar Publicidades"
 *
 * Gerencia anúncios com logo + imagem de apoio, CTA customizável,
 * estatísticas de impressões/cliques e preview visual moderno.
 *
 * @package Zimny_Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Zimny_Admin_Ads {

    /**
     * CSS inline da página de publicidades.
     */
    public function get_ads_css(): string {
        return '
        /* ─── Layout geral ─── */
        .zimny-ads-wrap { max-width: 1100px; }
        .zimny-ads-wrap h1 { display:flex; align-items:center; gap:10px; margin-bottom:24px; }
        .zimny-ads-list { display:flex; flex-direction:column; gap:20px; margin-top:16px; min-height:80px; padding:16px; background:#f0f0f1; border:2px dashed #dcdcde; border-radius:12px; }

        /* ─── Card principal ─── */
        .zimny-ad-item {
            background:#fff; border:1px solid #dcdcde;
            border-radius:12px; overflow:hidden; cursor:move;
            transition:box-shadow 0.3s, border-color 0.3s, transform 0.3s;
        }
        .zimny-ad-item:hover { border-color:#2271b1; box-shadow:0 4px 20px rgba(0,0,0,0.10); transform:translateY(-2px); }
        .zimny-ad-item.dragging { opacity:0.5; }

        /* ─── Header do card ─── */
        .zimny-ad-header {
            display:flex; align-items:center; gap:12px;
            padding:12px 16px; background:#f8f9fa;
            border-bottom:1px solid #e8e8eb;
        }
        .zimny-ad-header .handle { color:#8c8f94; font-size:20px; cursor:grab; flex-shrink:0; }
        .zimny-ad-header .ad-title-display { flex:1; font-weight:600; font-size:14px; color:#1d2327; }
        .zimny-ad-header .ad-title-display.empty { color:#8c8f94; font-style:italic; }
        .zimny-ad-header .ad-badge {
            display:inline-block; padding:3px 10px; border-radius:4px;
            font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;
        }
        .zimny-ad-header .ad-badge.banner { background:#e7f4e8; color:#46b450; }
        .zimny-ad-header .ad-badge.sponsored { background:#fcf9e8; color:#996800; }
        .zimny-ad-header .ad-badge.custom { background:#f0f6fc; color:#2271b1; }
        .zimny-ad-header .ad-badge.image_banner { background:#f0e6ff; color:#7b2ff7; }

        /* ─── Corpo do card: dois painéis ─── */
        .zimny-ad-body {
            display:flex; gap:0; min-height:280px;
        }

        /* ─── Painel esquerdo: Preview visual ─── */
        .zimny-ad-preview {
            width:320px; flex-shrink:0;
            background:linear-gradient(135deg, #fafafa 0%, #f0f0f1 100%);
            border-right:1px solid #e8e8eb;
            display:flex; align-items:center; justify-content:center;
            padding:20px; position:relative; overflow:hidden;
        }
        .zimny-ad-preview-inner {
            width:100%; max-width:260px; border-radius:12px; overflow:hidden;
            background:#fff; box-shadow:0 4px 20px rgba(0,0,0,0.10);
            transition:transform 0.3s;
        }
        .zimny-ad-item:hover .zimny-ad-preview-inner { transform:scale(1.02); }

        /* Preview: Imagem de fundo */
        .zimny-ad-preview-image {
            width:100%; height:120px; overflow:hidden;
            background:#e8e8eb; position:relative;
        }
        .zimny-ad-preview-image img { width:100%; height:100%; object-fit:cover; }
        .zimny-ad-preview-image .no-image {
            display:flex; align-items:center; justify-content:center;
            height:100%; color:#8c8f94; font-size:11px;
        }

        /* Preview: Logo sobreposto */
        .zimny-ad-preview-logo {
            width:48px; height:48px; border-radius:8px; overflow:hidden;
            border:2px solid #fff; box-shadow:0 2px 8px rgba(0,0,0,0.12);
            background:#fff; margin-top:-24px; margin-left:12px;
            display:flex; align-items:center; justify-content:center;
        }
        .zimny-ad-preview-logo img { width:100%; height:100%; object-fit:contain; padding:4px; }
        .zimny-ad-preview-logo .no-logo {
            font-size:18px; color:#8c8f94;
        }

        /* Preview: Conteúdo textual */
        .zimny-ad-preview-content { padding:10px 12px 14px; }
        .zimny-ad-preview-content .preview-title {
            font-family:Georgia,serif; font-size:15px; font-weight:700;
            color:#1d2327; margin:0 0 4px; line-height:1.2;
        }
        .zimny-ad-preview-content .preview-title.empty { color:#8c8f94; font-style:italic; font-family:sans-serif; }
        .zimny-ad-preview-content .preview-desc {
            font-size:11px; color:#646970; margin:0 0 10px; line-height:1.4;
            display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
        }
        .zimny-ad-preview-content .preview-desc.empty { color:#c3c4c7; font-style:italic; }
        .zimny-ad-preview-content .preview-cta {
            display:inline-block; font-size:10px; font-weight:700; text-transform:uppercase;
            letter-spacing:0.1em; color:#fff; background:#1d2327;
            padding:6px 16px; border-radius:4px;
            transition:all 0.2s;
        }
        .zimny-ad-preview-content .preview-cta.empty { background:#dcdcde; color:#8c8f94; }

        /* ─── Painel direito: Campos de edição ─── */
        .zimny-ad-fields {
            flex:1; padding:16px 20px; display:flex; flex-direction:column; gap:10px;
            min-width:0;
        }
        .zimny-ad-fields .field-row {
            display:flex; gap:10px; align-items:flex-start; flex-wrap:wrap;
        }
        .zimny-ad-fields .field-row .field-group {
            flex:1; min-width:140px; display:flex; flex-direction:column; gap:3px;
        }
        .zimny-ad-fields .field-row .field-group label {
            font-size:11px; font-weight:600; color:#50575e; text-transform:uppercase; letter-spacing:0.3px;
        }
        .zimny-ad-fields input[type="text"],
        .zimny-ad-fields textarea,
        .zimny-ad-fields select {
            width:100%; padding:7px 10px; border:1px solid #dcdcde;
            border-radius:6px; font-size:13px; background:#fff;
            transition:border-color 0.2s, box-shadow 0.2s;
        }
        .zimny-ad-fields input[type="text"]:focus,
        .zimny-ad-fields textarea:focus,
        .zimny-ad-fields select:focus {
            border-color:#2271b1; box-shadow:0 0 0 1px #2271b1; outline:none;
        }
        .zimny-ad-fields textarea { min-height:50px; resize:vertical; font-size:12px; }
        .zimny-ad-fields .image-preview-thumb {
            width:36px; height:36px; border-radius:6px; overflow:hidden;
            border:1px solid #dcdcde; flex-shrink:0; background:#f0f0f1;
            display:flex; align-items:center; justify-content:center; cursor:pointer;
            transition:border-color 0.2s;
        }
        .zimny-ad-fields .image-preview-thumb:hover { border-color:#2271b1; }
        .zimny-ad-fields .image-preview-thumb img { width:100%; height:100%; object-fit:cover; }
        .zimny-ad-fields .image-preview-thumb .dashicons { font-size:18px; width:18px; height:18px; color:#8c8f94; }

        /* ─── Estatísticas ─── */
        .zimny-ad-stats {
            display:flex; gap:12px; padding:10px 14px;
            background:#f8f9fa; border-radius:8px; border:1px solid #e8e8eb;
            margin-top:4px;
        }
        .zimny-ad-stats .stat-item {
            flex:1; text-align:center;
        }
        .zimny-ad-stats .stat-item .stat-value {
            font-size:18px; font-weight:700; color:#1d2327; line-height:1.2;
        }
        .zimny-ad-stats .stat-item .stat-label {
            font-size:9px; text-transform:uppercase; letter-spacing:0.5px;
            color:#8c8f94; font-weight:600;
        }
        .zimny-ad-stats .stat-item .stat-value.ctr { color:#2271b1; }
        .zimny-ad-stats .stat-divider { width:1px; background:#e0e0e0; }

        /* ─── Ações do card ─── */
        .zimny-ad-actions {
            display:flex; align-items:center; gap:8px; padding:10px 16px;
            background:#f8f9fa; border-top:1px solid #e8e8eb;
            flex-wrap:wrap;
        }
        .zimny-ad-actions .action-btn {
            display:inline-flex; align-items:center; gap:4px;
            padding:6px 12px; border-radius:6px; border:1px solid #dcdcde;
            background:#fff; cursor:pointer; font-size:12px; font-weight:500;
            transition:all 0.2s; text-decoration:none; color:#1d2327;
        }
        .zimny-ad-actions .action-btn:hover { border-color:#2271b1; background:#f0f6fc; }
        .zimny-ad-actions .action-btn.primary { background:#2271b1; border-color:#2271b1; color:#fff; }
        .zimny-ad-actions .action-btn.primary:hover { background:#135e96; border-color:#135e96; }
        .zimny-ad-actions .action-btn.danger { color:#d63638; }
        .zimny-ad-actions .action-btn.danger:hover { border-color:#d63638; background:#fcf0f1; }
        .zimny-ad-actions .action-btn .dashicons { font-size:14px; width:14px; height:14px; }

        /* ─── Status toggle ─── */
        .zimny-ad-status-toggle {
            display:inline-flex; align-items:center; gap:6px;
            padding:5px 12px; border-radius:20px; border:1px solid #dcdcde;
            background:#fff; cursor:pointer; font-size:11px; font-weight:600;
            transition:all 0.2s;
        }
        .zimny-ad-status-toggle.active { background:#e7f4e8; border-color:#46b450; color:#46b450; }
        .zimny-ad-status-toggle.inactive { background:#f0f0f1; border-color:#8c8f94; color:#8c8f94; }
        .zimny-ad-status-toggle .dashicons { font-size:14px; width:14px; height:14px; }

        /* ─── Botão adicionar ─── */
        .zimny-add-ad-area {
            margin-top:16px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;
        }

        /* ─── Status message ─── */
        .zimny-layout-status {
            display:none; padding:10px 14px; border-radius:6px;
            font-size:13px; font-weight:500; margin-bottom:12px;
        }
        .zimny-layout-status.success { display:block; background:#e7f4e8; border-left:4px solid #46b450; color:#2c5f2d; }
        .zimny-layout-status.error { display:block; background:#fcf0f1; border-left:4px solid #d63638; color:#8a2424; }

        /* ─── Responsivo ─── */
        @media (max-width: 782px) {
            .zimny-ad-body { flex-direction:column; }
            .zimny-ad-preview { width:100%; border-right:none; border-bottom:1px solid #e8e8eb; }
            .zimny-ad-preview-inner { max-width:100%; }
        }
        ';
    }

    public function render_ads_page() {
        $ads = get_option(Zimny_Admin::OPTION_ADS, array());
        if (!is_array($ads)) $ads = array();
        ?>
        <div class="wrap zimny-ads-wrap">
            <h1>
                <span class="dashicons dashicons-megaphone" style="font-size:32px; width:32px; height:32px;"></span>
                Gerenciar Publicidades
            </h1>
            <p>Gerencie os anúncios exibidos no aplicativo Zimny Magazine. Arraste para reordenar. Os anúncios ativos são exibidos aleatoriamente nas áreas configuradas.</p>

            <div id="ads-status" class="zimny-layout-status"></div>

            <div style="background:#fff; border:1px solid #dcdcde; padding:20px; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                <div id="zimny-ads-list" class="zimny-ads-list">
                    <?php if (!empty($ads)): ?>
                        <?php foreach ($ads as $i => $ad):
                            $this->render_ad_item($i, $ad);
                        endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="zimny-add-ad-area">
                    <button type="button" class="button" id="add-ad-btn" style="display:flex; align-items:center; gap:4px;">
                        <span class="dashicons dashicons-plus-alt2" style="font-size:16px; width:16px; height:16px;"></span> Nova Publicidade
                    </button>
                    <button type="button" class="button button-primary button-hero" onclick="saveAds()">
                        <span class="dashicons dashicons-cloud-saved" style="font-size:18px; width:18px; height:18px;"></span> Salvar Publicidades
                    </button>
                    <span id="ads-spinner" style="display:none;" class="spinner"></span>
                </div>
            </div>

            <div style="margin-top:24px; padding:16px; background:#f0f6fc; border-radius:8px; border-left:4px solid #2271b1;">
                <h3 style="margin:0 0 8px 0; font-size:14px;">📌 Como funciona</h3>
                <ul style="margin:0; padding-left:20px; font-size:12px; color:#50575e; line-height:1.8;">
                    <li>As publicidades aparecem no <strong>Bloco de Publicidade</strong> da Home (configurado no <strong>Layout da Home</strong>) e/ou dinamicamente dentro dos artigos.</li>
                    <li>Os anúncios são exibidos de forma <strong>aleatória</strong> a cada carregamento.</li>
                    <li><strong>Placement</strong>: define onde o anúncio aparece — Home, Artigo ou Ambos.</li>
                    <li><strong>Logo</strong>: imagem pequena (ex: logotipo). <strong>Imagem</strong>: imagem de fundo/apoio.</li>
                    <li>As <strong>estatísticas</strong> (impressões, cliques, CTR) são atualizadas automaticamente pelo app.</li>
                    <li>Arraste os itens para definir a ordem de prioridade (usada como critério de desempate).</li>
                </ul>
            </div>
        </div>

        <style>
        .zimny-ad-item .remove-ad:hover { background:#fcf0f1; border-color:#d63638; }
        </style>

        <script>
        // ─── Sortable ────────────────────────────────────────────────────────
        jQuery(document).ready(function($) {
            $("#zimny-ads-list").sortable({
                handle: ".handle",
                axis: "y",
                tolerance: "pointer",
                cursor: "grabbing",
                opacity: 0.6,
                items: ".zimny-ad-item",
                placeholder: {
                    element: function() {
                        return $('<div style="border:2px dashed #2271b1; background:#f0f6fc; border-radius:12px; height:100px; margin-bottom:20px;"></div>');
                    },
                    update: function() {}
                },
                update: function() {
                    $("#zimny-ads-list .zimny-ad-item").each(function(i) {
                        $(this).attr("data-index", i);
                    });
                }
            });
            $("#zimny-ads-list").disableSelection();
        });

        // ─── Live preview update ─────────────────────────────────────────────
        function updatePreview(item) {
            const title = item.querySelector('.ad-title-input').value.trim();
            const desc = item.querySelector('.ad-desc-input').value.trim();
            const cta = item.querySelector('.ad-cta-input').value.trim();
            const logoUrl = item.querySelector('.ad-logo-url').value;
            const imageUrl = item.querySelector('.ad-image-url').value;
            const type = item.querySelector('.ad-type-select').value;

            // Header badge
            const badge = item.querySelector('.ad-badge');
            if (badge) {
                badge.className = 'ad-badge ' + type;
                const labels = { banner: 'Banner', sponsored: 'Patrocinado', custom: 'Personalizado' };
                badge.textContent = labels[type] || type;
            }

            // Header title
            const titleDisplay = item.querySelector('.ad-title-display');
            if (titleDisplay) {
                titleDisplay.textContent = title || 'Sem título';
                titleDisplay.className = 'ad-title-display' + (title ? '' : ' empty');
            }

            // Preview title
            const previewTitle = item.querySelector('.preview-title');
            if (previewTitle) {
                previewTitle.textContent = title || 'Título do Anúncio';
                previewTitle.className = 'preview-title' + (title ? '' : ' empty');
            }

            // Preview description
            const previewDesc = item.querySelector('.preview-desc');
            if (previewDesc) {
                previewDesc.textContent = desc || 'Descrição do anúncio...';
                previewDesc.className = 'preview-desc' + (desc ? '' : ' empty');
            }

            // Preview CTA
            const previewCta = item.querySelector('.preview-cta');
            if (previewCta) {
                previewCta.textContent = cta || 'Saiba mais';
                previewCta.className = 'preview-cta' + (cta ? '' : ' empty');
            }

            // Preview image
            const previewImg = item.querySelector('.preview-image-img');
            const previewImgWrap = item.querySelector('.zimny-ad-preview-image');
            if (previewImg && previewImgWrap) {
                if (imageUrl) {
                    previewImg.src = imageUrl;
                    previewImg.style.display = 'block';
                    previewImgWrap.querySelector('.no-image').style.display = 'none';
                } else {
                    previewImg.style.display = 'none';
                    previewImgWrap.querySelector('.no-image').style.display = 'flex';
                }
            }

            // Preview logo
            const previewLogo = item.querySelector('.preview-logo-img');
            const previewLogoWrap = item.querySelector('.zimny-ad-preview-logo');
            if (previewLogo && previewLogoWrap) {
                if (logoUrl) {
                    previewLogo.src = logoUrl;
                    previewLogo.style.display = 'block';
                    previewLogoWrap.querySelector('.no-logo').style.display = 'none';
                } else {
                    previewLogo.style.display = 'none';
                    previewLogoWrap.querySelector('.no-logo').style.display = 'flex';
                }
            }
        }

        // ─── Toggle status ───────────────────────────────────────────────────
        function toggleAdStatus(btn) {
            const isActive = btn.classList.contains('active');
            btn.classList.toggle('active');
            btn.classList.toggle('inactive');
            const icon = btn.querySelector('.dashicons');
            if (icon) {
                icon.className = 'dashicons ' + (isActive ? 'dashicons-hidden' : 'dashicons-yes');
            }
            const label = btn.querySelector('.status-label');
            if (label) {
                label.textContent = isActive ? 'Inativo' : 'Ativo';
            }
        }

        // ─── Remove item ─────────────────────────────────────────────────────
        function removeAdItem(btn) {
            if (confirm('Remover esta publicidade?')) {
                const item = btn.closest('.zimny-ad-item');
                if (item) item.remove();
            }
        }

        // ─── Image picker (reusable) ─────────────────────────────────────────
        function openMediaPicker(inputUrl, inputId, previewSelector) {
            if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
                var frame = wp.media({
                    title: 'Selecionar imagem',
                    button: { text: 'Usar esta imagem' },
                    multiple: false,
                    library: { type: 'image' }
                });
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    inputUrl.value = attachment.url;
                    if (inputId) inputId.value = attachment.id;
                    // Update preview thumbnail
                    var preview = document.querySelector(previewSelector);
                    if (preview) {
                        preview.innerHTML = '<img src="' + attachment.url + '" alt="" />';
                    }
                    // Update live preview
                    var item = inputUrl.closest('.zimny-ad-item');
                    if (item) updatePreview(item);
                });
                frame.open();
            } else {
                var url = prompt('URL da imagem:');
                if (url) {
                    inputUrl.value = url;
                    var preview = document.querySelector(previewSelector);
                    if (preview) {
                        preview.innerHTML = '<img src="' + url + '" alt="" />';
                    }
                    var item = inputUrl.closest('.zimny-ad-item');
                    if (item) updatePreview(item);
                }
            }
        }

        // ─── Add new ad ──────────────────────────────────────────────────────
        document.getElementById('add-ad-btn').addEventListener('click', function() {
            const list = document.getElementById('zimny-ads-list');
            const div = document.createElement('div');
            div.className = 'zimny-ad-item';
            div.setAttribute('data-index', list.children.length);
            div.innerHTML = getAdItemTemplate('');
            list.appendChild(div);

            // Trigger image selection for the main image
            setTimeout(function() {
                var imgBtn = div.querySelector('.change-ad-image');
                if (imgBtn) imgBtn.click();
            }, 200);
        });

        // ─── Template for new ad item ────────────────────────────────────────
        function getAdItemTemplate(title) {
            return `
                <div class="zimny-ad-header">
                    <span class="handle dashicons dashicons-menu"></span>
                    <span class="ad-title-display empty">${title || 'Sem título'}</span>
                    <span class="ad-badge banner">Banner</span>
                </div>
                <div class="zimny-ad-body">
                    <div class="zimny-ad-preview">
                        <div class="zimny-ad-preview-inner">
                            <div class="zimny-ad-preview-image">
                                <img class="preview-image-img" src="" style="display:none;" alt="" />
                                <div class="no-image" style="display:flex;">
                                    <span class="dashicons dashicons-format-image" style="font-size:24px; color:#c3c4c7;"></span>
                                </div>
                            </div>
                            <div class="zimny-ad-preview-logo">
                                <img class="preview-logo-img" src="" style="display:none;" alt="" />
                                <span class="no-logo dashicons dashicons-store" style="display:flex;"></span>
                            </div>
                            <div class="zimny-ad-preview-content">
                                <div class="preview-title empty">Título do Anúncio</div>
                                <div class="preview-desc empty">Descrição do anúncio...</div>
                                <span class="preview-cta empty">Saiba mais</span>
                            </div>
                        </div>
                    </div>
                    <div class="zimny-ad-fields">
                        <div class="field-row">
                            <div class="field-group" style="flex:2;">
                                <label>Título / Nome do Anunciante</label>
                                <input type="text" class="ad-title-input" value="" placeholder="Ex: BEZ Group" oninput="updatePreview(this.closest('.zimny-ad-item'))" />
                            </div>
                            <div class="field-group" style="flex:1;">
                                <label>Tipo</label>
                                <select class="ad-type-select" onchange="updatePreview(this.closest('.zimny-ad-item'))">
                                    <option value="banner">Banner</option>
                                    <option value="sponsored">Conteúdo Patrocinado</option>
                                    <option value="custom">Personalizado</option>
                                    <option value="image_banner">🖼️ Banner de Imagem</option>
                                </select>
                            </div>
                            <div class="field-group" style="flex:1;">
                                <label>Placement</label>
                                <select class="ad-placement-select">
                                    <option value="both">Home + Artigos</option>
                                    <option value="home">Apenas Home</option>
                                    <option value="article">Apenas Artigos</option>
                                </select>
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label>Descrição</label>
                                <textarea class="ad-desc-input" placeholder="Descrição do anúncio (opcional)" oninput="updatePreview(this.closest('.zimny-ad-item'))"></textarea>
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group" style="flex:1;">
                                <label>Texto do CTA</label>
                                <input type="text" class="ad-cta-input" value="" placeholder="Ex: Saiba mais" oninput="updatePreview(this.closest('.zimny-ad-item'))" />
                            </div>
                            <div class="field-group" style="flex:2;">
                                <label>URL de Destino</label>
                                <input type="text" class="ad-link-input" value="" placeholder="https://..." />
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group" style="flex:1;">
                                <label>Logo do Anunciante</label>
                                <div style="display:flex; gap:6px; align-items:center;">
                                    <div class="image-preview-thumb ad-logo-preview" onclick="document.querySelector('.ad-logo-input').click()">
                                        <span class="dashicons dashicons-store"></span>
                                    </div>
                                    <input type="text" class="ad-logo-url" value="" placeholder="URL da logo" style="flex:1; font-size:11px;" oninput="updatePreview(this.closest('.zimny-ad-item'))" />
                                    <input type="hidden" class="ad-logo-id" value="0" />
                                    <button type="button" class="action-btn" onclick="openMediaPicker(this.closest('.zimny-ad-fields').querySelector('.ad-logo-url'), this.closest('.zimny-ad-fields').querySelector('.ad-logo-id'), '.ad-logo-preview')" title="Selecionar logo" style="padding:4px 8px;">
                                        <span class="dashicons dashicons-admin-media" style="font-size:14px; width:14px; height:14px;"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="field-group" style="flex:1;">
                                <label>Imagem de Apoio</label>
                                <div style="display:flex; gap:6px; align-items:center;">
                                    <div class="image-preview-thumb ad-image-preview" onclick="document.querySelector('.ad-image-input').click()">
                                        <span class="dashicons dashicons-format-image"></span>
                                    </div>
                                    <input type="text" class="ad-image-url" value="" placeholder="URL da imagem" style="flex:1; font-size:11px;" oninput="updatePreview(this.closest('.zimny-ad-item'))" />
                                    <input type="hidden" class="ad-image-id" value="0" />
                                    <button type="button" class="action-btn change-ad-image" onclick="openMediaPicker(this.closest('.zimny-ad-fields').querySelector('.ad-image-url'), this.closest('.zimny-ad-fields').querySelector('.ad-image-id'), '.ad-image-preview')" title="Selecionar imagem" style="padding:4px 8px;">
                                        <span class="dashicons dashicons-admin-media" style="font-size:14px; width:14px; height:14px;"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="zimny-ad-stats">
                            <div class="stat-item">
                                <div class="stat-value" id="impressions-value">0</div>
                                <div class="stat-label">Impressões</div>
                            </div>
                            <div class="stat-divider"></div>
                            <div class="stat-item">
                                <div class="stat-value" id="clicks-value">0</div>
                                <div class="stat-label">Cliques</div>
                            </div>
                            <div class="stat-divider"></div>
                            <div class="stat-item">
                                <div class="stat-value ctr" id="ctr-value">0%</div>
                                <div class="stat-label">CTR</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="zimny-ad-actions">
                    <button type="button" class="zimny-ad-status-toggle active" onclick="toggleAdStatus(this)" title="Ativar/Desativar">
                        <span class="dashicons dashicons-yes"></span>
                        <span class="status-label">Ativo</span>
                    </button>
                    <button type="button" class="action-btn" onclick="this.closest('.zimny-ad-item').querySelector('.ad-logo-url').closest('.field-group').querySelector('.image-preview-thumb').click()">
                        <span class="dashicons dashicons-store"></span> Logo
                    </button>
                    <button type="button" class="action-btn change-ad-image" onclick="this.closest('.zimny-ad-item').querySelector('.ad-image-url').closest('.field-group').querySelector('.image-preview-thumb').click()">
                        <span class="dashicons dashicons-format-image"></span> Imagem
                    </button>
                    <button type="button" class="action-btn danger remove-ad" onclick="removeAdItem(this)" title="Remover publicidade">
                        <span class="dashicons dashicons-trash"></span> Remover
                    </button>
                </div>
            `;
        }

        // ─── Language tab switcher ──────────────────────────────────────────
        function switchLang(btn, index) {
            const container = btn.closest('.zimny-ad-fields');
            // Update tab styles
            container.querySelectorAll('.lang-tab').forEach(function(tab) {
                tab.style.background = '#f0f0f1';
                tab.style.color = '#50575e';
            });
            btn.style.background = '#2271b1';
            btn.style.color = '#fff';
            // Show/hide fields
            const lang = btn.getAttribute('data-lang');
            container.querySelectorAll('[data-lang]').forEach(function(field) {
                field.style.display = field.getAttribute('data-lang') === lang ? 'block' : 'none';
            });
        }

        // ─── Save ────────────────────────────────────────────────────────────
        async function saveAds() {
            const statusEl = document.getElementById('ads-status');
            const spinner = document.getElementById('ads-spinner');
            spinner.style.display = 'inline-block';

            const items = document.querySelectorAll('.zimny-ad-item');
            const ads = [];

            items.forEach(function(item) {
                const logoUrl = item.querySelector('.ad-logo-url').value;
                const logoId = parseInt(item.querySelector('.ad-logo-id').value) || 0;
                const imageUrl = item.querySelector('.ad-image-url').value;
                const imageId = parseInt(item.querySelector('.ad-image-id').value) || 0;
                const titlePt = item.querySelector('.lang-field-pt.ad-title-input')?.value.trim() || '';
                const titleEn = item.querySelector('.lang-field-en.ad-title-input')?.value.trim() || '';
                const titleEs = item.querySelector('.lang-field-es.ad-title-input')?.value.trim() || '';
                const descPt = item.querySelector('.lang-field-pt.ad-desc-input')?.value.trim() || '';
                const descEn = item.querySelector('.lang-field-en.ad-desc-input')?.value.trim() || '';
                const descEs = item.querySelector('.lang-field-es.ad-desc-input')?.value.trim() || '';
                const ctaPt = item.querySelector('.lang-field-pt.ad-cta-input')?.value.trim() || '';
                const ctaEn = item.querySelector('.lang-field-en.ad-cta-input')?.value.trim() || '';
                const ctaEs = item.querySelector('.lang-field-es.ad-cta-input')?.value.trim() || '';
                const link = item.querySelector('.ad-link-input').value.trim();
                const typeSelect = item.querySelector('.ad-type-select');
                const type = typeSelect ? typeSelect.value : 'banner';
                const placementSelect = item.querySelector('.ad-placement-select');
                const placement = placementSelect ? placementSelect.value : 'both';
                const active = item.querySelector('.zimny-ad-status-toggle').classList.contains('active');

                // Preserve existing stats if they exist
                const impressionsEl = item.querySelector('#impressions-value');
                const clicksEl = item.querySelector('#clicks-value');
                const impressions = impressionsEl ? parseInt(impressionsEl.textContent.replace(/\./g, '')) || 0 : 0;
                const clicks = clicksEl ? parseInt(clicksEl.textContent.replace(/\./g, '')) || 0 : 0;

                ads.push({
                    logo_url: logoUrl,
                    logo_id: logoId,
                    image_url: imageUrl,
                    image_id: imageId,
                    title_pt: titlePt,
                    title_en: titleEn,
                    title_es: titleEs,
                    description_pt: descPt,
                    description_en: descEn,
                    description_es: descEs,
                    cta_text_pt: ctaPt,
                    cta_text_en: ctaEn,
                    cta_text_es: ctaEs,
                    link: link,
                    type: type,
                    placement: placement,
                    active: active,
                    impressions: impressions,
                    clicks: clicks,
                });
            });

            try {
                const formData = new FormData();
                formData.append('action', 'zimny_save_ads');
                formData.append('ads', JSON.stringify(ads));
                formData.append('_wpnonce', '<?php echo wp_create_nonce('zimny_save_ads'); ?>');

                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();

                statusEl.className = 'zimny-layout-status ' + (result.success ? 'success' : 'error');
                statusEl.textContent = result.success
                    ? '✅ ' + ads.length + ' publicidade(s) salva(s) com sucesso!'
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

    /**
     * Renderiza um item de anúncio individual (para edição).
     */
    private function render_ad_item(int $i, array $ad) {
        $logo_url   = $ad['logo_url'] ?? '';
        $logo_id    = $ad['logo_id'] ?? 0;
        $img_url    = $ad['image_url'] ?? '';
        $img_id     = $ad['image_id'] ?? 0;
        // Campos trilíngues
        $title_pt   = $ad['title_pt'] ?? $ad['title'] ?? '';
        $title_en   = $ad['title_en'] ?? '';
        $title_es   = $ad['title_es'] ?? '';
        $desc_pt    = $ad['description_pt'] ?? $ad['description'] ?? '';
        $desc_en    = $ad['description_en'] ?? '';
        $desc_es    = $ad['description_es'] ?? '';
        $cta_pt     = $ad['cta_text_pt'] ?? $ad['cta_text'] ?? '';
        $cta_en     = $ad['cta_text_en'] ?? '';
        $cta_es     = $ad['cta_text_es'] ?? '';
        // Fallback para exibição no admin (mostra PT)
        $title      = $title_pt;
        $desc       = $desc_pt;
        $cta_text   = $cta_pt;
        $link       = $ad['link'] ?? '';
        $type       = $ad['type'] ?? 'banner';
        $placement  = $ad['placement'] ?? 'both';
        $active     = !empty($ad['active']);
        $impressions = intval($ad['impressions'] ?? 0);
        $clicks     = intval($ad['clicks'] ?? 0);
        $ctr        = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;

        $logo_thumb = $logo_id ? wp_get_attachment_image_url($logo_id, 'thumbnail') : $logo_url;
        $img_thumb  = $img_id ? wp_get_attachment_image_url($img_id, 'medium') : $img_url;

        $type_labels = array(
            'banner'    => 'Banner',
            'sponsored' => 'Patrocinado',
            'custom'    => 'Personalizado',
        );
        $placement_labels = array(
            'home'    => 'Apenas Home',
            'article' => 'Apenas Artigos',
            'both'    => 'Home + Artigos',
        );
        ?>
        <div class="zimny-ad-item" data-index="<?php echo esc_attr($i); ?>">
            <!-- Header -->
            <div class="zimny-ad-header">
                <span class="handle dashicons dashicons-menu"></span>
                <span class="ad-title-display <?php echo $title ? '' : 'empty'; ?>"><?php echo esc_html($title ?: 'Sem título'); ?></span>
                <span class="ad-badge <?php echo esc_attr($type); ?>"><?php echo esc_html($type_labels[$type] ?? ucfirst($type)); ?></span>
            </div>

            <!-- Body -->
            <div class="zimny-ad-body">
                <!-- Preview Panel -->
                <div class="zimny-ad-preview">
                    <div class="zimny-ad-preview-inner">
                        <div class="zimny-ad-preview-image">
                            <?php if ($img_thumb): ?>
                                <img class="preview-image-img" src="<?php echo esc_url($img_thumb); ?>" alt="" style="display:block;" />
                                <div class="no-image" style="display:none;">
                                    <span class="dashicons dashicons-format-image" style="font-size:24px; color:#c3c4c7;"></span>
                                </div>
                            <?php else: ?>
                                <img class="preview-image-img" src="" alt="" style="display:none;" />
                                <div class="no-image" style="display:flex;">
                                    <span class="dashicons dashicons-format-image" style="font-size:24px; color:#c3c4c7;"></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="zimny-ad-preview-logo">
                            <?php if ($logo_thumb): ?>
                                <img class="preview-logo-img" src="<?php echo esc_url($logo_thumb); ?>" alt="" style="display:block;" />
                                <span class="no-logo dashicons dashicons-store" style="display:none;"></span>
                            <?php else: ?>
                                <img class="preview-logo-img" src="" alt="" style="display:none;" />
                                <span class="no-logo dashicons dashicons-store" style="display:flex;"></span>
                            <?php endif; ?>
                        </div>
                        <div class="zimny-ad-preview-content">
                            <div class="preview-title <?php echo $title ? '' : 'empty'; ?>"><?php echo esc_html($title ?: 'Título do Anúncio'); ?></div>
                            <div class="preview-desc <?php echo $desc ? '' : 'empty'; ?>"><?php echo esc_html($desc ?: 'Descrição do anúncio...'); ?></div>
                            <span class="preview-cta <?php echo $cta_text ? '' : 'empty'; ?>"><?php echo esc_html($cta_text ?: 'Saiba mais'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Fields Panel -->
                <div class="zimny-ad-fields">
                    <!-- Language Tabs -->
                    <div class="lang-tabs" style="display:flex; gap:4px; margin-bottom:8px;">
                        <button type="button" class="lang-tab lang-tab-active" data-lang="pt" onclick="switchLang(this, '<?php echo esc_js($i); ?>')" style="padding:4px 12px; border:1px solid #dcdcde; border-radius:4px; cursor:pointer; font-size:11px; font-weight:600; background:#2271b1; color:#fff;">🇧🇷 PT</button>
                        <button type="button" class="lang-tab" data-lang="en" onclick="switchLang(this, '<?php echo esc_js($i); ?>')" style="padding:4px 12px; border:1px solid #dcdcde; border-radius:4px; cursor:pointer; font-size:11px; font-weight:600; background:#f0f0f1; color:#50575e;">🇺🇸 EN</button>
                        <button type="button" class="lang-tab" data-lang="es" onclick="switchLang(this, '<?php echo esc_js($i); ?>')" style="padding:4px 12px; border:1px solid #dcdcde; border-radius:4px; cursor:pointer; font-size:11px; font-weight:600; background:#f0f0f1; color:#50575e;">🇪🇸 ES</button>
                    </div>

                    <div class="field-row">
                        <div class="field-group" style="flex:2;">
                            <label>Título / Nome do Anunciante</label>
                            <!-- PT (visible by default) -->
                            <input type="text" class="ad-title-input lang-field-pt" data-lang="pt" value="<?php echo esc_attr($title_pt); ?>" placeholder="PT: Ex: BEZ Group" oninput="updatePreview(this.closest('.zimny-ad-item'))" style="display:block;" />
                            <!-- EN (hidden by default) -->
                            <input type="text" class="ad-title-input lang-field-en" data-lang="en" value="<?php echo esc_attr($title_en); ?>" placeholder="EN: Ex: BEZ Group" oninput="updatePreview(this.closest('.zimny-ad-item'))" style="display:none;" />
                            <!-- ES (hidden by default) -->
                            <input type="text" class="ad-title-input lang-field-es" data-lang="es" value="<?php echo esc_attr($title_es); ?>" placeholder="ES: Ex: BEZ Group" oninput="updatePreview(this.closest('.zimny-ad-item'))" style="display:none;" />
                        </div>
                        <div class="field-group" style="flex:1;">
                            <label>Tipo</label>
                            <select class="ad-type-select" onchange="updatePreview(this.closest('.zimny-ad-item'))">
                                <option value="banner" <?php selected($type, 'banner'); ?>>Banner</option>
                                <option value="sponsored" <?php selected($type, 'sponsored'); ?>>Conteúdo Patrocinado</option>
                                <option value="custom" <?php selected($type, 'custom'); ?>>Personalizado</option>
                                <option value="image_banner" <?php selected($type, 'image_banner'); ?>>🖼️ Banner de Imagem</option>
                            </select>
                        </div>
                        <div class="field-group" style="flex:1;">
                            <label>Placement</label>
                            <select class="ad-placement-select">
                                <option value="both" <?php selected($placement, 'both'); ?>>Home + Artigos</option>
                                <option value="home" <?php selected($placement, 'home'); ?>>Apenas Home</option>
                                <option value="article" <?php selected($placement, 'article'); ?>>Apenas Artigos</option>
                            </select>
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field-group">
                            <label>Descrição</label>
                            <textarea class="ad-desc-input lang-field-pt" data-lang="pt" placeholder="PT: Descrição do anúncio (opcional)" oninput="updatePreview(this.closest('.zimny-ad-item'))" style="display:block;"><?php echo esc_textarea($desc_pt); ?></textarea>
                            <textarea class="ad-desc-input lang-field-en" data-lang="en" placeholder="EN: Ad description (optional)" oninput="updatePreview(this.closest('.zimny-ad-item'))" style="display:none;"><?php echo esc_textarea($desc_en); ?></textarea>
                            <textarea class="ad-desc-input lang-field-es" data-lang="es" placeholder="ES: Descripción del anuncio (opcional)" oninput="updatePreview(this.closest('.zimny-ad-item'))" style="display:none;"><?php echo esc_textarea($desc_es); ?></textarea>
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field-group" style="flex:1;">
                            <label>Texto do CTA</label>
                            <input type="text" class="ad-cta-input lang-field-pt" data-lang="pt" value="<?php echo esc_attr($cta_pt); ?>" placeholder="PT: Ex: Saiba mais" oninput="updatePreview(this.closest('.zimny-ad-item'))" style="display:block;" />
                            <input type="text" class="ad-cta-input lang-field-en" data-lang="en" value="<?php echo esc_attr($cta_en); ?>" placeholder="EN: Ex: Learn more" oninput="updatePreview(this.closest('.zimny-ad-item'))" style="display:none;" />
                            <input type="text" class="ad-cta-input lang-field-es" data-lang="es" value="<?php echo esc_attr($cta_es); ?>" placeholder="ES: Ex: Saber más" oninput="updatePreview(this.closest('.zimny-ad-item'))" style="display:none;" />
                        </div>
                        <div class="field-group" style="flex:2;">
                            <label>URL de Destino</label>
                            <input type="text" class="ad-link-input" value="<?php echo esc_attr($link); ?>" placeholder="https://..." />
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field-group" style="flex:1;">
                            <label>Logo do Anunciante</label>
                            <div style="display:flex; gap:6px; align-items:center;">
                                <div class="image-preview-thumb ad-logo-preview" onclick="this.closest('.field-group').querySelector('.ad-logo-url').focus()">
                                    <?php if ($logo_thumb): ?>
                                        <img src="<?php echo esc_url($logo_thumb); ?>" alt="" />
                                    <?php else: ?>
                                        <span class="dashicons dashicons-store"></span>
                                    <?php endif; ?>
                                </div>
                                <input type="text" class="ad-logo-url" value="<?php echo esc_attr($logo_url); ?>" placeholder="URL da logo" style="flex:1; font-size:11px;" oninput="updatePreview(this.closest('.zimny-ad-item'))" />
                                <input type="hidden" class="ad-logo-id" value="<?php echo esc_attr($logo_id); ?>" />
                                <button type="button" class="action-btn" onclick="openMediaPicker(this.closest('.zimny-ad-fields').querySelector('.ad-logo-url'), this.closest('.zimny-ad-fields').querySelector('.ad-logo-id'), '.ad-logo-preview')" title="Selecionar logo" style="padding:4px 8px;">
                                    <span class="dashicons dashicons-admin-media" style="font-size:14px; width:14px; height:14px;"></span>
                                </button>
                            </div>
                        </div>
                        <div class="field-group" style="flex:1;">
                            <label>Imagem de Apoio</label>
                            <div style="display:flex; gap:6px; align-items:center;">
                                <div class="image-preview-thumb ad-image-preview" onclick="this.closest('.field-group').querySelector('.ad-image-url').focus()">
                                    <?php if ($img_thumb): ?>
                                        <img src="<?php echo esc_url($img_thumb); ?>" alt="" />
                                    <?php else: ?>
                                        <span class="dashicons dashicons-format-image"></span>
                                    <?php endif; ?>
                                </div>
                                <input type="text" class="ad-image-url" value="<?php echo esc_attr($img_url); ?>" placeholder="URL da imagem" style="flex:1; font-size:11px;" oninput="updatePreview(this.closest('.zimny-ad-item'))" />
                                <input type="hidden" class="ad-image-id" value="<?php echo esc_attr($img_id); ?>" />
                                <button type="button" class="action-btn change-ad-image" onclick="openMediaPicker(this.closest('.zimny-ad-fields').querySelector('.ad-image-url'), this.closest('.zimny-ad-fields').querySelector('.ad-image-id'), '.ad-image-preview')" title="Selecionar imagem" style="padding:4px 8px;">
                                    <span class="dashicons dashicons-admin-media" style="font-size:14px; width:14px; height:14px;"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="zimny-ad-stats">
                        <div class="stat-item">
                            <div class="stat-value" id="impressions-value"><?php echo number_format($impressions); ?></div>
                            <div class="stat-label">Impressões</div>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <div class="stat-value" id="clicks-value"><?php echo number_format($clicks); ?></div>
                            <div class="stat-label">Cliques</div>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <div class="stat-value ctr" id="ctr-value"><?php echo $ctr; ?>%</div>
                            <div class="stat-label">CTR</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Footer -->
            <div class="zimny-ad-actions">
                <button type="button" class="zimny-ad-status-toggle <?php echo $active ? 'active' : 'inactive'; ?>" onclick="toggleAdStatus(this)" title="Ativar/Desativar">
                    <span class="dashicons <?php echo $active ? 'dashicons-yes' : 'dashicons-hidden'; ?>"></span>
                    <span class="status-label"><?php echo $active ? 'Ativo' : 'Inativo'; ?></span>
                </button>
                <button type="button" class="action-btn" onclick="this.closest('.zimny-ad-item').querySelector('.ad-logo-url').closest('.field-group').querySelector('.image-preview-thumb').click()">
                    <span class="dashicons dashicons-store"></span> Logo
                </button>
                <button type="button" class="action-btn change-ad-image" onclick="this.closest('.zimny-ad-item').querySelector('.ad-image-url').closest('.field-group').querySelector('.image-preview-thumb').click()">
                    <span class="dashicons dashicons-format-image"></span> Imagem
                </button>
                <button type="button" class="action-btn danger remove-ad" onclick="removeAdItem(this)" title="Remover publicidade">
                    <span class="dashicons dashicons-trash"></span> Remover
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Renderiza a página de relatórios de publicidade.
     */
    public function render_ads_reports_page() {
        $ads = get_option(Zimny_Admin::OPTION_ADS, array());
        if (!is_array($ads)) $ads = array();

        $total_impressions = 0;
        $total_clicks = 0;
        ?>
        <div class="wrap zimny-ads-wrap">
            <h1>
                <span class="dashicons dashicons-chart-bar" style="font-size:32px; width:32px; height:32px;"></span>
                Relatórios de Publicidade
            </h1>
            <p>Visão geral do desempenho de todas as publicidades.</p>

            <!-- Summary Cards -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px;">
                <div style="background:#fff; border:1px solid #dcdcde; border-radius:10px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                    <div style="font-size:28px; font-weight:700; color:#1d2327;"><?php echo count($ads); ?></div>
                    <div style="font-size:12px; color:#646970; text-transform:uppercase; letter-spacing:0.5px; font-weight:600;">Total de Anúncios</div>
                </div>
                <div style="background:#fff; border:1px solid #dcdcde; border-radius:10px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                    <div style="font-size:28px; font-weight:700; color:#1d2327;"><?php echo count(array_filter($ads, function($a) { return !empty($a['active']); })); ?></div>
                    <div style="font-size:12px; color:#646970; text-transform:uppercase; letter-spacing:0.5px; font-weight:600;">Anúncios Ativos</div>
                </div>
                <?php
                foreach ($ads as $ad) {
                    $total_impressions += intval($ad['impressions'] ?? 0);
                    $total_clicks += intval($ad['clicks'] ?? 0);
                }
                $ctr = $total_impressions > 0 ? round(($total_clicks / $total_impressions) * 100, 2) : 0;
                ?>
                <div style="background:#fff; border:1px solid #dcdcde; border-radius:10px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                    <div style="font-size:28px; font-weight:700; color:#2271b1;"><?php echo number_format($total_impressions); ?></div>
                    <div style="font-size:12px; color:#646970; text-transform:uppercase; letter-spacing:0.5px; font-weight:600;">Impressões Totais</div>
                </div>
                <div style="background:#fff; border:1px solid #dcdcde; border-radius:10px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                    <div style="font-size:28px; font-weight:700; color:#46b450;"><?php echo number_format($total_clicks); ?></div>
                    <div style="font-size:12px; color:#646970; text-transform:uppercase; letter-spacing:0.5px; font-weight:600;">Cliques Totais</div>
                </div>
                <div style="background:#fff; border:1px solid #dcdcde; border-radius:10px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                    <div style="font-size:28px; font-weight:700; color:<?php echo $ctr > 2 ? '#46b450' : ($ctr > 0.5 ? '#996800' : '#d63638'); ?>;"><?php echo $ctr; ?>%</div>
                    <div style="font-size:12px; color:#646970; text-transform:uppercase; letter-spacing:0.5px; font-weight:600;">CTR Global</div>
                </div>
            </div>

            <!-- Table -->
            <div style="background:#fff; border:1px solid #dcdcde; border-radius:10px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                <table class="wp-list-table widefat fixed striped" style="border:none;">
                    <thead>
                        <tr>
                            <th style="padding:12px 14px; font-weight:600;">Anúncio</th>
                            <th style="padding:12px 14px; font-weight:600;">Tipo</th>
                            <th style="padding:12px 14px; font-weight:600;">Placement</th>
                            <th style="padding:12px 14px; font-weight:600;">Status</th>
                            <th style="padding:12px 14px; font-weight:600; text-align:center;">Impressões</th>
                            <th style="padding:12px 14px; font-weight:600; text-align:center;">Cliques</th>
                            <th style="padding:12px 14px; font-weight:600; text-align:center;">CTR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ads)): ?>
                            <tr>
                                <td colspan="7" style="padding:20px; text-align:center; color:#8c8f94;">
                                    Nenhuma publicidade cadastrada.
                                    <a href="?post_type=zimny_video&page=zimny-ads">Criar publicidades</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ads as $i => $ad):
                                $title = $ad['title'] ?? 'Sem título';
                                $type = $ad['type'] ?? 'banner';
                                $placement = $ad['placement'] ?? 'both';
                                $active = !empty($ad['active']);
                                $impressions = intval($ad['impressions'] ?? 0);
                                $clicks = intval($ad['clicks'] ?? 0);
                                $ad_ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;

                                $type_labels = array('banner' => 'Banner', 'sponsored' => 'Patrocinado', 'custom' => 'Personalizado');
                                $placement_labels = array('home' => 'Home', 'article' => 'Artigo', 'both' => 'Home + Artigo');
                            ?>
                            <tr>
                                <td style="padding:12px 14px; font-weight:600;"><?php echo esc_html($title); ?></td>
                                <td style="padding:12px 14px;"><span class="ad-badge <?php echo esc_attr($type); ?>"><?php echo esc_html($type_labels[$type] ?? $type); ?></span></td>
                                <td style="padding:12px 14px; font-size:12px;"><?php echo esc_html($placement_labels[$placement] ?? $placement); ?></td>
                                <td style="padding:12px 14px;">
                                    <span style="display:inline-flex; align-items:center; gap:4px; padding:2px 10px; border-radius:10px; font-size:11px; font-weight:600; <?php echo $active ? 'background:#e7f4e8; color:#46b450;' : 'background:#f0f0f1; color:#8c8f94;'; ?>">
                                        <span class="dashicons <?php echo $active ? 'dashicons-yes' : 'dashicons-hidden'; ?>" style="font-size:12px; width:12px; height:12px;"></span>
                                        <?php echo $active ? 'Ativo' : 'Inativo'; ?>
                                    </span>
                                </td>
                                <td style="padding:12px 14px; text-align:center; font-weight:600;"><?php echo number_format($impressions); ?></td>
                                <td style="padding:12px 14px; text-align:center; font-weight:600;"><?php echo number_format($clicks); ?></td>
                                <td style="padding:12px 14px; text-align:center; font-weight:600; color:<?php echo $ad_ctr > 2 ? '#46b450' : ($ad_ctr > 0.5 ? '#996800' : '#8c8f94'); ?>;"><?php echo $ad_ctr; ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top:24px; padding:16px; background:#f0f6fc; border-radius:8px; border-left:4px solid #2271b1;">
                <h3 style="margin:0 0 8px 0; font-size:14px;">📊 Sobre as estatísticas</h3>
                <ul style="margin:0; padding-left:20px; font-size:12px; color:#50575e; line-height:1.8;">
                    <li><strong>Impressões</strong>: número de vezes que o anúncio foi exibido no app.</li>
                    <li><strong>Cliques</strong>: número de vezes que o usuário tocou no anúncio.</li>
                    <li><strong>CTR</strong> (Click-Through Rate): porcentagem de impressões que resultaram em clique. <em>CTR = (Cliques / Impressões) × 100</em></li>
                    <li>As estatísticas são atualizadas em tempo real pelo aplicativo.</li>
                </ul>
            </div>
        </div>
        <?php
    }
}