document.addEventListener('DOMContentLoaded', () => {
    const archivePage = document.querySelector('.news-archive-page');
    if (!archivePage) return;

    const containerSelector = '.news-list__container';

    function fetchNews(url) {
        const container = document.querySelector(containerSelector);
        if (!container) return;

        container.style.opacity = '0.5';
        container.style.pointerEvents = 'none';
        container.style.transition = 'opacity 0.3s ease';

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContainer = doc.querySelector(containerSelector);
                
                if (newContainer) {
                    container.innerHTML = newContainer.innerHTML;
                    window.history.pushState({ path: url }, '', url);
                    
                    // Re-attach event listeners to new elements
                    attachListeners();
                }
            })
            .catch(error => console.error('Fetch error:', error))
            .finally(() => {
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
                
                // Scroll to top of the list if we scrolled past it
                const listSection = document.querySelector('.news-list');
                if (listSection) {
                    const rect = listSection.getBoundingClientRect();
                    if (rect.top < 0) {
                        window.scrollTo({ top: listSection.offsetTop - 100, behavior: 'smooth' });
                    }
                }
            });
    }

    function attachListeners() {
        const links = document.querySelectorAll('.news-filter__categories a, .news-pagination a, .news-filter__reset');
        links.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const url = link.getAttribute('href');
                if (url) {
                    fetchNews(url);
                }
            });
        });

        const form = document.querySelector('.news-filter__form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                const searchParams = new URLSearchParams();
                
                for (const pair of formData.entries()) {
                    if (pair[1]) {
                        searchParams.append(pair[0], pair[1]);
                    }
                }
                
                const url = new URL(form.action);
                url.search = searchParams.toString();
                fetchNews(url.toString());
            });
        }
    }

    window.addEventListener('popstate', (e) => {
        if (e.state && e.state.path) {
            fetchNews(e.state.path);
        } else {
            fetchNews(window.location.href);
        }
    });

    attachListeners();
});
