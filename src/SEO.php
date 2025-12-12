<?php
class SEO {
    private static $defaultTitle = "APAFUT - Associação de Pais e Amigos do Futebol Caxias do Sul";
    private static $defaultDescription = "A APAFUT é uma associação sem fins lucrativos que promove o desenvolvimento de jovens atletas através do futebol, oferecendo categorias de base e programas de formação em Caxias do Sul/RS.";
    private static $defaultImage = "/assets/hero.png";
    private static $siteUrl = "http://localhost:8000";
    private static $siteName = "APAFUT";
    
    public static function renderMetaTags($page = 'home', $data = []) {
        $title = $data['title'] ?? self::$defaultTitle;
        $description = $data['description'] ?? self::$defaultDescription;
        $image = $data['image'] ?? self::$defaultImage;
        $url = $data['url'] ?? self::$siteUrl;
        $type = $data['type'] ?? 'website';
        $keywords = $data['keywords'] ?? 'futebol, categorias de base, escolinha de futebol, APAFUT, jovens atletas, formação esportiva, Caxias do Sul, futebol RS';
        $siteName = self::$siteName;
        
        // Garante URL completa para imagem
        if (strpos($image, 'http') !== 0) {
            $image = self::$siteUrl . $image;
        }
        
        echo <<<HTML
<!-- SEO Meta Tags -->
<meta name="description" content="{$description}">
<meta name="keywords" content="{$keywords}">
<meta name="author" content="APAFUT">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<link rel="canonical" href="{$url}">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="{$type}">
<meta property="og:url" content="{$url}">
<meta property="og:title" content="{$title}">
<meta property="og:description" content="{$description}">
<meta property="og:image" content="{$image}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:site_name" content="{$siteName}">
<meta property="og:locale" content="pt_BR">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{$url}">
<meta name="twitter:title" content="{$title}">
<meta name="twitter:description" content="{$description}">
<meta name="twitter:image" content="{$image}">

HTML;
    }
    
    public static function renderOrganizationSchema() {
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "SportsOrganization",
            "name" => "APAFUT",
            "description" => self::$defaultDescription,
            "url" => self::$siteUrl,
            "logo" => self::$siteUrl . "/assets/logo.png",
            "address" => [
                "@type" => "PostalAddress",
                "addressLocality" => "Caxias do Sul",
                "addressRegion" => "RS",
                "addressCountry" => "BR"
            ],
            "sameAs" => [
                "https://facebook.com/apafut",
                "https://instagram.com/apafut"
            ]
        ];
        
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }
    
    public static function renderNoticiaSchema($noticia) {
        $imageUrl = self::$siteUrl . '/' . ltrim($noticia['imagem'], '/');
        
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "NewsArticle",
            "headline" => htmlspecialchars($noticia['titulo'], ENT_QUOTES, 'UTF-8'),
            "description" => htmlspecialchars(substr(strip_tags($noticia['conteudo'] ?? ''), 0, 200), ENT_QUOTES, 'UTF-8'),
            "image" => $imageUrl,
            "datePublished" => date('c', strtotime($noticia['data_publicacao'])),
            "dateModified" => date('c', strtotime($noticia['data_publicacao'])),
            "author" => [
                "@type" => "Organization",
                "name" => "APAFUT"
            ],
            "publisher" => [
                "@type" => "Organization",
                "name" => "APAFUT",
                "logo" => [
                    "@type" => "ImageObject",
                    "url" => self::$siteUrl . "/assets/logo.png"
                ]
            ]
        ];
        
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }
    
    public static function renderBreadcrumbSchema($items) {
        $listItems = [];
        foreach ($items as $index => $item) {
            $listItems[] = [
                "@type" => "ListItem",
                "position" => $index + 1,
                "name" => $item['name'],
                "item" => self::$siteUrl . $item['url']
            ];
        }
        
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "BreadcrumbList",
            "itemListElement" => $listItems
        ];
        
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }
}
