-- Depoimentos de Exemplo para Teste
-- Execute este arquivo após aplicar a migração add_depoimento_video_url.sql

-- Limpar depoimentos antigos (opcional - comente se quiser manter)
-- TRUNCATE TABLE depoimentos;

-- Depoimento 1: YouTube com texto
INSERT INTO depoimentos (nome, depoimento, video_url, tipo_depoimento, ordem, ativo) VALUES 
('Maria Silva', 'A APAFUT transformou a vida do meu filho! Ele começou tímido e hoje é um líder em campo. A estrutura e os profissionais são excepcionais.', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'video_url', 1, 1);

-- Depoimento 2: Apenas texto
INSERT INTO depoimentos (nome, depoimento, tipo_depoimento, ordem, ativo) VALUES 
('João Santos', 'Meu filho estava sem foco, mas na APAFUT ele encontrou disciplina, amigos e um sonho. Hoje treina com garra todos os dias e vejo o quanto evoluiu tecnicamente e como pessoa.', 'texto', 2, 1);

-- Depoimento 3: Vimeo com texto
INSERT INTO depoimentos (nome, depoimento, video_url, tipo_depoimento, ordem, ativo) VALUES 
('Pedro Oliveira', 'Experiência incrível! A metodologia de treino e o acompanhamento personalizado fazem toda a diferença no desenvolvimento dos atletas.', 'https://vimeo.com/148751763', 'video_url', 3, 1);

-- Depoimento 4: Apenas texto (ex-jogador)
INSERT INTO depoimentos (nome, depoimento, tipo_depoimento, ordem, ativo) VALUES 
('Carlos Eduardo', 'Passei pela base da APAFUT e hoje sou jogador profissional. Tudo que aprendi sobre disciplina, trabalho em equipe e técnica, foi aqui. Gratidão eterna!', 'texto', 4, 1);

-- Depoimento 5: YouTube (aluno)
INSERT INTO depoimentos (nome, depoimento, video_url, tipo_depoimento, ordem, ativo) VALUES 
('Ana Paula Costa', 'Minha filha sempre sonhou em jogar futebol. Na APAFUT ela encontrou um ambiente acolhedor e profissional. Hoje joga no time feminino com muito orgulho!', 'https://www.youtube.com/watch?v=jNQXAC9IVRw', 'video_url', 5, 1);

-- Depoimento 6: Apenas texto (pai)
INSERT INTO depoimentos (nome, depoimento, tipo_depoimento, ordem, ativo) VALUES 
('Roberto Mendes', 'O que mais me impressiona é o cuidado que a equipe tem com cada atleta. Não é só futebol, é formação de caráter. Meu filho cresceu muito aqui.', 'texto', 6, 1);
