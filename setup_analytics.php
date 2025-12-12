<?php
require_once 'config/db.php';

$sql = file_get_contents('create_analytics_table.sql');

try {
    $pdo = getConnection();
    $pdo->exec($sql);
    echo "✅ Tabela analytics_pageviews criada com sucesso!\n";
} catch (PDOException $e) {
    echo "❌ Erro ao criar tabela: " . $e->getMessage() . "\n";
}
