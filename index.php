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
            height: 60vh;
            min-height: 400px;
            overflow: hidden;
            background: #000;
        }
        
        .banner-slides {
            position: relative;
            width: 100%;
            height: 100%;
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
        }
        
        .banner-slide.active {
            opacity: 1;
            z-index: 2;
        }
        
        .banner-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
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
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            border: 2px solid white;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0;
        }
        
        .indicator.active {
            background: white;
            width: 40px;
            border-radius: 6px;
        }
        
        .indicator:hover {
            background: rgba(255, 255, 255, 0.8);
        }
        
        /* Responsivo */
        @media (max-width: 768px) {
            .banner-carousel {
                height: 50vh;
                min-height: 350px;
            }
            
            .banner-indicators {
                bottom: 20px;
            }
            
            .indicator {
                width: 10px;
                height: 10px;
            }
            
            .indicator.active {
                width: 30px;
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
                    <a href="https://wa.me/5554991592954?text=Olá!%20Gostaria%20de%20mais%20informações%20sobre%20a%20inscrição%20para%20aluno%20da%20APAFUT" target="_blank" rel="noopener" class="btn-agendar btn-aluno" aria-label="Entre em contato via WhatsApp">
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
    <section class="banner-carousel">
        <div class="banner-slides">
            <div class="banner-slide active">
                <img src="assets/images/banner1.jpg" alt="Banner 1" loading="eager">
            </div>
            <div class="banner-slide">
                <img src="assets/images/banner2.jpg" alt="Banner 2" loading="lazy">
            </div>
            <div class="banner-slide">
                <img src="assets/images/banner3.jpg" alt="Banner 3" loading="lazy">
            </div>
        </div>
        
        <!-- Indicadores -->
        <div class="banner-indicators">
            <button class="indicator active" data-slide="0" aria-label="Banner 1"></button>
            <button class="indicator" data-slide="1" aria-label="Banner 2"></button>
            <button class="indicator" data-slide="2" aria-label="Banner 3"></button>
        </div>
    </section>
    
    <section>
        <!-- Destaques -->
        <section id="noticias" class="destaques-section">
            <div class="destaques-container">
                <div class="destaques-wrapper">
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
                        
                        // Notícia principal
                        $principal = $noticias[0];
                        $imagemPrincipal = !empty($principal['imagem']) ? htmlspecialchars($principal['imagem']) : 'assets/fundo.jpg';
                        $dataPrincipal = date('d/m/Y', strtotime($principal['data_publicacao']));
                    ?>
                    
                    <div class="destaque-card destaque-card-principal">
                        <a href="noticia.php?id=<?= $principal['id'] ?>" class="destaque-link">
                            <img src="<?= $imagemPrincipal ?>" alt="<?= htmlspecialchars($principal['titulo']) ?>" class="destaque-imagem" loading="lazy">
                        </a>
                        <div class="destaque-borda">
                            <div class="destaque-borda-conteudo">
                                <span class="destaque-data"><?= $dataPrincipal ?></span>
                                <p class="destaque-resumo"><?= htmlspecialchars($principal['resumo']) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="destaques-grid-secundario">
                        <?php 
                        // Primeiro card secundário (esquerda)
                        $noticia = $noticias[1];
                        $imagemUrl = !empty($noticia['imagem']) ? htmlspecialchars($noticia['imagem']) : 'assets/fundo.jpg';
                        $dataNoticia = date('d/m/Y', strtotime($noticia['data_publicacao']));
                        ?>
                        <div class="destaque-card destaque-card-secundario">
                            <a href="noticia.php?id=<?= $noticia['id'] ?>" class="destaque-link">
                                <img src="<?= $imagemUrl ?>" alt="<?= htmlspecialchars($noticia['titulo']) ?>" class="destaque-imagem" loading="lazy">
                            </a>
                            <div class="destaque-borda">
                                <div class="destaque-borda-conteudo">
                                    <span class="destaque-data"><?= $dataNoticia ?></span>
                                    <p class="destaque-resumo"><?= htmlspecialchars($noticia['resumo']) ?></p>
                                </div>
                            </div>
                        </div>

                        <?php 
                        // Segundo card secundário (direita)
                        $noticia = $noticias[2];
                        $imagemUrl2 = !empty($noticia['imagem']) ? htmlspecialchars($noticia['imagem']) : 'assets/fundo.jpg';
                        $dataNoticia = date('d/m/Y', strtotime($noticia['data_publicacao']));
                        ?>
                        <div class="destaque-card destaque-card-secundario">
                            <a href="noticia.php?id=<?= $noticia['id'] ?>" class="destaque-link">
                                <img src="<?= $imagemUrl2 ?>" alt="<?= htmlspecialchars($noticia['titulo']) ?>" class="destaque-imagem" loading="lazy">
                            </a>
                            <div class="destaque-borda">
                                <div class="destaque-borda-conteudo">
                                    <span class="destaque-data"><?= $dataNoticia ?></span>
                                    <p class="destaque-resumo"><?= htmlspecialchars($noticia['resumo']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="destaques-grid-terciario">
                        <?php 
                        // Cards terciários
                        for ($i = 3; $i <= 5; $i++):
                            $noticia = $noticias[$i];
                            $dataNoticia = date('d/m/Y', strtotime($noticia['data_publicacao']));
                            $imagemTerciaria = !empty($noticia['imagem']) ? htmlspecialchars($noticia['imagem']) : 'assets/fundo.jpg';
                        ?>
                        <div class="destaque-card destaque-card-terciario">
                            <a href="noticia.php?id=<?= $noticia['id'] ?>" class="destaque-link">
                                <img src="<?= $imagemTerciaria ?>" alt="<?= htmlspecialchars($noticia['titulo']) ?>" class="destaque-imagem" loading="lazy">
                            </a>
                            <div class="destaque-borda">
                                <div class="destaque-borda-conteudo">
                                    <span class="destaque-data"><?= $dataNoticia ?></span>
                                    <p class="destaque-resumo"><?= htmlspecialchars($noticia['resumo']) ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endfor; ?>
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
    <!-- 6 depoimentos de pais-->
    <?php
    // Buscar depoimentos ativos
    $depoimentos = [];
    try {
        $stmt = $conn->query("SELECT * FROM depoimentos WHERE ativo = 1 ORDER BY ordem ASC LIMIT 10");
        $depoimentos = $stmt->fetchAll();
    } catch (Exception $e) {
        // Silencioso no front-end
    }
    ?>
    
    <?php if (!empty($depoimentos)): ?>
    <section id="depoimentos" class="depoimentos">
        <div class="depoimentos-header">
            <h2>O que Dizem sobre a Apafut</h2>
            <p>Conheça a experiência de quem faz parte da nossa família</p>
        </div>
        
        <div class="depoimentos-player">
            <!-- Player de Vídeo -->
            <div class="video-container">
                <video id="videoPlayer" controls>
                    <source id="videoSource" src="<?= htmlspecialchars(str_replace('../', '', $depoimentos[0]['video'])) ?>" type="video/mp4">
                    Seu navegador não suporta a tag de vídeo.
                </video>
            </div>
            
            <!-- Informações do Depoimento -->
            <div class="depoimento-info">
                <h3 id="depoimento-nome"><?= htmlspecialchars($depoimentos[0]['nome']) ?></h3>
                <p id="depoimento-descricao"><?= htmlspecialchars($depoimentos[0]['descricao'] ?? '') ?></p>
            </div>
            
            <!-- Controles de Navegação -->
            <div class="video-controls">
                <button id="prevVideo" class="control-btn" title="Anterior">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="video-indicator">
                    <span id="currentVideo">1</span> / <span id="totalVideos"><?= count($depoimentos) ?></span>
                </div>
                <button id="nextVideo" class="control-btn" title="Próximo">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </section>
    
    <script>
        // Dados dos depoimentos
        const depoimentos = <?= json_encode($depoimentos) ?>;
        let currentIndex = 0;
        const videoPlayer = document.getElementById('videoPlayer');
        const videoSource = document.getElementById('videoSource');
        
        // Atualizar informações do depoimento
        function updateDepoimentoInfo(index) {
            const depoimento = depoimentos[index];
            document.getElementById('depoimento-nome').textContent = depoimento.nome;
            document.getElementById('depoimento-descricao').textContent = depoimento.descricao || '';
            document.getElementById('currentVideo').textContent = index + 1;
        }
        
        // Carregar vídeo
        function loadVideo(index) {
            if (index >= 0 && index < depoimentos.length) {
                currentIndex = index;
                const depoimento = depoimentos[index];
                
                // Remover ../ do caminho
                const videoPath = depoimento.video.replace('../', '');
                videoSource.src = videoPath;
                videoPlayer.load();
                videoPlayer.play();
                
                updateDepoimentoInfo(index);
            }
        }
        
        // Ir para próximo vídeo
        function nextVideo() {
            const next = (currentIndex + 1) % depoimentos.length;
            loadVideo(next);
        }
        
        // Ir para vídeo anterior
        function prevVideo() {
            const prev = (currentIndex - 1 + depoimentos.length) % depoimentos.length;
            loadVideo(prev);
        }
        
        // Auto-play: ir para próximo quando terminar
        videoPlayer.addEventListener('ended', function() {
            setTimeout(nextVideo, 1000);
        });
        
        // Event Listeners para os botões
        document.getElementById('prevVideo')?.addEventListener('click', prevVideo);
        document.getElementById('nextVideo')?.addEventListener('click', nextVideo);
    </script>
    <?php endif; ?>

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
                    <span class="preco-por">12x R$ 20<span class="preco-mes">,00</span></span>
                </div>
                <div class="parcelamento">
                    <i class="fas fa-credit-card"></i> Cartão de crédito, Pix ou Boleto
                </div>
                <ul class="plano-beneficios">
                    <li><i class="fas fa-check"></i> Jantar Sócios</li>
                    <li><i class="fas fa-check"></i> Descontos Parceiros APA</li>
                    <li><i class="fas fa-check"></i> Ingressos Jogos Oficiais</li>
                </ul>
                <a href="#contato" class="btn-plano">Assinar Agora</a>
            </div>

            <!-- Plano Ouro (Destaque) -->
            <div class="plano-card plano-destaque">
                <div class="plano-badge badge-popular">Ouro</div>
                <h3>Sócio APA Ouro</h3>
                <div class="plano-preco">
                    <span class="preco-por">12x R$ 30<span class="preco-mes">,00</span></span>
                </div>
                <div class="parcelamento">
                    <i class="fas fa-credit-card"></i> Cartão de crédito ou Pix
                </div>
                <ul class="plano-beneficios">
                    <li><i class="fas fa-check"></i> Camiseta Oficial Temporada 2026</li>
                    <li><i class="fas fa-check"></i> Jantar Sócios</li>
                    <li><i class="fas fa-check"></i> Descontos Parceiros APA</li>
                    <li><i class="fas fa-check"></i> Ingressos Jogos Oficiais</li>
                </ul>
                <a href="#contato" class="btn-plano btn-plano-destaque">Garantir Vaga</a>
            </div>
            
            <!-- Plano Diamante -->
            <div class="plano-card plano-diamante">
                <div class="plano-badge badge-diamante">Diamante</div>
                <h3>Sócio APA Diamante</h3>
                <div class="plano-preco">
                    <span class="preco-por">12x R$ 60<span class="preco-mes">,00</span></span>
                </div>
                <div class="economia-tag">
                    <i class="fas fa-gem"></i> Premium
                </div>
                <div class="parcelamento">
                    <i class="fas fa-credit-card"></i> Cartão de crédito ou Pix
                </div>
                <ul class="plano-beneficios">
                    <li><i class="fas fa-check"></i> Kit Diamante</li>
                    <li><i class="fas fa-check"></i> Jantar Sócios</li>
                    <li><i class="fas fa-check"></i> Descontos Parceiros APA</li>
                    <li><i class="fas fa-check"></i> Ingressos Jogos Oficiais</li>
                </ul>
                <a href="#contato" class="btn-plano btn-plano-diamante">Assinar Agora</a>
            </div>
        </div>
    </section>
    </main>

    <!-- footer -->
    <footer id="contato" role="contentinfo" aria-label="Rodapé">
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo">
                    <img src="assets/logo.png" alt="Logo Apafut">
                    <h3>Apafut Caxias do Sul</h3>
                </div>
                <p>Formando campeões dentro e fora de campo desde 2010.</p>
                <div class="social-media" role="navigation" aria-label="Redes sociais">
                    <a href="#" aria-label="Visite nosso Facebook" target="_blank" rel="noopener"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                    <a href="#" aria-label="Visite nosso Instagram" target="_blank" rel="noopener"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                    <a href="#" aria-label="Fale conosco no WhatsApp" target="_blank" rel="noopener"><i class="fab fa-whatsapp" aria-hidden="true"></i></a>
                    <a href="#" aria-label="Visite nosso YouTube" target="_blank" rel="noopener"><i class="fab fa-youtube" aria-hidden="true"></i></a>
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
    
    <script src="assets/js/script.min.js" defer></script>
    <script src="assets/js/lazy-loader.min.js" defer></script>
</body>
</html>