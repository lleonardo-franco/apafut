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
    $descricao = trim($_POST['descricao'] ?? '');
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
            
            if (move_uploaded_file($video['tmp_name'], $videoPath)) {
                try {
                    $pdo = getConnection();
                    $videoDbPath = '../assets/videos/' . $videoName;
                    $stmt = $pdo->prepare("INSERT INTO depoimentos (nome, descricao, video, ordem, ativo) VALUES (:nome, :descricao, :video, :ordem, :ativo)");
                    $stmt->bindParam(':nome', $nome);
                    $stmt->bindParam(':descricao', $descricao);
                    $stmt->bindParam(':video', $videoDbPath);
                    $stmt->bindParam(':ordem', $ordem, PDO::PARAM_INT);
                    $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
                    
                    if ($stmt->execute()) {
                        header('Location: depoimentos.php?msg=created');
                        exit;
                    } else {
                        unlink($videoPath);
                        $erro = 'Erro ao criar depoimento.';
                    }
                } catch (PDOException $e) {
                    unlink($videoPath);
                    $erro = 'Erro ao criar depoimento: ' . $e->getMessage();
                }
            } else {
                $erro = 'Erro ao fazer upload do vídeo.';
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
                    <h1><i class="fas fa-video"></i> Novo Depoimento</h1>
                    <p>Adicione um novo depoimento de atleta ou responsável</p>
                </div>
            </div>
            
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
                            value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
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
                        ><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>
                        <small>Breve descrição sobre quem é a pessoa (opcional)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="video">Vídeo (MP4) <span class="required">*</span></label>
                        <input 
                            type="file" 
                            id="video" 
                            name="video" 
                            accept="video/mp4"
                            required
                        >
                        <small>Faça upload de um arquivo de vídeo MP4 (máx: <?= ini_get('upload_max_filesize') ?>)</small>
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
                                value="<?= htmlspecialchars($_POST['ordem'] ?? '0') ?>"
                            >
                            <small>Defina a ordem em que o depoimento será exibido (0 = primeiro)</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input 
                                    type="checkbox" 
                                    name="ativo" 
                                    <?= isset($_POST['ativo']) ? 'checked' : '' ?>
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
                            Criar Depoimento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <style>
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
        }
    </style>
    
    <script>
        // Preview do vídeo
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
