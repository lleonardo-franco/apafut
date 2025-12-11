<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    $conn = getConnection();
    
    // Buscar notícias em destaque ordenadas
    $stmt = $conn->prepare("SELECT * FROM noticias WHERE ativo = 1 AND destaque = 1 ORDER BY ordem ASC, data_publicacao DESC LIMIT 3");
    $stmt->execute();
    $noticias = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $noticias
    ], JSON_UNESCAPED_UNICODE);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
