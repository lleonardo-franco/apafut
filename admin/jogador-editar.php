<?php
require_once 'auth.php';
Auth::require();

$user = Auth::user();
$jogadorId = Security::validateInt($_GET['id'] ?? 0, 1);

if (!$jogadorId) {
    header('Location: jogadores.php');
    exit;
}

$error = '';

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
        $carreira = Security::sanitizeString($_POST['carreira'] ?? '');
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
                carreira = :carreira, posicao = :posicao, numero = :numero, 
                foto = :foto, ativo = :ativo, ordem = :ordem
            WHERE id = :id
        ");
        
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':nome_completo', $nomeCompleto);
        $stmt->bindParam(':cidade', $cidade);
        $stmt->bindParam(':altura', $altura);
        $stmt->bindParam(':peso', $peso);
        $stmt->bindParam(':data_nascimento', $dataNascimento);
        $stmt->bindParam(':carreira', $carreira);
        $stmt->bindParam(':posicao', $posicao);
        $stmt->bindParam(':numero', $numero, PDO::PARAM_INT);
        $stmt->bindParam(':foto', $fotoPath);
        $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
        $stmt->bindParam(':ordem', $ordem, PDO::PARAM_INT);
        $stmt->bindParam(':id', $jogadorId, PDO::PARAM_INT);
        
        $stmt->execute();
        
        logError('Jogador atualizado', [
            'id' => $jogadorId,
            'nome' => $nome,
            'user' => $user['email']
        ]);
        
        header('Location: jogadores.php?success=updated');
        exit;
        
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/15d6bd6a1c.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/jogadores.css">
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

                <div class="page-header">
                    <div>
                        <h1><i class="fas fa-user-edit"></i> Editar Jogador</h1>
                        <p>Atualize as informações do jogador</p>
                    </div>
                    <a href="jogadores.php" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <?php if (!empty($jogador['foto'])): ?>
                    <div class="form-card" style="margin-bottom: 24px;">
                        <h3 style="margin-bottom: 16px;">Foto Atual</h3>
                        <div class="foto-preview">
                            <img src="../<?= htmlspecialchars($jogador['foto']) ?>" alt="Foto atual" onerror="this.style.display='none'">
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-card">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nome">Nome *</label>
                                <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars($jogador['nome']) ?>">
                                <small>Nome curto para exibição no card</small>
                            </div>
                            <div class="form-group">
                                <label for="nome_completo">Nome Completo</label>
                                <input type="text" id="nome_completo" name="nome_completo" value="<?= htmlspecialchars($jogador['nome_completo'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cidade">Cidade</label>
                                <input type="text" id="cidade" name="cidade" placeholder="Ex: Caxias do Sul (RS)" value="<?= htmlspecialchars($jogador['cidade'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="numero">Número da Camisa *</label>
                                <input type="number" id="numero" name="numero" min="1" max="99" required value="<?= htmlspecialchars($jogador['numero']) ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="posicao">Posição *</label>
                                <select id="posicao" name="posicao" required>
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
                                <label for="data_nascimento">Data de Nascimento</label>
                                <input type="text" id="data_nascimento" name="data_nascimento" placeholder="Ex: 15/03/2000" value="<?= htmlspecialchars($jogador['data_nascimento'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="altura">Altura</label>
                                <input type="text" id="altura" name="altura" placeholder="Ex: 1.85m" value="<?= htmlspecialchars($jogador['altura'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="peso">Peso</label>
                                <input type="text" id="peso" name="peso" placeholder="Ex: 78kg" value="<?= htmlspecialchars($jogador['peso'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="ordem">Ordem de Exibição</label>
                                <input type="number" id="ordem" name="ordem" min="0" value="<?= htmlspecialchars($jogador['ordem']) ?>">
                                <small>Jogadores com menor ordem aparecem primeiro</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="carreira">Carreira</label>
                            <textarea id="carreira" name="carreira" rows="4" placeholder="Ex: 2022 – Fluminense (RJ) 2023; – Red Bull Bragantino (SP); 2024 – APOEL (Chipre)"><?= htmlspecialchars($jogador['carreira'] ?? '') ?></textarea>
                            <small>Descreva o histórico de clubes do jogador</small>
                        </div>

                        <div class="form-group">
                            <label for="foto">Nova Foto do Jogador</label>
                            <input type="file" id="foto" name="foto" accept="image/*" onchange="previewFoto(this)">
                            <small>Deixe em branco para manter a foto atual. Formatos aceitos: JPG, PNG, GIF, WEBP. Tamanho máximo: 5MB</small>
                            <div id="foto-preview" class="foto-preview" style="display: none;">
                                <img src="" alt="Preview">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="ativo" name="ativo" <?= $jogador['ativo'] ? 'checked' : '' ?>>
                                <label for="ativo" style="margin: 0;">Jogador ativo no elenco</label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                            <a href="jogadores.php" class="btn btn-light">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function previewFoto(input) {
            const preview = document.getElementById('foto-preview');
            const img = preview.querySelector('img');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>
