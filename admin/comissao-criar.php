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

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= $success ?>
                    </div>
                <?php endif; ?>

                <div class="page-header">
                    <div>
                        <h1><i class="fas fa-user-plus"></i> Novo Membro da Comissão</h1>
                        <p>Adicione um novo membro à comissão técnica</p>
                    </div>
                    <a href="comissao.php" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <div class="form-card">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nome">Nome *</label>
                                <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="cargo">Cargo *</label>
                                <select id="cargo" name="cargo" required>
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

                        <div class="form-group">
                            <label for="ordem">Ordem de Exibição</label>
                            <input type="number" id="ordem" name="ordem" min="0" value="<?= htmlspecialchars($_POST['ordem'] ?? '0') ?>">
                            <small>Membros com menor ordem aparecem primeiro</small>
                        </div>

                        <div class="form-group">
                            <label for="foto">Foto do Membro</label>
                            <input type="file" id="foto" name="foto" accept="image/*" onchange="previewFoto(this)">
                            <small>Formatos aceitos: JPG, PNG, GIF, WEBP. Tamanho máximo: 5MB</small>
                            <div id="foto-preview" class="foto-preview" style="display: none;">
                                <img src="" alt="Preview">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="ativo" name="ativo" checked>
                                <label for="ativo" style="margin: 0;">Membro ativo na comissão</label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Membro
                            </button>
                            <a href="comissao.php" class="btn btn-light">
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
            }
        }
    </script>
</body>
</html>
