<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config/db.php';

try {
    $conn = getConnection();
    
    // Buscar todas as notícias ativas
    $stmt = $conn->prepare("SELECT id, titulo, categoria, resumo, imagem, data_publicacao FROM noticias WHERE ativo = 1 ORDER BY data_publicacao DESC, created_at DESC");
    $stmt->execute();
    $noticias = $stmt->fetchAll();
    
} catch(PDOException $e) {
    error_log('Erro ao buscar notícias: ' . $e->getMessage());
    $noticias = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todas as Notícias - Apafut Caxias do Sul</title>
    <meta name="description" content="Confira todas as notícias e novidades da Apafut - Associação de Pais e Atletas de Futebol de Caxias do Sul">
    <meta name="keywords" content="apafut, notícias, futebol, caxias do sul, novidades">
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
    <link rel="stylesheet" href="assets/css/noticia.min.css">
    <style>
        .noticias-page {
            padding: 120px 20px 80px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .noticias-container {
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
        }
        
        .noticias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .noticia-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .noticia-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        
        .noticia-imagem {
            width: 100%;
            height: 220px;
            overflow: hidden;
            position: relative;
        }
        
        .noticia-imagem img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .noticia-card:hover .noticia-imagem img {
            transform: scale(1.05);
        }
        
        .noticia-categoria {
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
        
        .noticia-conteudo {
            padding: 24px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .noticia-titulo {
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
        
        .noticia-resumo {
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
        
        .noticia-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid #e9ecef;
        }
        
        .noticia-data {
            font-size: 0.875rem;
            color: #999;
        }
        
        .btn-ler-mais {
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
        
        .btn-ler-mais:hover {
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
        
        .sem-noticias {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            margin: 40px auto;
            max-width: 500px;
        }
        
        .sem-noticias i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .sem-noticias h3 {
            font-size: 1.5rem;
            color: #666;
            margin-bottom: 10px;
        }
        
        .sem-noticias p {
            color: #999;
        }
        
        @media (max-width: 768px) {
            .noticias-page {
                padding: 100px 15px 60px;
            }
            
            .noticias-grid {
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
        <nav role="navigation" aria-label="Menu principal">
            <div class="logo">
                <img src="assets/logo.png" alt="Logo Apafut">
            </div>
            <div class="menu">
                <ul>
                    <li><a href="index.php#home">Home</a></li>
                    <li><a href="index.php#sobre">Sobre</a></li>
                    <li><a href="index.php#categorias">Categorias</a></li>
                    <li><a href="index.php#depoimentos">Depoimentos</a></li>
                    <li><a href="index.php#planos">Planos</a></li>
                    <li><a href="index.php#noticias" class="active">Notícias</a></li>
                </ul>
            </div>
            <button class="menu-hamburguer" aria-label="Menu de navegação" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
        </nav>
    </header>

    <main class="noticias-page">
        <div class="noticias-container">
            <div class="page-header">
                <h1><i class="fas fa-newspaper"></i> Todas as Notícias</h1>
                <p>Fique por dentro de todas as novidades da Apafut</p>
            </div>

            <?php if (empty($noticias)): ?>
                <div class="sem-noticias">
                    <i class="fas fa-inbox"></i>
                    <h3>Nenhuma notícia disponível</h3>
                    <p>Em breve teremos novidades por aqui!</p>
                </div>
            <?php else: ?>
                <div class="noticias-grid">
                    <?php foreach ($noticias as $noticia): ?>
                        <article class="noticia-card">
                            <div class="noticia-imagem">
                                <img src="<?= htmlspecialchars($noticia['imagem']) ?>" 
                                     alt="<?= htmlspecialchars($noticia['titulo']) ?>"
                                     loading="lazy">
                                <span class="noticia-categoria"><?= htmlspecialchars($noticia['categoria']) ?></span>
                            </div>
                            <div class="noticia-conteudo">
                                <h2 class="noticia-titulo"><?= htmlspecialchars($noticia['titulo']) ?></h2>
                                <p class="noticia-resumo"><?= htmlspecialchars($noticia['resumo']) ?></p>
                                <div class="noticia-footer">
                                    <span class="noticia-data">
                                        <i class="far fa-calendar-alt"></i>
                                        <?= date('d/m/Y', strtotime($noticia['data_publicacao'])) ?>
                                    </span>
                                    <a href="noticia.php?id=<?= $noticia['id'] ?>" class="btn-ler-mais">
                                        Ler mais <i class="fas fa-arrow-right"></i>
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
                <div class="footer-contact-quick">
                    <a href="historia.html#unidades" class="footer-link-unidades">
                        <i class="fas fa-map-marked-alt"></i> Nossas Unidades
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
