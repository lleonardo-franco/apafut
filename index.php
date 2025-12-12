<?php
require_once 'config/db.php';

// Buscar jogadores ativos
$jogadores = [];
try {
    $conn = getConnection();
    $stmt = $conn->query("SELECT * FROM jogadores WHERE ativo = 1 ORDER BY ordem ASC, numero ASC");
    $jogadores = $stmt->fetchAll();
} catch (Exception $e) {
    // Silencioso no front-end
}

// Função para mapear ícone da posição
function getPosicaoIcon($posicao) {
    $icons = [
        'Goleiro' => 'fa-hand-paper',
        'Zagueiro' => 'fa-shield-alt',
        'Lateral' => 'fa-exchange-alt',
        'Lateral Direito' => 'fa-exchange-alt',
        'Lateral Esquerdo' => 'fa-exchange-alt',
        'Volante' => 'fa-compress',
        'Meio Campo' => 'fa-futbol',
        'Meia' => 'fa-futbol',
        'Ponta' => 'fa-running',
        'Atacante' => 'fa-bullseye'
    ];
    return $icons[$posicao] ?? 'fa-futbol';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="Apafut - Associação de Pais e Amigos do Futebol de Caxias do Sul. Formação de jovens atletas com infraestrutura completa e treinadores especializados.">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Apafut - Caxias do Sul</title>
    <!-- favicon -->
    <link rel="shortcut icon" href="assets/logo.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="assets/logo.png">
    <!-- fontawesome -->
    <script src="https://kit.fontawesome.com/15d6bd6a1c.js" crossorigin="anonymous"></script>
    <!-- Lato Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <!-- css -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <!-- NAVBAR -->
        <nav>
            <div class="logo">
                <img src="assets/logo.png" alt="Logo Apafut">
            </div>
            <div class="menu">
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#sobre">Sobre</a></li>
                    <li><a href="#categorias">Categorias</a></li>
                    <li><a href="#depoimentos">Depoimentos</a></li>
                    <li><a href="#noticias">Notícias</a></li>
                </ul>
                <a href="#planos" class="btn-agendar">Seja Sócio</a>
            </div>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>
    <main>
        <!-- Hero -->
        <section id="home" class="hero">
            <div class="hero-content">
                <h1>Bem-vindo à Apafut<br>Caxias do Sul</h1>
                <p>Referência na formação de atletas na cidade de Caxias do Sul/RS.</p>
                <a href="#sobre" class="btn-hero">Saiba Mais</a>
            </div>
            <div class="hero-image">
                <img src="assets/hero.png" alt="Imagem Hero">
            </div>
        </section>
    </main>
    <section>
        <!-- Notícias -->
        <section id="noticias" class="noticias">
            <div class="noticias-header">
                <h2>Últimas Notícias</h2>
                <p>Fique por dentro de tudo que acontece na Apafut</p>
            </div>
            
            <div class="noticias-carousel">
                <button class="carousel-nav prev-noticia" aria-label="Notícia anterior">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <div class="noticias-container">
                    <?php
                    // Buscar notícias ativas do banco de dados
                    try {
                        require_once 'config/db.php';
                        $conn = getConnection();
                        
                        $stmt = $conn->query("
                            SELECT id, titulo, categoria, resumo, imagem, data_publicacao 
                            FROM noticias 
                            WHERE ativo = 1 
                            ORDER BY destaque DESC, data_publicacao DESC 
                            LIMIT 6
                        ");
                        $noticias = $stmt->fetchAll();
                        
                        if (empty($noticias)) {
                            echo '<p style="text-align: center; padding: 40px;">Nenhuma notícia disponível no momento.</p>';
                        } else {
                            $first = true;
                            foreach ($noticias as $noticia):
                                $imagemUrl = !empty($noticia['imagem']) ? htmlspecialchars($noticia['imagem']) : 'assets/hero.png';
                                
                                // Formatar data em português
                                $mesesPt = [
                                    'Jan' => 'Jan', 'Feb' => 'Fev', 'Mar' => 'Mar', 'Apr' => 'Abr',
                                    'May' => 'Mai', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ago',
                                    'Sep' => 'Set', 'Oct' => 'Out', 'Nov' => 'Nov', 'Dec' => 'Dez'
                                ];
                                $dataEn = date('d M Y', strtotime($noticia['data_publicacao']));
                                $dataFormatada = str_replace(array_keys($mesesPt), array_values($mesesPt), $dataEn);
                    ?>
                        <div class="noticia-card <?= $first ? 'active' : '' ?>" data-noticia="<?= $noticia['id'] ?>">
                            <div class="noticia-imagem">
                                <img src="<?= $imagemUrl ?>" alt="<?= htmlspecialchars($noticia['titulo']) ?>">
                                <div class="patrocinadores-faixa">
                                    <div class="patrocinadores-track">
                                        <img src="assets/ucs.png" alt="UCS">
                                        <img src="assets/brisa.png" alt="Brisa">
                                        <img src="assets/saltur.png" alt="Saltur">
                                        <img src="assets/chiesa.png" alt="Chiesa">
                                        <img src="assets/ucs.png" alt="UCS">
                                        <img src="assets/brisa.png" alt="Brisa">
                                        <img src="assets/saltur.png" alt="Saltur">
                                        <img src="assets/chiesa.png" alt="Chiesa">
                                    </div>
                                </div>
                                <div class="noticia-categoria"><?= htmlspecialchars($noticia['categoria']) ?></div>
                                <div class="noticia-data">
                                    <i class="far fa-calendar"></i> <?= $dataFormatada ?>
                                </div>
                            </div>
                            <div class="noticia-conteudo">
                                <h3><?= htmlspecialchars($noticia['titulo']) ?></h3>
                                <p class="noticia-resumo"><?= htmlspecialchars($noticia['resumo']) ?></p>
                                <a href="noticia.php?id=<?= $noticia['id'] ?>" class="btn-noticia">
                                    Ler mais <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php
                                $first = false;
                            endforeach;
                        }
                    } catch (Exception $e) {
                        logError('Erro ao carregar notícias no index', ['error' => $e->getMessage()]);
                        echo '<p style="text-align: center; padding: 40px;">Erro ao carregar notícias. Tente novamente mais tarde.</p>';
                    }
                    ?>
                </div>
                
                <button class="carousel-nav next-noticia" aria-label="Próxima notícia">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <div class="carousel-dots" id="noticias-dots">
                <?php if (!empty($noticias)): ?>
                    <?php for ($i = 0; $i < count($noticias); $i++): ?>
                        <span class="dot <?= $i === 0 ? 'active' : '' ?>" data-slide="<?= $i ?>"></span>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Sobre -->
        <section id="sobre" class="sobre sobre-simples">
            <div class="sobre-content-centralizado">
                <div class="sobre-texto">
                    <h2>Sobre a Apafut</h2>
                    <p>A Apafut é uma academia de futebol dedicada a formar atletas de alta performance, promovendo o desenvolvimento técnico, tático e físico dos jogadores. Com uma equipe de treinadores experientes e uma infraestrutura moderna, oferecemos um ambiente ideal para o crescimento esportivo.</p>
                    <a href="historia.html" class="btn-sobre">Saiba mais</a>
                </div>
                <div class="sobre-logo">
                    <img src="assets/logo.png" alt="Logo Apafut">
                </div>
            </div>
        </section>

        <section id="categorias" class="categorias">
            <h2>Nossas Categorias</h2>
            <div class="bento-grid">
                <div class="card" data-categoria="sub8">
                    <div class="card-icon">
                        <i class="fas fa-child"></i>
                    </div>
                    <h3>Sub-8 <i class="fas fa-chevron-down arrow-icon"></i></h3>
                    <p>Iniciação esportiva com foco em diversão e desenvolvimento motor básico.</p>
                    <div class="categoria-detalhes">
                        <div class="detalhes-content">
                            <div class="detalhe-item">
                                <i class="fas fa-birthday-cake"></i>
                                <strong>Idade:</strong> 6 a 8 anos
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-calendar-alt"></i>
                                <strong>Horários:</strong> Segunda, Quarta e Sexta - 16h às 17h
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-dumbbell"></i>
                                <strong>Foco:</strong> Coordenação motora e diversão
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-user-tie"></i>
                                <strong>Treinador:</strong> Prof. Carlos Silva
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card" data-categoria="sub11">
                    <div class="card-icon">
                        <i class="fas fa-running"></i>
                    </div>
                    <h3>Sub-11 <i class="fas fa-chevron-down arrow-icon"></i></h3>
                    <p>Desenvolvimento de habilidades técnicas fundamentais e coordenação motora.</p>
                    <div class="categoria-detalhes">
                        <div class="detalhes-content">
                            <div class="detalhe-item">
                                <i class="fas fa-birthday-cake"></i>
                                <strong>Idade:</strong> 9 a 11 anos
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-calendar-alt"></i>
                                <strong>Horários:</strong> Terça e Quinta - 17h às 18h30
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-dumbbell"></i>
                                <strong>Foco:</strong> Técnica individual e fundamentos
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-user-tie"></i>
                                <strong>Treinador:</strong> Prof. Ricardo Oliveira
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card" data-categoria="sub13">
                    <div class="card-icon">
                        <i class="fas fa-futbol"></i>
                    </div>
                    <h3>Sub-13 <i class="fas fa-chevron-down arrow-icon"></i></h3>
                    <p>Aprimoramento técnico e introdução a conceitos táticos básicos.</p>
                    <div class="categoria-detalhes">
                        <div class="detalhes-content">
                            <div class="detalhe-item">
                                <i class="fas fa-birthday-cake"></i>
                                <strong>Idade:</strong> 12 a 13 anos
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-calendar-alt"></i>
                                <strong>Horários:</strong> Segunda a Sexta - 18h às 19h30
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-dumbbell"></i>
                                <strong>Foco:</strong> Tática básica e preparação física
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-user-tie"></i>
                                <strong>Treinador:</strong> Prof. Fernando Costa
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card" data-categoria="sub15">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Sub-15 <i class="fas fa-chevron-down arrow-icon"></i></h3>
                    <p>Formação tática avançada e preparação física específica para o futebol.</p>
                    <div class="categoria-detalhes">
                        <div class="detalhes-content">
                            <div class="detalhe-item">
                                <i class="fas fa-birthday-cake"></i>
                                <strong>Idade:</strong> 14 a 15 anos
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-calendar-alt"></i>
                                <strong>Horários:</strong> Segunda a Sexta - 19h às 21h
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-dumbbell"></i>
                                <strong>Foco:</strong> Tática avançada e físico
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-user-tie"></i>
                                <strong>Treinador:</strong> Prof. Marcelo Santos
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card" data-categoria="sub17">
                    <div class="card-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                    <h3>Sub-17 <i class="fas fa-chevron-down arrow-icon"></i></h3>
                    <p>Preparação para o futebol profissional com treinos de alta performance.</p>
                    <div class="categoria-detalhes">
                        <div class="detalhes-content">
                            <div class="detalhe-item">
                                <i class="fas fa-birthday-cake"></i>
                                <strong>Idade:</strong> 16 a 17 anos
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-calendar-alt"></i>
                                <strong>Horários:</strong> Segunda a Sábado - 8h às 11h
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-dumbbell"></i>
                                <strong>Foco:</strong> Alta performance e competição
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-user-tie"></i>
                                <strong>Treinador:</strong> Prof. André Ferreira
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card" data-categoria="sub20">
                    <div class="card-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Sub-20 <i class="fas fa-chevron-down arrow-icon"></i></h3>
                    <p>Transição para o profissionalismo com foco em competições de alto nível.</p>
                    <div class="categoria-detalhes">
                        <div class="detalhes-content">
                            <div class="detalhe-item">
                                <i class="fas fa-birthday-cake"></i>
                                <strong>Idade:</strong> 18 a 20 anos
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-calendar-alt"></i>
                                <strong>Horários:</strong> Segunda a Sábado - 9h às 12h
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-dumbbell"></i>
                                <strong>Foco:</strong> Profissionalização e alto rendimento
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-user-tie"></i>
                                <strong>Treinador:</strong> Prof. Paulo Mendes
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Time Profissional -->
        <section id="profissional" class="time-profissional">
            <div class="profissional-title">
                <div class="profissional-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <h2>Time Profissional</h2>
            </div>

            <!-- Elenco -->
            <div class="elenco-section">
                <!-- Filtros de Posição -->
                <div class="posicao-filtros">
                    <button class="filtro-btn active" data-posicao="todos">
                        <i class="fas fa-users"></i> Todos
                    </button>
                    <button class="filtro-btn" data-posicao="Goleiro">
                        <i class="fas fa-hand-paper"></i> Goleiros
                    </button>
                    <button class="filtro-btn" data-posicao="Zagueiro">
                        <i class="fas fa-shield-alt"></i> Zagueiros
                    </button>
                    <button class="filtro-btn" data-posicao="Lateral">
                        <i class="fas fa-exchange-alt"></i> Laterais
                    </button>
                    <button class="filtro-btn" data-posicao="Volante">
                        <i class="fas fa-compress"></i> Volantes
                    </button>
                    <button class="filtro-btn" data-posicao="Meio-Campo">
                        <i class="fas fa-futbol"></i> Meio-Campos
                    </button>
                    <button class="filtro-btn" data-posicao="Ponta">
                        <i class="fas fa-running"></i> Pontas
                    </button>
                    <button class="filtro-btn" data-posicao="Atacante">
                        <i class="fas fa-bullseye"></i> Atacantes
                    </button>
                </div>
                
                <div class="jogadores-carousel">
                    <button class="carousel-btn prev-jogador">❮</button>
                    <div class="jogadores-container">
                        <?php foreach ($jogadores as $jogador): ?>
                            <div class="jogador-card" data-posicao="<?= htmlspecialchars($jogador['posicao']) ?>">
                                <div class="jogador-foto">
                                    <?php if (!empty($jogador['foto'])): ?>
                                        <img src="<?= htmlspecialchars(str_replace('../', '', $jogador['foto'])) ?>" alt="<?= htmlspecialchars($jogador['nome']) ?>">
                                    <?php else: ?>
                                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #111D69, #eb3835); display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-user" style="font-size: 60px; color: white; opacity: 0.7;"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="jogador-numero"><?= $jogador['numero'] ?></div>
                                </div>
                                <div class="jogador-info">
                                    <h4><?= htmlspecialchars($jogador['nome']) ?></h4>
                                    <p class="jogador-posicao">
                                        <i class="fas <?= getPosicaoIcon($jogador['posicao']) ?>"></i> <?= htmlspecialchars($jogador['posicao']) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-btn next-jogador">❯</button>
                </div>
            </div>

            <!-- Campeonatos -->
            <div class="campeonatos-section">
                <h3>Campeonatos que Disputamos</h3>
                <div class="campeonatos-grid">
                    <div class="campeonato-card">
                        <div class="campeonato-icon">
                            <img src="assets/gauchao.png" alt="Campeonato Gaúcho">
                        </div>
                        <h4>Campeonato Gaúcho</h4>
                        <p>Principal competição estadual do Rio Grande do Sul</p>
                    </div>
                    <div class="campeonato-card">
                        <div class="campeonato-icon">
                            <img src="assets/fgf.png" alt="Copa FGF">
                        </div>
                        <h4>Copa FGF</h4>
                        <p>Torneio oficial da Federação Gaúcha de Futebol</p>
                    </div>
                    <div class="campeonato-card">
                        <div class="campeonato-icon">
                            <img src="assets/copa_caxias.png" alt="Copa Caxias">
                        </div>
                        <h4>Copa Caxias</h4>
                        <p>Torneio municipal com times da cidade</p>
                    </div>
                </div>
            </div>
        </section>
    </section>
    <!-- 6 depoimentos de pais-->
    <section id="depoimentos" class="depoimentos">
        <h2>O que os Pais Dizem</h2>
        <div class="testimonials">
            <div class="testimonial-card">
                <p>"A Apafut tem sido fundamental no desenvolvimento do meu filho. A equipe é dedicada e o ambiente é muito positivo."</p>
                <h3>- Maria S.</h3>
            </div>
            <div class="testimonial-card">
                <p>"Desde que meu filho começou na Apafut, ele melhorou muito tecnicamente e ganhou muita confiança em campo."</p>
                <h3>- João P.</h3>
            </div>
            <div class="testimonial-card">
                <p>"A estrutura e o profissionalismo da Apafut são excepcionais. Recomendo a todos os pais que querem o melhor para seus filhos."</p>
                <h3>- Ana L.</h3>
            </div>
            <div class="testimonial-card">
                <p>"Meu filho adora treinar na Apafut. Ele está sempre motivado e feliz, o que é o mais importante para nós."</p>
                <h3>- Carlos M.</h3>
            </div>
            <div class="testimonial-card">
                <p>"Os treinadores são muito atenciosos e realmente se importam com o desenvolvimento dos atletas. Excelente academia!"</p>
                <h3>- Fernanda R.</h3>
            </div>
            <div class="testimonial-card">
                <p>"O ambiente de treinamento na Apafut é muito positivo e estimulante. Os atletas se divertem e aprendem muito."</p>
                <h3>- Pedro C.</h3>
            </div>
        </div>
    </section>

    <!-- Planos -->
    <section id="planos" class="planos">
        <div class="planos-header">
            <h2>Escolha Seu Plano</h2>
            <p>Invista no futuro do seu atleta com nossos planos completos</p>
        </div>
        <div class="planos-container">
            <!-- Plano Prata -->
            <div class="plano-card">
                <div class="plano-badge">Prata</div>
                <h3>Sócio APA Prata</h3>
                <div class="plano-preco">
                    <span class="preco-por">R$ 200<span class="preco-mes">/ano</span></span>
                </div>
                <div class="parcelamento">
                    <i class="fas fa-credit-card"></i> ou 2x de R$ 100
                </div>
                <ul class="plano-beneficios">
                    <li><i class="fas fa-check"></i> Jantar de fim de temporada</li>
                    <li><i class="fas fa-check"></i> Descontos com parceiros</li>
                    <li><i class="fas fa-check"></i> Carteirinha de sócio</li>
                    <li><i class="fas fa-check"></i> Newsletter exclusiva</li>
                    <li><i class="fas fa-times"></i> Camiseta oficial</li>
                    <li><i class="fas fa-times"></i> Acesso prioritário a eventos</li>
                </ul>
                <a href="#contato" class="btn-plano">Assinar Agora</a>
            </div>

            <!-- Plano Ouro (Destaque) -->
            <div class="plano-card plano-destaque">
                <div class="plano-badge badge-popular">Ouro</div>
                <h3>Sócio APA Ouro</h3>
                <div class="plano-preco">
                    <span class="preco-por">R$ 300<span class="preco-mes">/ano</span></span>
                </div>
                <div class="economia-tag">
                    <i class="fas fa-star"></i> Plano Completo
                </div>
                <div class="parcelamento">
                    <i class="fas fa-credit-card"></i> ou 2x de R$ 150
                </div>
                <ul class="plano-beneficios">
                    <li><i class="fas fa-check"></i> Camiseta oficial exclusiva</li>
                    <li><i class="fas fa-check"></i> Jantar de fim de temporada</li>
                    <li><i class="fas fa-check"></i> Descontos com parceiros</li>
                    <li><i class="fas fa-check"></i> Carteirinha de sócio premium</li>
                    <li><i class="fas fa-check"></i> Newsletter exclusiva</li>
                    <li><i class="fas fa-check"></i> Acesso prioritário a eventos</li>
                    <li><i class="fas fa-check"></i> Conteúdo exclusivo dos bastidores</li>
                </ul>
                <a href="#contato" class="btn-plano btn-plano-destaque">Garantir Vaga</a>
            </div>
        </div>
    </section>

    <!-- footer -->
    <footer id="contato">
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo">
                    <img src="assets/logo.png" alt="Logo Apafut">
                    <h3>Apafut Caxias do Sul</h3>
                </div>
                <p>Formando campeões dentro e fora de campo desde 2010.</p>
                <div class="social-media">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <div class="footer-section">
                <h4>Links Rápidos</h4>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#sobre">Sobre Nós</a></li>
                    <li><a href="#categorias">Categorias</a></li>
                    <li><a href="#depoimentos">Depoimentos</a></li>
                    <li><a href="#patrocinadores">Patrocinadores</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Contato</h4>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>Rua Exemplo, 123<br>Caxias do Sul - RS</p>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <p>(54) 3232-3232</p>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <p>contato@apafut.com.br</p>
                    </div>
                </div>
            </div>

            <div class="footer-section">
                <h4>Horário de Atendimento</h4>
                <div class="schedule">
                    <p><strong>Segunda a Sexta:</strong><br>08:00 - 18:00</p>
                    <p><strong>Sábado:</strong><br>08:00 - 12:00</p>
                    <p><strong>Domingo:</strong><br>Fechado</p>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2025 Apafut Caxias do Sul. Todos os direitos reservados.</p>
        </div>
    </footer>
    
    <script src="assets/js/script.js"></script>
</body>
</html>