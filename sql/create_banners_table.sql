-- Tabela de banners
CREATE TABLE IF NOT EXISTS banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    imagem VARCHAR(255) NOT NULL,
    ordem INT DEFAULT 0,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir banners padrão
INSERT INTO banners (titulo, imagem, ordem, ativo) VALUES
('Banner Principal 1', 'assets/images/banner1.jpg', 1, 1),
('Banner Principal 2', 'assets/images/banner2.jpg', 2, 1),
('Banner Principal 3', 'assets/images/banner3.jpg', 3, 1);
