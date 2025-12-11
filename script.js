document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const menu = document.querySelector('.menu');

    if (hamburger && menu) {
        // Toggle menu ao clicar no hamburger
        hamburger.addEventListener('click', function() {
            menu.classList.toggle('active');
            hamburger.classList.toggle('active');
            
            // Trocar entre hamburger e X
            if (hamburger.classList.contains('active')) {
                hamburger.innerHTML = '<i class="fas fa-times" style="font-size: inherit;"></i>';
                // Prevenir scroll quando menu está aberto
                document.body.style.overflow = 'hidden';
            } else {
                hamburger.innerHTML = '<span></span><span></span><span></span>';
                // Restaurar scroll quando menu fecha
                document.body.style.overflow = '';
            }
        });

        // Fechar menu ao clicar em um link
        const menuLinks = menu.querySelectorAll('a');
        menuLinks.forEach(link => {
            link.addEventListener('click', function() {
                menu.classList.remove('active');
                hamburger.classList.remove('active');
                hamburger.innerHTML = '<span></span><span></span><span></span>';
                // Restaurar scroll
                document.body.style.overflow = '';
            });
        });

        // Fechar menu ao pressionar ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && menu.classList.contains('active')) {
                menu.classList.remove('active');
                hamburger.classList.remove('active');
                hamburger.innerHTML = '<span></span><span></span><span></span>';
                document.body.style.overflow = '';
            }
        });
    }

    // Carousel
    const carousel = document.querySelector('.carousel');
    if (carousel) {
        const images = carousel.querySelectorAll('img');
        const prevBtn = carousel.querySelector('.prev');
        const nextBtn = carousel.querySelector('.next');
        let currentIndex = 0;
        let autoPlayInterval;
        let touchStartX = 0;
        let touchEndX = 0;

        // Garantir que a primeira imagem seja exibida
        if (images.length > 0) {
            images[0].classList.add('active');
        }

        function showImage(index) {
            images.forEach((img, i) => {
                img.classList.remove('active');
                if (i === index) {
                    img.classList.add('active');
                }
            });
        }

        function nextImage() {
            currentIndex = (currentIndex + 1) % images.length;
            showImage(currentIndex);
        }

        function prevImage() {
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            showImage(currentIndex);
        }

        function startAutoPlay() {
            autoPlayInterval = setInterval(nextImage, 5000); // Muda a cada 5 segundos
        }

        function stopAutoPlay() {
            clearInterval(autoPlayInterval);
        }

        // Event listeners para os botões
        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                nextImage();
                stopAutoPlay();
                startAutoPlay(); // Reinicia o autoplay
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                prevImage();
                stopAutoPlay();
                startAutoPlay(); // Reinicia o autoplay
            });
        }

        // Suporte para touch/swipe em mobile
        carousel.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
            stopAutoPlay();
        }, { passive: true });

        carousel.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
            startAutoPlay();
        }, { passive: true });

        function handleSwipe() {
            const swipeThreshold = 50; // Mínimo de pixels para considerar um swipe
            const diff = touchStartX - touchEndX;

            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Swipe left - próxima imagem
                    nextImage();
                } else {
                    // Swipe right - imagem anterior
                    prevImage();
                }
            }
        }

        // Pausar autoplay quando o mouse estiver sobre o carousel
        carousel.addEventListener('mouseenter', stopAutoPlay);
        carousel.addEventListener('mouseleave', startAutoPlay);

        // Pausar autoplay quando a aba não está visível
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopAutoPlay();
            } else {
                startAutoPlay();
            }
        });

        // Iniciar autoplay
        startAutoPlay();
    }

    // Smooth scroll para links de navegação
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const headerHeight = document.querySelector('header').offsetHeight;
                const targetPosition = target.offsetTop - headerHeight;
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Toggle detalhes das categorias - NOVA ABORDAGEM COM DELEGAÇÃO
    const categoriasSection = document.querySelector('#categorias');
    
    if (categoriasSection) {
        categoriasSection.addEventListener('click', function(e) {
            // Verificar se o clique foi em um h3 dentro de um card com data-categoria
            const clickedTitle = e.target.closest('.card[data-categoria] h3');
            
            if (clickedTitle) {
                e.stopPropagation();
                e.preventDefault();
                
                // Encontrar o card pai
                const parentCard = clickedTitle.closest('.card[data-categoria]');
                
                if (parentCard) {
                    // Pegar o valor único do data-categoria
                    const categoria = parentCard.getAttribute('data-categoria');
                    
                    // Verificar se este card está ativo
                    const isActive = parentCard.classList.contains('active');
                    
                    // Remover active de TODOS os cards
                    const allCards = categoriasSection.querySelectorAll('.card[data-categoria]');
                    allCards.forEach(card => card.classList.remove('active'));
                    
                    // Se não estava ativo, ativar apenas este
                    if (!isActive) {
                        parentCard.classList.add('active');
                    }
                }
            }
        }, false);
    }

    // Carrossel de Jogadores
    const jogadoresContainer = document.querySelector('.jogadores-container');
    const prevJogadorBtn = document.querySelector('.prev-jogador');
    const nextJogadorBtn = document.querySelector('.next-jogador');

    if (jogadoresContainer && prevJogadorBtn && nextJogadorBtn) {
        const scrollAmount = 275; // largura do card + gap

        nextJogadorBtn.addEventListener('click', () => {
            jogadoresContainer.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
        });

        prevJogadorBtn.addEventListener('click', () => {
            jogadoresContainer.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
        });

        // Suporte para touch/swipe em mobile
        let startX;
        let scrollLeft;

        jogadoresContainer.addEventListener('touchstart', (e) => {
            startX = e.touches[0].pageX - jogadoresContainer.offsetLeft;
            scrollLeft = jogadoresContainer.scrollLeft;
        });

        jogadoresContainer.addEventListener('touchmove', (e) => {
            if (!startX) return;
            const x = e.touches[0].pageX - jogadoresContainer.offsetLeft;
            const walk = (x - startX) * 2;
            jogadoresContainer.scrollLeft = scrollLeft - walk;
        });

        jogadoresContainer.addEventListener('touchend', () => {
            startX = null;
        });
    }

    // Carrossel de Notícias
    const noticiasContainer = document.querySelector('.noticias-container');
    const noticiaCards = document.querySelectorAll('.noticia-card');
    const prevNoticiaBtn = document.querySelector('.prev-noticia');
    const nextNoticiaBtn = document.querySelector('.next-noticia');
    const dots = document.querySelectorAll('.carousel-dots .dot');
    let currentNoticiaIndex = 0;
    let noticiaAutoPlayInterval;

    if (noticiasContainer && noticiaCards.length > 0) {
        function showNoticia(index) {
            noticiaCards.forEach((card, i) => {
                card.classList.remove('active');
                if (i === index) {
                    card.classList.add('active');
                }
            });

            // Atualizar dots
            dots.forEach((dot, i) => {
                dot.classList.remove('active');
                if (i === index) {
                    dot.classList.add('active');
                }
            });
        }

        function nextNoticia() {
            currentNoticiaIndex = (currentNoticiaIndex + 1) % noticiaCards.length;
            showNoticia(currentNoticiaIndex);
        }

        function prevNoticia() {
            currentNoticiaIndex = (currentNoticiaIndex - 1 + noticiaCards.length) % noticiaCards.length;
            showNoticia(currentNoticiaIndex);
        }

        function startNoticiaAutoPlay() {
            noticiaAutoPlayInterval = setInterval(nextNoticia, 6000); // Muda a cada 6 segundos
        }

        function stopNoticiaAutoPlay() {
            clearInterval(noticiaAutoPlayInterval);
        }

        // Botões de navegação
        if (nextNoticiaBtn) {
            nextNoticiaBtn.addEventListener('click', function() {
                nextNoticia();
                stopNoticiaAutoPlay();
                startNoticiaAutoPlay();
            });
        }

        if (prevNoticiaBtn) {
            prevNoticiaBtn.addEventListener('click', function() {
                prevNoticia();
                stopNoticiaAutoPlay();
                startNoticiaAutoPlay();
            });
        }

        // Dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', function() {
                currentNoticiaIndex = index;
                showNoticia(currentNoticiaIndex);
                stopNoticiaAutoPlay();
                startNoticiaAutoPlay();
            });
        });

        // Suporte para swipe em mobile
        let touchStartXNoticia = 0;
        let touchEndXNoticia = 0;

        noticiasContainer.addEventListener('touchstart', (e) => {
            touchStartXNoticia = e.changedTouches[0].screenX;
        });

        noticiasContainer.addEventListener('touchend', (e) => {
            touchEndXNoticia = e.changedTouches[0].screenX;
            handleSwipeNoticia();
        });

        function handleSwipeNoticia() {
            if (touchEndXNoticia < touchStartXNoticia - 50) {
                nextNoticia();
                stopNoticiaAutoPlay();
                startNoticiaAutoPlay();
            }
            if (touchEndXNoticia > touchStartXNoticia + 50) {
                prevNoticia();
                stopNoticiaAutoPlay();
                startNoticiaAutoPlay();
            }
        }

        // Iniciar autoplay
        startNoticiaAutoPlay();

        // Pausar ao passar o mouse
        noticiasContainer.addEventListener('mouseenter', stopNoticiaAutoPlay);
        noticiasContainer.addEventListener('mouseleave', startNoticiaAutoPlay);
    }

    // Melhorar performance de scroll
    let ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                // Adicionar classe ao header quando rolar a página
                const header = document.querySelector('header');
                if (window.scrollY > 50) {
                    header.style.boxShadow = '0 4px 15px rgba(0, 0, 0, 0.1)';
                } else {
                    header.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
                }
                ticking = false;
            });
            ticking = true;
        }
    });
});
