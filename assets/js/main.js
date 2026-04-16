/**
 * AIUB Alumni Society - Main JavaScript
 */
document.addEventListener('DOMContentLoaded', function () {

    // ===== Navbar Scroll Effect =====
    const navbar = document.querySelector('.main-navbar');
    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // ===== Back to Top Button =====
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });

        backToTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ===== Auto-dismiss Flash Messages =====
    const flashMessages = document.querySelectorAll('.flash-message .alert');
    flashMessages.forEach(function (alert) {
        setTimeout(function () {
            alert.classList.remove('show');
            setTimeout(function () {
                alert.remove();
            }, 300);
        }, 5000);
    });

    // ===== Smooth Scrolling for Anchor Links =====
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // ===== Close Mobile Navbar on Link Click =====
    const navLinks = document.querySelectorAll('.main-navbar .nav-link');
    const navCollapse = document.querySelector('#mainNav');
    navLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            if (navCollapse && navCollapse.classList.contains('show')) {
                const bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
                if (bsCollapse) bsCollapse.hide();
            }
        });
    });

    // ===== Form Validation Enhancement =====
    const forms = document.querySelectorAll('form[method="POST"]');
    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(function (field) {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    });

    // ===== Remove Invalid Class on Input =====
    document.querySelectorAll('.form-control, .form-select').forEach(function (input) {
        input.addEventListener('input', function () {
            this.classList.remove('is-invalid');
        });
    });

});
