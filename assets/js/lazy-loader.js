/**
 * Lazy Loading Avançado com Intersection Observer
 * Skeleton screens e progressive loading
 */

class LazyLoader {
    constructor(options = {}) {
        this.options = {
            rootMargin: options.rootMargin || '50px',
            threshold: options.threshold || 0.01,
            skeletonClass: options.skeletonClass || 'skeleton',
            ...options
        };
        
        this.observer = null;
        this.init();
    }
    
    init() {
        if (!('IntersectionObserver' in window)) {
            // Fallback para navegadores antigos
            this.loadAllImages();
            return;
        }
        
        this.observer = new IntersectionObserver(
            (entries) => this.handleIntersection(entries),
            {
                rootMargin: this.options.rootMargin,
                threshold: this.options.threshold
            }
        );
        
        this.observeImages();
        this.observeSections();
    }
    
    observeImages() {
        const images = document.querySelectorAll('img[data-src], img[loading="lazy"]');
        images.forEach(img => {
            // Adicionar skeleton se não tiver
            if (!img.classList.contains(this.options.skeletonClass)) {
                img.classList.add(this.options.skeletonClass);
            }
            this.observer.observe(img);
        });
    }
    
    observeSections() {
        const sections = document.querySelectorAll('[data-lazy-section]');
        sections.forEach(section => {
            section.classList.add('lazy-hidden');
            this.observer.observe(section);
        });
    }
    
    handleIntersection(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = entry.target;
                
                if (target.tagName === 'IMG') {
                    this.loadImage(target);
                } else if (target.hasAttribute('data-lazy-section')) {
                    this.loadSection(target);
                }
                
                this.observer.unobserve(target);
            }
        });
    }
    
    loadImage(img) {
        const src = img.dataset.src || img.src;
        const srcset = img.dataset.srcset;
        
        // Criar nova imagem para pré-carregar
        const tempImg = new Image();
        
        tempImg.onload = () => {
            // Remover skeleton
            img.classList.remove(this.options.skeletonClass);
            
            // Progressive JPEG effect
            img.classList.add('lazy-loading');
            
            // Aplicar src
            if (img.dataset.src) {
                img.src = img.dataset.src;
                delete img.dataset.src;
            }
            
            if (srcset) {
                img.srcset = srcset;
                delete img.dataset.srcset;
            }
            
            // Animação de fade-in
            setTimeout(() => {
                img.classList.remove('lazy-loading');
                img.classList.add('lazy-loaded');
            }, 50);
        };
        
        tempImg.onerror = () => {
            img.classList.remove(this.options.skeletonClass);
            img.classList.add('lazy-error');
        };
        
        tempImg.src = src;
        if (srcset) tempImg.srcset = srcset;
    }
    
    loadSection(section) {
        section.classList.remove('lazy-hidden');
        section.classList.add('lazy-visible');
        
        // Se tem conteúdo dinâmico para carregar
        if (section.dataset.lazyContent) {
            this.loadDynamicContent(section);
        }
    }
    
    loadDynamicContent(section) {
        const endpoint = section.dataset.lazyContent;
        
        fetch(endpoint)
            .then(response => response.text())
            .then(html => {
                section.innerHTML = html;
                // Re-observar novas imagens
                this.observeImages();
            })
            .catch(error => {
                console.error('Erro ao carregar conteúdo:', error);
                section.innerHTML = '<p>Erro ao carregar conteúdo.</p>';
            });
    }
    
    loadAllImages() {
        // Fallback para navegadores sem IntersectionObserver
        const images = document.querySelectorAll('img[data-src]');
        images.forEach(img => {
            if (img.dataset.src) {
                img.src = img.dataset.src;
                delete img.dataset.src;
            }
            if (img.dataset.srcset) {
                img.srcset = img.dataset.srcset;
                delete img.dataset.srcset;
            }
            img.classList.remove(this.options.skeletonClass);
        });
    }
    
    // Método público para adicionar novos elementos
    observe(element) {
        if (this.observer) {
            this.observer.observe(element);
        }
    }
    
    // Método para desconectar observer
    disconnect() {
        if (this.observer) {
            this.observer.disconnect();
        }
    }
}

// CSS para skeleton e animações (adicionar ao style.css)
const lazyLoadingStyles = `
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s ease-in-out infinite;
    }
    
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    
    .lazy-loading {
        opacity: 0.6;
        filter: blur(5px);
        transition: all 0.3s ease-out;
    }
    
    .lazy-loaded {
        opacity: 1;
        filter: blur(0);
    }
    
    .lazy-error {
        background: #f8d7da;
        border: 2px dashed #f5c6cb;
        min-height: 200px;
    }
    
    .lazy-hidden {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }
    
    .lazy-visible {
        opacity: 1;
        transform: translateY(0);
    }
`;

// Inicialização automática
document.addEventListener('DOMContentLoaded', () => {
    window.lazyLoader = new LazyLoader({
        rootMargin: '100px',
        threshold: 0.01
    });
});

// Export para uso em módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LazyLoader;
}
