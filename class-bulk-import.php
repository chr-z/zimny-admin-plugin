<?php
/**
 * Zimny_Admin_Bulk_Import - Página "Importador em Lote"
 *
 * @package Zimny_Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Zimny_Admin_Bulk_Import {

    public function render_bulk_import_page() {
        ?>
        <div class="wrap">
            <h1 style="display:flex; align-items:center; gap:10px;">
                <span class="dashicons dashicons-upload" style="font-size:32px; width:32px; height:32px;"></span>
                Zimny Admin — Importador em Lote de Pastas (Bulk Sync)
            </h1>
            <p>Selecione a pasta raiz contendo as subpastas criadas pelo script PowerShell (onde cada subpasta possui um <code>video.mp4</code> e <code>thumb.jpg</code>). O WordPress vai importar, vincular os arquivos automaticamente e criar os posts!</p>

            <div style="background:#fff; border:1px solid #ccc; padding:25px; border-radius:8px; max-width:800px; margin-top:20px; box-shadow:0 2px 5px rgba(0,0,0,0.05);">
                <h3>1. Selecionar Pastas do Computador</h3>
                <p>Escolha a pasta raiz <strong><code>zimny_videos_prontos</code></strong> no seu computador:</p>

                <input type="file" id="zimny_folder_input" webkitdirectory directory multiple style="display:none;" onchange="handleFolderSelect(event)">

                <button type="button" class="button button-primary button-hero" onclick="document.getElementById('zimny_folder_input').click();">
                    📂 Selecionar Pasta "zimny_videos_prontos"
                </button>

                <div id="import_summary" style="margin-top:20px; display:none;">
                    <hr>
                    <h3>2. Carrossel de Destino Inicial</h3>
                    <p>Escolha em qual carrossel/coluna esses vídeos devem ser inseridos por padrão (você pode alterar individualmente depois):</p>

                    <?php
                    $terms = get_terms(array('taxonomy' => 'video_carousel', 'hide_empty' => false));
                    if (!empty($terms)) {
                        echo '<select id="target_carousel" style="font-size:16px; padding:6px; min-width:300px;">';
                        echo '<option value="">-- Não associar a nenhum carrossel agora --</option>';
                        foreach ($terms as $term) {
                            echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . ' (' . esc_html($term->slug) . ')</option>';
                        }
                        echo '</select>';
                    } else {
                        echo '<p style="color:#d63638;">Nenhum carrossel criado ainda. Crie um carrossel no menu lateral "Carrosséis".</p>';
                    }
                    ?>

                    <br><br>
                    <button type="button" id="start_upload_btn" class="button button-secondary button-hero" onclick="startBulkImport()">
                        🚀 Iniciar Importação em Lote (<span id="pair_count">0</span> Pares Identificados)
                    </button>
                </div>

                <div id="progress_container" style="margin-top:25px; display:none;">
                    <div style="background:#e0e0e0; border-radius:10px; height:24px; width:100%; overflow:hidden;">
                        <div id="progress_bar" style="background:#2271b1; height:100%; width:0%; transition:width 0.3s; text-align:center; color:#fff; font-weight:bold; line-height:24px; font-size:12px;">0%</div>
                    </div>
                    <p id="progress_status" style="margin-top:8px; font-weight:600; color:#50575e;">Enviando arquivos...</p>
                </div>
            </div>

            <div id="import_log" style="margin-top:20px; max-width:800px;"></div>
        </div>

        <script>
        let detectedPairs = {};

        function handleFolderSelect(event) {
            const files = event.target.files;
            detectedPairs = {};

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const pathParts = file.webkitRelativePath.split('/');

                if (pathParts.length >= 2) {
                    const folderName = pathParts[pathParts.length - 2];
                    const fileName = pathParts[pathParts.length - 1].toLowerCase();

                    if (!detectedPairs[folderName]) {
                        detectedPairs[folderName] = { video: null, thumb: null, title: folderName };
                    }

                    if (fileName.endsWith('.mp4')) {
                        detectedPairs[folderName].video = file;
                    } else if (fileName.endsWith('.jpg') || fileName.endsWith('.jpeg') || fileName.endsWith('.png')) {
                        detectedPairs[folderName].thumb = file;
                    }
                }
            }

            const validFolderKeys = Object.keys(detectedPairs).filter(key => detectedPairs[key].video && detectedPairs[key].thumb);

            if (validFolderKeys.length === 0) {
                alert('Nenhuma pasta contendo simultaneamente "video.mp4" e "thumb.jpg" foi encontrada.');
                return;
            }

            document.getElementById('pair_count').innerText = validFolderKeys.length;
            document.getElementById('import_summary').style.display = 'block';
        }

        async function startBulkImport() {
            const validFolderKeys = Object.keys(detectedPairs).filter(key => detectedPairs[key].video && detectedPairs[key].thumb);
            const total = validFolderKeys.length;
            if (total === 0) return;

            const targetCarousel = document.getElementById('target_carousel') ? document.getElementById('target_carousel').value : '';

            document.getElementById('start_upload_btn').disabled = true;
            document.getElementById('progress_container').style.display = 'block';

            const logContainer = document.getElementById('import_log');
            logContainer.innerHTML = '<h3>Status do Processamento:</h3>';

            let completed = 0;

            for (const folderName of validFolderKeys) {
                const item = detectedPairs[folderName];

                const formData = new FormData();
                formData.append('action', 'zimny_bulk_import_folder');
                formData.append('folder_name', folderName);
                formData.append('carousel_id', targetCarousel);
                formData.append('video_file', item.video);
                formData.append('thumb_file', item.thumb);

                try {
                    const response = await fetch(ajaxurl, {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.success) {
                        logContainer.innerHTML += `<div style="background:#e7f4e8; border-left:4px solid #46b450; padding:10px; margin-bottom:8px; border-radius:4px;">
                            ✅ <strong>${folderName}</strong> importado com sucesso! Post ID: ${result.data.post_id} | <a href="${result.data.edit_url}" target="_blank">Editar Título/Carrossel</a>
                        </div>`;
                    } else {
                        logContainer.innerHTML += `<div style="background:#fcf0f1; border-left:4px solid #dc3232; padding:10px; margin-bottom:8px; border-radius:4px;">
                            ❌ <strong>${folderName}</strong> erro: ${result.data}
                        </div>`;
                    }
                } catch (err) {
                    logContainer.innerHTML += `<div style="background:#fcf0f1; border-left:4px solid #dc3232; padding:10px; margin-bottom:8px; border-radius:4px;">
                        ❌ <strong>${folderName}</strong> falha de conexão na requisição.
                    </div>`;
                }

                completed++;
                const percent = Math.round((completed / total) * 100);
                document.getElementById('progress_bar').style.width = percent + '%';
                document.getElementById('progress_bar').innerText = percent + '%';
                document.getElementById('progress_status').innerText = `Processando ${completed} de ${total} vídeos...`;
            }

            document.getElementById('progress_status').innerText = '✨ Processo finalizado com sucesso!';
            document.getElementById('progress_status').style.color = '#46b450';
        }
        </script>
        <?php
    }
}