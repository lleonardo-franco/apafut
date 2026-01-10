<?php
require_once 'auth.php';
Auth::require();

$user = Auth::user();

// Mensagens de sucesso/erro
$success = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'created') $success = 'Membro da comissão cadastrado com sucesso!';
    if ($_GET['success'] === 'updated') $success = 'Membro da comissão atualizado com sucesso!';
    if ($_GET['success'] === 'deleted') $success = 'Membro da comissão excluído com sucesso!';
}

// Buscar membros da comissão
$comissao = [];
try {
    $conn = getConnection();
    
    // Filtros
    $search = $_GET['search'] ?? '';
    $cargo = $_GET['cargo'] ?? '';
    
    $sql = "SELECT * FROM comissao_tecnica WHERE 1=1";
    
    if ($search) {
        $sql .= " AND (nome LIKE :search OR cargo LIKE :search)";
    }
    if ($cargo) {
        $sql .= " AND cargo = :cargo";
    }
    
    $sql .= " ORDER BY ordem ASC, id DESC";
    
    $stmt = $conn->prepare($sql);
    
    if ($search) {
        $searchParam = "%$search%";
        $stmt->bindParam(':search', $searchParam);
    }
    if ($cargo) {
        $stmt->bindParam(':cargo', $cargo);
    }
    
    $stmt->execute();
    $comissao = $stmt->fetchAll();
    
} catch (Exception $e) {
    logError('Erro ao buscar comissão técnica', ['error' => $e->getMessage()]);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Comissão Técnica - Painel Administrativo</title>
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
    <link rel="stylesheet" href="assets/css/jogadores.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <?php include 'includes/topbar.php'; ?>

            <div class="content">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= $success ?>
                    </div>
                <?php endif; ?>

                <div class="page-header-balanced">
                    <div class="header-left">
                        <div class="icon-wrapper">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <div class="header-text">
                            <h1>Comissão Técnica</h1>
                            <p>Gerencie os membros da comissão técnica</p>
                        </div>
                    </div>
                    <a href="comissao-criar.php" class="btn-balanced">
                        <i class="fas fa-plus"></i> Novo Membro
                    </a>
                </div>

                <!-- Filtros -->
                <div class="filters-card">
                    <form method="GET" class="filters-form">
                        <div class="filter-group search-group">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Buscar por nome ou cargo..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="filter-group">
                            <select name="cargo">
                                <option value="">Todos os cargos</option>
                                <option value="Técnico Principal" <?= $cargo === 'Técnico Principal' ? 'selected' : '' ?>>Técnico Principal</option>
                                <option value="Auxiliar Técnico" <?= $cargo === 'Auxiliar Técnico' ? 'selected' : '' ?>>Auxiliar Técnico</option>
                                <option value="Preparador Físico" <?= $cargo === 'Preparador Físico' ? 'selected' : '' ?>>Preparador Físico</option>
                                <option value="Preparador de Goleiros" <?= $cargo === 'Preparador de Goleiros' ? 'selected' : '' ?>>Preparador de Goleiros</option>
                                <option value="Fisioterapeuta" <?= $cargo === 'Fisioterapeuta' ? 'selected' : '' ?>>Fisioterapeuta</option>
                                <option value="Médico" <?= $cargo === 'Médico' ? 'selected' : '' ?>>Médico</option>
                                <option value="Nutricionista" <?= $cargo === 'Nutricionista' ? 'selected' : '' ?>>Nutricionista</option>
                                <option value="Analista de Desempenho" <?= $cargo === 'Analista de Desempenho' ? 'selected' : '' ?>>Analista de Desempenho</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <?php if ($search || $cargo): ?>
                            <a href="comissao.php" class="btn btn-light">
                                <i class="fas fa-redo"></i> Limpar
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Grid da Comissão -->
                <div class="jogadores-grid">
                    <?php if (empty($comissao)): ?>
                        <p class="empty-message">Nenhum membro encontrado</p>
                    <?php else: ?>
                        <?php foreach ($comissao as $membro): ?>
                            <div class="jogador-card">
                                <div class="jogador-foto <?= empty($membro['foto']) ? 'sem-foto' : '' ?>">
                                    <?php if (!empty($membro['foto'])): ?>
                                        <img src="<?= htmlspecialchars($membro['foto']) ?>" alt="<?= htmlspecialchars($membro['nome']) ?>">
                                    <?php else: ?>
                                        <div class="foto-placeholder">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!$membro['ativo']): ?>
                                        <div class="status-badge inativo">Inativo</div>
                                    <?php endif; ?>
                                </div>
                                <div class="jogador-info">
                                    <h3><?= htmlspecialchars($membro['nome']) ?></h3>
                                    <p class="posicao"><i class="fas fa-briefcase"></i> <?= htmlspecialchars($membro['cargo']) ?></p>
                                </div>
                                <div class="jogador-actions">
                                    <a href="comissao-editar.php?id=<?= $membro['id'] ?>" class="btn-icon btn-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn-icon btn-danger" title="Excluir" onclick="confirmarExclusao(<?= $membro['id'] ?>, '<?= htmlspecialchars($membro['nome'], ENT_QUOTES) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
    function confirmarExclusao(id, nome) {
        if (confirm(`Tem certeza que deseja excluir ${nome}?`)) {
            window.location.href = `comissao-excluir.php?id=${id}`;
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
