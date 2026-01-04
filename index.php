<?php
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
require_once 'config/security-headers.php';
require_once 'config/db.php';
require_once 'includes/analytics-tracker.php';
require_once 'src/SEO.php';
require_once 'src/Cache.php';
require_once 'src/BotProtection.php';

// Proteção contra bots
BotProtection::check();

// Buscar jogadores ativos com cache
$jogadores = Cache::remember('jogadores_ativos', function() {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT id, nome, nome_completo, cidade, altura, peso, data_nascimento, numero, posicao, foto, ordem FROM jogadores WHERE ativo = 1 ORDER BY ordem ASC, numero ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Erro ao buscar jogadores: " . $e->getMessage());
        return [];
    }
}, 3600);

// Buscar comissão técnica ativa com cache
$comissao = Cache::remember('comissao_tecnica_ativos', function() {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT id, nome, cargo, foto, descricao, ordem FROM comissao_tecnica WHERE ativo = 1 ORDER BY ordem ASC, id ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Erro ao buscar comissão técnica: " . $e->getMessage());
        return [];
    }
}, 3600);

// Buscar banners ativos
$banners = Cache::remember('banners_ativos', function() {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT * FROM banners WHERE ativo = 1 ORDER BY ordem ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Erro ao buscar banners: " . $e->getMessage());
        return [];
    }
}, 1800);

// Buscar planos ativos
$planos = Cache::remember('planos_ativos', function() {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT id, nome, tipo, preco_anual, parcelas, beneficios, destaque FROM planos WHERE ativo = 1 ORDER BY ordem ASC, preco_anual ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Erro ao buscar planos: " . $e->getMessage());
        return [];
    }
}, 3600);

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <?php 
    SEO::renderMetaTags('home', [
        'title' => 'APAFUT - Associação de Pais e Amigos do Futebol | Caxias do Sul',
        'description' => 'APAFUT Caxias do Sul: Formação de jovens atletas com infraestrutura completa, categorias de base e treinadores especializados. Venha conhecer a melhor escolinha de futebol da região!',
        'keywords' => 'apafut, apafut caxias do sul, escolinha de futebol caxias, futebol caxias do sul, categorias de base, formação atletas, futebol juvenil, associação futebol, escola de futebol',
        'image' => 'https://' . $_SERVER['HTTP_HOST'] . '/assets/hero.png'
    ]);
    ?>
    <!-- favicon -->
    <link rel="shortcut icon" href="assets/logo.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="assets/logo.png">
    
    <!-- Preconnect para recursos externos -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://kit.fontawesome.com">
    
    <!-- Lato Font com font-display swap -->
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    
    <!-- FontAwesome async -->
    <script src="https://kit.fontawesome.com/15d6bd6a1c.js" crossorigin="anonymous" async></script>
    
    <!-- css com preload -->
    <link rel="preload" href="assets/css/style.css" as="style">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/ctas.min.css">
    <link rel="stylesheet" href="assets/css/depoimentos-modern.css">
    <style>
        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: var(--vermelho-primario);
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            z-index: 10000;
            font-weight: bold;
            border-radius: 0 0 4px 0;
        }
        .skip-link:focus {
            top: 0;
        }
        
        /* Carrossel de Banners Fullscreen */
        .banner-carousel {
            position: relative;
            width: 100%;
            height: auto;
            overflow: hidden;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        
        .banner-slides {
            position: relative;
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            aspect-ratio: 21 / 9;
            overflow: hidden;
        }
        
        /* Estilos desktop */
        @media (min-width: 769px) {
            .banner-carousel {
                background: transparent;
                padding: 0 20px;
                margin-top: 80px !important;
            }
            
            .banner-slides {
                border-radius: 20px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            }
        }
        
        .banner-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.8s ease-in-out;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .banner-slide.active {
            opacity: 1;
            z-index: 2;
        }
        
        .banner-slide img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
        }
        
        /* Mostrar apenas imagem desktop por padrão */
        .banner-mobile {
            display: none;
        }
        
        .banner-desktop {
            display: block;
        }
        
        /* Indicadores */
        .banner-indicators {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 12px;
            z-index: 10;
        }
        
        .indicator {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            border: 2px solid white;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0;
        }
        
        .indicator.active {
            background: white;
        }
        
        .indicator:hover {
            background: rgba(255, 255, 255, 0.8);
        }
        
        /* Responsivo */
        @media (min-width: 769px) and (max-width: 1400px) {
            .banner-slides {
                max-width: 95%;
            }
        }
        
        @media (max-width: 768px) {
            .banner-carousel {
                width: 100%;
                height: auto;
                padding: 0;
                margin-top: 60px !important;
                background: transparent;
            }
            
            .banner-slides {
                position: relative;
                width: 100%;
                aspect-ratio: 7 / 6;
                overflow: hidden;
            }
            
            .banner-slide {
                position: absolute;
                width: 100%;
                height: 100%;
                top: 0;
                left: 0;
                opacity: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                background: transparent;
            }
            
            .banner-slide.active {
                opacity: 1;
            }
            
            /* Trocar para imagem mobile */
            .banner-desktop {
                display: none !important;
            }
            
            .banner-mobile {
                display: block !important;
                width: 100%;
                height: 100%;
                object-fit: contain;
                object-position: center;
            }
            
            .banner-indicators {
                bottom: 20px;
            }
        }
    </style>
    <?php SEO::renderOrganizationSchema(); ?>
