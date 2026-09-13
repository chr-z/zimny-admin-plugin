<?php
/**
 * Zimny_Admin_Anuncie_Cards - Página "Anuncie Conosco Cards"
 *
 * @package Zimny_Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Zimny_Admin_Anuncie_Cards {

    /**
     * CSS inline da página de cards.
     */
    public function get_anuncie_cards_css(): string {
        return '
        /* ─── Layout ─── */
        .zimny-anuncie-wrap { max-width: 960px; }
        .zimny-anuncie-wrap h1 { display:flex; align-items:center; gap:10px; margin-bottom:24px; }
        .zimny-anuncie-list { display:flex; flex-direction:column; gap:16px; margin-top:16px; min-height:100px; padding:16px; background:#f0f0f1; border:2px dashed #dcdcde; border-radius:12px; }

        /* ─── Card base ─── */
        .zimny-anuncie-item {
            position:relative; display:flex; align-items:stretch; gap:0;
            background:#fff; border:1px solid #dcdcde;
            border-radius:12px; overflow:hidden; cursor:move;
            transition:box-shadow 0.3s ease, border-color 0.3s ease, transform 0.3s ease;
        }
        .zimny-anuncie-item:hover { border-color:#2271b1; box-shadow:0 4px 16px rgba(0,0,0,0.10); transform:translateY(-2px); }

        /* ─── Variant 1: Minimal Elegance ─── */
        .zimny-anuncie-item.variant-1 { border-left:4px solid #0A0A0A; }
        .zimny-anuncie-item.variant-1 .anuncie-preview {
            display:flex; align-items:center; gap:20px; padding:20px;
            background:linear-gradient(135deg, #ffffff 0%, #f8f8f8 100%);
            width:100%;
        }
        .zimny-anuncie-item.variant-1 .anuncie-preview .preview-img {
            width:140px; height:140px; border-radius:8px; overflow:hidden;
            flex-shrink:0; box-shadow:0 2px 12px rgba(0,0,0,0.08);
            transition:transform 0.4s ease;
        }
        .zimny-anuncie-item.variant-1:hover .anuncie-preview .preview-img { transform:scale(1.03); }
        .zimny-anuncie-item.variant-1 .preview-content { flex:1; }
        .zimny-anuncie-item.variant-1 .preview-content h3 {
            font-family:Georgia,serif; font-size:20px; font-weight:700;
            color:#0A0A0A; margin:0 0 6px 0; line-height:1.2;
        }
        .zimny-anuncie-item.variant-1 .preview-content p {
            font-size:13px; color:#646970; margin:0 0 12px 0; line-height:1.5;
        }
        .zimny-anuncie-item.variant-1 .preview-cta {
            display:inline-block; font-size:11px; font-weight:700; text-transform:uppercase;
            letter-spacing:0.15em; color:#0A0A0A; padding:0 0 2px 0;
            border-bottom:2px solid #0A0A0A; transition:all 0.3s ease;
            position:relative; overflow:hidden;
        }
        .zimny-anuncie-item.variant-1 .preview-cta::after {
            content:""; position:absolute; bottom:0; left:0; width:0; height:2px;
            background:#0A0A0A; transition:width 0.3s ease;
        }
        .zimny-anuncie-item.variant-1:hover .preview-cta::after { width:100%; }

        /* ─── Variant 2: Bordered Premium ─── */
        .zimny-anuncie-item.variant-2 {
            border:3px solid #0A0A0A; box-shadow:0 0 0 1px #dcdcde inset;
            animation:borderPulse 3s ease-in-out infinite;
        }
        @keyframes borderPulse {
            0%, 100% { border-color:#0A0A0A; }
            50% { border-color:#555555; }
        }
        .zimny-anuncie-item.variant-2 .anuncie-preview {
            display:flex; align-items:center; gap:20px; padding:20px;
            background:#ffffff; width:100%;
        }
        .zimny-anuncie-item.variant-2 .anuncie-preview .preview-img {
            width:120px; height:120px; border-radius:50%; overflow:hidden;
            flex-shrink:0; border:3px solid #0A0A0A; padding:3px;
            transition:transform 0.4s ease, border-color 0.4s ease;
        }
        .zimny-anuncie-item.variant-2:hover .anuncie-preview .preview-img {
            transform:rotate(-3deg) scale(1.05); border-color:#555;
        }
        .zimny-anuncie-item.variant-2 .preview-content { flex:1; }
        .zimny-anuncie-item.variant-2 .preview-content h3 {
            font-size:16px; font-weight:800; text-transform:uppercase;
            letter-spacing:0.2em; color:#0A0A0A; margin:0 0 6px 0;
        }
        .zimny-anuncie-item.variant-2 .preview-content p {
            font-size:13px; color:#646970; margin:0 0 14px 0; line-height:1.5;
        }
        .zimny-anuncie-item.variant-2 .preview-cta {
            display:inline-flex; align-items:center; gap:6px;
            font-size:11px; font-weight:700; text-transform:uppercase;
            letter-spacing:0.1em; color:#0A0A0A;
            transition:gap 0.3s ease;
        }
        .zimny-anuncie-item.variant-2 .preview-cta .arrow {
            display:inline-block; transition:transform 0.3s ease;
        }
        .zimny-anuncie-item.variant-2:hover .preview-cta { gap:10px; }
        .zimny-anuncie-item.variant-2:hover .preview-cta .arrow { transform:translateX(4px); }

        /* ─── Variant 3: Split Content ─── */
        .zimny-anuncie-item.variant-3 { border:none; box-shadow:0 1px 6px rgba(0,0,0,0.06); }
        .zimny-anuncie-item.variant-3 .anuncie-preview {
            display:flex; align-items:stretch; width:100%; min-height:160px;
        }
        .zimny-anuncie-item.variant-3 .anuncie-preview .preview-img {
            width:180px; flex-shrink:0; overflow:hidden;
            background:#f0f0f1; position:relative;
            transition:width 0.4s ease;
        }
        .zimny-anuncie-item.variant-3:hover .anuncie-preview .preview-img { width:200px; }
        .zimny-anuncie-item.variant-3 .anuncie-preview .preview-img img {
            width:100%; height:100%; object-fit:cover;
            transition:transform 0.5s ease;
        }
        .zimny-anuncie-item.variant-3:hover .anuncie-preview .preview-img img { transform:scale(1.08); }
        .zimny-anuncie-item.variant-3 .preview-content {
            flex:1; padding:20px 24px; display:flex; flex-direction:column;
            justify-content:center; position:relative;
            background:linear-gradient(90deg, #fafafa 0%, #ffffff 30%);
        }
        .zimny-anuncie-item.variant-3 .preview-content::before {
            content:""; position:absolute; left:0; top:10%; height:80%; width:3px;
            background:#0A0A0A; transition:height 0.3s ease;
        }
        .zimny-anuncie-item.variant-3:hover .preview-content::before { height:90%; }
        .zimny-anuncie-item.variant-3 .preview-content h3 {
            font-family:Georgia,serif; font-size:18px; font-weight:700;
            color:#0A0A0A; margin:0 0 4px 0;
        }
        .zimny-anuncie-item.variant-3 .preview-content .decorative-line {
            width:40px; height:2px; background:#0A0A0A; margin:8px 0 10px 0;
            transition:width 0.3s ease;
        }
        .zimny-anuncie-item.variant-3:hover .preview-content .decorative-line { width:60px; }
        .zimny-anuncie-item.variant-3 .preview-content p {
            font-size:12px; color:#646970; margin:0 0 12px 0; line-height:1.5;
        }
        .zimny-anuncie-item.variant-3 .preview-cta {
            display:inline-block; font-size:11px; font-weight:700; text-transform:uppercase;
            letter-spacing:0.1em; color:#fff; background:#0A0A0A;
            padding:8px 20px; border-radius:4px; align-self:flex-start;
            transition:all 0.3s ease; position:relative; overflow:hidden;
        }
        .zimny-anuncie-item.variant-3 .preview-cta::before {
            content:""; position:absolute; top:0; left:-100%; width:100%; height:100%;
            background:rgba(255,255,255,0.15); transition:left 0.4s ease;
        }
        .zimny-anuncie-item.variant-3:hover .preview-cta::before { left:100%; }
        .zimny-anuncie-item.variant-3:hover .preview-cta {
            background:#2C2C2E; transform:translateY(-1px);
            box-shadow:0 2px 8px rgba(0,0,0,0.15);
        }

        /* ─── Variant 4: Full Bleed Bold ─── */
        .zimny-anuncie-item.variant-4 { border:none; overflow:hidden; }
        .zimny-anuncie-item.variant-4 .anuncie-preview {
            position:relative; width:100%; min-height:200px;
            display:flex; align-items:center; justify-content:center;
            overflow:hidden;
        }
        .zimny-anuncie-item.variant-4 .anuncie-preview .preview-img {
            position:absolute; inset:0; width:100%; height:100%;
            transition:transform 0.6s ease;
        }
        .zimny-anuncie-item.variant-4:hover .anuncie-preview .preview-img { transform:scale(1.05); }
        .zimny-anuncie-item.variant-4 .anuncie-preview .preview-img img {
            width:100%; height:100%; object-fit:cover;
        }
        .zimny-anuncie-item.variant-4 .anuncie-preview .preview-overlay {
            position:absolute; inset:0;
            background:linear-gradient(135deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.40) 100%);
            transition:opacity 0.4s ease;
        }
        .zimny-anuncie-item.variant-4:hover .anuncie-preview .preview-overlay { opacity:0.8; }
        .zimny-anuncie-item.variant-4 .preview-content {
            position:relative; z-index:2; text-align:center;
            padding:40px 30px; max-width:400px;
        }
        .zimny-anuncie-item.variant-4 .preview-content h3 {
            font-family:Georgia,serif; font-size:26px; font-weight:700;
            color:#fff; margin:0 0 8px 0; line-height:1.15;
            text-shadow:0 2px 8px rgba(0,0,0,0.3);
        }
        .zimny-anuncie-item.variant-4 .preview-content p {
            font-size:13px; color:rgba(255,255,255,0.75); margin:0 0 18px 0;
            line-height:1.5;
        }
        .zimny-anuncie-item.variant-4 .preview-cta {
            display:inline-block; font-size:12px; font-weight:800; text-transform:uppercase;
            letter-spacing:0.15em; color:#0A0A0A; background:#fff;
            padding:12px 32px; border-radius:6px;
            transition:all 0.3s ease; box-shadow:0 0 0 0 rgba(255,255,255,0);
            animation:ctaGlow 2.5s ease-in-out infinite;
        }
        @keyframes ctaGlow {
            0%, 100% { box-shadow:0 0 0 0 rgba(255,255,255,0.4); }
            50% { box-shadow:0 0 20px 4px rgba(255,255,255,0.2); }
        }
        .zimny-anuncie-item.variant-4:hover .preview-cta {
            background:#f0f0f0; transform:translateY(-2px);
            box-shadow:0 4px 16px rgba(0,0,0,0.3);
        }

        /* ─── Admin controls ─── */
        .zimny-anuncie-item .anuncie-controls {
            display:flex; flex-direction:column; gap:4px; padding:12px;
            background:#fafafa; border-left:1px solid #e0e0e0;
            flex-shrink:0; justify-content:center; align-items:center;
            min-width:90px;
        }
        .zimny-anuncie-item .anuncie-controls .handle {
            color:#8c8f94; font-size:20px; cursor:grab;
        }
        .zimny-anuncie-item .anuncie-controls .variant-badge {
            display:inline-block; padding:2px 8px; border-radius:4px;
            font-size:9px; font-weight:700; text-transform:uppercase;
            letter-spacing:0.05em; white-space:nowrap;
        }
        .zimny-anuncie-item .anuncie-controls .variant-badge.v1 { background:#0A0A0A; color:#fff; }
        .zimny-anuncie-item .anuncie-controls .variant-badge.v2 { background:#fff; color:#0A0A0A; border:2px solid #0A0A0A; }
        .zimny-anuncie-item .anuncie-controls .variant-badge.v3 { background:#f0f0f0; color:#0A0A0A; border-left:3px solid #0A0A0A; }
        .zimny-anuncie-item .anuncie-controls .variant-badge.v4 { background:linear-gradient(135deg,#0A0A0A,#555); color:#fff; }

        .zimny-anuncie-item .anuncie-controls .remove-anuncie-btn {
            width:30px; height:30px; border-radius:50%; border:1px solid #dcdcde;
            background:#fff; cursor:pointer; display:flex; align-items:center;
            justify-content:center; font-size:14px; color:#d63638;
            transition:all 0.2s;
        }
        .zimny-anuncie-item .anuncie-controls .remove-anuncie-btn:hover { background:#fcf0f1; border-color:#d63638; }

        /* ─── Add card area ─── */
        .zimny-anuncie-add-card { margin-top:24px; padding:24px; background:#f0f0f1; border-radius:12px; }
        .zimny-anuncie-add-card h3 { margin-top:0; margin-bottom:12px; display:flex; align-items:center; gap:8px; }
        .zimny-anuncie-add-card select { font-size:14px; padding:6px; min-width:250px; }
        .zimny-anuncie-add-card .button { margin-left:8px; }
        .zimny-anuncie-actions { margin-top:20px; display:flex; gap:12px; align-items:center; }
        ';
    }

    public function render_anuncie_cards_page() {
        $cards = get_option(Zimny_Admin::OPTION_ANUNCIE_CARDS, array());
        if (!is_array($cards)) $cards = array();

        $variant_labels = array(
            1 => 'Minimal Elegance',
            2 => 'Bordered Premium',
            3 => 'Split Content',
            4 => 'Full Bleed Bold',
        );
        $variant_icons = array(1 => '◆', 2 => '◇', 3 => '◈', 4 => '⬟');
        $default_ctas = array(
            1 => 'SAIBA MAIS',
            2 => 'FALE CONOSCO',
            3 => 'QUERO ANUNCIAR',
            4 => 'COMECE AGORA',
        );
        ?>
        <div class="wrap zimny-anuncie-wrap">
            <h1>
                <span class="dashicons dashicons-megaphone" style="font-size:32px; width:32px; height:32px;"></span>
                Anuncie Conosco — Cards
            </h1>
            <p>Gerencie os cards <strong>"Anuncie Conosco"</strong> exibidos no app. Crie cards com 4 variantes visuais exclusivas. Arraste para reordenar. Remova cards para a lista de disponíveis.</p>

            <div id="anuncie-status" class="zimny-layout-status"></div>

            <div style="background:#fff; border:1px solid #dcdcde; padding:20px; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                <div id="zimny-anuncie-list" class="zimny-anuncie-list">
                    <?php if (!empty($cards)): ?>
                        <?php foreach ($cards as $i => $card):
                            $variant   = absint($card['variant'] ?? 1);
                            $img_url   = $card['image_url'] ?? '';
                            $img_id    = absint($card['image_id'] ?? 0);
                            $title     = $card['title'] ?? '';
                            $desc      = $card['description'] ?? '';
                            $cta_text  = $card['cta_text'] ?? ($default_ctas[$variant] ?? 'SAIBA MAIS');
                            $cta_link  = $card['cta_link'] ?? '';
                            $thumb     = $img_id ? wp_get_attachment_image_url($img_id, 'medium') : $img_url;
                            $vclass    = 'variant-' . $variant;
                        ?>
                        <div class="zimny-anuncie-item <?php echo $vclass; ?>" data-index="<?php echo esc_attr($i); ?>" data-variant="<?php echo esc_attr($variant); ?>">
                            <div class="anuncie-preview">
                                <div class="preview-img">
                                    <?php if ($thumb): ?>
                                        <img src="<?php echo esc_url($thumb); ?>" alt="" style="width:100%;height:100%;object-fit:cover;" />
                                    <?php else: ?>
                                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#f0f0f1;color:#8c8f94;font-size:28px;">
                                            <?php echo $variant_icons[$variant] ?? '◆'; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="preview-content">
                                    <h3><?php echo esc_html($title ?: 'Título do Card'); ?></h3>
                                    <?php if ($variant === 3): ?>
                                    <div class="decorative-line"></div>
                                    <?php endif; ?>
                                    <p><?php echo esc_html($desc ?: 'Descrição do card de publicidade. Chame a atenção do seu público-alvo.'); ?></p>
                                    <?php if ($variant === 2): ?>
                                    <span class="preview-cta"><?php echo esc_html($cta_text); ?> <span class="arrow">→</span></span>
                                    <?php else: ?>
                                    <span class="preview-cta"><?php echo esc_html($cta_text); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="anuncie-controls">
                                <span class="handle dashicons dashicons-menu"></span>
                                <span class="variant-badge v<?php echo $variant; ?>"><?php echo $variant_labels[$variant] ?? 'Variant ' . $variant; ?></span>
                                <input type="hidden" class="anuncie-image-url" value="<?php echo esc_attr($img_url); ?>" />
                                <input type="hidden" class="anuncie-image-id" value="<?php echo esc_attr($img_id); ?>" />
                                <input type="hidden" class="anuncie-variant" value="<?php echo esc_attr($variant); ?>" />
                                <input type="hidden" class="anuncie-title" value="<?php echo esc_attr($title); ?>" />
                                <input type="hidden" class="anuncie-desc" value="<?php echo esc_attr($desc); ?>" />
                                <input type="hidden" class="anuncie-cta-text" value="<?php echo esc_attr($cta_text); ?>" />
                                <input type="hidden" class="anuncie-cta-link" value="<?php echo esc_attr($cta_link); ?>" />
                                <button type="button" class="change-anuncie-image button button-small" title="Trocar imagem" style="font-size:11px;padding:2px 8px;">🖼️</button>
                                <button type="button" class="remove-anuncie-btn" onclick="removeAnuncieCard(this)" title="Remover card">✕</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="zimny-anuncie-actions">
                    <button type="button" class="button button-primary button-hero" onclick="saveAnuncieCards()">
                        💾 Salvar Cards
                    </button>
                    <span id="anuncie-spinner" style="display:none;" class="spinner"></span>
                </div>
            </div>

            <!-- Add new card -->
            <div class="zimny-anuncie-add-card">
                <h3>➕ Adicionar Novo Card</h3>
                <p>Escolha uma variante visual e clique em "Adicionar". Configure os detalhes (imagem, título, CTA) depois de adicionado.</p>
                <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                    <select id="add-anuncie-variant">
                        <option value="1">◆ Variante 1 — Minimal Elegance</option>
                        <option value="2">◇ Variante 2 — Bordered Premium</option>
                        <option value="3">◈ Variante 3 — Split Content</option>
                        <option value="4">⬟ Variante 4 — Full Bleed Bold</option>
                    </select>
                    <button type="button" class="button button-secondary" onclick="addAnuncieCard()">
                        <span class="dashicons dashicons-plus-alt2" style="font-size:16px; width:16px; height:16px; margin-top:4px;"></span> Adicionar Card com esta Variante
                    </button>
                </div>
                <div style="margin-top:16px; display:flex; gap:20px; flex-wrap:wrap;">
                    <?php foreach ($variant_labels as $v => $label): ?>
                    <div style="flex:1; min-width:180px; padding:14px; background:#fff; border-radius:8px; border:1px solid #dcdcde; text-align:center;">
                        <div style="font-size:24px; margin-bottom:6px;"><?php echo $variant_icons[$v]; ?></div>
                        <div style="font-weight:700; font-size:12px; color:#0A0A0A;"><?php echo $label; ?></div>
                        <div style="font-size:10px; color:#8c8f94; margin-top:4px;">
                            <?php
                            $descriptions = array(
                                1 => 'Clean minimal · Serif title · Underline CTA',
                                2 => 'Double border · Round image · Arrow CTA',
                                3 => 'Split layout · Accent bar · Fill button CTA',
                                4 => 'Full-bleed image · Gradient overlay · Glow CTA',
                            );
                            echo $descriptions[$v];
                            ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Edição rápida do card selecionado -->
            <div style="margin-top:24px; background:#fff; border:1px solid #dcdcde; padding:20px; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                <h2 style="margin-top:0; font-size:16px; display:flex; align-items:center; gap:8px;">
                    <span class="dashicons dashicons-edit"></span>
                    Editar Card Selecionado
                </h2>
                <p style="font-size:12px; color:#646970;">Clique em um card acima para editar seus detalhes. As alterações são aplicadas em tempo real.</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; max-width:600px;">
                    <div>
                        <label style="display:block; font-weight:600; font-size:12px; margin-bottom:4px;">Título</label>
                        <input type="text" id="anuncie-edit-title" value="" placeholder="Título do card" style="width:100%; padding:8px 10px; border:1px solid #dcdcde; border-radius:6px; font-size:13px; font-family:Georgia,serif; font-weight:700;" oninput="updateSelectedAnuncieField('title', this.value)" />
                    </div>
                    <div>
                        <label style="display:block; font-weight:600; font-size:12px; margin-bottom:4px;">Texto do CTA</label>
                        <input type="text" id="anuncie-edit-cta" value="" placeholder="Ex: SAIBA MAIS" style="width:100%; padding:8px 10px; border:1px solid #dcdcde; border-radius:6px; font-size:13px; font-weight:700; letter-spacing:0.05em;" oninput="updateSelectedAnuncieField('cta', this.value)" />
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="display:block; font-weight:600; font-size:12px; margin-bottom:4px;">Descrição</label>
                        <textarea id="anuncie-edit-desc" value="" placeholder="Descrição do card" style="width:100%; padding:8px 10px; border:1px solid #dcdcde; border-radius:6px; font-size:13px; min-height:60px; resize:vertical;" oninput="updateSelectedAnuncieField('desc', this.value)"></textarea>
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="display:block; font-weight:600; font-size:12px; margin-bottom:4px;">Link do CTA (URL de destino)</label>
                        <input type="text" id="anuncie-edit-link" value="" placeholder="https://..." style="width:100%; padding:8px 10px; border:1px solid #dcdcde; border-radius:6px; font-size:13px; color:#646970;" oninput="updateSelectedAnuncieField('link', this.value)" />
                    </div>
                </div>
                <p style="font-size:11px; color:#8c8f94; margin-top:12px;">💡 Dica: Para trocar a imagem, clique no botão 🖼️ no card desejado.</p>
            </div>

            <div style="margin-top:24px; padding:16px; background:#f0f6fc; border-radius:8px; border-left:4px solid #2271b1;">
                <h3 style="margin:0 0 8px 0; font-size:14px;">📌 Como funciona</h3>
                <ul style="margin:0; padding-left:20px; font-size:12px; color:#50575e; line-height:1.8;">
                    <li>Cada card usa uma das <strong>4 variantes visuais</strong>, cada uma com design e animação exclusivos.</li>
                    <li>Os cards aparecem na seção <strong>"Anuncie Conosco (Cards)"</strong> da Home, configurada no <strong>Layout da Home</strong>.</li>
                    <li>Arraste os cards para definir a ordem de exibição. O primeiro da lista aparece primeiro.</li>
                    <li><strong>Remover</strong> um card o tira do layout atual. Você pode adicioná-lo novamente depois.</li>
                    <li>As animações (hover, pulse, glow, slide) são automáticas — o usuário final vê o efeito sem configuração extra.</li>
                </ul>
            </div>
        </div>

        <style>
        .zimny-anuncie-item .remove-anuncie-btn:hover { background:#fcf0f1; border-color:#d63638; }
        </style>

        <script>
        // ─── Selected card tracking ──────────────────────────────────────────
        var _selectedAnuncieItem = null;

        // ─── Click to select a card for editing ──────────────────────────────
        document.addEventListener('click', function(e) {
            var item = e.target.closest('.zimny-anuncie-item');
            if (!item) return;
            // Don't select when clicking controls
            if (e.target.closest('.anuncie-controls')) return;

            // Deselect previous
            document.querySelectorAll('.zimny-anuncie-item.selected').forEach(function(el) {
                el.style.outline = 'none';
                el.style.boxShadow = '';
                el.classList.remove('selected');
            });

            item.classList.add('selected');
            item.style.outline = '2px solid #2271b1';
            item.style.outlineOffset = '2px';
            _selectedAnuncieItem = item;

            // Populate edit fields
            document.getElementById('anuncie-edit-title').value = item.querySelector('.anuncie-title').value;
            document.getElementById('anuncie-edit-cta').value = item.querySelector('.anuncie-cta-text').value;
            document.getElementById('anuncie-edit-desc').value = item.querySelector('.anuncie-desc').value;
            document.getElementById('anuncie-edit-link').value = item.querySelector('.anuncie-cta-link').value;
        });

        // ─── Update selected card field ──────────────────────────────────────
        function updateSelectedAnuncieField(field, value) {
            if (!_selectedAnuncieItem) {
                alert('Selecione um card clicando nele primeiro.');
                return;
            }

            var hiddenInput = null;
            var displayEl = null;

            switch (field) {
                case 'title':
                    hiddenInput = _selectedAnuncieItem.querySelector('.anuncie-title');
                    displayEl = _selectedAnuncieItem.querySelector('.preview-content h3');
                    break;
                case 'desc':
                    hiddenInput = _selectedAnuncieItem.querySelector('.anuncie-desc');
                    displayEl = _selectedAnuncieItem.querySelector('.preview-content p');
                    break;
                case 'cta':
                    hiddenInput = _selectedAnuncieItem.querySelector('.anuncie-cta-text');
                    displayEl = _selectedAnuncieItem.querySelector('.preview-cta');
                    break;
                case 'link':
                    hiddenInput = _selectedAnuncieItem.querySelector('.anuncie-cta-link');
                    break;
            }

            if (hiddenInput) hiddenInput.value = value;

            if (displayEl) {
                // For CTA with arrow, preserve the arrow span
                if (field === 'cta' && displayEl.querySelector('.arrow')) {
                    displayEl.childNodes[0].textContent = value;
                } else {
                    displayEl.textContent = value;
                }
            }
        }

        // ─── Sortable ────────────────────────────────────────────────────────
        jQuery(document).ready(function($) {
            $("#zimny-anuncie-list").sortable({
                handle: ".handle",
                axis: "y",
                tolerance: "pointer",
                cursor: "grabbing",
                opacity: 0.6,
                items: ".zimny-anuncie-item",
                placeholder: {
                    element: function() {
                        return $('<div style="border:2px dashed #2271b1; background:#f0f6fc; border-radius:12px; height:100px; margin-bottom:16px;"></div>');
                    },
                    update: function() {}
                },
                update: function() {
                    $("#zimny-anuncie-list .zimny-anuncie-item").each(function(i) {
                        $(this).attr("data-index", i);
                    });
                }
            });
            $("#zimny-anuncie-list").disableSelection();
        });

        // ─── Remove card ─────────────────────────────────────────────────────
        function removeAnuncieCard(btn) {
            if (confirm('Remover este card do layout? Ele poderá ser adicionado novamente depois.')) {
                var item = btn.closest('.zimny-anuncie-item');
                if (item) item.remove();
            }
        }

        // ─── Add new card ────────────────────────────────────────────────────
        function addAnuncieCard() {
            var select = document.getElementById('add-anuncie-variant');
            var variant = parseInt(select.value);

            var variantLabels = {1:'Minimal Elegance',2:'Bordered Premium',3:'Split Content',4:'Full Bleed Bold'};
            var variantIcons = {1:'◆',2:'◇',3:'◈',4:'⬟'};
            var defaultCtas = {1:'SAIBA MAIS',2:'FALE CONOSCO',3:'QUERO ANUNCIAR',4:'COMECE AGORA'};
            var defaultDescs = {
                1:'Descrição do card de publicidade. Chame a atenção do seu público-alvo.',
                2:'Anuncie na Zimny Magazine e alcance milhares de leitores engajados.',
                3:'Sua marca aqui. Conteúdo patrocinado com alta relevância editorial.',
                4:'Chegou a hora de crescer. Faça parte do universo Zimny Magazine.',
            };

            var label = variantLabels[variant] || 'Variant ' + variant;
            var icon = variantIcons[variant] || '◆';
            var ctaText = defaultCtas[variant] || 'SAIBA MAIS';
            var descText = defaultDescs[variant] || '';

            var list = document.getElementById('zimny-anuncie-list');
            var div = document.createElement('div');
            div.className = 'zimny-anuncie-item variant-' + variant;
            div.dataset.variant = variant;

            // Build preview HTML based on variant
            var ctaHtml = '';
            if (variant === 2) {
                ctaHtml = '<span class="preview-cta">' + ctaText + ' <span class="arrow">→</span></span>';
            } else {
                ctaHtml = '<span class="preview-cta">' + ctaText + '</span>';
            }

            var decorativeLine = variant === 3 ? '<div class="decorative-line"></div>' : '';

            div.innerHTML = `
                <div class="anuncie-preview">
                    <div class="preview-img">
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#f0f0f1;color:#8c8f94;font-size:28px;">
                            ${icon}
                        </div>
                    </div>
                    <div class="preview-content">
                        <h3>Novo Card ${label}</h3>
                        ${decorativeLine}
                        <p>${descText}</p>
                        ${ctaHtml}
                    </div>
                </div>
                <div class="anuncie-controls">
                    <span class="handle dashicons dashicons-menu"></span>
                    <span class="variant-badge v${variant}">${label}</span>
                    <input type="hidden" class="anuncie-image-url" value="" />
                    <input type="hidden" class="anuncie-image-id" value="0" />
                    <input type="hidden" class="anuncie-variant" value="${variant}" />
                    <input type="hidden" class="anuncie-title" value="Novo Card ${label}" />
                    <input type="hidden" class="anuncie-desc" value="${descText}" />
                    <input type="hidden" class="anuncie-cta-text" value="${ctaText}" />
                    <input type="hidden" class="anuncie-cta-link" value="" />
                    <button type="button" class="change-anuncie-image button button-small" title="Trocar imagem" style="font-size:11px;padding:2px 8px;">🖼️</button>
                    <button type="button" class="remove-anuncie-btn" onclick="removeAnuncieCard(this)" title="Remover card">✕</button>
                </div>
            `;
            list.appendChild(div);

            // Auto-select the new card
            setTimeout(function() {
                div.click();
            }, 100);
        }

        // ─── Save ────────────────────────────────────────────────────────────
        async function saveAnuncieCards() {
            const statusEl = document.getElementById('anuncie-status');
            const spinner = document.getElementById('anuncie-spinner');
            spinner.style.display = 'inline-block';

            const items = document.querySelectorAll('.zimny-anuncie-item');
            const cards = [];

            items.forEach(function(item) {
                cards.push({
                    image_url:  item.querySelector('.anuncie-image-url').value,
                    image_id:   parseInt(item.querySelector('.anuncie-image-id').value) || 0,
                    variant:    parseInt(item.querySelector('.anuncie-variant').value) || 1,
                    title:      item.querySelector('.anuncie-title').value.trim(),
                    description: item.querySelector('.anuncie-desc').value.trim(),
                    cta_text:   item.querySelector('.anuncie-cta-text').value.trim(),
                    cta_link:   item.querySelector('.anuncie-cta-link').value.trim(),
                });
            });

            try {
                const formData = new FormData();
                formData.append('action', 'zimny_save_anuncie_cards');
                formData.append('cards', JSON.stringify(cards));
                formData.append('_wpnonce', '<?php echo wp_create_nonce('zimny_save_anuncie_cards'); ?>');

                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();

                statusEl.className = 'zimny-layout-status ' + (result.success ? 'success' : 'error');
                statusEl.textContent = result.success
                    ? '✅ ' + cards.length + ' card(s) salvo(s) com sucesso!'
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