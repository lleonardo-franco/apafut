-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS u754804453_apafut CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE u754804453_apafut;

-- Tabela de jogadores
CREATE TABLE IF NOT EXISTS jogadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    posicao VARCHAR(50) NOT NULL,
    numero INT NOT NULL,
    foto VARCHAR(255),
    ativo TINYINT(1) DEFAULT 1,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de notícias
CREATE TABLE IF NOT EXISTS noticias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    resumo TEXT NOT NULL,
    conteudo TEXT,
    data_publicacao DATE NOT NULL,
    imagem VARCHAR(255) DEFAULT 'assets/noticia-default.jpg',
    autor VARCHAR(100) DEFAULT 'Redação Apafut',
    tempo_leitura INT DEFAULT 5,
    ativo TINYINT(1) DEFAULT 1,
    destaque TINYINT(1) DEFAULT 0,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de categorias de base
CREATE TABLE IF NOT EXISTS categorias_base (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    faixa_etaria VARCHAR(50) NOT NULL,
    horarios TEXT,
    objetivo TEXT,
    treinador VARCHAR(100),
    ativo TINYINT(1) DEFAULT 1,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de depoimentos
CREATE TABLE IF NOT EXISTS depoimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    depoimento TEXT NOT NULL,
    video VARCHAR(255) NULL,
    ativo TINYINT(1) DEFAULT 1,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de planos
CREATE TABLE IF NOT EXISTS planos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    preco_anual DECIMAL(10,2) NOT NULL,
    parcelas INT DEFAULT 2,
    beneficios TEXT NOT NULL,
    destaque TINYINT(1) DEFAULT 0,
    ativo TINYINT(1) DEFAULT 1,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de campeonatos
CREATE TABLE IF NOT EXISTS campeonatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    logo VARCHAR(255),
    ativo TINYINT(1) DEFAULT 1,
    ordem INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir dados dos jogadores
INSERT INTO jogadores (nome, posicao, numero, foto, ordem) VALUES
('Carlos Silva', 'Atacante', 10, 'assets/jogador1.jpg', 1),
('Rafael Santos', 'Meio-Campo', 7, 'assets/jogador2.jpg', 2),
('Lucas Oliveira', 'Zagueiro', 3, 'assets/jogador3.jpg', 3),
('Thiago Costa', 'Goleiro', 1, 'assets/jogador4.jpg', 4),
('Fernando Lima', 'Volante', 8, 'assets/jogador5.jpg', 5),
('Gabriel Souza', 'Ponta', 11, 'assets/jogador6.jpg', 6),
('Rodrigo Alves', 'Lateral', 4, 'assets/jogador7.jpg', 7),
('Bruno Ferreira', 'Atacante', 9, 'assets/jogador8.jpg', 8);

-- Inserir dados das notícias
INSERT INTO noticias (titulo, categoria, resumo, data_publicacao, imagem, destaque, ordem) VALUES
('Apafut conquista título inédito do Regional da Serra', 'Campeonatos', 'Em uma final emocionante, o time Sub-17 da Apafut sagrou-se campeão regional após vencer nos pênaltis. A equipe mostrou garra e determinação durante toda a competição.', '2025-12-05', 'assets/hero.png', 1, 1),
('Peneiras 2026: Apafut abre inscrições para categorias de base', 'Categorias de Base', 'Estão abertas as inscrições para as peneiras da Apafut. Jovens talentos de 8 a 17 anos podem se inscrever e fazer parte do nosso time. Vagas limitadas!', '2025-12-01', 'assets/noticia2.jpg', 1, 2),
('Inauguração do novo campo de treino com grama sintética', 'Infraestrutura', 'A Apafut inaugura mais um campo de treino com grama sintética de última geração. O investimento visa proporcionar ainda mais qualidade na preparação dos atletas.', '2025-11-28', 'assets/noticia3.jpg', 1, 3);

-- Inserir dados dos depoimentos
INSERT INTO depoimentos (nome, depoimento, ordem) VALUES
('Maria S.', 'A Apafut tem sido fundamental no desenvolvimento do meu filho. A equipe é dedicada e o ambiente é muito positivo.', 1),
('Carlos M.', 'Meu filho adora treinar na Apafut. Ele está sempre motivado e feliz, o que é o mais importante para nós.', 2),
('Fernanda R.', 'Os treinadores são muito atenciosos e realmente se importam com o desenvolvimento dos atletas. Excelente academia!', 3),
('Pedro C.', 'O ambiente de treinamento na Apafut é muito positivo e estimulante. Os atletas se divertem e aprendem muito.', 4);

-- Inserir dados dos planos
INSERT INTO planos (nome, tipo, preco_anual, parcelas, beneficios, destaque, ordem) VALUES
('Sócio APA Prata', 'Prata', 200.00, 2, 'Jantar de fim de temporada|Descontos com parceiros|Carteirinha de sócio|Newsletter exclusiva', 0, 1),
('Sócio APA Ouro', 'Ouro', 300.00, 2, 'Camiseta oficial exclusiva|Jantar de fim de temporada|Descontos com parceiros|Carteirinha de sócio premium|Newsletter exclusiva|Acesso prioritário a eventos|Conteúdo exclusivo dos bastidores', 1, 2);

-- Inserir dados dos campeonatos
INSERT INTO campeonatos (nome, descricao, logo, ordem) VALUES
('Campeonato Gaúcho', 'Principal competição estadual do Rio Grande do Sul', 'assets/gauchao.png', 1),
('Copa FGF', 'Torneio oficial da Federação Gaúcha de Futebol', 'assets/fgf.png', 2),
('Campeonato Regional', 'Competição da região da Serra Gaúcha', 'assets/regional.png', 3),
('Copa Caxias', 'Torneio municipal com times da cidade', 'assets/copa_caxias.png', 4);
