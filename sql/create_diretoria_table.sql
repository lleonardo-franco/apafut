-- Tabela para Diretoria
CREATE TABLE IF NOT EXISTS diretoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cargo VARCHAR(255) NOT NULL,
    foto VARCHAR(500),
    ordem INT DEFAULT 0,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ordem (ordem),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir membros exemplo (baseado na imagem)
INSERT INTO diretoria (nome, cargo, foto, ordem, ativo) VALUES
('Fábio Pizzamiglio', 'Presidente', 'assets/diretoria/fabio.jpg', 1, 1),
('Paulo Stumpf', '1º Vice-Presidente / Comitê Gestor de Futebol', 'assets/diretoria/paulo.jpg', 2, 1),
('Luis Carlos Bianchi', 'Vice-Presidente de Futebol / Comitê Gestor de Futebol', 'assets/diretoria/luis.jpg', 3, 1),
('Leonardo Tonietto', 'Vice-Presidente Adm. e Fin.', 'assets/diretoria/leonardo.jpg', 4, 1),
('Rafael Bellei', 'Vice-Presidente de Patrimônio', 'assets/diretoria/rafael.jpg', 5, 1),
('Bruno Zaballa', 'Vice-Presidente de Marketing', 'assets/diretoria/bruno.jpg', 6, 1),
('Almir Adami', 'Diretor do Comitê Gestor de Futebol', 'assets/diretoria/almir.jpg', 7, 1),
('Adão Marques', 'Diretor do Comitê Gestor de Futebol', 'assets/diretoria/adao.jpg', 8, 1);
