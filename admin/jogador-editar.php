<?php
require_once 'auth.php';
require_once '../src/Cache.php';
Auth::require();

$user = Auth::user();
$jogadorId = Security::validateInt($_GET['id'] ?? 0, 1);

if (!$jogadorId) {
    header('Location: jogadores.php');
    exit;
}

$error = '';
$success = '';

// Buscar jogador
try {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM jogadores WHERE id = :id");
    $stmt->bindParam(':id', $jogadorId);
    $stmt->execute();
    $jogador = $stmt->fetch();
    
    if (!$jogador) {
        header('Location: jogadores.php');
        exit;
    }
} catch (Exception $e) {
    $error = 'Erro ao buscar jogador';
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    try {
        $nome = Security::sanitizeString($_POST['nome'] ?? '');
        $nomeCompleto = Security::sanitizeString($_POST['nome_completo'] ?? '');
        $cidade = Security::sanitizeString($_POST['cidade'] ?? '');
        $altura = Security::sanitizeString($_POST['altura'] ?? '');
        $peso = Security::sanitizeString($_POST['peso'] ?? '');
        $dataNascimento = Security::sanitizeString($_POST['data_nascimento'] ?? '');
        $posicao = Security::sanitizeString($_POST['posicao'] ?? '');
        $numero = Security::validateInt($_POST['numero'] ?? 0, 1);
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        $ordem = Security::validateInt($_POST['ordem'] ?? 0, 0);
        
        if (empty($nome)) {
            throw new Exception('Nome é obrigatório');
        }
        if (empty($posicao)) {
            throw new Exception('Posição é obrigatória');
        }
        if ($numero === false || $numero < 1 || $numero > 99) {
            throw new Exception('Número deve estar entre 1 e 99');
        }
        
        $fotoPath = $jogador['foto'];
        
        // Upload de nova foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/images/jogadores/';
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
            
            // Deletar foto antiga
            if (!empty($jogador['foto']) && file_exists('../' . $jogador['foto'])) {
                unlink('../' . $jogador['foto']);
            }
            
            $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $fileName = 'jogador-' . preg_replace('/[^a-z0-9]/i', '-', strtolower($nome)) . '-' . time() . '.' . $extension;
            $fotoPath = '../assets/images/jogadores/' . $fileName;
            
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fileName)) {
                throw new Exception('Erro ao fazer upload da foto');
            }
        }
        
        $stmt = $conn->prepare("
            UPDATE jogadores 
            SET nome = :nome, nome_completo = :nome_completo, cidade = :cidade, 
                altura = :altura, peso = :peso, data_nascimento = :data_nascimento, 
                posicao = :posicao, numero = :numero, 
                foto = :foto, ativo = :ativo, ordem = :ordem
            WHERE id = :id
        ");
        
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':nome_completo', $nomeCompleto);
        $stmt->bindParam(':cidade', $cidade);
        $stmt->bindParam(':altura', $altura);
        $stmt->bindParam(':peso', $peso);
        $stmt->bindParam(':data_nascimento', $dataNascimento);
        $stmt->bindParam(':posicao', $posicao);
        $stmt->bindParam(':numero', $numero, PDO::PARAM_INT);
        $stmt->bindParam(':foto', $fotoPath);
        $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
        $stmt->bindParam(':ordem', $ordem, PDO::PARAM_INT);
        $stmt->bindParam(':id', $jogadorId, PDO::PARAM_INT);
        
        $stmt->execute();
        
        // Limpar cache de jogadores
        Cache::delete('jogadores_ativos');
        
        logError('Jogador atualizado', [
            'id' => $jogadorId,
            'nome' => $nome,
            'user' => $user['email']
        ]);
        
        $success = 'Jogador atualizado com sucesso!';
        
        // Recarregar dados do jogador
        $stmt = $conn->prepare("SELECT * FROM jogadores WHERE id = :id");
        $stmt->bindParam(':id', $jogadorId);
        $stmt->execute();
        $jogador = $stmt->fetch();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        logError('Erro ao atualizar jogador', [
            'id' => $jogadorId,
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
    <title>Editar Jogador - Painel Administrativo</title>
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
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= $success ?>
                    </div>
                <?php endif; ?>

                <div class="page-header-balanced">
                    <div class="header-left">
                        <div class="icon-wrapper">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <div class="header-text">
                            <h1>Editar Jogador</h1>
                            <p>Atualize as informações do jogador</p>
                        </div>
                    </div>
                    <a href="jogadores.php" class="btn-balanced-light">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <?php if (!empty($jogador['foto'])): ?>
                    <div class="form-section" style="margin-bottom: 24px;">
                        <h3><i class="fas fa-image"></i> Foto Atual</h3>
                        <div class="image-preview-container" style="display: block;">
                            <img src="../<?= htmlspecialchars($jogador['foto']) ?>" alt="Foto atual" onerror="this.style.display='none'" style="max-width: 100%; max-height: 300px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="form-balanced" id="jogadorForm">
                    <!-- Informações Básicas -->
                    <div class="form-section">
                        <h3><i class="fas fa-id-card"></i> Informações Básicas</h3>
                        
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="nome">Nome</label>
                                <input type="text" id="nome" name="nome" placeholder="Ex: João Silva" value="<?= htmlspecialchars($jogador['nome']) ?>">
                                <small>Nome curto para exibição no card</small>
                            </div>
                            <div class="form-group">
                                <label for="nome_completo">Nome Completo</label>
                                <input type="text" id="nome_completo" name="nome_completo" placeholder="Nome completo do jogador" value="<?= htmlspecialchars($jogador['nome_completo'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="form-grid-3">
                            <div class="form-group">
                                <label for="numero">Número da Camisa</label>
                                <input type="number" id="numero" name="numero" min="1" max="99" placeholder="1-99" value="<?= htmlspecialchars($jogador['numero']) ?>">
                            </div>
                            <div class="form-group">
                                <label for="posicao">Posição</label>
                                <select id="posicao" name="posicao">
                                    <option value="">Selecione...</option>
                                    <option value="Goleiro" <?= $jogador['posicao'] === 'Goleiro' ? 'selected' : '' ?>>Goleiro</option>
                                    <option value="Zagueiro" <?= $jogador['posicao'] === 'Zagueiro' ? 'selected' : '' ?>>Zagueiro</option>
                                    <option value="Lateral" <?= $jogador['posicao'] === 'Lateral' ? 'selected' : '' ?>>Lateral</option>
                                    <option value="Volante" <?= $jogador['posicao'] === 'Volante' ? 'selected' : '' ?>>Volante</option>
                                    <option value="Meia" <?= $jogador['posicao'] === 'Meia' ? 'selected' : '' ?>>Meia</option>
                                    <option value="Atacante" <?= $jogador['posicao'] === 'Atacante' ? 'selected' : '' ?>>Atacante</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="cidade">Cidade</label>
                                <input type="text" id="cidade" name="cidade" placeholder="Ex: Caxias do Sul (RS)" value="<?= htmlspecialchars($jogador['cidade'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Dados Físicos -->
                    <div class="form-section">
                        <h3><i class="fas fa-heartbeat"></i> Dados Físicos</h3>
                        
                        <div class="form-grid-3">
                            <div class="form-group">
                                <label for="data_nascimento">Data de Nascimento</label>
                                <input type="text" id="data_nascimento" name="data_nascimento" placeholder="15/03/2000" value="<?= htmlspecialchars($jogador['data_nascimento'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="altura">Altura</label>
                                <input type="text" id="altura" name="altura" placeholder="1.85m" value="<?= htmlspecialchars($jogador['altura'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="peso">Peso</label>
                                <input type="text" id="peso" name="peso" placeholder="78kg" value="<?= htmlspecialchars($jogador['peso'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Nova Foto -->
                    <div class="form-section">
                        <h3><i class="fas fa-camera"></i> Atualizar Foto</h3>
                        
                        <div class="form-group">
                            <label>Selecionar Nova Foto</label>
                            <div class="image-upload-box">
                                <input type="file" id="foto" name="foto" accept="image/*" onchange="previewImage(this, 'fotoPreview', 'fotoPreviewContainer')">
                                <label for="foto" class="upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Clique para selecionar ou arraste a foto</span>
                                    <small>Deixe em branco para manter a foto atual · Formatos: JPG, PNG, GIF, WEBP · Máximo 5MB</small>
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
                                <input type="number" id="ordem" name="ordem" min="0" value="<?= htmlspecialchars($jogador['ordem']) ?>">
                                <small>Jogadores com menor ordem aparecem primeiro</small>
                            </div>
                        </div>
                        
                        <div class="checkbox-wrapper">
                            <div class="checkbox-item">
                                <label>
                                    <input type="checkbox" name="ativo" <?= $jogador['ativo'] ? 'checked' : '' ?>>
                                    <span class="checkbox-label-text">
                                        <strong>Jogador Ativo</strong>
                                        <small>Apenas jogadores ativos são exibidos no site</small>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="form-actions">
                        <button type="submit" class="btn-balanced">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                        <a href="jogadores.php" class="btn-balanced-cancel">
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

    <script src="assets/js/masks.js"></script>
    <script>
        const form = document.getElementById('jogadorForm');
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
            
            const numero = parseInt(document.getElementById('numero').value);
            if (!numero || numero < 1 || numero > 99) {
                errors.push('O número da camisa deve estar entre 1 e 99');
            }
            
            const posicao = document.getElementById('posicao').value;
            if (!posicao) {
                errors.push('A posição é obrigatória');
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
