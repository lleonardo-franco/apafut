<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../src/Cache.php';
require_once 'auth.php';

Auth::require();

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = intval($data['id'] ?? 0);
    $status = $data['status'] ?? '';
    
    if ($id <= 0 || !in_array($status, ['aprovado', 'cancelado', 'expirado'])) {
        throw new Exception('Dados inválidos');
    }
    
    $pdo = getConnection();
    $stmt = $pdo->prepare("UPDATE assinaturas SET status = :status WHERE id = :id");
    $stmt->execute([
        ':status' => $status,
        ':id' => $id
    ]);
    
    // Limpar cache de planos se houver
    Cache::delete('planos_ativos');
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
