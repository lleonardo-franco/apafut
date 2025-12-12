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
        $posicao = Security::sanitizeString($_POST['posicao'] ?? '');
        $numero = Security::validateInt($_POST['numero'] ?? 0, 1);
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        $ordem = Security::validateInt($_POST['ordem'] ?? 0, 0);
        
        // Validações
        if (empty($nome)) {
            throw new Exception('Nome é obrigatório');
        }
        if (empty($posicao)) {
            throw new Exception('Posição é obrigatória');
        }
        if ($numero === false || $numero < 1 || $numero > 99) {
            throw new Exception('Número deve estar entre 1 e 99');
        }
        
        // Upload de foto
        $fotoPath = '';
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
            
            $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $fileName = 'jogador-' . preg_replace('/[^a-z0-9]/i', '-', strtolower($nome)) . '-' . time() . '.' . $extension;
            $fotoPath = '../assets/images/jogadores/' . $fileName;
            
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fileName)) {
                throw new Exception('Erro ao fazer upload da foto');
            }
        }
        
        // Inserir no banco
        $conn = getConnection();
        $stmt = $conn->prepare("
            INSERT INTO jogadores (nome, posicao, numero, foto, ativo, ordem)
            VALUES (:nome, :posicao, :numero, :foto, :ativo, :ordem)
        ");
        
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':posicao', $posicao);
        $stmt->bindParam(':numero', $numero, PDO::PARAM_INT);
        $stmt->bindParam(':foto', $fotoPath);
        $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
        $stmt->bindParam(':ordem', $ordem, PDO::PARAM_INT);
        
        $stmt->execute();
        
        // Limpar cache de jogadores
        Cache::delete('jogadores_ativos');
        
        logError('Jogador criado', [
            'nome' => $nome,
            'numero' => $numero,
            'user' => $user['email']
        ]);
        
        header('Location: jogadores.php?success=created');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        logError('Erro ao criar jogador', [
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
    <title>Novo Jogador - Painel Administrativo</title>
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
                        <h1><i class="fas fa-user-plus"></i> Novo Jogador</h1>
                        <p>Adicione um novo jogador ao elenco</p>
                    </div>
                    <a href="jogadores.php" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <div class="form-card">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nome">Nome Completo *</label>
                                <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="numero">Número da Camisa *</label>
                                <input type="number" id="numero" name="numero" min="1" max="99" required value="<?= htmlspecialchars($_POST['numero'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="posicao">Posição *</label>
                                <select id="posicao" name="posicao" required>
                                    <option value="">Selecione...</option>
                                    <option value="Goleiro" <?= ($_POST['posicao'] ?? '') === 'Goleiro' ? 'selected' : '' ?>>Goleiro</option>
                                    <option value="Zagueiro" <?= ($_POST['posicao'] ?? '') === 'Zagueiro' ? 'selected' : '' ?>>Zagueiro</option>
                                    <option value="Lateral" <?= ($_POST['posicao'] ?? '') === 'Lateral' ? 'selected' : '' ?>>Lateral</option>
                                    <option value="Volante" <?= ($_POST['posicao'] ?? '') === 'Volante' ? 'selected' : '' ?>>Volante</option>
                                    <option value="Meia" <?= ($_POST['posicao'] ?? '') === 'Meia' ? 'selected' : '' ?>>Meia</option>
                                    <option value="Atacante" <?= ($_POST['posicao'] ?? '') === 'Atacante' ? 'selected' : '' ?>>Atacante</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="ordem">Ordem de Exibição</label>
                                <input type="number" id="ordem" name="ordem" min="0" value="<?= htmlspecialchars($_POST['ordem'] ?? '0') ?>">
                                <small>Jogadores com menor ordem aparecem primeiro</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="foto">Foto do Jogador</label>
                            <input type="file" id="foto" name="foto" accept="image/*" onchange="previewFoto(this)">
                            <small>Formatos aceitos: JPG, PNG, GIF, WEBP. Tamanho máximo: 5MB</small>
                            <div id="foto-preview" class="foto-preview" style="display: none;">
                                <img src="" alt="Preview">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="ativo" name="ativo" checked>
                                <label for="ativo" style="margin: 0;">Jogador ativo no elenco</label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Jogador
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
