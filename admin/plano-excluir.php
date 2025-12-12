<?php
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
require_once '../config/db.php';
require_once '../src/Cache.php';
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    
    if ($id > 0) {
        try {
            $pdo = getConnection();
            
            // Buscar plano antes de excluir
            $stmt = $pdo->prepare("SELECT nome FROM planos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $plano = $stmt->fetch();
            
            if ($plano) {
                // Excluir plano
                $stmt = $pdo->prepare("DELETE FROM planos WHERE id = :id");
                $stmt->execute([':id' => $id]);
                
                // Limpar cache de planos
                Cache::delete('planos_ativos');
                
                header('Location: planos.php?sucesso=excluido');
                exit;
            } else {
                header('Location: planos.php?erro=nao_encontrado');
                exit;
            }
            
        } catch (Exception $e) {
            header('Location: planos.php?erro=exclusao');
            exit;
        }
    }
}

header('Location: planos.php');
exit;
