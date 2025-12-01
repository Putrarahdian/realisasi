import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import 'bootstrap/dist/css/bootstrap.min.css';
import './bootstrap';

// Sidebar toggle logic
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    const overlay = document.querySelector('.body-overlay');

    if (!toggleBtn || !sidebar || !overlay || !content) return;

    function openSidebar() {
        sidebar.classList.add('active');
        overlay.classList.add('show-nav');
        // Untuk desktop, geser konten
        if (window.innerWidth > 991) {
            content.classList.add('active');
        }
    }

    function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('show-nav');
        content.classList.remove('active');
    }

    toggleBtn.addEventListener('click', function() {
        if (sidebar.classList.contains('active')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    overlay.addEventListener('click', closeSidebar);

    // Optional: tutup sidebar dengan Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSidebar();
    });
    
    document.addEventListener('scroll', function() {
    const navbar = document.querySelector('.top-navbar');
    if(window.scrollY > 10) {
        navbar.classList.add('sticky-shadow');
    } else {
        navbar.classList.remove('sticky-shadow');
    }
});
});
