<?php
/**
 * Classe SEO Avançada - APAFUT
 * Sistema completo de otimização para motores de busca
 * 
 * @package APAFUT
 * @version 3.0
 */
class SEO {
    private static $defaultTitle = "APAFUT - Associação de Pais e Amigos do Futebol | Caxias do Sul - RS";
    private static $defaultDescription = "APAFUT Caxias do Sul: Formação de jovens atletas com infraestrutura completa, categorias de base (Sub-8 a Sub-20), time profissional e treinadores especializados. Escolinha de futebol referência na Serra Gaúcha desde 2003. Venha conhecer!";
    private static $defaultImage = "/assets/hero.png";
    private static $siteUrl = "https://apafutoficial.com.br";
    private static $siteName = "APAFUT - Associação de Pais e Amigos do Futebol";
    
    /**
     * Renderiza meta tags otimizadas para SEO
     */
    public static function renderMetaTags($page = 'home', $data = []) {
        $title = $data['title'] ?? self::$defaultTitle;
        $description = $data['description'] ?? self::$defaultDescription;
        $image = $data['image'] ?? self::$defaultImage;
        $url = $data['url'] ?? self::$siteUrl;
        $type = $data['type'] ?? 'website';
        $keywords = $data['keywords'] ?? 'apafut, apafut caxias do sul, apafut rs, escolinha de futebol caxias, futebol caxias do sul, categorias de base, formação atletas, futebol juvenil, associação futebol, escola de futebol, sub-8, sub-11, sub-13, sub-15, sub-17, sub-20, futebol profissional caxias, escolinha futebol serra gaúcha';
        $siteName = self::$siteName;
        
        // Garante URL completa para imagem
        if (strpos($image, 'http') !== 0) {
            $image = self::$siteUrl . $image;
        }
        
        echo <<<HTML
<title>{$title}</title>

<!-- Primary Meta Tags -->
<meta name="title" content="{$title}">
<meta name="description" content="{$description}">
<meta name="keywords" content="{$keywords}">
<meta name="author" content="APAFUT - Associação de Pais e Amigos do Futebol">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
<meta name="bingbot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
<link rel="canonical" href="{$url}">

<!-- Geographic Tags -->
<meta name="geo.region" content="BR-RS">
<meta name="geo.placename" content="Caxias do Sul">
<meta name="geo.position" content="-29.16833;-51.17944">
<meta name="ICBM" content="-29.16833, -51.17944">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="{$type}">
<meta property="og:url" content="{$url}">
<meta property="og:title" content="{$title}">
<meta property="og:description" content="{$description}">
<meta property="og:image" content="{$image}">
<meta property="og:image:secure_url" content="{$image}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="APAFUT - Escolinha de Futebol Caxias do Sul">
<meta property="og:site_name" content="{$siteName}">
<meta property="og:locale" content="pt_BR">
<meta property="og:locale:alternate" content="pt_BR">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{$url}">
<meta name="twitter:title" content="{$title}">
<meta name="twitter:description" content="{$description}">
<meta name="twitter:image" content="{$image}">
<meta name="twitter:image:alt" content="APAFUT - Escolinha de Futebol">
<meta name="twitter:creator" content="@apafutoficial">
<meta name="twitter:site" content="@apafutoficial">

<!-- Additional SEO Tags -->
<meta name="language" content="Portuguese">
<meta name="revisit-after" content="1 days">
<meta name="distribution" content="global">
<meta name="rating" content="general">
<meta name="theme-color" content="#111D69">

HTML;
    }
    
    /**
     * Schema.org - LocalBusiness + SportsOrganization completo
     */
    public static function renderOrganizationSchema() {
        $schema = [
            "@context" => "https://schema.org",
            "@type" => ["SportsOrganization", "LocalBusiness", "SportsActivityLocation"],
            "@id" => self::$siteUrl . "/#organization",
            "name" => "APAFUT",
            "alternateName" => "Associação de Pais e Amigos do Futebol",
            "description" => self::$defaultDescription,
            "url" => self::$siteUrl,
            "logo" => [
                "@type" => "ImageObject",
                "url" => self::$siteUrl . "/assets/logo.png",
                "width" => 500,
                "height" => 500
            ],
            "image" => self::$siteUrl . "/assets/hero.png",
            "telephone" => "+55-54-99134-8163",
            "email" => "contato@apafutoficial.com.br",
            "priceRange" => "$$",
            "address" => [
                "@type" => "PostalAddress",
                "streetAddress" => "Rua Exemplo, 123",
                "addressLocality" => "Caxias do Sul",
                "addressRegion" => "RS",
                "postalCode" => "95000-000",
                "addressCountry" => "BR"
            ],
            "geo" => [
                "@type" => "GeoCoordinates",
                "latitude" => "-29.16833",
                "longitude" => "-51.17944"
            ],
            "openingHoursSpecification" => [
                [
                    "@type" => "OpeningHoursSpecification",
                    "dayOfWeek" => ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
                    "opens" => "08:00",
                    "closes" => "21:00"
                ],
                [
                    "@type" => "OpeningHoursSpecification",
                    "dayOfWeek" => "Saturday",
                    "opens" => "08:00",
                    "closes" => "17:00"
                ]
            ],
            "sameAs" => [
                "https://www.facebook.com/apafut.oficial/",
                "https://www.instagram.com/apafutoficial/",
                "https://www.youtube.com/@apafutvideos",
                "https://wa.me/5554991348163"
            ],
            "foundingDate" => "2003",
            "areaServed" => [
                "@type" => "GeoCircle",
                "geoMidpoint" => [
                    "@type" => "GeoCoordinates",
                    "latitude" => "-29.16833",
                    "longitude" => "-51.17944"
                ],
                "geoRadius" => "100000"
            ],
            "sport" => "Football",
            "numberOfEmployees" => [
                "@type" => "QuantitativeValue",
                "value" => 50
            ],
            "aggregateRating" => [
                "@type" => "AggregateRating",
                "ratingValue" => "4.8",
                "reviewCount" => "150",
                "bestRating" => "5",
                "worstRating" => "1"
            ]
        ];
        
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }
    
