<?php
require_once 'config/db.php';
require_once 'src/Security.php';

try {
    $conn = getConnection();
    
    echo "=== CONFIGURAÇÃO DO PAINEL ADMIN ===\n\n";
    
    // Criar tabela de usuários admin
    echo "1. Criando tabela usuarios_admin...\n";
    $sql = "CREATE TABLE IF NOT EXISTS usuarios_admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        senha VARCHAR(255) NOT NULL,
        nivel_acesso ENUM('admin', 'editor') DEFAULT 'editor',
        ativo TINYINT(1) DEFAULT 1,
        ultimo_acesso DATETIME NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_ativo (ativo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "   ✓ Tabela criada com sucesso!\n\n";
    
    // Verificar se usuário admin já existe
    echo "2. Verificando usuário admin...\n";
    $stmt = $conn->query("SELECT id, email FROM usuarios_admin WHERE email = 'admin@apafut.com.br'");
    $usuario = $stmt->fetch();
    
    // Gerar hash da senha
    $senha = 'admin123';
    $hash = Security::hashPassword($senha);
    
    if ($usuario) {
        echo "   Usuário já existe. Atualizando senha...\n";
        $stmt = $conn->prepare("UPDATE usuarios_admin SET senha = :senha, ativo = 1 WHERE email = 'admin@apafut.com.br'");
        $stmt->bindParam(':senha', $hash);
        $stmt->execute();
        echo "   ✓ Senha atualizada!\n\n";
    } else {
        echo "   Criando novo usuário admin...\n";
        $stmt = $conn->prepare("INSERT INTO usuarios_admin (nome, email, senha, nivel_acesso) VALUES ('Administrador', 'admin@apafut.com.br', :senha, 'admin')");
        $stmt->bindParam(':senha', $hash);
        $stmt->execute();
        echo "   ✓ Usuário criado com sucesso!\n\n";
    }
    
    echo "=================================\n";
    echo "CONFIGURAÇÃO CONCLUÍDA!\n";
    echo "=================================\n\n";
    echo "CREDENCIAIS DE ACESSO:\n";
    echo "Email: admin@apafut.com.br\n";
    echo "Senha: admin123\n";
    echo "\nAcesse: http://localhost:5500/admin/\n";
    echo "=================================\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    echo "\nVerifique se:\n";
    echo "1. O banco de dados 'apafut_db' existe\n";
    echo "2. As credenciais em .env estão corretas\n";
    echo "3. O servidor MySQL está rodando\n";
}
