-- Corrigir encoding das notícias
USE apafut_db;

-- Deletar notícias com encoding errado
DELETE FROM noticias;

-- Reinserir com encoding correto
INSERT INTO noticias (id, titulo, categoria, resumo, conteudo, data_publicacao, imagem, autor, tempo_leitura, ativo, destaque, ordem) VALUES
(1, 'Apafut conquista título inédito do Regional da Serra', 'Campeonatos', 
'Em uma final emocionante, o time Sub-17 da Apafut sagrou-se campeão regional após vencer nos pênaltis. A equipe mostrou garra e determinação durante toda a competição.', 
'Em uma disputa emocionante, o time Sub-17 da Apafut mostrou toda sua determinação e técnica para conquistar o título inédito do Regional da Serra. A final foi decidida nos pênaltis após um empate de 2 a 2 no tempo normal.

Durante toda a competição, o time demonstrou evolução constante. Com 8 vitórias, 2 empates e apenas 1 derrota na fase de grupos, a equipe garantiu a classificação com folga para as fases eliminatórias.

O técnico Fernando Costa destacou o comprometimento dos atletas: "Foi uma campanha memorável. Os garotos trabalharam duro e mereceram esse título. Estou muito orgulhoso de cada um."

O jogo decisivo foi marcado por grande emoção. A Apafut abriu o placar logo aos 5 minutos com gol de Carlos Silva. O adversário empatou antes do intervalo, mas no segundo tempo a equipe voltou a ficar na frente com golaço de Bruno Ferreira.

Nos minutos finais, o adversário conseguiu o empate e levou a decisão para os pênaltis. Na disputa, o goleiro Thiago Costa foi o grande herói, defendendo duas cobranças e garantindo o título para a academia.

Este é o primeiro título regional da categoria Sub-17 da Apafut, marcando uma nova era de conquistas para o clube.', 
'2025-12-05', 'assets/hero.png', 'Redação Apafut', 5, 1, 1, 1),

(2, 'Peneiras 2026: Apafut abre inscrições para categorias de base', 'Categorias de Base', 
'Estão abertas as inscrições para as peneiras da Apafut. Jovens talentos de 8 a 17 anos podem se inscrever e fazer parte do nosso time. Vagas limitadas!', 
'A Apafut anuncia a abertura das inscrições para as peneiras 2026, oferecendo oportunidades para jovens talentos de 8 a 17 anos que sonham em fazer parte de uma das academias de futebol mais conceituadas da região.

As peneiras acontecerão durante o mês de janeiro de 2026 e serão divididas por categorias, garantindo uma avaliação justa e adequada para cada faixa etária. Os jovens atletas serão avaliados por uma equipe técnica experiente.

O coordenador técnico Prof. Ricardo Oliveira explica: "Buscamos não apenas habilidade técnica, mas também comprometimento, disciplina e vontade de evoluir. Queremos atletas que estejam dispostos a se dedicar ao desenvolvimento completo."

As vagas são limitadas e as inscrições podem ser feitas através do site da Apafut ou presencialmente na secretaria da academia. Os pré-requisitos incluem apresentação de documentos, autorização dos responsáveis e atestado médico.

Durante as peneiras, os candidatos passarão por testes técnicos, táticos e físicos. Aqueles que forem aprovados terão a oportunidade de integrar as categorias de base da Apafut e contar com toda a infraestrutura de treinamento.

A academia oferece horários flexíveis de treino, acompanhamento profissional e a chance de participar de competições estaduais e regionais. Não perca essa oportunidade de fazer parte do nosso time!', 
'2025-12-01', 'assets/noticia2.jpg', 'Redação Apafut', 5, 1, 1, 2),

(3, 'Inauguração do novo campo de treino com grama sintética', 'Infraestrutura', 
'A Apafut inaugura mais um campo de treino com grama sintética de última geração. O investimento visa proporcionar ainda mais qualidade na preparação dos atletas.', 
'A Apafut inaugura mais um investimento em infraestrutura com a entrega do novo campo de treino com grama sintética de última geração. O investimento de mais de R$ 500 mil visa proporcionar ainda mais qualidade na preparação dos atletas.

O novo campo possui dimensões oficiais (105m x 68m) e utiliza tecnologia de ponta em grama sintética, proporcionando melhor desempenho, segurança e conforto aos atletas. O sistema de drenagem garante uso mesmo em dias de chuva.

Segundo o presidente da Apafut, João Silva: "Este é mais um passo importante no nosso compromisso com a formação de atletas de alto nível. A infraestrutura de qualidade é fundamental para o desenvolvimento técnico e tático."

Além da grama sintética, o campo conta com iluminação LED de alta performance, permitindo treinos noturnos, e arquibancadas com capacidade para 200 pessoas. O espaço também possui vestiários modernos e sala de análise de vídeo.

O campo será utilizado principalmente pelas categorias Sub-15 e Sub-17, mas todas as categorias de base terão acesso ao novo espaço de acordo com a programação de treinos. A inauguração oficial acontecerá no próximo sábado com um amistoso festivo.

Com este novo campo, a Apafut passa a contar com três campos de treino de alta qualidade, consolidando-se como referência em infraestrutura para formação de atletas na região da Serra Gaúcha.', 
'2025-11-28', 'assets/noticia3.jpg', 'Redação Apafut', 5, 1, 1, 3);
