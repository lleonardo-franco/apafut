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
    $tipo_depoimento = 'video_local'; // Fixo em video_local
    $ordem = (int)($_POST['ordem'] ?? 0);
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    // Validações
    if (empty($nome)) {
        $erro = 'O nome é obrigatório.';
    } elseif (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        $erro = 'O vídeo é obrigatório.';
    } else {
        $video = $_FILES['video'];
        $videoExtension = strtolower(pathinfo($video['name'], PATHINFO_EXTENSION));
        
        // Validar extensão e tamanho
        if ($videoExtension !== 'mp4') {
            $erro = 'Apenas arquivos MP4 são permitidos.';
        } elseif ($video['size'] > 50 * 1024 * 1024) {
            $erro = 'O vídeo deve ter no máximo 50MB.';
        }
    }
    
    if (empty($erro)) {
        $videoDbPath = null;
        
        // Upload de vídeo
        if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
            $video = $_FILES['video'];
            $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($nome));
            $videoName = 'depoimento-' . $slug . '-' . time() . '.mp4';
            $videoPath = '../assets/videos/' . $videoName;
            
            if (!move_uploaded_file($video['tmp_name'], $videoPath)) {
                $erro = 'Erro ao fazer upload do vídeo.';
            } else {
                $videoDbPath = 'assets/videos/' . $videoName;
            }
        }
        
        // Inserir no banco se não houver erros
        if (empty($erro) && $videoDbPath) {
            try {
                $pdo = getConnection();
                $stmt = $pdo->prepare("INSERT INTO depoimentos (nome, video, tipo_depoimento, ordem, ativo) VALUES (:nome, :video, :tipo_depoimento, :ordem, :ativo)");
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':video', $videoDbPath);
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
    <link rel="icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="../assets/logo.png">
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
                            
                        </div>
                        
                        <!-- Seção: Vídeo do Depoimento -->
                        <div class="form-section">
                            <h3 class="section-title">Vídeo do Depoimento</h3>
                            <p class="section-description">Faça upload do vídeo em formato MP4</p>
                            
                            <div class="form-group">
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
        
        form.addEventListener('submit', function(e) {
            const errors = [];
            
            const nome = document.getElementById('nome').value.trim();
            if (!nome) {
                errors.push('O nome é obrigatório');
            } else if (nome.length < 3) {
                errors.push('O nome deve ter pelo menos 3 caracteres');
            }
            
            const video = document.getElementById('video').files[0];
            if (!video) {
                errors.push('O vídeo é obrigatório');
            } else if (video.type !== 'video/mp4') {
                errors.push('Apenas arquivos MP4 são permitidos');
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
    </script>
</body>
</html>
