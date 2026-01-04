<?php
require_once 'auth.php';
require_once '../src/Cache.php';
Auth::require();

$user = Auth::user();
$membroId = Security::validateInt($_GET['id'] ?? 0, 1);

if (!$membroId) {
    header('Location: comissao.php');
    exit;
}

$error = '';
$success = '';

// Buscar membro
try {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM comissao_tecnica WHERE id = :id");
    $stmt->bindParam(':id', $membroId);
    $stmt->execute();
    $membro = $stmt->fetch();
    
    if (!$membro) {
        header('Location: comissao.php');
        exit;
    }
} catch (Exception $e) {
    $error = 'Erro ao buscar membro da comissão';
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    try {
        $nome = Security::sanitizeString($_POST['nome'] ?? '');
        $cargo = Security::sanitizeString($_POST['cargo'] ?? '');
        $descricao = Security::sanitizeString($_POST['descricao'] ?? '');
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        $ordem = Security::validateInt($_POST['ordem'] ?? 0, 0);
        
        if (empty($nome)) {
            throw new Exception('Nome é obrigatório');
        }
        if (empty($cargo)) {
            throw new Exception('Cargo é obrigatório');
        }
        
        $fotoPath = $membro['foto'];
        
        // Upload de nova foto
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
            
            // Deletar foto antiga
            if (!empty($membro['foto']) && file_exists($membro['foto'])) {
                unlink($membro['foto']);
            }
            
            $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $fileName = 'comissao-' . preg_replace('/[^a-z0-9]/i', '-', strtolower($nome)) . '-' . time() . '.' . $extension;
            $fotoPath = '../assets/images/comissao/' . $fileName;
            
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fileName)) {
                throw new Exception('Erro ao fazer upload da foto');
            }
        }
        
        $stmt = $conn->prepare("
            UPDATE comissao_tecnica 
            SET nome = :nome, cargo = :cargo, descricao = :descricao,
                foto = :foto, ativo = :ativo, ordem = :ordem
            WHERE id = :id
        ");
        
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':cargo', $cargo);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':foto', $fotoPath);
        $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
        $stmt->bindParam(':ordem', $ordem, PDO::PARAM_INT);
        $stmt->bindParam(':id', $membroId, PDO::PARAM_INT);
        
        $stmt->execute();
        
        // Limpar cache
        Cache::delete('comissao_tecnica_ativos');
        
        logError('Membro da comissão atualizado', [
            'id' => $membroId,
            'nome' => $nome,
            'user' => $user['email']
        ]);
        
        $success = 'Membro da comissão atualizado com sucesso!';
        
        // Recarregar dados
        $stmt = $conn->prepare("SELECT * FROM comissao_tecnica WHERE id = :id");
        $stmt->bindParam(':id', $membroId);
        $stmt->execute();
        $membro = $stmt->fetch();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        logError('Erro ao atualizar membro da comissão', [
            'id' => $membroId,
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
    <title>Editar Membro da Comissão - Painel Administrativo</title>
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
                    <div class="alert alert-danger">
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
                        <h1><i class="fas fa-user-edit"></i> Editar Membro da Comissão</h1>
                        <p>Atualize as informações do membro</p>
                    </div>
                    <a href="comissao.php" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <?php if (!empty($membro['foto'])): ?>
                    <div class="form-card" style="margin-bottom: 24px;">
                        <h3 style="margin-bottom: 16px;">Foto Atual</h3>
                        <div class="foto-preview">
                            <img src="<?= htmlspecialchars($membro['foto']) ?>" alt="Foto atual" onerror="this.style.display='none'">
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-card">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nome">Nome *</label>
                                <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars($membro['nome']) ?>">
                            </div>
                            <div class="form-group">
                                <label for="cargo">Cargo *</label>
                                <select id="cargo" name="cargo" required>
                                    <option value="">Selecione...</option>
                                    <option value="Técnico Principal" <?= $membro['cargo'] === 'Técnico Principal' ? 'selected' : '' ?>>Técnico Principal</option>
                                    <option value="Auxiliar Técnico" <?= $membro['cargo'] === 'Auxiliar Técnico' ? 'selected' : '' ?>>Auxiliar Técnico</option>
                                    <option value="Preparador Físico" <?= $membro['cargo'] === 'Preparador Físico' ? 'selected' : '' ?>>Preparador Físico</option>
                                    <option value="Preparador de Goleiros" <?= $membro['cargo'] === 'Preparador de Goleiros' ? 'selected' : '' ?>>Preparador de Goleiros</option>
                                    <option value="Fisioterapeuta" <?= $membro['cargo'] === 'Fisioterapeuta' ? 'selected' : '' ?>>Fisioterapeuta</option>
                                    <option value="Médico" <?= $membro['cargo'] === 'Médico' ? 'selected' : '' ?>>Médico</option>
                                    <option value="Nutricionista" <?= $membro['cargo'] === 'Nutricionista' ? 'selected' : '' ?>>Nutricionista</option>
                                    <option value="Analista de Desempenho" <?= $membro['cargo'] === 'Analista de Desempenho' ? 'selected' : '' ?>>Analista de Desempenho</option>
                                    <option value="Outro" <?= $membro['cargo'] === 'Outro' ? 'selected' : '' ?>>Outro</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="descricao">Descrição</label>
                            <textarea id="descricao" name="descricao" rows="4" placeholder="Breve descrição sobre o profissional..."><?= htmlspecialchars($membro['descricao'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="ordem">Ordem de Exibição</label>
                            <input type="number" id="ordem" name="ordem" min="0" value="<?= htmlspecialchars($membro['ordem']) ?>">
                            <small>Membros com menor ordem aparecem primeiro</small>
                        </div>

                        <div class="form-group">
                            <label for="foto">Nova Foto (deixe em branco para manter a atual)</label>
                            <input type="file" id="foto" name="foto" accept="image/*" onchange="previewFoto(this)">
                            <small>Formatos aceitos: JPG, PNG, GIF, WEBP. Tamanho máximo: 5MB</small>
                            <div id="foto-preview" class="foto-preview" style="display: none;">
                                <img src="" alt="Preview">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="ativo" name="ativo" <?= $membro['ativo'] ? 'checked' : '' ?>>
                                <label for="ativo" style="margin: 0;">Membro ativo na comissão</label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Atualizar Membro
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

        // Auto-hide success message
        setTimeout(() => {
            const alert = document.querySelector('.alert-success');
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        }, 3000);
    </script>
</body>
</html>
