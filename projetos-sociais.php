<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config/db.php';

try {
    $conn = getConnection();
    
    // Buscar notícias da categoria "Projetos Sociais"
    $stmt = $conn->prepare("SELECT id, titulo, categoria, resumo, imagem, data_publicacao FROM noticias WHERE ativo = 1 AND categoria = 'Projetos Sociais' ORDER BY data_publicacao DESC, created_at DESC LIMIT 6");
    $stmt->execute();
    $projetos = $stmt->fetchAll();
    
} catch(PDOException $e) {
    error_log('Erro ao buscar projetos sociais: ' . $e->getMessage());
    $projetos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projetos Sociais - Apafut Caxias do Sul</title>
    <meta name="description" content="Conheça os projetos sociais da Apafut - Associação de Pais e Atletas de Futebol de Caxias do Sul">
    <meta name="keywords" content="apafut, projetos sociais, responsabilidade social, futebol, caxias do sul">
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
    <link rel="stylesheet" href="assets/css/style.min.css">
    <link rel="stylesheet" href="assets/css/ctas.min.css">
    <link rel="stylesheet" href="assets/css/noticia.min.css">
    <style>
        .projetos-page {
            padding: 120px 20px 80px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .projetos-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .page-header h1 {
            font-size: clamp(2rem, 5vw, 3rem);
            color: var(--azul-secundario);
            margin-bottom: 15px;
        }
        
        .page-header p {
            font-size: 1.125rem;
            color: #666;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .projetos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .projeto-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .projeto-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        
        .projeto-imagem {
            width: 100%;
            height: 220px;
            overflow: hidden;
            position: relative;
        }
        
        .projeto-imagem img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .projeto-card:hover .projeto-imagem img {
            transform: scale(1.05);
        }
        
        .projeto-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--vermelho-primario);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .projeto-conteudo {
            padding: 24px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .projeto-titulo {
            font-size: 1.375rem;
            color: var(--azul-secundario);
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .projeto-resumo {
            font-size: 0.9375rem;
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }
        
        .projeto-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid #e9ecef;
        }
        
        .projeto-data {
            font-size: 0.875rem;
            color: #999;
        }
        
        .btn-saiba-mais {
            background: var(--vermelho-primario);
            color: white;
            padding: 10px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9375rem;
            transition: background 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-saiba-mais:hover {
            background: #c62828;
        }
        
        .btn-voltar {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--azul-secundario);
            color: white;
            padding: 14px 32px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            margin: 40px auto 0;
            transition: background 0.3s ease;
        }
        
        .btn-voltar:hover {
            background: #0d1440;
        }
        
        .voltar-container {
            text-align: center;
        }
        
        .sem-projetos {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            margin: 40px auto;
            max-width: 500px;
        }
        
        .sem-projetos i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .sem-projetos h3 {
            font-size: 1.5rem;
            color: #666;
            margin-bottom: 10px;
        }
        
        .sem-projetos p {
            color: #999;
        }
        
        @media (max-width: 768px) {
            .projetos-page {
                padding: 100px 15px 60px;
            }
            
            .projetos-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .page-header {
                margin-bottom: 40px;
            }
        }
    </style>
</head>
<body>
    <header role="banner">
        <!-- NAVBAR -->
        <nav role="navigation" aria-label="Menu principal">
            <a href="index.php" class="logo">
                <img src="assets/logo.png" alt="Logo Apafut">
            </a>
            <div class="menu">
                <ul>
                    <li><a href="index.php#home">Home</a></li>
                    <li><a href="index.php#sobre">Sobre</a></li>
                    <li><a href="index.php#categorias">Categorias</a></li>
                    <li><a href="index.php#depoimentos">Depoimentos</a></li>
                    <li><a href="index.php#noticias">Notícias</a></li>
                </ul>
                <div class="nav-buttons">
                    <a href="index.php#planos" class="btn-agendar" aria-label="Ver planos de sócio">Seja Sócio</a>
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

    <main class="projetos-page">
        <div class="projetos-container">
            <div class="page-header">
                <h1><i class="fas fa-hands-helping"></i> Projetos Sociais</h1>
                <p>Conheça as iniciativas sociais da Apafut que transformam vidas através do esporte e da solidariedade</p>
            </div>

            <?php if (empty($projetos)): ?>
                <div class="sem-projetos">
                    <i class="fas fa-heart"></i>
                    <h3>Em breve novos projetos</h3>
                    <p>Estamos trabalhando em novas iniciativas sociais!</p>
                </div>
            <?php else: ?>
                <div class="projetos-grid">
                    <?php foreach ($projetos as $projeto): ?>
                        <article class="projeto-card">
                            <div class="projeto-imagem">
                                <img src="<?= htmlspecialchars($projeto['imagem']) ?>" 
                                     alt="<?= htmlspecialchars($projeto['titulo']) ?>"
                                     loading="lazy">
                                <span class="projeto-badge">Projeto Social</span>
                            </div>
                            <div class="projeto-conteudo">
                                <h2 class="projeto-titulo"><?= htmlspecialchars($projeto['titulo']) ?></h2>
                                <p class="projeto-resumo"><?= htmlspecialchars($projeto['resumo']) ?></p>
                                <div class="projeto-footer">
                                    <span class="projeto-data">
                                        <i class="far fa-calendar-alt"></i>
                                        <?= date('d/m/Y', strtotime($projeto['data_publicacao'])) ?>
                                    </span>
                                    <a href="noticia.php?id=<?= $projeto['id'] ?>" class="btn-saiba-mais">
                                        Saiba mais <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="voltar-container">
                <a href="index.php#noticias" class="btn-voltar">
                    <i class="fas fa-arrow-left"></i> Voltar para Home
                </a>
            </div>
        </div>
    </main>

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
                    <a href="index.php#planos" class="footer-link-socio">
                        <i class="fas fa-id-card"></i> Seja Sócio Torcedor
                    </a>
                </div>
            </div>

            <div class="footer-section">
                <h4>Links Rápidos</h4>
                <ul>
                    <li><a href="index.php#home">Home</a></li>
                    <li><a href="index.php#sobre">Sobre Nós</a></li>
                    <li><a href="index.php#depoimentos">Depoimentos</a></li>
                    <li><a href="index.php#planos">Planos</a></li>
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

    <script src="assets/js/script.min.js"></script>
</body>
</html>
