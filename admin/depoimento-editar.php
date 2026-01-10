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
$depoimento = null;

// Buscar depoimento
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM depoimentos WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $depoimento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$depoimento) {
        header('Location: depoimentos.php?erro=notfound');
        exit;
    }
} else {
    header('Location: depoimentos.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $tipo_depoimento = 'video_local'; // Fixo em video_local
    $ordem = (int)($_POST['ordem'] ?? 0);
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    // Validações
    if (empty($nome)) {
        $erro = 'O nome é obrigatório.';
    } else {
        $videoPath = $depoimento['video']; // Manter vídeo atual por padrão
        
        // Se um novo vídeo foi enviado
        if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
            $video = $_FILES['video'];
            $videoExtension = strtolower(pathinfo($video['name'], PATHINFO_EXTENSION));
            
            if ($videoExtension !== 'mp4') {
                $erro = 'Apenas arquivos MP4 são permitidos.';
            } elseif ($video['size'] > 50 * 1024 * 1024) {
                $erro = 'O vídeo deve ter no máximo 50MB.';
            } else {
                $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($nome));
                $videoName = 'depoimento-' . $slug . '-' . time() . '.mp4';
                $newVideoPath = '../assets/videos/' . $videoName;
                
                if (move_uploaded_file($video['tmp_name'], $newVideoPath)) {
                    // Deletar vídeo antigo se existir
                    if ($depoimento['video'] && file_exists('../' . $depoimento['video'])) {
                        unlink('../' . $depoimento['video']);
                    }
                    $videoPath = 'assets/videos/' . $videoName;
                } else {
                    $erro = 'Erro ao fazer upload do novo vídeo.';
                }
            }
        }
        
        if (empty($erro)) {
            try {
                $pdo = getConnection();
                $stmt = $pdo->prepare("UPDATE depoimentos SET nome = :nome, video = :video, tipo_depoimento = :tipo_depoimento, ordem = :ordem, ativo = :ativo WHERE id = :id");
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':video', $videoPath);
                $stmt->bindParam(':tipo_depoimento', $tipo_depoimento);
                $stmt->bindParam(':ordem', $ordem, PDO::PARAM_INT);
                $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    header('Location: depoimentos.php?msg=updated');
                    exit;
                } else {
                    $erro = 'Erro ao atualizar depoimento.';
                }
            } catch (PDOException $e) {
                $erro = 'Erro ao atualizar depoimento: ' . $e->getMessage();
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
    <title>Editar Depoimento - Painel Admin</title>
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
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="header-text">
                            <h1>Editar Depoimento</h1>
                            <p>Altere as informações do depoimento</p>
                        </div>
                    </div>
                    <a href="depoimentos.php" class="btn-balanced-light">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
                
                <!-- Preview do Conteúdo Atual -->
                <div class="form-card" style="margin-bottom: 24px;">
                    <div class="form-section">
                        <h3 class="section-title">Conteúdo Atual</h3>
                        <p class="section-description">Visualização do depoimento antes da edição</p>
                <?php 
                $tipoAtual = $depoimento['tipo_depoimento'] ?? 'video_local';
                if ($tipoAtual === 'video_local' && !empty($depoimento['video'])): 
                ?>
                    <div class="video-preview">
                        <video controls style="width: 100%; max-width: 560px; height: auto; border-radius: 8px;">
                            <source src="<?= htmlspecialchars($depoimento['video']) ?>" type="video/mp4">
                            Seu navegador não suporta a tag de vídeo.
                        </video>
                    </div>
                <?php elseif ($tipoAtual === 'video_url' && !empty($depoimento['video_url'])): 
                    $videoUrl = $depoimento['video_url'];
                    $embedUrl = '';
                    if (strpos($videoUrl, 'youtube.com') !== false || strpos($videoUrl, 'youtu.be') !== false) {
                        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $videoUrl, $matches);
                        if (!empty($matches[1])) $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
                    } elseif (strpos($videoUrl, 'vimeo.com') !== false) {
                        preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $matches);
                        if (!empty($matches[1])) $embedUrl = 'https://player.vimeo.com/video/' . $matches[1];
                    }
                ?>
                    <div class="video-preview">
                        <?php if ($embedUrl): ?>
                            <iframe src="<?= htmlspecialchars($embedUrl) ?>" width="560" height="315" frameborder="0" allowfullscreen style="border-radius: 8px;"></iframe>
                        <?php else: ?>
                            <p>URL: <?= htmlspecialchars($videoUrl) ?></p>
                        <?php endif; ?>
                    </div>
                <?php elseif ($tipoAtual === 'texto' && !empty($depoimento['depoimento'])): ?>
                    <div class="text-preview" style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #DAA520;">
                        <div style="color: #333;"><?= $depoimento['depoimento'] ?></div>
                    </div>
                <?php elseif ($tipoAtual === 'video_com_texto'): ?>
                    <?php if (!empty($depoimento['video'])): ?>
                        <div class="video-preview" style="margin-bottom: 20px;">
                            <video controls style="width: 100%; max-width: 560px; height: auto; border-radius: 8px;">
                                <source src="<?= htmlspecialchars($depoimento['video']) ?>" type="video/mp4">
                                Seu navegador não suporta a tag de vídeo.
                            </video>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($depoimento['depoimento'])): ?>
                        <div class="text-preview" style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #DAA520;">
                            <div style="color: #333;"><?= $depoimento['depoimento'] ?></div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                    </div>
                </div>
                
                <!-- Formulário de Edição -->
                <div class="form-card">
                    <form method="POST" enctype="multipart/form-data" class="form-balanced" id="depoimentoForm">
                        <!-- Seção: Informações Básicas -->
                        <div class="form-section">
                            <h3 class="section-title">Informações Básicas</h3>
                            
                            <div class="form-group">
                                <label for="nome">Nome *</label>
                                <input type="text" id="nome" name="nome" placeholder="Ex: João Silva" value="<?= htmlspecialchars($_POST['nome'] ?? $depoimento['nome'] ?? '') ?>">
                            </div>
                    
                    <!-- Tipo de Depoimento -->
                    <div class="form-group">
                        <label for="tipo_depoimento">Tipo de Depoimento *</label>
                        <select id="tipo_depoimento" name="tipo_depoimento" onchange="toggleDepoimentoFields()">
                            <?php $tipoSelecionado = $_POST['tipo_depoimento'] ?? $depoimento['tipo_depoimento'] ?? 'video_local'; ?>
                            <option value="video_local" <?= $tipoSelecionado === 'video_local' ? 'selected' : '' ?>>Vídeo Local (MP4)</option>
                            <option value="video_url" <?= $tipoSelecionado === 'video_url' ? 'selected' : '' ?>>YouTube ou Vimeo (URL)</option>
                            <option value="texto" <?= $tipoSelecionado === 'texto' ? 'selected' : '' ?>>Apenas Texto</option>
                            <option value="video_com_texto" <?= $tipoSelecionado === 'video_com_texto' ? 'selected' : '' ?>>Vídeo com Texto</option>
                        </select>
                    </div>
                        </div>
                    
                        <!-- Seção: Conteúdo do Depoimento -->
                        <div class="form-section">
                            <h3 class="section-title">Conteúdo do Depoimento</h3>
                            <p class="section-description">Atualize o formato do conteúdo (deixe em branco para manter o atual)</p>
                            
                    <!-- Campo Vídeo Local -->
                    <div class="form-group" id="field-video-local">
                        <label for="video">Novo Vídeo (MP4)</label>
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
                        <input type="url" id="video_url" name="video_url" placeholder="Ex: https://www.youtube.com/watch?v=..." value="<?= htmlspecialchars($_POST['video_url'] ?? $depoimento['video_url'] ?? '') ?>">
                        <div id="urlPreview" style="display: none; margin-top: 12px;">
                            <iframe id="previewIframe" width="100%" height="315" frameborder="0" allowfullscreen style="border-radius: 8px;"></iframe>
                        </div>
                    </div>
                    
                    <!-- Campo Texto do Depoimento -->
                    <div class="form-group" id="field-depoimento-texto" style="display: none;">
                        <label for="depoimento">Conteúdo *</label>
                        <textarea id="depoimento" name="depoimento" rows="10"><?= htmlspecialchars($_POST['depoimento'] ?? $depoimento['depoimento'] ?? '') ?></textarea>
                        <small style="display: block; margin-top: 8px; color: #666;">Use o editor para formatar o conteúdo do depoimento</small>
                    </div>
                        </div>
                    
                        <!-- Seção: Configurações -->
                        <div class="form-section">
                            <h3 class="section-title">Configurações</h3>
                            
                            <div class="form-group">
                                <label for="ordem">Ordem de Exibição</label>
                                <input type="number" id="ordem" name="ordem" min="0" value="<?= htmlspecialchars($_POST['ordem'] ?? $depoimento['ordem'] ?? '0') ?>" placeholder="0">
                            </div>
                            
                            <div class="checkbox-wrapper">
                                <div class="checkbox-item">
                                    <label for="ativo">
                                        <input type="checkbox" id="ativo" name="ativo" <?= isset($_POST['ativo']) || (!$_POST && $depoimento['ativo']) ? 'checked' : '' ?>>
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
                            <i class="fas fa-check"></i> Salvar Alterações
                        </button>
                        <a href="depoimentos.php" class="btn-balanced-light">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <style>
        .video-preview-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 24px;
            margin-bottom: 24px;
        }
        
        .video-preview-card h3 {
            margin: 0 0 16px 0;
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .video-preview {
            width: 100%;
            max-width: 560px;
            height: 315px;
            border-radius: 8px;
            overflow: hidden;
            background: #000;
        }
        
        .video-preview iframe {
            width: 100%;
            height: 100%;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 32px;
        }
        
        .form {
            max-width: 800px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 14px;
        }
        
        .form-group .required {
            color: var(--danger-color);
        }
        
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: 'Lato', sans-serif;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--vermelho-primario);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-group small {
            display: block;
            margin-top: 6px;
            color: var(--text-light);
            font-size: 13px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 12px;
            background: #f5f5f5;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .checkbox-label:hover {
            background: #e0e0e0;
        }
        
        .checkbox-label input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .checkbox-label span {
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--border-color);
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }
        
        .alert-danger {
            background: #fee;
            color: var(--danger-color);
            border: 1px solid var(--danger-color);
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
            
            .video-preview {
                height: 200px;
            }
        }
    </style>
    
    <script>
        // Toggle campos baseado no tipo de depoimento
        function toggleDepoimentoFields() {
            const tipo = document.getElementById('tipo_depoimento').value;
            const videoLocal = document.getElementById('field-video-local');
            const videoUrl = document.getElementById('field-video-url');
            const depoimentoTexto = document.getElementById('field-depoimento-texto');
            const videoInput = document.getElementById('video');
            const videoUrlInput = document.getElementById('video_url');
            const depoimentoInput = document.getElementById('depoimento');
            
            // Resetar todos
            videoLocal.style.display = 'none';
            videoUrl.style.display = 'none';
            depoimentoTexto.style.display = 'none';
            videoInput.removeAttribute('required');
            videoUrlInput.removeAttribute('required');
            depoimentoInput.removeAttribute('required');
            
            // Mostrar campo apropriado
            if (tipo === 'video_local') {
                videoLocal.style.display = 'block';
            } else if (tipo === 'video_url') {
                videoUrl.style.display = 'block';
                videoUrlInput.setAttribute('required', 'required');
            } else if (tipo === 'texto') {
                depoimentoTexto.style.display = 'block';
                depoimentoInput.setAttribute('required', 'required');
            } else if (tipo === 'video_com_texto') {
                videoLocal.style.display = 'block';
                depoimentoTexto.style.display = 'block';
                depoimentoInput.setAttribute('required', 'required');
            }
        }
        
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
        
        // Inicializar ao carregar
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
        
        // Sincronizar TinyMCE antes de submeter o formulário
        document.querySelector('form')?.addEventListener('submit', function(e) {
            if (tinymce.get('depoimento')) {
                tinymce.get('depoimento').save();
            }
        });
    </script>
</body>
</html>
