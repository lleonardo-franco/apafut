-- Selecionar banco de dados
USE apafut_db;

-- Criar tabela de usuários administrativos
CREATE TABLE IF NOT EXISTS usuarios_admin (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir usuário admin padrão
-- Senha: admin123 (ALTERAR APÓS PRIMEIRO LOGIN!)
INSERT INTO usuarios_admin (nome, email, senha, nivel_acesso) 
VALUES ('Administrador', 'admin@apafut.com.br', '$argon2id$v=19$m=65536,t=4,p=1$VGhpc0lzQVNhbHRGb3JUZXN0$qXzJ3qYvKZ2LXwHY9pJ+4Qh3N/wF5R7jD9kS2tQ8Gho', 'admin');

-- IMPORTANTE: Após o primeiro login, altere a senha!
-- Para gerar nova senha, use: Security::hashPassword('sua_nova_senha')
