<?php
header('Content-Type: text/html; charset=UTF-8');

// Configurar limites de upload
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '110M');
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '300');

require_once '../config/db.php';
require_once 'auth.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar se o POST está vazio (upload maior que os limites)
    if (empty($_POST) && empty($_FILES)) {
        $postSize = ini_get('post_max_size');
        $uploadSize = ini_get('upload_max_filesize');
        $erro = "O vídeo é muito grande. Limites: upload={$uploadSize}, post={$postSize}. Use um vídeo menor ou reduza a qualidade.";
    } else {
    $nome = trim($_POST['nome'] ?? '');
    $depoimento = trim($_POST['depoimento'] ?? '');
    $video_url = trim($_POST['video_url'] ?? '');
    $tipo_depoimento = $_POST['tipo_depoimento'] ?? 'video_local';
    $ordem = (int)($_POST['ordem'] ?? 0);
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    // Validações
    if (empty($nome)) {
        $erro = 'O nome é obrigatório.';
    } elseif ($tipo_depoimento === 'video_local' && (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK)) {
        $erro = 'O vídeo é obrigatório quando o tipo é "Vídeo Local".';
    } elseif ($tipo_depoimento === 'video_url' && empty($video_url)) {
        $erro = 'A URL do vídeo é obrigatória quando o tipo é "YouTube/Vimeo".';
    } elseif ($tipo_depoimento === 'texto' && empty($depoimento)) {
        $erro = 'O texto do depoimento é obrigatório quando o tipo é "Apenas Texto".';
    } elseif ($tipo_depoimento === 'video_com_texto' && (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK)) {
        $erro = 'O vídeo é obrigatório quando o tipo é "Vídeo com Texto".';
    } elseif ($tipo_depoimento === 'video_com_texto' && empty($depoimento)) {
        $erro = 'O texto é obrigatório quando o tipo é "Vídeo com Texto".';
    } else {
        $videoDbPath = null;
        
        // Upload de vídeo local
        if (($tipo_depoimento === 'video_local' || $tipo_depoimento === 'video_com_texto') && isset($_FILES['video'])) {
            $video = $_FILES['video'];
            $videoExtension = strtolower(pathinfo($video['name'], PATHINFO_EXTENSION));
            
            // Validar extensão
            if ($videoExtension !== 'mp4') {
                $erro = 'Apenas arquivos MP4 são permitidos.';
            } elseif ($video['size'] > 50 * 1024 * 1024) { // 50MB
                $erro = 'O vídeo deve ter no máximo 50MB.';
            } else {
                // Gerar nome único para o arquivo
                $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($nome));
                $videoName = 'depoimento-' . $slug . '-' . time() . '.mp4';
                $videoPath = '../assets/videos/' . $videoName;
                
                if (!move_uploaded_file($video['tmp_name'], $videoPath)) {
                    $erro = 'Erro ao fazer upload do vídeo.';
                } else {
                    $videoDbPath = '../assets/videos/' . $videoName;
                }
            }
        }
        
        // Inserir no banco se não houver erros
        if (empty($erro)) {
            try {
                $pdo = getConnection();
                $stmt = $pdo->prepare("INSERT INTO depoimentos (nome, depoimento, video, video_url, tipo_depoimento, ordem, ativo) VALUES (:nome, :depoimento, :video, :video_url, :tipo_depoimento, :ordem, :ativo)");
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':depoimento', $depoimento);
                $stmt->bindParam(':video', $videoDbPath);
                $stmt->bindParam(':video_url', $video_url);
                $stmt->bindParam(':tipo_depoimento', $tipo_depoimento);
                $stmt->bindParam(':ordem', $ordem, PDO::PARAM_INT);
                $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    header('Location: depoimentos.php?msg=created');
                    exit;
                } else {
                    if ($videoDbPath && file_exists($videoPath)) unlink($videoPath);
                    $erro = 'Erro ao criar depoimento.';
                }
            } catch (PDOException $e) {
                if ($videoDbPath && file_exists($videoPath)) unlink($videoPath);
                $erro = 'Erro ao criar depoimento: ' . $e->getMessage();
            }
        }
    }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Depoimento - Painel Admin</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/noticias.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include 'includes/topbar.php'; ?>
            
            <div class="content">
                <?php if ($erro): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>
                
                <div class="page-header-balanced">
                    <div class="header-left">
                        <div class="icon-wrapper">
                            <i class="fas fa-video"></i>
                        </div>
                        <div class="header-text">
                            <h1>Novo Depoimento</h1>
                            <p>Adicione um novo depoimento de atleta ou responsável</p>
                        </div>
                    </div>
                    <a href="depoimentos.php" class="btn-balanced-light">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
                
                <div class="form-card">
                    <form method="POST" enctype="multipart/form-data" class="form-balanced" id="depoimentoForm">
                        <!-- Seção: Informações Básicas -->
                        <div class="form-section">
                            <h3 class="section-title">Informações Básicas</h3>
                            <div class="form-group">
                                <label for="nome">Nome *</label>
                                <input type="text" id="nome" name="nome" placeholder="Ex: João Silva" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="tipo_depoimento">Tipo de Depoimento *</label>
                                <select id="tipo_depoimento" name="tipo_depoimento" onchange="toggleDepoimentoFields()">
                                    <option value="video_local" <?= ($_POST['tipo_depoimento'] ?? '') === 'video_local' ? 'selected' : '' ?>>Vídeo Local (MP4)</option>
                                    <option value="video_url" <?= ($_POST['tipo_depoimento'] ?? '') === 'video_url' ? 'selected' : '' ?>>YouTube ou Vimeo (URL)</option>
                                    <option value="texto" <?= ($_POST['tipo_depoimento'] ?? '') === 'texto' ? 'selected' : '' ?>>Apenas Texto</option>
                                    <option value="video_com_texto" <?= ($_POST['tipo_depoimento'] ?? '') === 'video_com_texto' ? 'selected' : '' ?>>Vídeo com Texto</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Seção: Conteúdo do Depoimento -->
                        <div class="form-section">
                            <h3 class="section-title">Conteúdo do Depoimento</h3>
                            <p class="section-description">Escolha o formato do conteúdo</p>
                            
                            <!-- Campo Vídeo Local -->
                            <div class="form-group" id="field-video-local">
                                <label for="video">Vídeo (MP4) *</label>
                                <div class="image-upload-box">
                                    <input type="file" id="video" name="video" accept="video/mp4">
                                    <label for="video" class="upload-label">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>Clique para selecionar o vídeo</span>
                                        <small>Arquivo MP4 (máx: <?= ini_get('upload_max_filesize') ?>)</small>
                                    </label>
                                </div>
                                <div id="videoPreview" style="display: none; margin-top: 12px;">
                                    <video id="previewVideo" controls style="max-width: 100%; height: auto; border-radius: 8px;"></video>
                                </div>
                            </div>
                            
                            <!-- Campo URL YouTube/Vimeo -->
                            <div class="form-group" id="field-video-url" style="display: none;">
                                <label for="video_url">URL do Vídeo (YouTube ou Vimeo) *</label>
                                <input type="url" id="video_url" name="video_url" placeholder="Ex: https://www.youtube.com/watch?v=..." value="<?= htmlspecialchars($_POST['video_url'] ?? '') ?>">
                                <div id="urlPreview" style="display: none; margin-top: 12px;">
                                    <iframe id="previewIframe" width="100%" height="315" frameborder="0" allowfullscreen style="border-radius: 8px;"></iframe>
                                </div>
                            </div>
                            
                            <!-- Campo Texto do Depoimento -->
                            <div class="form-group" id="field-depoimento-texto" style="display: none;">
                                <label for="depoimento">Conteúdo *</label>
                                <textarea id="depoimento" name="depoimento" rows="10"><?= htmlspecialchars($_POST['depoimento'] ?? '') ?></textarea>
                                <small style="display: block; margin-top: 8px; color: #666;">Use o editor para formatar o conteúdo do depoimento</small>
                            </div>
                            </div>
                        </div>
                        
                        <!-- Seção: Configurações -->
                        <div class="form-section">
                            <h3 class="section-title">Configurações</h3>
                            
                            <div class="form-group">
                                <label for="ordem">Ordem de Exibição</label>
                                <input type="number" id="ordem" name="ordem" min="0" value="<?= htmlspecialchars($_POST['ordem'] ?? '0') ?>" placeholder="0">
                            </div>
                            
                            <div class="checkbox-wrapper">
                                <div class="checkbox-item">
                                    <label for="ativo">
                                        <input type="checkbox" id="ativo" name="ativo" <?= (!isset($_POST['nome']) || isset($_POST['ativo'])) ? 'checked' : '' ?>>
                                        <div class="checkbox-label-text">
                                            <strong>Depoimento Ativo</strong>
                                            <small>Apenas depoimentos ativos são exibidos no site</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-balanced">
                                <i class="fas fa-check"></i> Criar Depoimento
                            </button>
                            <a href="depoimentos.php" class="btn-balanced-light">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Modal de Erro -->
    <div id="errorModal" class="error-modal">
        <div class="error-content">
            <div class="error-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Atenção</h3>
            </div>
            <div class="error-body">
                <ul id="errorList"></ul>
            </div>
            <button onclick="closeErrorModal()" class="btn-balanced">
                <i class="fas fa-check"></i> Entendi
            </button>
        </div>
    </div>
    
    <script>
        const form = document.getElementById('depoimentoForm');
        const errorModal = document.getElementById('errorModal');
        const errorList = document.getElementById('errorList');
        
        function toggleDepoimentoFields() {
            const tipo = document.getElementById('tipo_depoimento').value;
            const videoLocal = document.getElementById('field-video-local');
            const videoUrl = document.getElementById('field-video-url');
            const depoimentoTexto = document.getElementById('field-depoimento-texto');
            const videoInput = document.getElementById('video');
            const videoUrlInput = document.getElementById('video_url');
            const depoimentoInput = document.getElementById('depoimento');
            
            videoLocal.style.display = 'none';
            videoUrl.style.display = 'none';
            depoimentoTexto.style.display = 'none';
            videoInput.removeAttribute('required');
            videoUrlInput.removeAttribute('required');
            depoimentoInput.removeAttribute('required');
            
            if (tipo === 'video_local') {
                videoLocal.style.display = 'block';
                videoInput.setAttribute('required', 'required');
            } else if (tipo === 'video_url') {
                videoUrl.style.display = 'block';
                videoUrlInput.setAttribute('required', 'required');
            } else if (tipo === 'texto') {
                depoimentoTexto.style.display = 'block';
                depoimentoInput.setAttribute('required', 'required');
            } else if (tipo === 'video_com_texto') {
                videoLocal.style.display = 'block';
                depoimentoTexto.style.display = 'block';
                videoInput.setAttribute('required', 'required');
                depoimentoInput.setAttribute('required', 'required');
            }
        }
        
        form.addEventListener('submit', function(e) {
            // Sincronizar conteúdo do TinyMCE antes de validar
            if (tinymce.get('depoimento')) {
                tinymce.get('depoimento').save();
            }
            
            const errors = [];
            
            const nome = document.getElementById('nome').value.trim();
            if (!nome) {
                errors.push('O nome é obrigatório');
            } else if (nome.length < 3) {
                errors.push('O nome deve ter pelo menos 3 caracteres');
            }
            
            const tipo = document.getElementById('tipo_depoimento').value;
            if (tipo === 'video_local') {
                const video = document.getElementById('video').files[0];
                if (!video) {
                    errors.push('O vídeo é obrigatório');
                } else if (video.type !== 'video/mp4') {
                    errors.push('Apenas arquivos MP4 são permitidos');
                }
            } else if (tipo === 'video_url') {
                const url = document.getElementById('video_url').value.trim();
                if (!url) {
                    errors.push('A URL do vídeo é obrigatória');
                }
            } else if (tipo === 'texto') {
                const texto = document.getElementById('depoimento').value.trim();
                if (!texto) {
                    errors.push('O texto do depoimento é obrigatório');
                }
            } else if (tipo === 'video_com_texto') {
                const video = document.getElementById('video').files[0];
                const texto = document.getElementById('depoimento').value.trim();
                if (!video) {
                    errors.push('O vídeo é obrigatório para Vídeo com Texto');
                } else if (video.type !== 'video/mp4') {
                    errors.push('Apenas arquivos MP4 são permitidos');
                }
                if (!texto) {
                    errors.push('O texto é obrigatório para Vídeo com Texto');
                }
            }
            
            if (errors.length > 0) {
                e.preventDefault();
                showError(errors);
                return false;
            }
        });
        
        function showError(errors) {
            errorList.innerHTML = '';
            errors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                errorList.appendChild(li);
            });
            errorModal.classList.add('show');
        }
        
        function closeErrorModal() {
            errorModal.classList.remove('show');
        }
        
        errorModal.addEventListener('click', function(e) {
            if (e.target === errorModal) {
                closeErrorModal();
            }
        });
        
        // Preview de vídeo local
        document.getElementById('video')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type === 'video/mp4') {
                const preview = document.getElementById('videoPreview');
                const video = document.getElementById('previewVideo');
                video.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            }
        });
        
        // Preview de URL (YouTube/Vimeo)
        document.getElementById('video_url')?.addEventListener('blur', function(e) {
            const url = e.target.value.trim();
            if (!url) return;
            
            let embedUrl = '';
            
            // YouTube
            if (url.includes('youtube.com') || url.includes('youtu.be')) {
                const match = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/);
                if (match && match[1]) {
                    embedUrl = 'https://www.youtube.com/embed/' + match[1];
                }
            }
            // Vimeo
            else if (url.includes('vimeo.com')) {
                const match = url.match(/vimeo\.com\/(\d+)/);
                if (match && match[1]) {
                    embedUrl = 'https://player.vimeo.com/video/' + match[1];
                }
            }
            
            if (embedUrl) {
                const urlPreview = document.getElementById('urlPreview');
                const previewIframe = document.getElementById('previewIframe');
                previewIframe.src = embedUrl;
                urlPreview.style.display = 'block';
            }
        });
        
        document.addEventListener('DOMContentLoaded', toggleDepoimentoFields);
    </script>
    
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/pjivdo2bif18etpq2hxcq117ejq55w9zlu2aa2u669mwgdpl/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        // Inicializar TinyMCE
        tinymce.init({
            selector: '#depoimento',
            language: 'pt_BR',
            height: 400,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | link | code',
            content_style: 'body { font-family: Lato, Arial, sans-serif; font-size: 14px; line-height: 1.6; }',
            branding: false,
            promotion: false,
            setup: function(editor) {
                editor.on('init', function() {
                    console.log('TinyMCE inicializado com sucesso no campo depoimento!');
                });
            }
        });
    </script>
</body>
</html>
