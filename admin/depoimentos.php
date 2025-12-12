<?php
require_once 'auth.php';
Auth::require();

$user = Auth::user();

// Mensagens de sucesso/erro
$success = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'created') $success = 'Depoimento cadastrado com sucesso!';
    if ($_GET['success'] === 'updated') $success = 'Depoimento atualizado com sucesso!';
    if ($_GET['success'] === 'deleted') $success = 'Depoimento excluído com sucesso!';
}

// Buscar depoimentos
$depoimentos = [];
try {
    $conn = getConnection();
    $search = $_GET['search'] ?? '';
    
    $sql = "SELECT * FROM depoimentos WHERE 1=1";
    
    if ($search) {
        $sql .= " AND nome LIKE :search";
    }
    
    $sql .= " ORDER BY ordem ASC, id DESC";
    
    $stmt = $conn->prepare($sql);
    
    if ($search) {
        $searchParam = "%$search%";
        $stmt->bindParam(':search', $searchParam);
    }
    
    $stmt->execute();
    $depoimentos = $stmt->fetchAll();
    
} catch (Exception $e) {
    logError('Erro ao buscar depoimentos', ['error' => $e->getMessage()]);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Depoimentos - Painel Administrativo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/15d6bd6a1c.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/depoimentos.css">
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

                <div class="page-header">
                    <div>
                        <h1><i class="fas fa-comments"></i> Depoimentos</h1>
                        <p>Gerencie os depoimentos em vídeo</p>
                    </div>
                    <a href="depoimento-criar.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Novo Depoimento
                    </a>
                </div>

                <!-- Filtros -->
                <div class="filters-card">
                    <form method="GET" class="filters-form">
                        <div class="filter-group search-group">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Buscar por nome..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <?php if ($search): ?>
                            <a href="depoimentos.php" class="btn btn-light">
                                <i class="fas fa-redo"></i> Limpar
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Lista de Depoimentos -->
                <div class="depoimentos-list">
                    <?php if (empty($depoimentos)): ?>
                        <p class="empty-message">Nenhum depoimento encontrado</p>
                    <?php else: ?>
                        <?php foreach ($depoimentos as $depoimento): ?>
                            <div class="depoimento-item">
                                <div class="depoimento-video">
                                    <video controls>
                                        <source src="<?= htmlspecialchars($depoimento['video']) ?>" type="video/mp4">
                                        Seu navegador não suporta a tag de vídeo.
                                    </video>
                                </div>
                                <div class="depoimento-info">
                                    <h3><?= htmlspecialchars($depoimento['nome']) ?></h3>
                                    <p><?= htmlspecialchars($depoimento['descricao']) ?></p>
                                    <div class="depoimento-meta">
                                        <span class="status-badge <?= $depoimento['ativo'] ? 'ativo' : 'inativo' ?>">
                                            <?= $depoimento['ativo'] ? 'Ativo' : 'Inativo' ?>
                                        </span>
                                        <span class="ordem-badge">Ordem: <?= $depoimento['ordem'] ?></span>
                                    </div>
                                </div>
                                <div class="depoimento-actions">
                                    <a href="depoimento-editar.php?id=<?= $depoimento['id'] ?>" class="btn-icon btn-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn-icon btn-danger" title="Excluir" onclick="confirmarExclusao(<?= $depoimento['id'] ?>, '<?= htmlspecialchars($depoimento['nome'], ENT_QUOTES) ?>')">
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

    <!-- Modal de Confirmação -->
    <div id="modalExcluir" class="modal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Confirmar Exclusão</h3>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o depoimento de <strong id="nomeDepoimento"></strong>?</p>
                <p class="modal-warning">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" onclick="fecharModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button id="btnConfirmarExcluir" type="button" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Excluir
                </button>
            </div>
        </div>
    </div>

    <script>
        function confirmarExclusao(id, nome) {
            document.getElementById('nomeDepoimento').textContent = nome;
            document.getElementById('btnConfirmarExcluir').onclick = function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'depoimento-excluir.php';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = id;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            };
            document.getElementById('modalExcluir').classList.add('show');
        }

        function fecharModal() {
            document.getElementById('modalExcluir').classList.remove('show');
        }

        document.querySelector('.modal-overlay').addEventListener('click', fecharModal);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                fecharModal();
            }
        });
    </script>
</body>
</html>
