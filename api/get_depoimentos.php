<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM depoimentos WHERE ativo = 1 ORDER BY ordem ASC, id ASC");
    $stmt->execute();
    $depoimentos = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $depoimentos
    ], JSON_UNESCAPED_UNICODE);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
