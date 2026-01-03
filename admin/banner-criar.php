<?php
require_once 'auth.php';
require_once '../src/Cache.php';
Auth::require();

$user = Auth::user();
$error = '';

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
        
        // Upload de imagem desktop
        $imagemPath = '';
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = $_FILES['imagem']['type'];
            
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('Formato de imagem inválido. Use JPG, PNG, GIF ou WEBP');
            }
            
            if ($_FILES['imagem']['size'] > 10 * 1024 * 1024) {
                throw new Exception('Imagem muito grande. Máximo 10MB');
            }
            
            $extension = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            $fileName = 'banner-desktop-' . preg_replace('/[^a-z0-9]/i', '-', strtolower($titulo)) . '-' . time() . '.' . $extension;
            $imagemPath = 'assets/images/' . $fileName;
            
            if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $uploadDir . $fileName)) {
                throw new Exception('Erro ao fazer upload da imagem desktop');
            }
        } else {
            throw new Exception('Imagem desktop é obrigatória');
        }
        
        // Upload de imagem mobile
        $imagemMobilePath = '';
        if (isset($_FILES['imagem_mobile']) && $_FILES['imagem_mobile']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = $_FILES['imagem_mobile']['type'];
            
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('Formato de imagem mobile inválido. Use JPG, PNG, GIF ou WEBP');
            }
            
            if ($_FILES['imagem_mobile']['size'] > 10 * 1024 * 1024) {
                throw new Exception('Imagem mobile muito grande. Máximo 10MB');
            }
            
            $extension = pathinfo($_FILES['imagem_mobile']['name'], PATHINFO_EXTENSION);
            $fileName = 'banner-mobile-' . preg_replace('/[^a-z0-9]/i', '-', strtolower($titulo)) . '-' . time() . '.' . $extension;
            $imagemMobilePath = 'assets/images/' . $fileName;
            
            if (!move_uploaded_file($_FILES['imagem_mobile']['tmp_name'], $uploadDir . $fileName)) {
                throw new Exception('Erro ao fazer upload da imagem mobile');
            }
        }
        
        // Inserir no banco
        $conn = getConnection();
        $stmt = $conn->prepare("INSERT INTO banners (titulo, imagem, imagem_mobile, ordem, ativo) VALUES (:titulo, :imagem, :imagem_mobile, :ordem, :ativo)");
        
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':imagem', $imagemPath);
        $stmt->bindParam(':imagem_mobile', $imagemMobilePath);
        $stmt->bindParam(':ordem', $ordem, PDO::PARAM_INT);
        $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
        
        $stmt->execute();
        
        // Limpar cache
        Cache::delete('banners_ativos');
        
        logError('Banner criado', [
            'titulo' => $titulo,
            'user' => $user['email']
        ]);
        
        header('Location: banners.php?success=created');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        logError('Erro ao criar banner', [
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
    <title>Novo Banner - Painel Administrativo</title>
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
                        <h1><i class="fas fa-plus-circle"></i> Novo Banner</h1>
                        <p>Adicione um novo banner ao carrossel</p>
                    </div>
                    <a href="banners.php" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <div class="form-card">
                    <form method="POST" enctype="multipart/form-data" class="form-grid">
                        <div class="form-group col-span-2">
                            <label for="titulo"><i class="fas fa-heading"></i> Título *</label>
                            <input type="text" id="titulo" name="titulo" required maxlength="100" placeholder="Ex: Banner Principal">
                            <small>Nome para identificação interna do banner</small>
                        </div>

                        <div class="form-group col-span-2">
                            <label for="imagem"><i class="fas fa-desktop"></i> Imagem Desktop (1400x600px) *</label>
                            <input type="file" id="imagem" name="imagem" accept="image/*" required onchange="previewImage(this, 'preview')">
                            <small>Recomendado: 1400x600px (proporção 21:9). Para alta resolução: 1920x823px. Máximo 10MB</small>
                            <div id="imagePreview" style="margin-top: 15px; display: none;">
                                <img id="preview" style="max-width: 100%; max-height: 300px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);" />
                            </div>
                        </div>

                        <div class="form-group col-span-2">
                            <label for="imagem_mobile"><i class="fas fa-mobile-alt"></i> Imagem Mobile (800x1200px)</label>
                            <input type="file" id="imagem_mobile" name="imagem_mobile" accept="image/*" onchange="previewImage(this, 'previewMobile')">
                            <small>Opcional. Recomendado: 800x1200px (proporção 2:3 vertical). Se não enviar, usa a imagem desktop</small>
                            <div id="imagePreviewMobile" style="margin-top: 15px; display: none;">
                                <img id="previewMobile" style="max-width: 100%; max-height: 300px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="ordem"><i class="fas fa-sort-numeric-down"></i> Ordem de Exibição *</label>
                            <input type="number" id="ordem" name="ordem" value="1" min="1" max="99" required>
                            <small>Define a sequência no carrossel (1 = primeiro)</small>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="ativo" checked>
                                <span><i class="fas fa-eye"></i> Banner Ativo</span>
                            </label>
                            <small>Apenas banners ativos são exibidos no site</small>
                        </div>

                        <div class="form-actions col-span-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cadastrar Banner
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
        function previewImage(input, previewId) {
            const previewContainer = previewId === 'preview' ? document.getElementById('imagePreview') : document.getElementById('imagePreviewMobile');
            const previewImg = document.getElementById(previewId);
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                
                reader.readAsDataURL(input.files[0]);
            } else {
                previewContainer.style.display = 'none';
            }
        }
    </script>
</body>
</html>
