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

    // Toggle detalhes das categorias - Clique no card inteiro ou no h3
    const categoriasSection = document.querySelector('#categorias');
    
    if (categoriasSection) {
        // Adicionar evento de clique em cada card
        const categoriaCards = categoriasSection.querySelectorAll('.card[data-categoria]');
        
        categoriaCards.forEach(card => {
            const title = card.querySelector('h3');
            
            if (title) {
                // Adicionar cursor pointer no h3
                title.style.cursor = 'pointer';
                
                // Adicionar evento no card inteiro
                card.addEventListener('click', function(e) {
                    // Verificar se este card está ativo
                    const isActive = this.classList.contains('active');
                    
                    // Remover active de TODOS os cards
                    categoriaCards.forEach(c => c.classList.remove('active'));
                    
                    // Se não estava ativo, ativar apenas este
                    if (!isActive) {
                        this.classList.add('active');
                    }
                });
            }
        });
    }

    // Carrossel de Jogadores
    const jogadoresContainer = document.querySelector('.jogadores-container');
    const prevJogadorBtn = document.querySelector('.prev-jogador');
    const nextJogadorBtn = document.querySelector('.next-jogador');

    if (jogadoresContainer && prevJogadorBtn && nextJogadorBtn) {
        const scrollAmount = 250; // largura do card + gap ajustada

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

    // Carrossel da Comissão Técnica
    const comissaoContainer = document.querySelector('.comissao-container');
    const prevComissaoBtn = document.querySelector('.prev-comissao');
    const nextComissaoBtn = document.querySelector('.next-comissao');

    if (comissaoContainer && prevComissaoBtn && nextComissaoBtn) {
        const scrollAmount = 250;

        nextComissaoBtn.addEventListener('click', () => {
            comissaoContainer.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
        });

        prevComissaoBtn.addEventListener('click', () => {
            comissaoContainer.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
        });

        // Suporte para touch/swipe em mobile
        let startXComissao;
        let scrollLeftComissao;

        comissaoContainer.addEventListener('touchstart', (e) => {
            startXComissao = e.touches[0].pageX - comissaoContainer.offsetLeft;
            scrollLeftComissao = comissaoContainer.scrollLeft;
        });

        comissaoContainer.addEventListener('touchmove', (e) => {
            if (!startXComissao) return;
            const x = e.touches[0].pageX - comissaoContainer.offsetLeft;
            const walk = (x - startXComissao) * 2;
            comissaoContainer.scrollLeft = scrollLeftComissao - walk;
        });

        comissaoContainer.addEventListener('touchend', () => {
            startXComissao = null;
        });
    }

    // Tabs Profissional: Elenco e Comissão Técnica
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    if (tabBtns.length > 0) {
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetTab = btn.getAttribute('data-tab');
                
                // Remove active de todos os botões
                tabBtns.forEach(b => b.classList.remove('active'));
                // Adiciona active ao botão clicado
                btn.classList.add('active');
                
                // Esconde todos os conteúdos
                tabContents.forEach(content => {
                    content.classList.remove('active');
                });
                
                // Mostra o conteúdo da aba selecionada
                const targetContent = document.getElementById(targetTab);
                if (targetContent) {
                    targetContent.classList.add('active');
                    
                    // Resetar scroll para o início quando mudar de aba - múltiplas tentativas
                    const resetScroll = () => {
                        const container = targetContent.querySelector('.jogadores-container, .comissao-container');
                        if (container) {
                            container.scrollTo({ left: 0, behavior: 'instant' });
                            container.scrollLeft = 0;
                            console.log('Scroll resetado para:', targetTab, 'scrollLeft:', container.scrollLeft);
                        }
                    };
                    
                    // Tentar múltiplas vezes para garantir
                    setTimeout(resetScroll, 0);
                    setTimeout(resetScroll, 50);
                    setTimeout(resetScroll, 150);
                    setTimeout(resetScroll, 300);
                }
            });
        });
    }

    // Filtros de Posição dos Jogadores
    const filtrosBtns = document.querySelectorAll('.filtro-btn');
    const jogadorCards = document.querySelectorAll('.jogador-card');

    if (filtrosBtns.length > 0 && jogadorCards.length > 0) {
        filtrosBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active de todos os botões
                filtrosBtns.forEach(b => b.classList.remove('active'));
                // Adiciona active ao botão clicado
                btn.classList.add('active');

                const posicaoSelecionada = btn.getAttribute('data-posicao');

                jogadorCards.forEach(card => {
                    const posicaoCard = card.getAttribute('data-posicao');
                    
                    if (posicaoSelecionada === 'todos') {
                        // Mostra todos os cards
                        card.style.display = 'flex';
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'scale(1)';
                        }, 10);
                    } else if (posicaoCard === posicaoSelecionada || 
                               (posicaoSelecionada === 'Atacante' && posicaoCard === 'Atacante')) {
                        // Mostra cards da posição selecionada
                        card.style.display = 'flex';
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'scale(1)';
                        }, 10);
                    } else {
                        // Esconde cards de outras posições
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.8)';
                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 300);
                    }
                });

                // Scroll suave para o início da seção após filtrar
                if (jogadoresContainer) {
                    jogadoresContainer.scrollTo({
                        left: 0,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Adicionar transição CSS aos cards
        jogadorCards.forEach(card => {
            card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
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

    // Carrossel de Eventos Sociais (historia.html)
    const eventosContainer = document.querySelector('.eventos-container');
    const eventoCards = document.querySelectorAll('.evento-card');
    const prevEventoBtn = document.querySelector('.prev-evento');
    const nextEventoBtn = document.querySelector('.next-evento');
    const eventosDots = document.querySelectorAll('#eventos-dots .dot');
    let currentEventoIndex = 0;
    let eventoAutoPlayInterval;

    if (eventosContainer && eventoCards.length > 0) {
        function showEvento(index) {
            eventoCards.forEach((card, i) => {
                card.classList.remove('active');
                if (i === index) {
                    card.classList.add('active');
                }
            });

            // Atualizar dots
            eventosDots.forEach((dot, i) => {
                dot.classList.remove('active');
                if (i === index) {
                    dot.classList.add('active');
                }
            });
        }

        function nextEvento() {
            currentEventoIndex = (currentEventoIndex + 1) % eventoCards.length;
            showEvento(currentEventoIndex);
        }

        function prevEvento() {
            currentEventoIndex = (currentEventoIndex - 1 + eventoCards.length) % eventoCards.length;
            showEvento(currentEventoIndex);
        }

        function startEventoAutoPlay() {
            eventoAutoPlayInterval = setInterval(nextEvento, 6000);
        }

        function stopEventoAutoPlay() {
            clearInterval(eventoAutoPlayInterval);
        }

        // Botões de navegação
        if (nextEventoBtn) {
            nextEventoBtn.addEventListener('click', function() {
                nextEvento();
                stopEventoAutoPlay();
                startEventoAutoPlay();
            });
        }

        if (prevEventoBtn) {
            prevEventoBtn.addEventListener('click', function() {
                prevEvento();
                stopEventoAutoPlay();
                startEventoAutoPlay();
            });
        }

        // Dots
        eventosDots.forEach((dot, index) => {
            dot.addEventListener('click', function() {
                currentEventoIndex = index;
                showEvento(currentEventoIndex);
                stopEventoAutoPlay();
                startEventoAutoPlay();
            });
        });

        // Suporte para swipe em mobile
        let touchStartXEvento = 0;
        let touchEndXEvento = 0;

        eventosContainer.addEventListener('touchstart', (e) => {
            touchStartXEvento = e.changedTouches[0].screenX;
        });

        eventosContainer.addEventListener('touchend', (e) => {
            touchEndXEvento = e.changedTouches[0].screenX;
            handleSwipeEvento();
        });

        function handleSwipeEvento() {
            if (touchEndXEvento < touchStartXEvento - 50) {
                nextEvento();
                stopEventoAutoPlay();
                startEventoAutoPlay();
            }
            if (touchEndXEvento > touchStartXEvento + 50) {
                prevEvento();
                stopEventoAutoPlay();
                startEventoAutoPlay();
            }
        }

        // Iniciar autoplay
        startEventoAutoPlay();

        // Pausar ao passar o mouse
        eventosContainer.addEventListener('mouseenter', stopEventoAutoPlay);
        eventosContainer.addEventListener('mouseleave', startEventoAutoPlay);
    }

    // Modal para Eventos Sociais
    const eventoCardsModal = document.querySelectorAll('.evento-card');
    const modalOverlay = document.getElementById('evento-modal');
    const modalImage = modalOverlay ? modalOverlay.querySelector('.modal-image img') : null;
    const modalTitle = modalOverlay ? modalOverlay.querySelector('#modal-title') : null;
    const modalDesc = modalOverlay ? modalOverlay.querySelector('#modal-desc') : null;
    const modalClose = modalOverlay ? modalOverlay.querySelector('.modal-close') : null;

    function openEventoModal(card) {
        if (!modalOverlay) return;
        const title = card.getAttribute('data-title') || card.querySelector('h3')?.innerText || '';
        const desc = card.getAttribute('data-desc') || card.querySelector('p')?.innerText || '';
        const img = card.getAttribute('data-img') || card.querySelector('img')?.getAttribute('src') || '';

        if (modalTitle) modalTitle.textContent = title;
        if (modalDesc) modalDesc.textContent = desc;
        if (modalImage) {
            modalImage.setAttribute('src', img);
            modalImage.setAttribute('alt', title);
        }

        modalOverlay.classList.add('open');
        modalOverlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        // Trap focus: move focus to close button
        if (modalClose) modalClose.focus();
    }

    function closeEventoModal() {
        if (!modalOverlay) return;
        modalOverlay.classList.remove('open');
        modalOverlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    eventoCardsModal.forEach(card => {
        card.addEventListener('click', function() {
            openEventoModal(card);
        });
        // make keyboard accessible
        card.setAttribute('tabindex', '0');
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openEventoModal(card);
            }
        });
    });

    if (modalClose) modalClose.addEventListener('click', closeEventoModal);
    if (modalOverlay) modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) closeEventoModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeEventoModal();
    });

    // Carrossel da Galeria (historia.html)
    const galeriaSlides = document.querySelectorAll('.galeria-slide');
    const prevGaleriaBtn = document.querySelector('.prev-galeria');
    const nextGaleriaBtn = document.querySelector('.next-galeria');
    const galeriaDots = document.querySelectorAll('#galeria-dots .dot');
    let currentGaleriaIndex = 0;
    let galeriaAutoPlayInterval;

    if (galeriaSlides.length > 0) {
        function showGaleriaSlide(index) {
            galeriaSlides.forEach((slide, i) => {
                slide.classList.remove('active');
                if (i === index) {
                    slide.classList.add('active');
                }
            });

            // Atualizar dots
            galeriaDots.forEach((dot, i) => {
                dot.classList.remove('active');
                if (i === index) {
                    dot.classList.add('active');
                }
            });
        }

        function nextGaleria() {
            currentGaleriaIndex = (currentGaleriaIndex + 1) % galeriaSlides.length;
            showGaleriaSlide(currentGaleriaIndex);
        }

        function prevGaleria() {
            currentGaleriaIndex = (currentGaleriaIndex - 1 + galeriaSlides.length) % galeriaSlides.length;
            showGaleriaSlide(currentGaleriaIndex);
        }

        function startGaleriaAutoPlay() {
            galeriaAutoPlayInterval = setInterval(nextGaleria, 5000);
        }

        function stopGaleriaAutoPlay() {
            clearInterval(galeriaAutoPlayInterval);
        }

        // Botões de navegação
        if (nextGaleriaBtn) {
            nextGaleriaBtn.addEventListener('click', function() {
                nextGaleria();
                stopGaleriaAutoPlay();
                startGaleriaAutoPlay();
            });
        }

        if (prevGaleriaBtn) {
            prevGaleriaBtn.addEventListener('click', function() {
                prevGaleria();
                stopGaleriaAutoPlay();
                startGaleriaAutoPlay();
            });
        }

        // Dots
        galeriaDots.forEach((dot, index) => {
            dot.addEventListener('click', function() {
                currentGaleriaIndex = index;
                showGaleriaSlide(currentGaleriaIndex);
                stopGaleriaAutoPlay();
                startGaleriaAutoPlay();
            });
        });

        // Suporte para swipe em mobile
        const galeriaImages = document.querySelector('.galeria-images');
        let touchStartXGaleria = 0;
        let touchEndXGaleria = 0;

        if (galeriaImages) {
            galeriaImages.addEventListener('touchstart', (e) => {
                touchStartXGaleria = e.changedTouches[0].screenX;
            });

            galeriaImages.addEventListener('touchend', (e) => {
                touchEndXGaleria = e.changedTouches[0].screenX;
                handleSwipeGaleria();
            });

            function handleSwipeGaleria() {
                if (touchEndXGaleria < touchStartXGaleria - 50) {
                    nextGaleria();
                    stopGaleriaAutoPlay();
                    startGaleriaAutoPlay();
                }
                if (touchEndXGaleria > touchStartXGaleria + 50) {
                    prevGaleria();
                    stopGaleriaAutoPlay();
                    startGaleriaAutoPlay();
                }
            }

            // Pausar ao passar o mouse
            galeriaImages.addEventListener('mouseenter', stopGaleriaAutoPlay);
            galeriaImages.addEventListener('mouseleave', startGaleriaAutoPlay);
        }

        // Iniciar autoplay
        startGaleriaAutoPlay();
    }
});
