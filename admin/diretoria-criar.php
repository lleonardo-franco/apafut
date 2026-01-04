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
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
                <?php endif; ?>

                <div class="page-header">
                    <div>
                        <h1><i class="fas fa-plus-circle"></i> Novo Membro da Diretoria</h1>
                        <p>Cadastre um novo membro da diretoria</p>
                    </div>
                    <a href="diretoria.php" class="btn btn-light"><i class="fas fa-arrow-left"></i> Voltar</a>
                </div>

                <form method="POST" enctype="multipart/form-data" class="admin-form">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="nome"><i class="fas fa-user"></i> Nome Completo *</label>
                            <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="cargo"><i class="fas fa-briefcase"></i> Cargo/Função *</label>
                            <input type="text" id="cargo" name="cargo" required placeholder="Ex: Presidente" value="<?= htmlspecialchars($_POST['cargo'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="ordem"><i class="fas fa-sort-numeric-up"></i> Ordem de Exibição</label>
                            <input type="number" id="ordem" name="ordem" value="<?= htmlspecialchars($_POST['ordem'] ?? '0') ?>" min="0">
                            <small>Define a ordem de exibição (menor número aparece primeiro)</small>
                        </div>

                        <div class="form-group full-width">
                            <label for="foto"><i class="fas fa-image"></i> Foto</label>
                            <input type="file" id="foto" name="foto" accept="image/*">
                            <small>Formatos aceitos: JPG, PNG, GIF, WEBP (máx 5MB)</small>
                        </div>

                        <div class="form-group full-width">
                            <label class="checkbox-label">
                                <input type="checkbox" name="ativo" <?= isset($_POST['ativo']) || !isset($_POST['nome']) ? 'checked' : '' ?>>
                                <span>Membro ativo</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Membro</button>
                        <a href="diretoria.php" class="btn btn-light">Cancelar</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
