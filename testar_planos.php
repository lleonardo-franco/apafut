<?php
require_once 'config/db.php';

try {
    $pdo = getConnection();
    $pdo->exec("SET NAMES utf8mb4");
    
    echo "=== PLANOS NO BANCO DE DADOS ===\n\n";
    
    $stmt = $pdo->query("SELECT id, nome, tipo, preco_anual, parcelas, beneficios, ordem, ativo, destaque FROM planos ORDER BY ordem ASC");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: " . $row['id'] . "\n";
        echo "Nome: " . $row['nome'] . "\n";
        echo "Tipo: " . $row['tipo'] . "\n";
        echo "Preço Anual: R$ " . number_format($row['preco_anual'], 2, ',', '.') . "\n";
        echo "Parcelas: " . $row['parcelas'] . "x\n";
        echo "Ordem: " . $row['ordem'] . "\n";
        echo "Ativo: " . ($row['ativo'] ? 'SIM' : 'NÃO') . "\n";
        echo "Destaque: " . ($row['destaque'] ? 'SIM' : 'NÃO') . "\n";
        echo "Benefícios:\n";
        $beneficios = explode('|', $row['beneficios']);
        foreach ($beneficios as $beneficio) {
            echo "  - " . trim($beneficio) . "\n";
        }
        echo "\n" . str_repeat('-', 50) . "\n\n";
    }
    
    echo "\n=== TESTE DE CRIAÇÃO ===\n";
    echo "Tentando criar um novo plano de teste...\n\n";
    
    $stmt = $pdo->prepare("INSERT INTO planos (nome, tipo, preco_anual, parcelas, beneficios, ordem, ativo, destaque) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([
        'Teste Plano Bronze',
        'Bronze',
        150.00,
        2,
        'Benefício 1|Benefício 2|Benefício 3',
        4,
        0, // Inativo para não aparecer no site
        0
    ]);
    
    if ($result) {
        echo "✅ Plano criado com sucesso! ID: " . $pdo->lastInsertId() . "\n";
    } else {
        echo "❌ Erro ao criar plano\n";
    }
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}
