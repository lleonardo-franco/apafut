-- Criar tabela de usuários administrativos
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir usuário admin padrão
-- Email: admin@apafut.com.br
-- Senha: admin123 (hash gerado com password_hash)
INSERT INTO usuarios_admin (email, senha, nome, ativo) 
VALUES (
    'admin@apafut.com.br',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Administrador',
    1
) ON DUPLICATE KEY UPDATE 
    senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    nome = 'Administrador',
    ativo = 1;

-- Verificar se o usuário foi criado
SELECT id, email, nome, ativo, created_at FROM usuarios_admin WHERE email = 'admin@apafut.com.br';
