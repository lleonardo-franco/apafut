-- Tabela de comissão técnica
CREATE TABLE IF NOT EXISTS comissao_tecnica (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cargo VARCHAR(100) NOT NULL,
    foto VARCHAR(255),
    descricao TEXT,
    ativo TINYINT(1) DEFAULT 1,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir dados exemplo da comissão técnica
INSERT INTO comissao_tecnica (nome, cargo, foto, descricao, ordem) VALUES
('Carlos Mendes', 'Técnico Principal', 'assets/images/comissao/tecnico.jpg', 'Experiente treinador com mais de 15 anos de carreira', 1),
('João Silva', 'Auxiliar Técnico', 'assets/images/comissao/auxiliar.jpg', 'Especialista em táticas e análise de adversários', 2),
('Pedro Santos', 'Preparador Físico', 'assets/images/comissao/preparador.jpg', 'Graduado em Educação Física com especialização em alto rendimento', 3),
('Ana Costa', 'Fisioterapeuta', 'assets/images/comissao/fisio.jpg', 'Responsável pela recuperação e prevenção de lesões', 4);
