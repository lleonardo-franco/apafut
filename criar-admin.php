<?php
/**
 * Script para criar tabela usuarios_admin e usuário padrão
 * 
 * Execute este arquivo UMA VEZ no navegador:
 * https://apafutoficial.com.br/criar-admin.php
 * 
 * IMPORTANTE: DELETE este arquivo após executar!
 */

require_once 'config/db.php';

header('Content-Type: text/html; charset=utf-8');

// Senha que será usada (admin123)
$senha_padrao = 'admin123';
$senha_hash = password_hash($senha_padrao, PASSWORD_DEFAULT);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Usuário Admin - APAFUT</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 700px;
            width: 100%;
            padding: 40px;
        }
        h1 { color: #1a202c; font-size: 28px; margin-bottom: 24px; text-align: center; }
        .status {
            background: #f7fafc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .success { background: #c6f6d5; color: #22543d; padding: 16px; border-radius: 8px; margin: 16px 0; }
        .error { background: #fed7d7; color: #742a2a; padding: 16px; border-radius: 8px; margin: 16px 0; }
        .info { background: #bee3f8; color: #2c5282; padding: 16px; border-radius: 8px; margin: 16px 0; }
        .warning { background: #fef5e7; color: #7d6608; padding: 16px; border-radius: 8px; margin: 16px 0; }
        code {
            background: #2d3748;
            color: #68d391;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
        .credentials {
            background: #2d3748;
            color: white;
            padding: 16px;
            border-radius: 8px;
            margin: 16px 0;
            font-family: 'Courier New', monospace;
            line-height: 1.8;
        }
        .credentials strong { color: #68d391; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Criar Tabela e Usuário Admin</h1>
        
        <?php
        try {
            $pdo = getConnection();
            
            // Criar tabela usuarios_admin
            $sql_create_table = "
                CREATE TABLE IF NOT EXISTS usuarios_admin (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    senha VARCHAR(255) NOT NULL,
                    nome VARCHAR(100) NOT NULL,
                    ativo TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_email (email),
                    INDEX idx_ativo (ativo)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            
            $pdo->exec($sql_create_table);
            echo '<div class="success"><strong>✅ Tabela usuarios_admin criada com sucesso!</strong></div>';
            
            // Inserir usuário admin
            $sql_insert = "
                INSERT INTO usuarios_admin (email, senha, nome, ativo) 
                VALUES (:email, :senha, :nome, 1)
                ON DUPLICATE KEY UPDATE 
                    senha = :senha,
                    nome = :nome,
                    ativo = 1
            ";
            
            $stmt = $pdo->prepare($sql_insert);
            $stmt->execute([
                ':email' => 'admin@apafut.com.br',
                ':senha' => $senha_hash,
                ':nome' => 'Administrador'
            ]);
            
            echo '<div class="success"><strong>✅ Usuário admin criado/atualizado com sucesso!</strong></div>';
            
            // Verificar usuário
            $stmt = $pdo->query("SELECT id, email, nome, ativo, created_at FROM usuarios_admin WHERE email = 'admin@apafut.com.br'");
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin) {
                echo '<div class="info">';
                echo '<strong>📊 Dados do Usuário Admin:</strong><br>';
                echo 'ID: ' . $admin['id'] . '<br>';
                echo 'Email: ' . htmlspecialchars($admin['email']) . '<br>';
                echo 'Nome: ' . htmlspecialchars($admin['nome']) . '<br>';
                echo 'Ativo: ' . ($admin['ativo'] ? 'Sim' : 'Não') . '<br>';
                echo 'Criado em: ' . $admin['created_at'];
                echo '</div>';
                
                echo '<div class="credentials">';
                echo '<strong>🔑 Credenciais de Login:</strong><br>';
                echo '<strong>Email:</strong> admin@apafut.com.br<br>';
                echo '<strong>Senha:</strong> admin123<br>';
                echo '<strong>URL:</strong> https://apafutoficial.com.br/admin/';
                echo '</div>';
            }
            
            echo '<div class="warning">';
            echo '<strong>⚠️ IMPORTANTE - SEGURANÇA:</strong><br>';
            echo '1. <strong>DELETE este arquivo (criar-admin.php) IMEDIATAMENTE!</strong><br>';
            echo '2. Faça login no painel admin<br>';
            echo '3. Altere a senha padrão por uma senha forte<br>';
            echo '4. Considere criar outros usuários e remover o admin padrão';
            echo '</div>';
            
            echo '<div class="info">';
            echo '<strong>📝 Como deletar este arquivo:</strong><br>';
            echo '1. Acesse o File Manager da Hostinger<br>';
            echo '2. Navegue até /public_html/<br>';
            echo '3. Delete o arquivo <code>criar-admin.php</code><br>';
            echo 'OU use FTP/SFTP para removê-lo';
            echo '</div>';
            
        } catch (PDOException $e) {
            echo '<div class="error">';
            echo '<strong>❌ Erro ao criar tabela/usuário:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
            
            echo '<div class="info">';
            echo '<strong>💡 Possíveis soluções:</strong><br>';
            echo '1. Verifique se o arquivo config/db.php existe<br>';
            echo '2. Confirme as credenciais do banco de dados<br>';
            echo '3. Verifique se o banco de dados existe<br>';
            echo '4. Confirme se o usuário do BD tem permissões para CREATE TABLE';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>
