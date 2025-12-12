<?php
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
require_once '../config/db.php';
require_once 'auth.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $preco_anual = floatval($_POST['preco_anual'] ?? 0);
    $parcelas = intval($_POST['parcelas'] ?? 2);
    $beneficios = trim($_POST['beneficios'] ?? '');
    $ordem = intval($_POST['ordem'] ?? 0);
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    
    // Validação
    if (empty($nome) || empty($tipo) || $preco_anual <= 0 || empty($beneficios)) {
        $erro = 'Todos os campos obrigatórios devem ser preenchidos';
    } else {
        try {
            $pdo = getConnection();
            
            $sql = "INSERT INTO planos (nome, tipo, preco_anual, parcelas, beneficios, ordem, ativo, destaque) 
                    VALUES (:nome, :tipo, :preco_anual, :parcelas, :beneficios, :ordem, :ativo, :destaque)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':tipo' => $tipo,
                ':preco_anual' => $preco_anual,
                ':parcelas' => $parcelas,
                ':beneficios' => $beneficios,
                ':ordem' => $ordem,
                ':ativo' => $ativo,
                ':destaque' => $destaque
            ]);
            
            header('Location: planos.php?sucesso=criado');
            exit;
            
        } catch (Exception $e) {
            $erro = 'Erro ao criar plano: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Plano - Painel Administrativo</title>
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
                <div class="page-header">
                    <div>
                        <h1>Criar Novo Plano</h1>
                        <p>Adicione um novo plano de sócio</p>
                    </div>
                    <a href="planos.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Voltar
                    </a>
                </div>

                <?php if ($erro): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($sucesso) ?>
                    </div>
                <?php endif; ?>

                <div class="form-card">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nome">Nome do Plano *</label>
                                <input 
                                    type="text" 
                                    id="nome" 
                                    name="nome" 
                                    value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
                                    placeholder="Ex: Sócio APA Ouro"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="tipo">Tipo *</label>
                                <select id="tipo" name="tipo" required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="Prata" <?= ($_POST['tipo'] ?? '') === 'Prata' ? 'selected' : '' ?>>Prata</option>
                                    <option value="Ouro" <?= ($_POST['tipo'] ?? '') === 'Ouro' ? 'selected' : '' ?>>Ouro</option>
                                    <option value="Diamante" <?= ($_POST['tipo'] ?? '') === 'Diamante' ? 'selected' : '' ?>>Diamante</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="preco_anual">Preço Anual (R$) *</label>
                                <input 
                                    type="number" 
                                    id="preco_anual" 
                                    name="preco_anual" 
                                    step="0.01"
                                    min="0"
                                    value="<?= htmlspecialchars($_POST['preco_anual'] ?? '') ?>"
                                    placeholder="Ex: 300.00"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="parcelas">Número de Parcelas *</label>
                                <input 
                                    type="number" 
                                    id="parcelas" 
                                    name="parcelas" 
                                    min="1"
                                    max="12"
                                    value="<?= htmlspecialchars($_POST['parcelas'] ?? '2') ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="ordem">Ordem de Exibição</label>
                                <input 
                                    type="number" 
                                    id="ordem" 
                                    name="ordem" 
                                    value="<?= htmlspecialchars($_POST['ordem'] ?? '0') ?>"
                                    min="0"
                                >
                                <small>Define a ordem de exibição no site (menor aparece primeiro)</small>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label for="beneficios">Benefícios *</label>
                            <textarea 
                                id="beneficios" 
                                name="beneficios" 
                                rows="6"
                                placeholder="Digite um benefício por linha. Exemplo:&#10;Camiseta oficial exclusiva&#10;Jantar de fim de temporada&#10;Descontos com parceiros"
                                required
                            ><?= htmlspecialchars($_POST['beneficios'] ?? '') ?></textarea>
                            <small>Digite um benefício por linha</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group-checkbox">
                                <input 
                                    type="checkbox" 
                                    id="ativo" 
                                    name="ativo"
                                    <?= isset($_POST['ativo']) || !isset($_POST['nome']) ? 'checked' : '' ?>
                                >
                                <label for="ativo">Plano Ativo</label>
                            </div>

                            <div class="form-group-checkbox">
                                <input 
                                    type="checkbox" 
                                    id="destaque" 
                                    name="destaque"
                                    <?= isset($_POST['destaque']) ? 'checked' : '' ?>
                                >
                                <label for="destaque">Marcar como Destaque</label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="planos.php" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Criar Plano
                            </button>
                        </div>
                    </form>
                </div>

                <div class="help-card">
                    <h3><i class="fas fa-info-circle"></i> Dicas</h3>
                    <ul>
                        <li><strong>Benefícios:</strong> Liste um benefício por linha para facilitar a leitura</li>
                        <li><strong>Destaque:</strong> Marque apenas um plano como destaque (geralmente o mais vendido)</li>
                        <li><strong>Ordem:</strong> Use números sequenciais (0, 1, 2...) para organizar a exibição</li>
                        <li><strong>Tipos:</strong> Use nomes consistentes como Prata, Ouro, Diamante</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Auto-formatar benefícios: converter separadores | para quebras de linha
        document.getElementById('beneficios').addEventListener('blur', function() {
            if (this.value.includes('|')) {
                this.value = this.value.split('|').map(b => b.trim()).join('\n');
            }
        });

        // Antes de enviar, converter quebras de linha para |
        document.querySelector('form').addEventListener('submit', function(e) {
            const beneficiosField = document.getElementById('beneficios');
            const beneficios = beneficiosField.value
                .split('\n')
                .map(b => b.trim())
                .filter(b => b.length > 0)
                .join('|');
            beneficiosField.value = beneficios;
        });
    </script>
</body>
</html>