</head>
<body>
    <a href="#main-content" class="skip-link">Pular para o conteúdo principal</a>
    
    <header role="banner">
        <!-- NAVBAR -->
        <nav role="navigation" aria-label="Menu principal">
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
                <div class="nav-buttons">
                    <a href="#planos" class="btn-agendar" aria-label="Ver planos de sócio">Seja Sócio</a>
                    <a href="https://wa.me/5554991348163?text=Olá!%20Gostaria%20de%20mais%20informações%20sobre%20a%20inscrição%20para%20aluno%20da%20APAFUT" target="_blank" rel="noopener" class="btn-agendar btn-aluno" aria-label="Entre em contato via WhatsApp">
                        <i class="fab fa-whatsapp" aria-hidden="true"></i> Seja Aluno
                    </a>
                </div>
            </div>
            <div class="hamburger" role="button" aria-label="Abrir menu" aria-expanded="false" tabindex="0">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>
    
    <!-- Carrossel de Banners Fullscreen -->
    <section class="banner-carousel" style="margin-top: 60px;">
        <div class="banner-slides">
            <?php if (count($banners) > 0): ?>
                <?php foreach($banners as $index => $banner): ?>
                    <div class="banner-slide <?= $index === 0 ? 'active' : '' ?>">
                        <!-- Imagem Desktop -->
                        <img class="banner-desktop" 
                             src="<?= htmlspecialchars($banner['imagem']) ?>" 
                             alt="<?= htmlspecialchars($banner['titulo']) ?>" 
                             loading="<?= $index === 0 ? 'eager' : 'lazy' ?>"
                             onerror="this.src='assets/hero.png'">
                        <!-- Imagem Mobile -->
                        <?php $imagemMobile = !empty($banner['imagem_mobile']) ? $banner['imagem_mobile'] : $banner['imagem']; ?>
                        <img class="banner-mobile" 
                             src="<?= htmlspecialchars($imagemMobile) ?>" 
                             alt="<?= htmlspecialchars($banner['titulo']) ?>" 
                             loading="<?= $index === 0 ? 'eager' : 'lazy' ?>"
                             onerror="this.src='assets/hero.png'">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="banner-slide active">
                    <img class="banner-desktop" src="assets/images/banner1.jpg" alt="Banner 1" loading="eager">
                    <img class="banner-mobile" src="assets/images/banner1.jpg" alt="Banner 1" loading="eager">
                </div>
                <div class="banner-slide">
                    <img class="banner-desktop" src="assets/images/banner2.jpg" alt="Banner 2" loading="lazy">
                    <img class="banner-mobile" src="assets/images/banner2.jpg" alt="Banner 2" loading="lazy">
                </div>
                <div class="banner-slide">
                    <img class="banner-desktop" src="assets/images/banner3.jpg" alt="Banner 3" loading="lazy">
                    <img class="banner-mobile" src="assets/images/banner3.jpg" alt="Banner 3" loading="lazy">
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Indicadores -->
        <?php if (count($banners) > 1): ?>
        <div class="banner-indicators">
            <?php foreach($banners as $index => $banner): ?>
                <button class="indicator <?= $index === 0 ? 'active' : '' ?>" 
                        data-slide="<?= $index ?>" 
                        aria-label="<?= htmlspecialchars($banner['titulo']) ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php elseif (count($banners) === 0): ?>
        <div class="banner-indicators">
            <button class="indicator active" data-slide="0" aria-label="Banner 1"></button>
            <button class="indicator" data-slide="1" aria-label="Banner 2"></button>
            <button class="indicator" data-slide="2" aria-label="Banner 3"></button>
        </div>
        <?php endif; ?>
    </section>
    
    <!-- Carrossel de Patrocinadores -->
    <section class="patrocinadores-carousel">
        <div class="patrocinadores-container">
            <h3><i class="fas fa-handshake"></i> Nossos Patrocinadores</h3>
            <div class="patrocinadores-track">
                <div class="patrocinador-item">
                    <img src="assets/patrocinadores/ucs.png" alt="UCS">
                </div>
                <div class="patrocinador-item">
                    <img src="assets/patrocinadores/saltur.png" alt="Saltur">
                </div>
                <div class="patrocinador-item">
                    <img src="assets/patrocinadores/chiesa.png" alt="Chiesa">
                </div>
                <div class="patrocinador-item">
                    <img src="assets/patrocinadores/brisa.png" alt="Brisa">
                </div>
                <!-- Duplicação para loop infinito -->
                <div class="patrocinador-item">
                    <img src="assets/patrocinadores/ucs.png" alt="UCS">
                </div>
                <div class="patrocinador-item">
                    <img src="assets/patrocinadores/saltur.png" alt="Saltur">
                </div>
                <div class="patrocinador-item">
                    <img src="assets/patrocinadores/chiesa.png" alt="Chiesa">
                </div>
                <div class="patrocinador-item">
                    <img src="assets/patrocinadores/brisa.png" alt="Brisa">
                </div>
            </div>
        </div>
    </section>
    
    <section>
        <!-- Destaques -->
        <section id="noticias" class="destaques-section">
            <div class="destaques-container">
                <h2 class="destaques-titulo">DESTAQUES</h2>
                
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
                    
                    // Preencher com dados padrão se não houver notícias suficientes
                    while (count($noticias) < 6) {
                        $noticias[] = [
                            'id' => 0,
                            'titulo' => 'Notícia em breve',
                            'resumo' => 'Aguarde novidades',
                            'imagem' => 'assets/fundo.jpg',
                            'data_publicacao' => date('Y-m-d')
                        ];
                    }
                ?>
                
                <div class="bento-grid-noticias">
                    <?php foreach ($noticias as $index => $noticia): 
                        $imagemUrl = !empty($noticia['imagem']) ? htmlspecialchars($noticia['imagem']) : 'assets/fundo.jpg';
                        $dataNoticia = date('d/m/Y', strtotime($noticia['data_publicacao']));
                        $itemClass = 'bento-item-' . ($index + 1);
                    ?>
                        <article class="bento-item <?= $itemClass ?>">
                            <a href="noticia.php?id=<?= $noticia['id'] ?>" class="bento-link">
                                <div class="bento-image-wrapper">
                                    <img src="<?= $imagemUrl ?>" 
                                         alt="<?= htmlspecialchars($noticia['titulo']) ?>" 
                                         class="bento-image" 
                                         loading="lazy">
                                    <div class="bento-overlay"></div>
                                </div>
                                <div class="bento-content">
                                    <time class="bento-date" datetime="<?= $noticia['data_publicacao'] ?>">
                                        <?= $dataNoticia ?>
                                    </time>
                                    <h3 class="bento-resumo"><?= htmlspecialchars($noticia['resumo']) ?></h3>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
                
                <div class="destaques-botao-container">
                    <a href="noticias.php" class="btn-ver-todas-noticias">
                        Ver todas as notícias <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <?php
                } catch (Exception $e) {
                    error_log('Erro ao carregar notícias: ' . $e->getMessage());
                    echo '<p style="padding: 40px; text-align: center;">Erro ao carregar notícias. Tente novamente mais tarde.</p>';
                }
                ?>
            </div>
        </section>


        <!-- Sobre -->
        <section id="sobre" class="sobre sobre-simples" aria-labelledby="sobre-titulo">
            <div class="sobre-content-centralizado">
                <div class="sobre-texto">
                    <h2 id="sobre-titulo">Sobre a Apafut</h2>
                    <p>A Apafut é uma academia de futebol dedicada a formar atletas de alta performance, promovendo o desenvolvimento técnico, tático e físico dos jogadores. Com uma equipe de treinadores experientes e uma infraestrutura moderna, oferecemos um ambiente ideal para o crescimento esportivo.</p>
                    <a href="historia.html" class="btn-sobre">Saiba mais</a>
                </div>
                <div class="sobre-logo">
                    <img src="assets/logo.png" alt="Logo Apafut">
                </div>
            </div>
        </section>

        <!-- CTA Aula Experimental -->
        <section class="cta-aula-experimental">
            <div class="cta-experimental-container">
                <div class="cta-experimental-content">
                    <div class="cta-experimental-badge">
                        <i class="fas fa-star"></i> AGENDE SUA AVALIAÇÃO
                    </div>
                    <h2>Venha Conhecer a <span class="destaque">APAFUT</span></h2>
                    <p>Descubra nossa metodologia de treinamento, estrutura e equipe profissional!</p>
                    <ul class="cta-beneficios">
                        <li><i class="fas fa-check-circle"></i> Avaliação técnica completa</li>
                        <li><i class="fas fa-check-circle"></i> Conheça nossa infraestrutura</li>
                        <li><i class="fas fa-check-circle"></i> Orientação com nossos treinadores</li>
                    </ul>
                    <div class="cta-buttons">
                        <a href="https://wa.me/5554991348163?text=Olá!%20Gostaria%20de%20agendar%20uma%20visita%20e%20conhecer%20a%20APAFUT" target="_blank" class="btn-cta-primary">
                            <i class="fab fa-whatsapp"></i> Agendar Visita
                        </a>
                        <span class="cta-urgencia"><i class="fas fa-clock"></i> Vagas limitadas!</span>
                    </div>
                </div>
                <div class="cta-experimental-image">
                    <img src="assets/img1.jpg" alt="Treino APAFUT" loading="lazy">
                    <div class="cta-image-overlay">
                        <div class="cta-stats">
                            <div class="stat">
                                <strong>2000+</strong>
                                <span>Alunos Ativos</span>
                            </div>
                            <div class="stat">
                                <strong>20+</strong>
                                <span>Anos de Experiência</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="categorias" class="categorias" aria-labelledby="categorias-titulo">
            <h2 id="categorias-titulo">Nossas Categorias</h2>
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
                <h2>PROFISSIONAL</h2>
                <div class="profissional-categorias" style="display: flex; gap: 40px; justify-content: center; margin-bottom: 40px;">
                    <span style="font-size: 1.5rem; color: #fff; font-weight: 400; letter-spacing: 2px;">MASCULINO</span>
                </div>
            </div>

            <!-- Abas: Elenco e Comissão Técnica -->
            <div class="profissional-tabs">
                <button class="tab-btn active" data-tab="elenco">
                    <i class="fas fa-users"></i> Elenco
                </button>
                <button class="tab-btn" data-tab="comissao">
                    <i class="fas fa-users-cog"></i> Comissão Técnica
                </button>
            </div>

            <!-- Elenco -->
            <div class="elenco-section tab-content active" id="elenco">
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
                        <?php 
                        $contador = 0;
                        $fotosCustomizadas = ['assets/profi1.png', 'assets/profi2.png', 'assets/profi3.png', 'assets/profi4.png', 'assets/profi5.png', 'assets/profi6.png'];
                        foreach ($jogadores as $jogador): 
                            // Usar foto do banco se existir, senão usar foto customizada
                            $fotoBanco = str_replace('../', '', $jogador['foto']);
                            if (!empty($fotoBanco) && file_exists($fotoBanco)) {
                                $fotoExibir = $fotoBanco;
                            } else {
                                $fotoExibir = ($contador < 6) ? $fotosCustomizadas[$contador] : 'assets/images/jogadores/default.jpg';
                            }
                            $contador++;
                        ?>
                            <div class="jogador-card" 
                                 data-posicao="<?= htmlspecialchars($jogador['posicao']) ?>"
                                 data-jogador='<?= json_encode([
                                    'nome' => $jogador['nome'],
                                    'numero' => $jogador['numero'],
                                    'nomeCompleto' => $jogador['nome_completo'] ?? $jogador['nome'],
                                    'cidade' => $jogador['cidade'] ?? '-',
                                    'altura' => $jogador['altura'] ?? '-',
                                    'peso' => $jogador['peso'] ?? '-',
                                    'dataNascimento' => $jogador['data_nascimento'] ?? '-',
                                    'posicao' => $jogador['posicao'],
                                    'foto' => $fotoExibir
                                 ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                 onclick="abrirModalJogador(this)">
                                <div class="jogador-foto">
                                    <?php if (!empty($fotoExibir)): ?>
                                        <img src="<?= htmlspecialchars($fotoExibir) ?>" alt="<?= htmlspecialchars($jogador['nome']) ?>" loading="lazy" width="280" height="350">
                                    <?php else: ?>
                                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #111D69, #eb3835); display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-user" style="font-size: 60px; color: white; opacity: 0.7;"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="jogador-numero"><?= $jogador['numero'] ?></div>
                                </div>
                                <div class="jogador-info" style="background: #e8e8e8; padding: 15px; text-align: left;">
                                    <h4 style="color: #2d3436; font-size: 1.1rem; font-weight: 600; margin-bottom: 5px;"><?= $jogador['numero'] ?>. <?= htmlspecialchars($jogador['nome']) ?></h4>
                                    <p class="jogador-posicao" style="color: #636e72; font-size: 0.95rem; margin: 0;">
                                        <?= htmlspecialchars($jogador['posicao']) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-btn next-jogador">❯</button>
                </div>
            </div>

            <!-- Comissão Técnica -->
            <div class="comissao-section tab-content" id="comissao">
                <div class="jogadores-carousel">
                    <button class="carousel-btn prev-comissao">❮</button>
                    <div class="comissao-container">
                        <?php 
                        foreach ($comissao as $membro): 
                            // Usar foto do banco se existir
                            $fotoBanco = str_replace('../', '', $membro['foto']);
                            if (!empty($fotoBanco) && file_exists($fotoBanco)) {
                                $fotoExibir = $fotoBanco;
                            } else {
                                $fotoExibir = 'assets/images/comissao/default.jpg';
                            }
                        ?>
                            <div class="jogador-card comissao-card" 
                                 data-membro='<?= json_encode([
                                    'nome' => $membro['nome'],
                                    'cargo' => $membro['cargo'],
                                    'descricao' => $membro['descricao'] ?? '',
                                    'foto' => $fotoExibir
                                 ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                 onclick="abrirModalComissao(this)">
                                <div class="jogador-foto">
                                    <?php if (!empty($fotoExibir)): ?>
                                        <img src="<?= htmlspecialchars($fotoExibir) ?>" alt="<?= htmlspecialchars($membro['nome']) ?>" loading="lazy" width="280" height="350">
                                    <?php else: ?>
                                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #111D69, #eb3835); display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-user-tie" style="font-size: 60px; color: white; opacity: 0.7;"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="jogador-info" style="background: #e8e8e8; padding: 15px; text-align: left;">
                                    <h4 style="color: #2d3436; font-size: 1.1rem; font-weight: 600; margin-bottom: 5px;"><?= htmlspecialchars($membro['nome']) ?></h4>
                                    <p class="jogador-posicao" style="color: #636e72; font-size: 0.95rem; margin: 0;">
                                        <i class="fas fa-briefcase"></i> <?= htmlspecialchars($membro['cargo']) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-btn next-comissao">❯</button>
                </div>
            </div>

            <!-- Campeonatos -->
            <div class="campeonatos-section">
                <h3>Campeonatos que Disputamos</h3>
                <div class="campeonatos-grid">
                    <div class="campeonato-card">
                        <div class="campeonato-icon">
                            <img src="assets/gauchao.png" alt="Campeonato Gaúcho" loading="lazy" width="200" height="200">
                        </div>
                        <h4>Campeonato Gaúcho</h4>
                        <p>Principal competição estadual do Rio Grande do Sul</p>
                    </div>
                    <div class="campeonato-card">
                        <div class="campeonato-icon">
                            <img src="assets/fgf.png" alt="Copa FGF" loading="lazy" width="200" height="200">
                        </div>
                        <h4>Copa FGF</h4>
                        <p>Torneio oficial da Federação Gaúcha de Futebol</p>
                    </div>
                    <div class="campeonato-card">
                        <div class="campeonato-icon">
                            <img src="assets/copa_caxias.png" alt="Copa Caxias" loading="lazy" width="200" height="200">
                        </div>
                        <h4>Copa Caxias</h4>
                        <p>Torneio municipal com times da cidade</p>
                    </div>
                </div>
            </div>
        </section>
    </section>
    
    <!-- Depoimentos -->
    <?php
    // Buscar depoimentos ativos (máximo 6)
    $depoimentos = [];
    try {
        $stmt = $conn->query("SELECT * FROM depoimentos WHERE ativo = 1 ORDER BY ordem ASC LIMIT 6");
        $depoimentos = $stmt->fetchAll();
    } catch (Exception $e) {
        // Silencioso no front-end
    }
    ?>
    
    <?php if (!empty($depoimentos)): ?>
    <section id="depoimentos" class="depoimentos-section">
        <div class="container">
            <div class="section-header">
                <h2 data-aos="fade-up">Depoimentos</h2>
                <p data-aos="fade-up" data-aos-delay="100">Veja o que ex-profissionais, alunos e pais falam sobre a APAFUT</p>
            </div>
            
            <div class="depoimentos-grid">
                <?php foreach ($depoimentos as $index => $dep): 
                    $tipo = $dep['tipo_depoimento'] ?? 'video_local';
                    $delay = ($index % 3) * 100;
                ?>
                
                <div class="depoimento-card" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                    <?php if ($tipo === 'video_url' && !empty($dep['video_url'])): 
                        // Detectar tipo de vídeo (YouTube ou Vimeo)
                        $videoUrl = $dep['video_url'];
                        $embedUrl = '';
                        
                        if (strpos($videoUrl, 'youtube.com') !== false || strpos($videoUrl, 'youtu.be') !== false) {
                            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $videoUrl, $matches);
                            if (!empty($matches[1])) {
                                $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
                            }
                        } elseif (strpos($videoUrl, 'vimeo.com') !== false) {
                            preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $matches);
                            if (!empty($matches[1])) {
                                $embedUrl = 'https://player.vimeo.com/video/' . $matches[1];
                            }
                        }
                    ?>
                        <div class="video-wrapper">
                            <?php if ($embedUrl): ?>
                                <iframe src="<?= htmlspecialchars($embedUrl) ?>" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
                            <?php else: ?>
                                <div class="video-error">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <p>Vídeo indisponível</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="depoimento-content">
                            <div class="quote-icon">
                                <i class="fas fa-video"></i>
                            </div>
                            <h3><?= htmlspecialchars($dep['nome']) ?></h3>
                            <?php if (!empty($dep['depoimento'])): ?>
                                <p class="depoimento-text"><?= nl2br(htmlspecialchars($dep['depoimento'])) ?></p>
                            <?php endif; ?>
                        </div>
                    
                    <?php elseif ($tipo === 'video_local' && !empty($dep['video'])): ?>
                        <div class="video-wrapper">
                            <video controls preload="metadata">
                                <source src="<?= htmlspecialchars(str_replace('../', '', $dep['video'])) ?>" type="video/mp4">
                                Seu navegador não suporta vídeo.
                            </video>
                        </div>
                        <div class="depoimento-content">
                            <div class="quote-icon">
                                <i class="fas fa-video"></i>
                            </div>
                            <h3><?= htmlspecialchars($dep['nome']) ?></h3>
                            <?php if (!empty($dep['depoimento'])): ?>
                                <p class="depoimento-text"><?= nl2br(htmlspecialchars($dep['depoimento'])) ?></p>
                            <?php endif; ?>
                        </div>
                    
                    <?php else: // Depoimento apenas texto ?>
                        <div class="depoimento-text-only">
                            <div class="quote-icon">
                                <i class="fas fa-quote-left"></i>
                            </div>
                            <p class="depoimento-text"><?= nl2br(htmlspecialchars($dep['depoimento'])) ?></p>
                            <div class="depoimento-author">
                                <h3><?= htmlspecialchars($dep['nome']) ?></h3>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA Sócio Apoiador -->
    <div class="cta-socio-apoiador">
        <div class="socio-card">
            <div class="socio-icon">
                <i class="fas fa-hands-helping"></i>
            </div>
            <div class="socio-content">
                <h3>Seja Sócio da APAFUT</h3>
                <p>Apoie nosso time profissional e acompanhe de perto todas as partidas. Benefícios exclusivos para quem vive o futebol!</p>
            </div>
            <a href="#planos" class="btn-socio">
                <i class="fas fa-heart"></i> Quero Ser Sócio
            </a>
        </div>
    </div>

    <!-- Planos -->
    <section id="planos" class="planos">
        <div class="planos-header">
            <h2>Escolha Seu Plano</h2>
            <p>Invista no futuro do seu atleta com nossos planos completos</p>
        </div>
        <div class="planos-container">
            <?php if (!empty($planos)): ?>
                <?php foreach ($planos as $plano): 
                    $preco_mensal = $plano['preco_anual'] / ($plano['parcelas'] ?: 12);
                    $beneficios_lista = array_filter(array_map('trim', explode('|', $plano['beneficios'])));
                    
                    // Determinar classes baseadas no tipo
                    $card_class = 'plano-card';
                    $badge_class = 'plano-badge';
                    $btn_class = 'btn-plano';
                    $badge_text = ucfirst($plano['tipo']);
                    
                    if ($plano['destaque']) {
                        $card_class .= ' plano-destaque';
                        $badge_class .= ' badge-popular';
                        $btn_class .= ' btn-plano-destaque';
                    } elseif (strtolower($plano['tipo']) === 'diamante') {
                        $card_class .= ' plano-diamante';
                        $badge_class .= ' badge-diamante';
                        $btn_class .= ' btn-plano-diamante';
                    }
                ?>
                <div class="<?= $card_class ?>">
                    <div class="<?= $badge_class ?>"><?= htmlspecialchars($badge_text) ?></div>
                    <h3><?= htmlspecialchars($plano['nome']) ?></h3>
                    <div class="plano-preco">
                        <span class="preco-por"><?= $plano['parcelas'] ?>x R$ <?= number_format($preco_mensal, 0, ',', '.') ?><span class="preco-mes">,<?= str_pad((int)(($preco_mensal - floor($preco_mensal)) * 100), 2, '0', STR_PAD_LEFT) ?></span></span>
                    </div>
                    <div class="parcelamento">
                        <i class="fas fa-credit-card"></i> Cartão de crédito, Pix ou Boleto
                    </div>
                    <?php if (strtolower($plano['tipo']) === 'diamante'): ?>
                    <div class="economia-tag">
                        <i class="fas fa-gem"></i> Premium
                    </div>
                    <?php endif; ?>
                    <ul class="plano-beneficios">
                        <?php foreach ($beneficios_lista as $beneficio): ?>
                        <li><i class="fas fa-check"></i> <?= htmlspecialchars($beneficio) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="checkout.php?plano=<?= $plano['id'] ?>" class="<?= $btn_class ?>">
                        <?= $plano['destaque'] ? 'Garantir Vaga' : 'Assinar Agora' ?>
                    </a>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhum plano disponível no momento.</p>
            <?php endif; ?>
        </div>
    </section>
    </main>

    <!-- footer -->
    <footer id="contato" role="contentinfo" aria-label="Rodapé">
        <div class="footer-content">
            <div class="footer-section footer-about">
                <div class="footer-logo">
                    <img src="assets/logo.png" alt="Logo Apafut">
                    <h3>Apafut Caxias do Sul</h3>
                </div>
                <p>Formando campeões dentro e fora de campo desde 2010.</p>
                <div class="footer-contact-quick footer-links-centered">
                    <a href="historia.html#unidades" class="footer-link-unidades">
                        <i class="fas fa-map-marked-alt"></i> Nossas Unidades
                    </a>
                    <a href="#planos" class="footer-link-socio">
                        <i class="fas fa-heart"></i> Seja Sócio Torcedor
                    </a>
                </div>
            </div>

            <div class="footer-section">
                <h4>Links Rápidos</h4>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#sobre">Sobre Nós</a></li>
                    <li><a href="#depoimentos">Depoimentos</a></li>
                    <li><a href="#planos">Planos</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Redes Sociais</h4>
                <p class="footer-social-text">Siga-nos nas redes sociais e fique por dentro de todas as novidades!</p>
                <div class="social-media social-media-large" role="navigation" aria-label="Redes sociais">
                    <a href="https://www.facebook.com/apafut.oficial/" aria-label="Visite nosso Facebook" target="_blank" rel="noopener" title="Facebook">
                        <i class="fab fa-facebook-f" aria-hidden="true"></i>
                    </a>
                    <a href="https://www.instagram.com/apafutoficial/" aria-label="Visite nosso Instagram" target="_blank" rel="noopener" title="Instagram">
                        <i class="fab fa-instagram" aria-hidden="true"></i>
                    </a>
                    <a href="https://www.youtube.com/@apafutvideos" aria-label="Visite nosso YouTube" target="_blank" rel="noopener" title="YouTube">
                        <i class="fab fa-youtube" aria-hidden="true"></i>
                    </a>
                    <a href="https://wa.me/5554991348163" aria-label="Fale conosco no WhatsApp" target="_blank" rel="noopener" title="WhatsApp">
                        <i class="fab fa-whatsapp" aria-hidden="true"></i>
                    </a>
                    <a href="mailto:apafutoficial@gmail.com" aria-label="Envie um e-mail" target="_blank" rel="noopener" title="E-mail">
                        <i class="fas fa-envelope" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; <?= date('Y') ?> Apafut Caxias do Sul. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Modal do Jogador -->
    <div id="modalJogador" class="modal-jogador">
        <div class="modal-jogador-content">
            <span class="modal-close" onclick="fecharModalJogador()">&times;</span>
            <div class="modal-jogador-body">
                <div class="modal-jogador-foto">
                    <img id="modalFoto" src="" alt="">
                </div>
                <div class="modal-jogador-info">
                    <h2 class="modal-titulo">
                        <span id="modalNumero"></span>. <span id="modalNome"></span>
                    </h2>
                    <div class="modal-stats">
                        <p><strong>Nome completo:</strong> <span id="modalNomeCompleto"></span></p>
                        <p><strong>Cidade:</strong> <span id="modalCidade"></span></p>
                        <p><strong>Altura:</strong> <span id="modalAltura"></span></p>
                        <p><strong>Peso:</strong> <span id="modalPeso"></span></p>
                        <p><strong>Data de nascimento:</strong> <span id="modalDataNascimento"></span></p>
                        <p><strong>Posição:</strong> <span id="modalPosicao"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal da Comissão Técnica -->
    <div id="modalComissao" class="modal-jogador">
        <div class="modal-jogador-content">
            <span class="modal-close" onclick="fecharModalComissao()">&times;</span>
            <div class="modal-jogador-body">
                <div class="modal-jogador-foto">
                    <img id="modalComissaoFoto" src="" alt="">
                </div>
                <div class="modal-jogador-info">
                    <h2 class="modal-titulo">
                        <span id="modalComissaoNome"></span>
                    </h2>
                    <div class="modal-stats">
                        <p><strong>Cargo:</strong> <span id="modalComissaoCargo"></span></p>
                        <p style="margin-top: 20px;"><span id="modalComissaoDescricao"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Funções do Modal do Jogador
    function abrirModalJogador(element) {
        try {
            const jogadorDataStr = element.getAttribute('data-jogador');
            console.log('Data string:', jogadorDataStr); // Debug
            
            const jogadorData = JSON.parse(jogadorDataStr);
            console.log('Parsed data:', jogadorData); // Debug
            
            const modal = document.getElementById('modalJogador');
            
            document.getElementById('modalFoto').src = jogadorData.foto || '';
            document.getElementById('modalNumero').textContent = jogadorData.numero || '';
            document.getElementById('modalNome').textContent = jogadorData.nome || '';
            document.getElementById('modalNomeCompleto').textContent = jogadorData.nomeCompleto || '-';
            document.getElementById('modalCidade').textContent = jogadorData.cidade || '-';
            document.getElementById('modalAltura').textContent = jogadorData.altura || '-';
            document.getElementById('modalPeso').textContent = jogadorData.peso || '-';
            document.getElementById('modalDataNascimento').textContent = jogadorData.dataNascimento || '-';
            document.getElementById('modalPosicao').textContent = jogadorData.posicao || '';
            
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        } catch (error) {
            console.error('Erro ao abrir modal:', error);
            alert('Erro ao carregar informações do jogador');
        }
    }
    
    function fecharModalJogador() {
        const modal = document.getElementById('modalJogador');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // Fechar modal ao clicar fora
    document.getElementById('modalJogador')?.addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModalJogador();
        }
    });
    
    // Fechar modal com tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModalJogador();
            fecharModalComissao();
        }
    });

    // Funções do Modal da Comissão Técnica
    function abrirModalComissao(element) {
        try {
            const membroDataStr = element.getAttribute('data-membro');
            const membroData = JSON.parse(membroDataStr);
            
            const modal = document.getElementById('modalComissao');
            
            document.getElementById('modalComissaoFoto').src = membroData.foto || '';
            document.getElementById('modalComissaoNome').textContent = membroData.nome || '';
            document.getElementById('modalComissaoCargo').textContent = membroData.cargo || '';
            document.getElementById('modalComissaoDescricao').textContent = membroData.descricao || '';
            
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        } catch (error) {
            console.error('Erro ao abrir modal:', error);
            alert('Erro ao carregar informações do membro');
        }
    }
    
    function fecharModalComissao() {
        const modal = document.getElementById('modalComissao');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // Fechar modal ao clicar fora
    document.getElementById('modalComissao')?.addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModalComissao();
        }
    });
    </script>
    
    <!-- Script do Carrossel de Banners -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const slides = document.querySelectorAll('.banner-slide');
        const indicators = document.querySelectorAll('.indicator');
        const carousel = document.querySelector('.banner-carousel');
        
        if (!slides.length || !indicators.length) {
            console.error('Elementos do carrossel não encontrados');
            return;
        }
        
        let currentSlide = 0;
        let autoPlayInterval;
        
        // Função para mudar slide
        function goToSlide(n) {
            slides[currentSlide].classList.remove('active');
            indicators[currentSlide].classList.remove('active');
            
            currentSlide = (n + slides.length) % slides.length;
            
            slides[currentSlide].classList.add('active');
            indicators[currentSlide].classList.add('active');
        }
        
        // Próximo slide
        function nextSlide() {
            goToSlide(currentSlide + 1);
        }
        
        // Slide anterior
        function prevSlide() {
            goToSlide(currentSlide - 1);
        }
        
        // Auto-play
        function startAutoPlay() {
            stopAutoPlay();
            autoPlayInterval = setInterval(nextSlide, 5000);
        }
        
        function stopAutoPlay() {
            if (autoPlayInterval) {
                clearInterval(autoPlayInterval);
            }
        }
        
        // Indicadores
        indicators.forEach(function(indicator, index) {
            indicator.addEventListener('click', function() {
                goToSlide(index);
                startAutoPlay();
            });
        });
        
        // Pausar auto-play ao passar o mouse
        if (carousel) {
            carousel.addEventListener('mouseenter', stopAutoPlay);
            carousel.addEventListener('mouseleave', startAutoPlay);
        }
        
        // Controle por teclado
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                prevSlide();
                startAutoPlay();
            } else if (e.key === 'ArrowRight') {
                nextSlide();
                startAutoPlay();
            }
        });
        
        // Iniciar auto-play
        console.log('Carrossel iniciado com auto-play');
        startAutoPlay();
    });
    </script>
    
    <!-- Botão WhatsApp Flutuante -->
    <div class="whatsapp-float" id="whatsapp-float">
        <a href="https://wa.me/5554991348163?text=Olá!%20Gostaria%20de%20mais%20informações%20sobre%20a%20APAFUT" target="_blank" rel="noopener" aria-label="Fale conosco no WhatsApp">
            <i class="fab fa-whatsapp"></i>
            <span class="whatsapp-text">Fale Conosco!</span>
        </a>
    </div>

    <!-- Pop-up de Intenção de Saída -->
    <div class="exit-intent-popup" id="exitPopup">
        <div class="popup-overlay" onclick="closeExitPopup()"></div>
        <div class="popup-content">
            <button class="popup-close" onclick="closeExitPopup()" aria-label="Fechar">
                <i class="fas fa-times"></i>
            </button>
            <div class="popup-body">
                <div class="popup-icon">
                    <i class="fas fa-futbol"></i>
                </div>
                <h3>Espere! Conheça a APAFUT</h3>
                <p class="popup-subtitle">Agende uma <strong>AVALIAÇÃO TÉCNICA</strong></p>
                <p>Preencha o formulário e nossa equipe entrará em contato:</p>
                <form class="popup-form" onsubmit="return submitExitForm(event)">
                    <input type="text" name="nome" placeholder="Seu nome" required>
                    <input type="tel" name="telefone" placeholder="WhatsApp" required>
                    <select name="categoria" required>
                        <option value="">Selecione a categoria</option>
                        <option value="sub7">Sub-7 (5-7 anos)</option>
                        <option value="sub8">Sub-8 (6-8 anos)</option>
                        <option value="sub9">Sub-9 (7-9 anos)</option>
                        <option value="sub10">Sub-10 (8-10 anos)</option>
                        <option value="sub11">Sub-11 (9-11 anos)</option>
                        <option value="sub12">Sub-12 (10-12 anos)</option>
                        <option value="sub13">Sub-13 (11-13 anos)</option>
                        <option value="sub14">Sub-14 (12-14 anos)</option>
                        <option value="sub15">Sub-15 (13-15 anos)</option>
                        <option value="sub17">Sub-17 (15-17 anos)</option>
                    </select>
                    <button type="submit" class="btn-popup-submit">
                        <i class="fab fa-whatsapp"></i> Quero Agendar Avaliação
                    </button>
                </form>
                <p class="popup-disclaimer">* Disponível para novos alunos</p>
            </div>
        </div>
    </div>
    
    <script src="assets/js/script.min.js" defer></script>
    <script src="assets/js/lazy-loader.min.js" defer></script>
    <script>
        // Botão WhatsApp flutuante - aparecer após scroll
        window.addEventListener('scroll', function() {
            const whatsappBtn = document.getElementById('whatsapp-float');
            if (window.scrollY > 300) {
                whatsappBtn.classList.add('visible');
            } else {
                whatsappBtn.classList.remove('visible');
            }
        });

        // Pop-up de intenção de saída
        let exitPopupShown = false;
        let mouseLeaveTimeout;

        document.addEventListener('mouseleave', function(e) {
            if (e.clientY < 0 && !exitPopupShown && window.scrollY > 500) {
                clearTimeout(mouseLeaveTimeout);
                mouseLeaveTimeout = setTimeout(function() {
                    showExitPopup();
                }, 500);
            }
        });

        document.addEventListener('mouseenter', function() {
            clearTimeout(mouseLeaveTimeout);
        });

        function showExitPopup() {
            if (!exitPopupShown && !sessionStorage.getItem('exitPopupShown')) {
                document.getElementById('exitPopup').classList.add('show');
                document.body.style.overflow = 'hidden';
                exitPopupShown = true;
                sessionStorage.setItem('exitPopupShown', 'true');
            }
        }

        function closeExitPopup() {
            document.getElementById('exitPopup').classList.remove('show');
            document.body.style.overflow = '';
        }

        function submitExitForm(e) {
            e.preventDefault();
            const form = e.target;
            const nome = form.nome.value;
            const telefone = form.telefone.value;
            const categoria = form.categoria.value;
            
            const mensagem = encodeURIComponent(
                `Olá! Gostaria de agendar uma avaliação técnica!\\n\\n` +
                `Nome: ${nome}\\n` +
                `Telefone: ${telefone}\\n` +
                `Categoria: ${categoria}`
            );
            
            window.open(`https://wa.me/5554991348163?text=${mensagem}`, '_blank');
            closeExitPopup();
            return false;
        }

        // Fechar popup com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeExitPopup();
            }
        });
    </script>
</body>
</html>