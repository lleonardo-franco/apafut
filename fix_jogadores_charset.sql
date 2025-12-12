-- Corrigir charset dos jogadores
SET NAMES utf8mb4;

DELETE FROM jogadores;
ALTER TABLE jogadores AUTO_INCREMENT = 1;

INSERT INTO jogadores (nome, posicao, numero, foto, ativo, ordem) VALUES
('Carlos Silva', 'Meio Campo', 10, '../assets/jogador1.jpg', 1, 1),
('Rafael Santos', 'Atacante', 7, '../assets/jogador2.jpg', 1, 2),
('Lucas Oliveira', 'Zagueiro', 3, '../assets/jogador3.jpg', 1, 3),
('Pedro Santos', 'Zagueiro', 2, '../assets/jogador4.jpg', 1, 4),
('João Silva', 'Goleiro', 1, '../assets/jogador5.jpg', 1, 5),
('Fernando Alves', 'Meio Campo', 8, '../assets/jogador6.jpg', 1, 6),
('Gabriel Rodrigues', 'Atacante', 9, '../assets/jogador7.jpg', 1, 7),
('Matheus Lima', 'Volante', 5, '../assets/jogador8.jpg', 1, 8);