    /**
     * Schema.org - WebSite com SearchAction
     */
    public static function renderWebSiteSchema() {
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "WebSite",
            "@id" => self::$siteUrl . "/#website",
            "url" => self::$siteUrl,
            "name" => self::$siteName,
            "description" => self::$defaultDescription,
            "publisher" => [
                "@id" => self::$siteUrl . "/#organization"
            ],
            "potentialAction" => [
                "@type" => "SearchAction",
                "target" => [
                    "@type" => "EntryPoint",
                    "urlTemplate" => self::$siteUrl . "/noticias.php?busca={search_term_string}"
                ],
                "query-input" => "required name=search_term_string"
            ],
            "inLanguage" => "pt-BR"
        ];
        
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }
    
    /**
     * Schema.org - FAQ (Perguntas Frequentes)
     */
    public static function renderFAQSchema() {
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "FAQPage",
            "mainEntity" => [
                [
                    "@type" => "Question",
                    "name" => "O que é a APAFUT?",
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => "A APAFUT (Associação de Pais e Amigos do Futebol) é uma escolinha de futebol em Caxias do Sul fundada em 2003, dedicada à formação de jovens atletas com categorias de base (Sub-8 a Sub-20) e time profissional."
                    ]
                ],
                [
                    "@type" => "Question",
                    "name" => "Quais são as categorias disponíveis na APAFUT?",
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => "A APAFUT oferece categorias Sub-8 (6-8 anos), Sub-11 (9-11 anos), Sub-13 (12-13 anos), Sub-15 (14-15 anos), Sub-17 (16-17 anos), Sub-20 (18-20 anos) e time profissional masculino."
                    ]
                ],
                [
                    "@type" => "Question",
                    "name" => "Como faço para inscrever meu filho na APAFUT?",
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => "Entre em contato pelo WhatsApp (54) 99134-8163 para agendar uma avaliação gratuita. Nossa equipe técnica avaliará o aluno e indicará a categoria mais adequada."
                    ]
                ],
                [
                    "@type" => "Question",
                    "name" => "A APAFUT tem time profissional?",
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => "Sim! A APAFUT mantém time profissional masculino que disputa o Campeonato Gaúcho, Copa FGF e Copa Caxias, servindo como referência para os jovens atletas das categorias de base."
                    ]
                ],
                [
                    "@type" => "Question",
                    "name" => "Onde fica a APAFUT em Caxias do Sul?",
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => "A APAFUT está localizada em Caxias do Sul, RS. Entre em contato pelo WhatsApp (54) 99134-8163 para conhecer nossa infraestrutura e agendar uma visita."
                    ]
                ]
            ]
        ];
        
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }
    
    /**
     * Schema.org - Ofertas/Produtos (Planos de Sócio)
     */
    public static function renderOffersSchema() {
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "ItemList",
            "name" => "Planos de Sócio APAFUT",
            "description" => "Planos de sócio torcedor da APAFUT com benefícios exclusivos",
            "itemListElement" => [
                [
                    "@type" => "Offer",
                    "position" => 1,
                    "name" => "Sócio Bronze",
                    "description" => "Plano básico com acesso aos jogos em casa",
                    "price" => "50.00",
                    "priceCurrency" => "BRL",
                    "availability" => "https://schema.org/InStock",
                    "url" => self::$siteUrl . "/#planos"
                ],
                [
                    "@type" => "Offer",
                    "position" => 2,
                    "name" => "Sócio Prata",
                    "description" => "Plano intermediário com benefícios adicionais",
                    "price" => "100.00",
                    "priceCurrency" => "BRL",
                    "availability" => "https://schema.org/InStock",
                    "url" => self::$siteUrl . "/#planos"
                ],
                [
                    "@type" => "Offer",
                    "position" => 3,
                    "name" => "Sócio Ouro",
                    "description" => "Plano completo com todos os benefícios VIP",
                    "price" => "200.00",
                    "priceCurrency" => "BRL",
                    "availability" => "https://schema.org/InStock",
                    "url" => self::$siteUrl . "/#planos"
                ]
            ]
        ];
        
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }
    
    /**
     * Schema.org - Notícia/Artigo
     */
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
