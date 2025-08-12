// Sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContent = document.querySelector('.main-content') || document.querySelector('main');
    
    // Create overlay element
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);
    
    // Toggle sidebar function
    function toggleSidebar() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        
        // Only adjust main content margin on desktop
        if (window.innerWidth > 768 && mainContent) {
            mainContent.classList.toggle('sidebar-active');
        }
    }
    
    // Event listeners
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    // Close sidebar when clicking overlay (mobile)
    overlay.addEventListener('click', function() {
        if (sidebar.classList.contains('active')) {
            toggleSidebar();
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768 && mainContent) {
            mainContent.classList.remove('sidebar-active');
        } else if (window.innerWidth > 768 && sidebar.classList.contains('active') && mainContent) {
            mainContent.classList.add('sidebar-active');
        }
    });
    
    // Category filtering functionality
    const categoryLinks = document.querySelectorAll('.category-link');
    categoryLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const category = this.getAttribute('data-category');
            
            // Remove active class from all category links
            categoryLinks.forEach(l => l.classList.remove('active'));
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Filter products if on products page
            filterProductsByCategory(category);
        });
    });
    
    // Cart badge update function
    function updateCartBadge(count) {
        const cartBadge = document.querySelector('.nav-link .badge');
        if (cartBadge) {
            cartBadge.textContent = count;
            cartBadge.style.display = count > 0 ? 'inline-block' : 'none';
        }
    }
    
    // Product filtering function (to be implemented based on products structure)
    function filterProductsByCategory(category) {
        const productCards = document.querySelectorAll('.product-card');
        
        if (category === 'all' || !category) {
            productCards.forEach(card => {
                card.style.display = 'block';
            });
            return;
        }
        
        productCards.forEach(card => {
            const productCategory = card.getAttribute('data-category');
            if (productCategory === category) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    // Initialize cart badge
    updateCartBadge(0);
    
    // Add smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // Add active state to current page navigation link
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const linkHref = link.getAttribute('href');
        if (linkHref === currentPage || (currentPage === '' && linkHref === 'index.php')) {
            link.classList.add('active');
        }
    });
});
