<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

try {
    $conn = getConnection();
    
    // Parâmetros opcionais
    $categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 3;
    
    // Construir query baseada nos parâmetros
    if ($categoria) {
        $stmt = $conn->prepare("SELECT * FROM noticias WHERE ativo = 1 AND categoria = :categoria ORDER BY data_publicacao DESC LIMIT :limit");
        $stmt->bindParam(':categoria', $categoria, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    } else {
        $stmt = $conn->prepare("SELECT * FROM noticias WHERE ativo = 1 AND destaque = 1 ORDER BY ordem ASC, data_publicacao DESC LIMIT :limit");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    $noticias = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'noticias' => $noticias
    ], JSON_UNESCAPED_UNICODE);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
