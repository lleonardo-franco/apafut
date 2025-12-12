-- Criar tabela de jogadores
CREATE TABLE IF NOT EXISTS jogadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    posicao VARCHAR(50) NOT NULL,
    numero INT NOT NULL,
    foto VARCHAR(500),
    ativo TINYINT(1) DEFAULT 1,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir alguns jogadores de exemplo
INSERT INTO jogadores (nome, posicao, numero, foto, ativo, ordem) VALUES
('Carlos Silva', 'Meio Campo', 10, '../assets/jogador1.jpg', 1, 1),
('Rafael Santos', 'Atacante', 7, '../assets/jogador2.jpg', 1, 2),
('Lucas Oliveira', 'Zagueiro', 3, '../assets/jogador3.jpg', 1, 3),
('Pedro Santos', 'Zagueiro', 2, '../assets/jogador4.jpg', 1, 4),
('João Silva', 'Goleiro', 1, '../assets/jogador5.jpg', 1, 5),
('Fernando Alves', 'Meio Campo', 8, '../assets/jogador6.jpg', 1, 6),
('Gabriel Rodrigues', 'Atacante', 9, '../assets/jogador7.jpg', 1, 7),
('Matheus Lima', 'Volante', 5, '../assets/jogador8.jpg', 1, 8);
