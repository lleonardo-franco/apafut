<?php
require_once 'auth.php';
require_once '../src/Cache.php';
Auth::require();

$user = Auth::user();
$error = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = Security::sanitizeString($_POST['nome'] ?? '');
        $cargo = Security::sanitizeString($_POST['cargo'] ?? '');
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        $ordem = Security::validateInt($_POST['ordem'] ?? 0, 0);
        
        if (empty($nome)) throw new Exception('Nome é obrigatório');
        if (empty($cargo)) throw new Exception('Cargo é obrigatório');
        
        // Upload de foto
        $fotoPath = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/diretoria/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($_FILES['foto']['type'], $allowedTypes)) {
                throw new Exception('Formato inválido. Use JPG, PNG, GIF ou WEBP');
            }
            
            if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                throw new Exception('Imagem muito grande. Máximo 5MB');
            }
            
            $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $fileName = 'diretoria-' . preg_replace('/[^a-z0-9]/i', '-', strtolower($nome)) . '-' . time() . '.' . $extension;
            $fotoPath = 'assets/diretoria/' . $fileName;
            
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fileName)) {
                throw new Exception('Erro ao fazer upload da foto');
            }
        }
        
        $conn = getConnection();
        $stmt = $conn->prepare("INSERT INTO diretoria (nome, cargo, foto, ativo, ordem) VALUES (:nome, :cargo, :foto, :ativo, :ordem)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':cargo', $cargo);
        $stmt->bindParam(':foto', $fotoPath);
        $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
        $stmt->bindParam(':ordem', $ordem, PDO::PARAM_INT);
        $stmt->execute();
        
        header('Location: diretoria.php?success=created');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Membro da Diretoria - Painel Admin</title>
    <link rel="icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="../assets/logo.png">
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
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
                <?php endif; ?>

                <div class="page-header-balanced">
                    <div class="header-left">
                        <div class="icon-wrapper">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div class="header-text">
                            <h1>Novo Membro da Diretoria</h1>
                            <p>Cadastre um novo membro da diretoria</p>
                        </div>
                    </div>
                    <a href="diretoria.php" class="btn-balanced-light">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <div class="form-card">
                    <form method="POST" enctype="multipart/form-data" class="form-balanced" id="diretoriaForm">
                        <!-- Seção: Informações Básicas -->
                        <div class="form-section">
                            <h3 class="section-title">Informações Básicas</h3>
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label for="nome">Nome Completo *</label>
                                    <input type="text" id="nome" name="nome" placeholder="Digite o nome completo" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label for="cargo">Cargo/Função *</label>
                                    <input type="text" id="cargo" name="cargo" placeholder="Ex: Presidente" value="<?= htmlspecialchars($_POST['cargo'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Seção: Foto do Membro -->
                        <div class="form-section">
                            <h3 class="section-title">Foto do Membro</h3>
                            <p class="section-description">Envie uma foto do membro da diretoria</p>
                            
                            <div class="image-upload-box">
                                <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/gif,image/webp" onchange="previewImage(this, 'fotoPreview', 'fotoPreviewContainer')">
                                <label for="foto" class="upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Clique para selecionar a foto</span>
                                    <small>JPG, PNG, GIF ou WEBP (máx. 5MB)</small>
                                </label>
                            </div>
                            
                            <div id="fotoPreviewContainer" class="image-preview-container" style="display: none;">
                                <img id="fotoPreview" src="" alt="Preview">
                                <button type="button" class="remove-preview" onclick="removePreview('foto', 'fotoPreviewContainer')">
                                    <i class="fas fa-times"></i>
                                </button>
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
                                        <input type="checkbox" id="ativo" name="ativo" <?= isset($_POST['ativo']) || !isset($_POST['nome']) ? 'checked' : '' ?>>
                                        <div class="checkbox-label-text">
                                            <strong>Membro Ativo</strong>
                                            <small>Apenas membros ativos são exibidos no site</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-balanced">
                                <i class="fas fa-save"></i> Salvar Membro
                            </button>
                            <a href="diretoria.php" class="btn-balanced-light">
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
        const form = document.getElementById('diretoriaForm');
        const errorModal = document.getElementById('errorModal');
        const errorList = document.getElementById('errorList');

        function previewImage(input, previewId, containerId) {
            const previewContainer = document.getElementById(containerId);
            const previewImg = document.getElementById(previewId);
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                if (file.size > 5 * 1024 * 1024) {
                    showError(['A foto não pode ter mais de 5MB']);
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
            
            const nome = document.getElementById('nome').value.trim();
            if (!nome) {
                errors.push('O nome é obrigatório');
            } else if (nome.length < 3) {
                errors.push('O nome deve ter pelo menos 3 caracteres');
            }
            
            const cargo = document.getElementById('cargo').value.trim();
            if (!cargo) {
                errors.push('O cargo é obrigatório');
            } else if (cargo.length < 3) {
                errors.push('O cargo deve ter pelo menos 3 caracteres');
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
