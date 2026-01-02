<?php
require_once 'auth.php';
require_once '../src/Cache.php';
Auth::require();

$user = Auth::user();
$error = '';
$bannerId = Security::validateInt($_GET['id'] ?? 0, 1);

if (!$bannerId) {
    header('Location: banners.php');
    exit;
}

// Buscar banner
try {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM banners WHERE id = :id");
    $stmt->bindParam(':id', $bannerId, PDO::PARAM_INT);
    $stmt->execute();
    $banner = $stmt->fetch();
    
    if (!$banner) {
        header('Location: banners.php');
        exit;
    }
} catch (Exception $e) {
    logError('Erro ao buscar banner', ['id' => $bannerId, 'error' => $e->getMessage()]);
    header('Location: banners.php');
    exit;
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $titulo = Security::sanitizeString($_POST['titulo'] ?? '');
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        $ordem = Security::validateInt($_POST['ordem'] ?? 1, 1);
        
        // Validações
        if (empty($titulo)) {
            throw new Exception('Título é obrigatório');
        }
        
        // Upload de nova imagem (opcional)
        $imagemPath = $banner['imagem'];
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/images/';
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = $_FILES['imagem']['type'];
            
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('Formato de imagem inválido. Use JPG, PNG, GIF ou WEBP');
            }
            
            if ($_FILES['imagem']['size'] > 10 * 1024 * 1024) {
                throw new Exception('Imagem muito grande. Máximo 10MB');
            }
            
            $extension = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            $fileName = 'banner-' . preg_replace('/[^a-z0-9]/i', '-', strtolower($titulo)) . '-' . time() . '.' . $extension;
            $imagemPath = 'assets/images/' . $fileName;
            
            if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $uploadDir . $fileName)) {
                throw new Exception('Erro ao fazer upload da imagem');
            }
            
            // Remover imagem antiga se não for padrão
            if ($banner['imagem'] && file_exists('../' . $banner['imagem']) && !str_contains($banner['imagem'], 'banner1.jpg') && !str_contains($banner['imagem'], 'banner2.jpg') && !str_contains($banner['imagem'], 'banner3.jpg')) {
                @unlink('../' . $banner['imagem']);
            }
        }
        
        // Atualizar no banco
        $conn = getConnection();
        $stmt = $conn->prepare("UPDATE banners SET titulo = :titulo, imagem = :imagem, ordem = :ordem, ativo = :ativo WHERE id = :id");
        
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':imagem', $imagemPath);
        $stmt->bindParam(':ordem', $ordem, PDO::PARAM_INT);
        $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
        $stmt->bindParam(':id', $bannerId, PDO::PARAM_INT);
        
        $stmt->execute();
        
        // Limpar cache
        Cache::delete('banners_ativos');
        
        logError('Banner atualizado', [
            'id' => $bannerId,
            'titulo' => $titulo,
            'user' => $user['email']
        ]);
        
        header('Location: banners.php?success=updated');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        logError('Erro ao atualizar banner', [
            'id' => $bannerId,
            'error' => $error,
            'user' => $user['email']
        ]);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Banner - Painel Administrativo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/15d6bd6a1c.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/jogadores.css">
    <link rel="stylesheet" href="assets/css/alerts.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <?php include 'includes/topbar.php'; ?>

            <div class="content">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <div class="page-header">
                    <div>
                        <h1><i class="fas fa-edit"></i> Editar Banner</h1>
                        <p>Atualize as informações do banner</p>
                    </div>
                    <a href="banners.php" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <div class="form-card">
                    <form method="POST" enctype="multipart/form-data" class="form-grid">
                        <div class="form-group col-span-2">
                            <label for="titulo"><i class="fas fa-heading"></i> Título *</label>
                            <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($banner['titulo']) ?>" required maxlength="100">
                            <small>Nome para identificação interna do banner</small>
                        </div>

                        <div class="form-group col-span-2">
                            <label><i class="fas fa-image"></i> Imagem Atual</label>
                            <div style="margin-bottom: 15px;">
                                <img src="../<?= htmlspecialchars($banner['imagem']) ?>" alt="Banner atual" 
                                     style="max-width: 100%; max-height: 200px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);"
                                     onerror="this.src='../assets/hero.png'">
                            </div>
                            
                            <label for="imagem"><i class="fas fa-upload"></i> Alterar Imagem</label>
                            <input type="file" id="imagem" name="imagem" accept="image/*" onchange="previewImage(this)">
                            <small>Deixe em branco para manter a imagem atual. Recomendado: 1920x600px. Máximo 10MB</small>
                            <div id="imagePreview" style="margin-top: 15px; display: none;">
                                <img id="preview" style="max-width: 100%; max-height: 300px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="ordem"><i class="fas fa-sort-numeric-down"></i> Ordem de Exibição *</label>
                            <input type="number" id="ordem" name="ordem" value="<?= $banner['ordem'] ?>" min="1" max="99" required>
                            <small>Define a sequência no carrossel (1 = primeiro)</small>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="ativo" <?= $banner['ativo'] ? 'checked' : '' ?>>
                                <span><i class="fas fa-eye"></i> Banner Ativo</span>
                            </label>
                            <small>Apenas banners ativos são exibidos no site</small>
                        </div>

                        <div class="form-actions col-span-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                            <a href="banners.php" class="btn btn-light">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('preview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                };
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>
