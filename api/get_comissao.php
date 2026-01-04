<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../config/db.php';

try {
    $conn = getConnection();
    
    // Buscar membros da comissão técnica ativos ordenados
    $stmt = $conn->prepare("SELECT * FROM comissao_tecnica WHERE ativo = 1 ORDER BY ordem ASC, id ASC");
    $stmt->execute();
    $comissao = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $comissao
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao buscar comissão técnica'
    ], JSON_UNESCAPED_UNICODE);
}
