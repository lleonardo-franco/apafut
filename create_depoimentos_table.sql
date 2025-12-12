-- Criar tabela de depoimentos
CREATE TABLE IF NOT EXISTS depoimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    video VARCHAR(500) NOT NULL,
    ativo TINYINT(1) DEFAULT 1,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir alguns depoimentos de exemplo
INSERT INTO depoimentos (nome, descricao, video, ativo, ordem) VALUES
('João Silva', 'Pai do atleta Pedro Silva, categoria sub-15', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1, 1),
('Maria Santos', 'Mãe da atleta Ana Santos, categoria sub-13', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1, 2),
('Carlos Oliveira', 'Ex-atleta formado pela Apafut', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1, 3);
