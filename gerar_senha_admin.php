<?php
require_once 'config/db.php';
require_once 'src/Security.php';

// Gerar hash da senha
$senha = 'admin123';
$hash = Security::hashPassword($senha);

echo "Hash gerado para senha 'admin123':\n";
echo $hash . "\n\n";

// Atualizar no banco
try {
    $conn = getConnection();
    
    // Verificar se usuário existe
    $stmt = $conn->query("SELECT id, email FROM usuarios_admin WHERE email = 'admin@apafut.com.br'");
    $usuario = $stmt->fetch();
    
    if ($usuario) {
        echo "Usuário encontrado: {$usuario['email']}\n";
        echo "Atualizando senha...\n";
        
        $stmt = $conn->prepare("UPDATE usuarios_admin SET senha = :senha WHERE email = 'admin@apafut.com.br'");
        $stmt->bindParam(':senha', $hash);
        $stmt->execute();
        
        echo "Senha atualizada com sucesso!\n";
    } else {
        echo "Usuário não encontrado. Criando...\n";
        
        $stmt = $conn->prepare("INSERT INTO usuarios_admin (nome, email, senha, nivel_acesso) VALUES ('Administrador', 'admin@apafut.com.br', :senha, 'admin')");
        $stmt->bindParam(':senha', $hash);
        $stmt->execute();
        
        echo "Usuário criado com sucesso!\n";
    }
    
    echo "\n=== CREDENCIAIS DE ACESSO ===\n";
    echo "Email: admin@apafut.com.br\n";
    echo "Senha: admin123\n";
    echo "=============================\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
