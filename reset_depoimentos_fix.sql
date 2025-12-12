SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

DELETE FROM depoimentos;

INSERT INTO depoimentos (nome, descricao, video, ordem, ativo) VALUES 
('João Silva', 'Pai do atleta Pedro Silva, categoria sub-15', '../assets/videos/exemplo1.mp4', 0, 1),
('Maria Santos', 'Mãe da atleta Ana Santos, categoria sub-13', '../assets/videos/exemplo2.mp4', 1, 1),
('Carlos Oliveira', 'Ex-atleta formado pela Apafut', '../assets/videos/exemplo3.mp4', 2, 1);

SELECT id, nome, descricao FROM depoimentos;