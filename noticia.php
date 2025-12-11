<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config/db.php';
require_once 'src/Security.php';

// Define headers de segurança
Security::setSecurityHeaders();

// Obter e validar ID da notícia
$noticiaId = Security::validateInt($_GET['id'] ?? 0, 1);

if ($noticiaId === false) {
    header('Location: index.php#noticias');
    exit;
}

try {
    $conn = getConnection();
    
    // Buscar notícia
    $stmt = $conn->prepare("SELECT * FROM noticias WHERE id = :id AND ativo = 1");
    $stmt->bindParam(':id', $noticiaId, PDO::PARAM_INT);
    $stmt->execute();
    $noticia = $stmt->fetch();
    
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
    
    // Buscar notícias relacionadas (mesma categoria ou mais recentes)
    $stmt = $conn->prepare("SELECT id, titulo, categoria, imagem, data_publicacao FROM noticias WHERE categoria = :categoria AND id != :id AND ativo = 1 ORDER BY data_publicacao DESC LIMIT 6");
    $stmt->bindParam(':categoria', $noticia['categoria'], PDO::PARAM_STR);
    $stmt->bindParam(':id', $noticiaId, PDO::PARAM_INT);
    $stmt->execute();
    $relacionadas = $stmt->fetchAll();
    
    // Se não houver notícias suficientes da mesma categoria, buscar outras
    if (count($relacionadas) < 3) {
        $stmt = $conn->prepare("SELECT id, titulo, categoria, imagem, data_publicacao FROM noticias WHERE id != :id AND ativo = 1 ORDER BY data_publicacao DESC LIMIT 6");
        $stmt->bindParam(':id', $noticiaId, PDO::PARAM_INT);
        $stmt->execute();
        $relacionadas = $stmt->fetchAll();
    }
    
    // Sanitiza notícias relacionadas
    foreach ($relacionadas as &$rel) {
        $rel['titulo'] = Security::sanitizeString($rel['titulo']);
        $rel['categoria'] = Security::sanitizeString($rel['categoria']);
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="<?= htmlspecialchars($noticia['resumo']) ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?= htmlspecialchars($noticia['titulo']) ?> - Apafut Caxias do Sul</title>
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
    <link rel="stylesheet" href="assets/css/noticia.css">
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
                    <li><a href="index.php#home">Home</a></li>
                    <li><a href="index.php#sobre">Sobre</a></li>
                    <li><a href="index.php#categorias">Categorias</a></li>
                    <li><a href="index.php#depoimentos">Depoimentos</a></li>
                    <li><a href="index.php#noticias">Notícias</a></li>
                </ul>
                <a href="index.php#planos" class="btn-agendar">Seja Sócio</a>
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
                    <?= nl2br(htmlspecialchars($noticia['conteudo'])) ?>
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

    <?php if (count($relacionadas) > 0): ?>
    <!-- Notícias Relacionadas -->
    <section class="noticias-relacionadas">
        <div class="container">
            <h2>Notícias Relacionadas</h2>
            <div class="noticias-grid">
                <?php foreach($relacionadas as $rel): ?>
                <a href="noticia.php?id=<?= $rel['id'] ?>" class="mini-noticia-card">
                    <?php 
                    $relImagemUrl = !empty($rel['imagem']) ? htmlspecialchars($rel['imagem']) : 'assets/hero.png';
                    ?>
                    <img src="<?= $relImagemUrl ?>" alt="<?= htmlspecialchars($rel['titulo']) ?>" onerror="this.src='assets/hero.png'">
                    <div class="mini-noticia-conteudo">
                        <span class="mini-categoria"><?= htmlspecialchars($rel['categoria']) ?></span>
                        <h3><?= htmlspecialchars($rel['titulo']) ?></h3>
                        <p class="mini-data"><i class="far fa-calendar"></i> <?= formatarData($rel['data_publicacao']) ?></p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Apafut Caxias do Sul</h3>
                <p>Formando atletas de alta performance com dedicação e excelência.</p>
            </div>
            <div class="footer-section">
                <h3>Contato</h3>
                <p><i class="fas fa-phone"></i> (54) 3221-0000</p>
                <p><i class="fas fa-envelope"></i> contato@apafut.com.br</p>
            </div>
            <div class="footer-section">
                <h3>Redes Sociais</h3>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Apafut. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
