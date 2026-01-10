<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config/security-headers.php';
require_once 'config/db.php';
require_once 'src/Security.php';
require_once 'src/SEO.php';
require_once 'src/Cache.php';
require_once 'src/BotProtection.php';

// Proteção contra bots
BotProtection::check();

// Obter e validar ID da notícia
$noticiaId = Security::validateInt($_GET['id'] ?? 0, 1);

if ($noticiaId === false) {
    header('Location: index.php#noticias');
    exit;
}

try {
    $conn = getConnection();
    
    // Buscar notícia com cache
    $noticia = Cache::remember('noticia_' . $noticiaId, function() use ($conn, $noticiaId) {
        $stmt = $conn->prepare("SELECT * FROM noticias WHERE id = :id AND ativo = 1");
        $stmt->bindParam(':id', $noticiaId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }, 1800); // 30 minutos
    
    if (!$noticia) {
        header('Location: index.php#noticias');
        exit;
    }
    
    // Sanitiza dados de saída para prevenir XSS
    $noticia['titulo'] = Security::sanitizeString($noticia['titulo']);
    $noticia['autor'] = Security::sanitizeString($noticia['autor']);
    $noticia['categoria'] = Security::sanitizeString($noticia['categoria']);
    $noticia['resumo'] = Security::sanitizeString($noticia['resumo']);
    // Conteúdo permite HTML básico
    $noticia['conteudo'] = Security::sanitizeHtml($noticia['conteudo'], '<p><br><strong><em><ul><ol><li><h2><h3><h4>');
    
    // Buscar as 2 notícias mais recentes cadastradas (exceto a atual)
    $relacionadas = Cache::remember('relacionadas_' . $noticiaId, function() use ($conn, $noticiaId) {
        $stmt = $conn->prepare("SELECT id, titulo, categoria, resumo, imagem, data_publicacao FROM noticias WHERE id != :id AND ativo = 1 ORDER BY created_at DESC LIMIT 2");
        $stmt->bindParam(':id', $noticiaId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }, 1800);
    
    // Sanitiza notícias relacionadas
    for ($i = 0; $i < count($relacionadas); $i++) {
        $relacionadas[$i]['titulo'] = Security::sanitizeString($relacionadas[$i]['titulo']);
        $relacionadas[$i]['categoria'] = Security::sanitizeString($relacionadas[$i]['categoria']);
    }
    
} catch(PDOException $e) {
    logError('Erro ao buscar notícia', [
        'id' => $noticiaId,
        'error' => $e->getMessage()
    ]);
    header('Location: index.php#noticias');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php 
    SEO::renderMetaTags('noticia', [
        'title' => htmlspecialchars($noticia['titulo']) . ' - Apafut Caxias do Sul',
        'description' => htmlspecialchars($noticia['resumo']),
        'keywords' => 'apafut, ' . htmlspecialchars($noticia['categoria']) . ', notícias futebol, caxias do sul',
        'image' => 'https://' . $_SERVER['HTTP_HOST'] . '/' . htmlspecialchars($noticia['imagem']),
        'type' => 'article'
    ]);
    
    SEO::renderNoticiaSchema($noticia);
    ?>
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
        }
        .skip-link:focus { top: 0; }
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link">Pular para o conteúdo principal</a>
    
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
                    <a href="index.php#planos" class="btn-agendar">Seja Sócio</a>
                    <a href="https://wa.me/5554991348163?text=Ol%C3%A1!%20Gostaria%20de%20mais%20informa%C3%A7%C3%B5es%20sobre%20a%20inscri%C3%A7%C3%A3o%20para%20aluno%20da%20APAFUT" target="_blank" class="btn-agendar btn-aluno">
                        <i class="fab fa-whatsapp"></i> Seja Aluno
                    </a>
                </div>
            </div>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>

    <!-- Conteúdo da Notícia -->
    <article class="noticia-completa">
        <div class="noticia-hero">
            <?php 
            $imagemUrl = !empty($noticia['imagem']) ? htmlspecialchars($noticia['imagem']) : 'assets/hero.png';
            ?>
            <img src="<?= $imagemUrl ?>" alt="<?= htmlspecialchars($noticia['titulo']) ?>" onerror="this.src='assets/hero.png'">
            <div class="noticia-hero-content">
                <span class="categoria-tag"><?= htmlspecialchars($noticia['categoria']) ?></span>
                <h1 class="titulo"><?= htmlspecialchars($noticia['titulo']) ?></h1>
                <div class="noticia-meta">
                    <span><i class="far fa-calendar"></i> <?= formatarData($noticia['data_publicacao']) ?></span>
                    <span><i class="far fa-user"></i> <?= htmlspecialchars($noticia['autor']) ?></span>
                    <span><i class="far fa-clock"></i> <?= $noticia['tempo_leitura'] ?> min de leitura</span>
                </div>
            </div>
        </div>

        <div class="noticia-conteudo">
            <div class="noticia-texto">
                <p class="lead"><?= htmlspecialchars($noticia['resumo']) ?></p>
                
                <?php if (!empty($noticia['conteudo'])): ?>
                    <?= $noticia['conteudo'] ?>
                <?php else: ?>
                    <p>Em uma disputa emocionante, o time Sub-17 da Apafut mostrou toda sua determinação e técnica para conquistar o título inédito do Regional da Serra. A final foi decidida nos pênaltis após um empate de 2 a 2 no tempo normal.</p>

                    <h2>Uma Campanha Impecável</h2>
                    <p>Durante toda a competição, o time demonstrou evolução constante. Com 8 vitórias, 2 empates e apenas 1 derrota na fase de grupos, a equipe garantiu a classificação com folga para as fases eliminatórias.</p>

                    <blockquote>
                        <p>"Foi uma campanha memorável. Os garotos trabalharam duro e mereceram esse título. Estou muito orgulhoso de cada um."</p>
                        <cite>— Prof. Fernando Costa, Treinador</cite>
                    </blockquote>

                    <h2>A Grande Final</h2>
                    <p>O jogo decisivo foi marcado por grande emoção. A Apafut abriu o placar logo aos 5 minutos, mas o adversário empatou antes do intervalo. No segundo tempo, a equipe voltou a ficar na frente, porém sofreu o empate nos minutos finais.</p>

                    <p>Na disputa de pênaltis, o goleiro Thiago Costa foi o grande herói, defendendo duas cobranças e garantindo o título para a academia.</p>
                <?php endif; ?>
                
                <?php if (!empty($noticia['depoimento_texto']) && !empty($noticia['depoimento_autor'])): ?>
                    <blockquote class="noticia-depoimento">
                        <p><?= nl2br(htmlspecialchars($noticia['depoimento_texto'])) ?></p>
                        <footer class="depoimento-autor">
                            <?= htmlspecialchars($noticia['depoimento_autor']) ?>
                        </footer>
                    </blockquote>
                <?php endif; ?>
            </div>

            <div class="noticia-compartilhar">
                <h3>Compartilhar</h3>
                <div class="social-share">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($_SERVER['REQUEST_URI']) ?>" target="_blank" class="share-btn facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode($_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($noticia['titulo']) ?>" target="_blank" class="share-btn twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://wa.me/?text=<?= urlencode($noticia['titulo'] . ' - ' . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="share-btn whatsapp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($_SERVER['REQUEST_URI']) ?>" target="_blank" class="share-btn linkedin">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>

            <a href="index.php#noticias" class="btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar para Notícias
            </a>
        </div>
    </article>
    </main>

    <!-- Notícias Relacionadas -->
    <section class="noticias-relacionadas" aria-labelledby="relacionadas-titulo">
        <div class="container">
            <h2 id="relacionadas-titulo">Outras Notícias</h2>
            <?php if (count($relacionadas) > 0): ?>
            <div class="sugestoes-grid">
                <?php foreach($relacionadas as $rel): ?>
                <div class="sugestao-card">
                    <a href="noticia.php?id=<?= $rel['id'] ?>" class="sugestao-link">
                        <?php 
                        $relImagemUrl = !empty($rel['imagem']) ? htmlspecialchars($rel['imagem']) : 'assets/images/fundo.jpg';
                        ?>
                        <img class="sugestao-imagem" src="<?= $relImagemUrl ?>" alt="<?= htmlspecialchars($rel['titulo']) ?>" onerror="this.src='assets/images/fundo.jpg'">
                        <div class="sugestao-borda">
                            <div class="sugestao-borda-conteudo">
                                <span class="sugestao-data"><?= formatarData($rel['data_publicacao']) ?></span>
                                <p class="sugestao-resumo"><?= htmlspecialchars($rel['resumo']) ?></p>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p style="text-align: center; padding: 20px;">Nenhuma notícia disponível no momento.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer role="contentinfo" aria-label="Rodapé">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Apafut Caxias do Sul</h3>
                <p>Formando atletas de alta performance com dedicação e excelência.</p>
            </div>
            <div class="footer-section">
                <h3>Contato</h3>
                <p><i class="fab fa-whatsapp"></i> <a href="https://wa.me/5554991348163" target="_blank" rel="noopener" style="color: inherit; text-decoration: none;">(54) 9134-8163</a></p>
                <p><i class="fas fa-envelope"></i> apafutoficial@gmail.com</p>
            </div>
            <div class="footer-section">
                <h3>Redes Sociais</h3>
                <div class="social-links">
                    <a href="https://www.facebook.com/apafut.oficial/" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/apafutoficial/" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Apafut. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="assets/js/script.min.js" defer></script>
</body>
</html>
