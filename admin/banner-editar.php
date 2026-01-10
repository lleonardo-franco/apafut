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
    <link rel="icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="shortcut icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="apple-touch-icon" href="/apafut/assets/logo.png">
    <link rel="icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/logo.ico" type="image/x-icon">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/15d6bd6a1c.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/noticias.css">
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

                <div class="page-header-balanced">
                    <div class="header-left">
                        <div class="icon-wrapper">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="header-text">
                            <h1>Editar Banner</h1>
                            <p>Atualize as informações do banner</p>
                        </div>
                    </div>
                    <a href="banners.php" class="btn-balanced-light">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <?php if (!empty($banner['imagem'])): ?>
                    <div class="form-card" style="margin-bottom: 24px;">
                        <h3 style="margin-bottom: 16px;">Imagem Atual</h3>
                        <div class="foto-preview" style="max-width: 600px;">
                            <img src="../<?= htmlspecialchars($banner['imagem']) ?>" alt="Banner atual" onerror="this.src='../assets/hero.png'" style="width: 100%; height: auto; border-radius: 8px;">
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-card">
                    <form method="POST" enctype="multipart/form-data" class="form-balanced" id="bannerForm">
                        <!-- Seção: Informações Básicas -->
                        <div class="form-section">
                            <h3 class="section-title">Informações Básicas</h3>
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label for="titulo">Título *</label>
                                    <input type="text" id="titulo" name="titulo" placeholder="Digite o título do banner" value="<?= htmlspecialchars($banner['titulo']) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="ordem">Ordem de Exibição</label>
                                    <input type="number" id="ordem" name="ordem" min="1" max="99" value="<?= $banner['ordem'] ?>" placeholder="1">
                                </div>
                            </div>
                        </div>

                        <!-- Seção: Atualizar Imagem Desktop -->
                        <div class="form-section">
                            <h3 class="section-title">Atualizar Imagem Desktop</h3>
                            <p class="section-description">Envie uma nova imagem ou mantenha a atual</p>
                            
                            <div class="image-upload-box">
                                <input type="file" id="imagem" name="imagem" accept="image/jpeg,image/png,image/gif,image/webp" onchange="previewImage(this, 'imagemPreview', 'imagemPreviewContainer')">
                                <label for="imagem" class="upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Clique para selecionar uma nova imagem</span>
                                    <small>JPG, PNG, GIF ou WEBP • Recomendado: 1400x600px (máx. 10MB)</small>
                                </label>
                            </div>
                            
                            <div id="imagemPreviewContainer" class="image-preview-container" style="display: none;">
                                <img id="imagemPreview" src="" alt="Preview">
                                <button type="button" class="remove-preview" onclick="removePreview('imagem', 'imagemPreviewContainer')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Seção: Configurações -->
                        <div class="form-section">
                            <h3 class="section-title">Configurações</h3>
                            <div class="checkbox-wrapper">
                                <div class="checkbox-item">
                                    <label for="ativo">
                                        <input type="checkbox" id="ativo" name="ativo" <?= $banner['ativo'] ? 'checked' : '' ?>>
                                        <div class="checkbox-label-text">
                                            <strong>Banner Ativo</strong>
                                            <small>Apenas banners ativos são exibidos no carrossel do site</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-balanced">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                            <a href="banners.php" class="btn-balanced-light">
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
        const form = document.getElementById('bannerForm');
        const errorModal = document.getElementById('errorModal');
        const errorList = document.getElementById('errorList');

        function previewImage(input, previewId, containerId) {
            const previewContainer = document.getElementById(containerId);
            const previewImg = document.getElementById(previewId);
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                if (file.size > 10 * 1024 * 1024) {
                    showError(['A imagem não pode ter mais de 10MB']);
                    input.value = '';
                    return;
                }
                
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    showError(['Formato inválido. Use JPG, PNG, GIF ou WEBP']);
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        function removePreview(inputId, containerId) {
            const input = document.getElementById(inputId);
            const container = document.getElementById(containerId);
            input.value = '';
            container.style.display = 'none';
        }

        form.addEventListener('submit', function(e) {
            const errors = [];
            
            const titulo = document.getElementById('titulo').value.trim();
            if (!titulo) {
                errors.push('O título é obrigatório');
            } else if (titulo.length < 3) {
                errors.push('O título deve ter pelo menos 3 caracteres');
            }
            
            const ordem = parseInt(document.getElementById('ordem').value);
            if (!ordem || ordem < 1 || ordem > 99) {
                errors.push('A ordem deve estar entre 1 e 99');
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
    </script>
</body>
</html>
