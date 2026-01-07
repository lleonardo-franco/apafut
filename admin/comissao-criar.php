<?php
require_once 'auth.php';
require_once '../src/Cache.php';
Auth::require();

$user = Auth::user();
$error = '';
$success = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = Security::sanitizeString($_POST['nome'] ?? '');
        $cargo = Security::sanitizeString($_POST['cargo'] ?? '');
        $descricao = Security::sanitizeString($_POST['descricao'] ?? '');
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        $ordem = Security::validateInt($_POST['ordem'] ?? 0, 0);
        
        // Validações
        if (empty($nome)) {
            throw new Exception('Nome é obrigatório');
        }
        if (empty($cargo)) {
            throw new Exception('Cargo é obrigatório');
        }
        
        // Upload de foto
        $fotoPath = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/images/comissao/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = $_FILES['foto']['type'];
            
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('Formato de imagem inválido. Use JPG, PNG, GIF ou WEBP');
            }
            
            if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                throw new Exception('Imagem muito grande. Máximo 5MB');
            }
            
            $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $fileName = 'comissao-' . preg_replace('/[^a-z0-9]/i', '-', strtolower($nome)) . '-' . time() . '.' . $extension;
            $fotoPath = '../assets/images/comissao/' . $fileName;
            
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fileName)) {
                throw new Exception('Erro ao fazer upload da foto');
            }
        }
        
        // Inserir no banco
        $conn = getConnection();
        $stmt = $conn->prepare("
            INSERT INTO comissao_tecnica (nome, cargo, foto, descricao, ativo, ordem)
            VALUES (:nome, :cargo, :foto, :descricao, :ativo, :ordem)
        ");
        
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':cargo', $cargo);
        $stmt->bindParam(':foto', $fotoPath);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
        $stmt->bindParam(':ordem', $ordem, PDO::PARAM_INT);
        
        $stmt->execute();
        
        // Limpar cache de comissão
        Cache::delete('comissao_tecnica_ativos');
        
        logError('Membro da comissão criado', [
            'nome' => $nome,
            'cargo' => $cargo,
            'user' => $user['email']
        ]);
        
        header('Location: comissao.php?success=created');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        logError('Erro ao criar membro da comissão', [
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
    <title>Novo Membro da Comissão - Painel Administrativo</title>
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
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="header-text">
                            <h1>Novo Membro da Comissão</h1>
                            <p>Adicione um novo membro à comissão técnica</p>
                        </div>
                    </div>
                    <a href="comissao.php" class="btn-balanced-light">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <form method="POST" enctype="multipart/form-data" class="form-balanced" id="comissaoForm">
                    <!-- Informações Básicas -->
                    <div class="form-section">
                        <h3><i class="fas fa-id-card"></i> Informações Básicas</h3>
                        
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="nome">Nome</label>
                                <input type="text" id="nome" name="nome" placeholder="Ex: Carlos Silva" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="cargo">Cargo</label>
                                <select id="cargo" name="cargo">
                                    <option value="">Selecione...</option>
                                    <option value="Técnico Principal" <?= ($_POST['cargo'] ?? '') === 'Técnico Principal' ? 'selected' : '' ?>>Técnico Principal</option>
                                    <option value="Auxiliar Técnico" <?= ($_POST['cargo'] ?? '') === 'Auxiliar Técnico' ? 'selected' : '' ?>>Auxiliar Técnico</option>
                                    <option value="Preparador Físico" <?= ($_POST['cargo'] ?? '') === 'Preparador Físico' ? 'selected' : '' ?>>Preparador Físico</option>
                                    <option value="Preparador de Goleiros" <?= ($_POST['cargo'] ?? '') === 'Preparador de Goleiros' ? 'selected' : '' ?>>Preparador de Goleiros</option>
                                    <option value="Fisioterapeuta" <?= ($_POST['cargo'] ?? '') === 'Fisioterapeuta' ? 'selected' : '' ?>>Fisioterapeuta</option>
                                    <option value="Médico" <?= ($_POST['cargo'] ?? '') === 'Médico' ? 'selected' : '' ?>>Médico</option>
                                    <option value="Nutricionista" <?= ($_POST['cargo'] ?? '') === 'Nutricionista' ? 'selected' : '' ?>>Nutricionista</option>
                                    <option value="Analista de Desempenho" <?= ($_POST['cargo'] ?? '') === 'Analista de Desempenho' ? 'selected' : '' ?>>Analista de Desempenho</option>
                                    <option value="Outro" <?= ($_POST['cargo'] ?? '') === 'Outro' ? 'selected' : '' ?>>Outro</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="descricao">Descrição</label>
                            <textarea id="descricao" name="descricao" rows="4" placeholder="Breve descrição sobre o profissional..."><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Foto -->
                    <div class="form-section">
                        <h3><i class="fas fa-camera"></i> Foto do Membro</h3>
                        
                        <div class="form-group">
                            <label>Selecionar Foto</label>
                            <div class="image-upload-box">
                                <input type="file" id="foto" name="foto" accept="image/*" onchange="previewImage(this, 'fotoPreview', 'fotoPreviewContainer')">
                                <label for="foto" class="upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Clique para selecionar ou arraste a foto</span>
                                    <small>Formatos aceitos: JPG, PNG, GIF, WEBP · Máximo 5MB</small>
                                </label>
                            </div>
                            <div id="fotoPreviewContainer" class="image-preview-container" style="display: none;">
                                <img id="fotoPreview" alt="Preview" />
                                <button type="button" onclick="removePreview('foto', 'fotoPreviewContainer')" class="btn-remove-preview">
                                    <i class="fas fa-times"></i> Remover
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Configurações -->
                    <div class="form-section">
                        <h3><i class="fas fa-cog"></i> Configurações</h3>
                        
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="ordem">Ordem de Exibição</label>
                                <input type="number" id="ordem" name="ordem" min="0" value="<?= htmlspecialchars($_POST['ordem'] ?? '0') ?>">
                                <small>Membros com menor ordem aparecem primeiro</small>
                            </div>
                        </div>
                        
                        <div class="checkbox-wrapper">
                            <div class="checkbox-item">
                                <label>
                                    <input type="checkbox" name="ativo" checked>
                                    <span class="checkbox-label-text">
                                        <strong>Membro Ativo</strong>
                                        <small>Apenas membros ativos são exibidos no site</small>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="form-actions">
                        <button type="submit" class="btn-balanced">
                            <i class="fas fa-save"></i> Cadastrar Membro
                        </button>
                        <a href="comissao.php" class="btn-balanced-cancel">
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
        const form = document.getElementById('comissaoForm');
        const errorModal = document.getElementById('errorModal');
        const errorList = document.getElementById('errorList');

        // Preview de imagem
        function previewImage(input, previewId, containerId) {
            const previewContainer = document.getElementById(containerId);
            const previewImg = document.getElementById(previewId);
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validar tamanho
                if (file.size > 5 * 1024 * 1024) {
                    showError(['A foto não pode ter mais de 5MB']);
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
            
            // Validar nome
            const nome = document.getElementById('nome').value.trim();
            if (!nome) {
                errors.push('O nome é obrigatório');
            } else if (nome.length < 3) {
                errors.push('O nome deve ter pelo menos 3 caracteres');
            }
            
            // Validar cargo
            const cargo = document.getElementById('cargo').value;
            if (!cargo) {
                errors.push('O cargo é obrigatório');
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
