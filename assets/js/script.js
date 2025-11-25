document.addEventListener('DOMContentLoaded', function() {

    // --- Hamburger Menu Logic ---
    const menuToggle = document.getElementById('menu-toggle');
    const mainNav = document.querySelector('.main-nav');
    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', () => {
            mainNav.classList.toggle('active');
        });
    }

    // --- Agreement Popup Logic ---
    const popup = document.getElementById('agreementPopup');
    if (popup) {
        // Show popup only if user has not agreed using localStorage
        if (localStorage.getItem('userAgreed') !== 'true') {
            popup.style.display = 'flex';
        }

        const agreeBtn = document.getElementById('agreeBtn');
        const disagreeBtn = document.getElementById('disagreeBtn');

        if (agreeBtn) {
            agreeBtn.addEventListener('click', () => {
                // Set the agreement in localStorage so it persists across tabs
                localStorage.setItem('userAgreed', 'true');
                popup.style.display = 'none';
            });
        }

        if (disagreeBtn) {
            disagreeBtn.addEventListener('click', () => {
                window.location.href = 'sorry.php';
            });
        }
    }

    // --- Search Bar Logic ---
    const searchInput = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('suggestions');
    if (searchInput && suggestionsBox) {
        searchInput.addEventListener('keyup', () => {
            let query = searchInput.value.trim();
            if (query.length > 1) {
                suggestionsBox.style.display = 'block';
                fetch('search_suggestions.php?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        suggestionsBox.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(item => {
                                const a = document.createElement('a');
                                a.href = item.url;
                                if (item.url.includes('video.php')) { a.target = '_blank'; }
                                a.textContent = item.title;
                                suggestionsBox.appendChild(a);
                            });
                        } else {
                            suggestionsBox.innerHTML = '<a href="#" style="pointer-events: none; color: var(--grey-text);">No results found</a>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching search suggestions:', error);
                        suggestionsBox.style.display = 'none';
                    });
            } else {
                suggestionsBox.innerHTML = '';
                suggestionsBox.style.display = 'none';
            }
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(event) {
            if (!searchInput.contains(event.target)) {
                suggestionsBox.style.display = 'none';
            }
        });
    }

    // --- Loading Animation Logic ---
    const loader = document.getElementById('loader');
    const loaderTriggers = document.querySelectorAll('.loader-trigger');
    if (loader && loaderTriggers) {
        loaderTriggers.forEach(link => {
            link.addEventListener('click', function(e) {
                if (e.ctrlKey || e.metaKey || link.target === '_blank') {
                    return; // Don't prevent new tab opening
                }
                e.preventDefault();
                loader.style.display = 'flex';
                const destination = this.href;
                setTimeout(() => {
                    window.location.href = destination;
                }, 400); // Wait for animation to be visible
            });
        });
    }
});