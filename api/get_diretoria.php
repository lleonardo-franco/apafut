<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../config/db.php';

try {
    $conn = getConnection();
    
    // Buscar apenas membros ativos, ordenados
    $stmt = $conn->prepare("SELECT id, nome, cargo, foto FROM diretoria WHERE ativo = 1 ORDER BY ordem ASC, id ASC");
    $stmt->execute();
    $diretoria = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'diretoria' => $diretoria
    ]);
    
} catch (PDOException $e) {
    error_log('Erro ao buscar diretoria: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao buscar diretoria'
    ]);
}
