<?php
require_once 'auth.php';
require_once '../src/Cache.php';
Auth::require();

$user = Auth::user();
$membroId = Security::validateInt($_GET['id'] ?? 0, 1);

if (!$membroId) {
    header('Location: comissao.php');
    exit;
}

try {
    $conn = getConnection();
    
    // Buscar membro
    $stmt = $conn->prepare("SELECT * FROM comissao_tecnica WHERE id = :id");
    $stmt->bindParam(':id', $membroId);
    $stmt->execute();
    $membro = $stmt->fetch();
    
    if (!$membro) {
        header('Location: comissao.php?error=notfound');
        exit;
    }
    
    // Deletar foto se existir
    if (!empty($membro['foto']) && file_exists($membro['foto'])) {
        unlink($membro['foto']);
    }
    
    // Deletar membro
    $stmt = $conn->prepare("DELETE FROM comissao_tecnica WHERE id = :id");
    $stmt->bindParam(':id', $membroId);
    $stmt->execute();
    
    // Limpar cache
    Cache::delete('comissao_tecnica_ativos');
    
    logError('Membro da comissão excluído', [
        'id' => $membroId,
        'nome' => $membro['nome'],
        'user' => $user['email']
    ]);
    
    header('Location: comissao.php?success=deleted');
    exit;
    
} catch (Exception $e) {
    logError('Erro ao excluir membro da comissão', [
        'id' => $membroId,
        'error' => $e->getMessage()
    ]);
    header('Location: comissao.php?error=delete');
    exit;
}
