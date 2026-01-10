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
        } else {
            throw new Exception('Imagem mobile é obrigatória');
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
    <link rel="icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="../assets/logo.png">
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
                <div class="page-header-balanced">
                    <div class="header-left">
                        <div class="icon-wrapper">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div class="header-text">
                            <h1>Novo Banner</h1>
                            <p>Adicione um novo banner ao carrossel da página inicial</p>
                        </div>
                    </div>
                    <a href="banners.php" class="btn-balanced-light">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <form method="POST" enctype="multipart/form-data" class="form-balanced" id="bannerForm">
                    <!-- Informações Básicas -->
                    <div class="form-section">
                        <h3><i class="fas fa-info-circle"></i> Informações Básicas</h3>
                        
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="titulo">Título do Banner</label>
                                <input type="text" id="titulo" name="titulo" maxlength="100" 
                                       placeholder="Ex: Banner Principal Temporada 2026">
                            </div>

                            <div class="form-group">
                                <label for="ordem">Ordem de Exibição</label>
                                <input type="number" id="ordem" name="ordem" value="1" min="1" max="99">
                                <small>Define a sequência no carrossel (1 = primeiro)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Imagem Desktop -->
                    <div class="form-section">
                        <h3><i class="fas fa-desktop"></i> Imagem Desktop</h3>
                        
                        <div class="form-group">
                            <label>Selecionar Imagem</label>
                            <div class="image-upload-box">
                                <input type="file" id="imagem" name="imagem" accept="image/*" 
                                       onchange="previewImage(this, 'preview', 'imagePreview')">
                                <label for="imagem" class="upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Clique para selecionar ou arraste a imagem</span>
                                    <small>Recomendado: 1400x600px (21:9) · Alta resolução: 1920x823px · Máximo 10MB</small>
                                </label>
                            </div>
                            <div id="imagePreview" class="image-preview-container" style="display: none;">
                                <img id="preview" alt="Preview Desktop" />
                                <button type="button" onclick="removePreview('imagem', 'imagePreview')" class="btn-remove-preview">
                                    <i class="fas fa-times"></i> Remover
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Imagem Mobile -->
                    <div class="form-section">
                        <h3><i class="fas fa-mobile-alt"></i> Imagem Mobile</h3>
                        
                        <div class="form-group">
                            <label>Selecionar Imagem</label>
                            <div class="image-upload-box">
                                <input type="file" id="imagem_mobile" name="imagem_mobile" accept="image/*" 
                                       onchange="previewImage(this, 'previewMobile', 'imagePreviewMobile')">
                                <label for="imagem_mobile" class="upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Clique para selecionar ou arraste a imagem</span>
                                    <small>Recomendado: 1400x1200px (7:6) · Máximo 10MB</small>
                                </label>
                            </div>
                            <div id="imagePreviewMobile" class="image-preview-container" style="display: none;">
                                <img id="previewMobile" alt="Preview Mobile" />
                                <button type="button" onclick="removePreview('imagem_mobile', 'imagePreviewMobile')" class="btn-remove-preview">
                                    <i class="fas fa-times"></i> Remover
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Configurações -->
                    <div class="form-section">
                        <h3><i class="fas fa-cog"></i> Configurações</h3>
                        
                        <div class="checkbox-wrapper">
                            <div class="checkbox-item">
                                <label>
                                    <input type="checkbox" name="ativo" checked>
                                    <span class="checkbox-label-text">
                                        <strong>Banner Ativo</strong>
                                        <small>Apenas banners ativos são exibidos no carrossel do site</small>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="form-actions">
                        <button type="submit" class="btn-balanced">
                            <i class="fas fa-save"></i> Cadastrar Banner
                        </button>
                        <a href="banners.php" class="btn-balanced-cancel">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
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
        // Variáveis globais
        const form = document.getElementById('bannerForm');
        const errorModal = document.getElementById('errorModal');
        const errorList = document.getElementById('errorList');

        // Preview de imagem
        function previewImage(input, previewId, containerId) {
            const previewContainer = document.getElementById(containerId);
            const previewImg = document.getElementById(previewId);
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validar tamanho
                if (file.size > 10 * 1024 * 1024) {
                    showError(['A imagem não pode ter mais de 10MB']);
                    input.value = '';
                    return;
                }
                
                // Validar tipo
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

        // Remover preview
        function removePreview(inputId, containerId) {
            const input = document.getElementById(inputId);
            const container = document.getElementById(containerId);
            
            input.value = '';
            container.style.display = 'none';
        }

        // Validação do formulário
        form.addEventListener('submit', function(e) {
            const errors = [];
            
            // Validar título
            const titulo = document.getElementById('titulo').value.trim();
            if (!titulo) {
                errors.push('O título é obrigatório');
            } else if (titulo.length < 3) {
                errors.push('O título deve ter pelo menos 3 caracteres');
            }
            
            // Validar ordem
            const ordem = parseInt(document.getElementById('ordem').value);
            if (!ordem || ordem < 1) {
                errors.push('A ordem deve ser um número maior que zero');
            }
            
            // Validar imagem desktop
            const imagem = document.getElementById('imagem');
            if (!imagem.files || imagem.files.length === 0) {
                errors.push('A imagem desktop é obrigatória');
            }
            
            // Validar imagem mobile
            const imagemMobile = document.getElementById('imagem_mobile');
            if (!imagemMobile.files || imagemMobile.files.length === 0) {
                errors.push('A imagem mobile é obrigatória');
            }
            
            // Se houver erros, exibir modal
            if (errors.length > 0) {
                e.preventDefault();
                showError(errors);
                return false;
            }
        });

        // Exibir modal de erro
        function showError(errors) {
            errorList.innerHTML = '';
            errors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                errorList.appendChild(li);
            });
            errorModal.classList.add('show');
        }

        // Fechar modal de erro
        function closeErrorModal() {
            errorModal.classList.remove('show');
        }

        // Fechar modal ao clicar fora
        errorModal.addEventListener('click', function(e) {
            if (e.target === errorModal) {
                closeErrorModal();
            }
        });
    </script>
</body>
</html>
