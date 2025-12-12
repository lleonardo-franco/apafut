<?php
require_once 'auth.php';
Auth::require();

$user = Auth::user();
$jogadorId = Security::validateInt($_GET['id'] ?? 0, 1);

if (!$jogadorId) {
    header('Location: jogadores.php');
    exit;
}

try {
    $conn = getConnection();
    
    // Buscar jogador
    $stmt = $conn->prepare("SELECT * FROM jogadores WHERE id = :id");
    $stmt->bindParam(':id', $jogadorId);
    $stmt->execute();
    $jogador = $stmt->fetch();
    
    if (!$jogador) {
        header('Location: jogadores.php?error=notfound');
        exit;
    }
    
    // Deletar foto se existir
    if (!empty($jogador['foto']) && file_exists('../' . $jogador['foto'])) {
        unlink('../' . $jogador['foto']);
    }
    
    // Deletar jogador
    $stmt = $conn->prepare("DELETE FROM jogadores WHERE id = :id");
    $stmt->bindParam(':id', $jogadorId);
    $stmt->execute();
    
    logError('Jogador excluído', [
        'id' => $jogadorId,
        'nome' => $jogador['nome'],
        'user' => $user['email']
    ]);
    
    header('Location: jogadores.php?success=deleted');
    exit;
    
} catch (Exception $e) {
    logError('Erro ao excluir jogador', [
        'id' => $jogadorId,
        'error' => $e->getMessage()
    ]);
    header('Location: jogadores.php?error=delete');
    exit;
}
