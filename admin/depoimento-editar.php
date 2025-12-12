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
    $descricao = trim($_POST['descricao'] ?? '');
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
                    // Deletar vídeo antigo
                    if (file_exists('../' . $depoimento['video'])) {
                        unlink('../' . $depoimento['video']);
                    }
                    $videoPath = '../assets/videos/' . $videoName;
                } else {
                    $erro = 'Erro ao fazer upload do novo vídeo.';
                }
            }
        }
        
        if (!$erro) {
            try {
                $pdo = getConnection();
                $stmt = $pdo->prepare("UPDATE depoimentos SET nome = :nome, descricao = :descricao, video = :video, ordem = :ordem, ativo = :ativo WHERE id = :id");
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':descricao', $descricao);
                $stmt->bindParam(':video', $videoPath);
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
    <link rel="stylesheet" href="assets/css/depoimentos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/topbar.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1><i class="fas fa-edit"></i> Editar Depoimento</h1>
                    <p>Altere as informações do depoimento</p>
                </div>
            </div>
            
            <!-- Preview do Vídeo Atual -->
            <?php if (!empty($depoimento['video'])): ?>
                <div class="video-preview-card">
                    <h3>Vídeo Atual</h3>
                    <div class="video-preview">
                        <video controls style="width: 100%; max-width: 560px; height: auto; border-radius: 8px;">
                            <source src="<?= htmlspecialchars($depoimento['video']) ?>" type="video/mp4">
                            Seu navegador não suporta a tag de vídeo.
                        </video>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Form -->
            <div class="card">
                <?php if ($erro): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" class="form">
                    <div class="form-group">
                        <label for="nome">Nome <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="nome" 
                            name="nome" 
                            required 
                            maxlength="100"
                            placeholder="Ex: João Silva"
                            value="<?= htmlspecialchars($_POST['nome'] ?? $depoimento['nome'] ?? '') ?>"
                        >
                        <small>Nome completo da pessoa que está dando o depoimento</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea 
                            id="descricao" 
                            name="descricao" 
                            rows="3"
                            placeholder="Ex: Pai do atleta Pedro Silva"
                        ><?= htmlspecialchars($_POST['descricao'] ?? $depoimento['descricao'] ?? '') ?></textarea>
                        <small>Breve descrição sobre quem é a pessoa (opcional)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="video">Novo Vídeo (MP4)</label>
                        <input 
                            type="file" 
                            id="video" 
                            name="video" 
                            accept="video/mp4"
                        >
                        <small>Deixe em branco para manter o vídeo atual. Máximo: <?= ini_get('upload_max_filesize') ?></small>
                        <div id="videoPreview" style="display: none; margin-top: 12px;">
                            <video id="previewVideo" controls style="max-width: 100%; height: auto; border-radius: 8px;"></video>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ordem">Ordem de Exibição</label>
                            <input 
                                type="number" 
                                id="ordem" 
                                name="ordem" 
                                min="0"
                                value="<?= htmlspecialchars($_POST['ordem'] ?? $depoimento['ordem'] ?? '0') ?>"
                            >
                            <small>Defina a ordem em que o depoimento será exibido (0 = primeiro)</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input 
                                    type="checkbox" 
                                    name="ativo" 
                                    <?= isset($_POST['ativo']) || (!$_POST && $depoimento['ativo']) ? 'checked' : '' ?>
                                >
                                <span>Ativo</span>
                            </label>
                            <small>Apenas depoimentos ativos são exibidos no site</small>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="depoimentos.php" class="btn btn-light">
                            <i class="fas fa-arrow-left"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i>
                            Salvar Alterações
                        </button>
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
        // Preview do novo vídeo
        document.getElementById('video').addEventListener('change', function(e) {
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
