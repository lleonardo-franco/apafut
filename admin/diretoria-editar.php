<?php
require_once 'auth.php';
Auth::require();

$id = Security::validateInt($_GET['id'] ?? 0, 1);
if ($id === false) {
    header('Location: diretoria.php');
    exit;
}

try {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM diretoria WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $membro = $stmt->fetch();
    
    if (!$membro) {
        header('Location: diretoria.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: diretoria.php');
    exit;
}

$user = Auth::user();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = Security::sanitizeString($_POST['nome'] ?? '');
        $cargo = Security::sanitizeString($_POST['cargo'] ?? '');
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        $ordem = Security::validateInt($_POST['ordem'] ?? 0, 0);
        
        if (empty($nome)) throw new Exception('Nome é obrigatório');
        if (empty($cargo)) throw new Exception('Cargo é obrigatório');
        
        $fotoPath = $membro['foto'];
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/diretoria/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($_FILES['foto']['type'], $allowedTypes)) {
                throw new Exception('Formato inválido');
            }
            
            if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                throw new Exception('Imagem muito grande');
            }
            
            // Deletar foto antiga
            if (!empty($membro['foto']) && file_exists('../' . $membro['foto'])) {
                unlink('../' . $membro['foto']);
            }
            
            $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $fileName = 'diretoria-' . preg_replace('/[^a-z0-9]/i', '-', strtolower($nome)) . '-' . time() . '.' . $extension;
            $fotoPath = 'assets/diretoria/' . $fileName;
            
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fileName)) {
                throw new Exception('Erro ao fazer upload');
            }
        }
        
        $stmt = $conn->prepare("UPDATE diretoria SET nome = :nome, cargo = :cargo, foto = :foto, ativo = :ativo, ordem = :ordem WHERE id = :id");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':cargo', $cargo);
        $stmt->bindParam(':foto', $fotoPath);
        $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
        $stmt->bindParam(':ordem', $ordem, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        header('Location: diretoria.php?success=updated');
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
    <title>Editar Membro - Painel Admin</title>
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
                        <h1><i class="fas fa-edit"></i> Editar Membro</h1>
                        <p>Atualizar informações de <?= htmlspecialchars($membro['nome']) ?></p>
                    </div>
                    <a href="diretoria.php" class="btn btn-light"><i class="fas fa-arrow-left"></i> Voltar</a>
                </div>

                <form method="POST" enctype="multipart/form-data" class="admin-form">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="nome"><i class="fas fa-user"></i> Nome Completo *</label>
                            <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars($membro['nome']) ?>">
                        </div>

                        <div class="form-group">
                            <label for="cargo"><i class="fas fa-briefcase"></i> Cargo/Função *</label>
                            <input type="text" id="cargo" name="cargo" required value="<?= htmlspecialchars($membro['cargo']) ?>">
                        </div>

                        <div class="form-group">
                            <label for="ordem"><i class="fas fa-sort-numeric-up"></i> Ordem</label>
                            <input type="number" id="ordem" name="ordem" value="<?= $membro['ordem'] ?>" min="0">
                        </div>

                        <?php if (!empty($membro['foto'])): ?>
                            <div class="form-group full-width">
                                <label>Foto Atual</label>
                                <div class="current-image">
                                    <img src="../<?= htmlspecialchars($membro['foto']) ?>" alt="Foto atual" style="max-width: 200px; border-radius: 8px;">
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="form-group full-width">
                            <label for="foto"><i class="fas fa-image"></i> Nova Foto</label>
                            <input type="file" id="foto" name="foto" accept="image/*">
                        </div>

                        <div class="form-group full-width">
                            <label class="checkbox-label">
                                <input type="checkbox" name="ativo" <?= $membro['ativo'] ? 'checked' : '' ?>>
                                <span>Membro ativo</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Alterações</button>
                        <a href="diretoria.php" class="btn btn-light">Cancelar</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
