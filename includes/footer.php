<!-- Flash Messages -->
<?php if ($flash = getFlash()): ?>
<div class="flash-message">
    <div class="alert alert-<?= sanitize($flash['type']) ?> alert-dismissible fade show shadow">
        <?= sanitize($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<!-- Footer -->
<footer class="site-footer">
    <div class="container">
    <div class="row text-center text-lg-start">
        
            <!-- About Column -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="footer-logo">
                    <img src="<?= SITE_URL ?>/assets/images/logo.png" alt="<?= SITE_NAME ?>" onerror="this.style.display='none'">
                </div>
                <h5><?= SITE_NAME ?></h5>
                <p><?= SITE_TAGLINE ?></p>
                <div class="footer-social mt-3">
                    <a href="https://www.facebook.com/kheora.alumni.association" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://youtu.be/sia8WlMQyY4?si=Ui5dtidK4-7ToJdU" target="_blank" title="YouTube"><i class="fab fa-youtube"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>

            <!-- Quick Links Column -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h5>Quick Links</h5>
                <ul class="footer-links">
                    <li><a href="<?= SITE_URL ?>/index.php"><i class="bi bi-chevron-right"></i> Home</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/about.php"><i class="bi bi-chevron-right"></i> About Us</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/committee.php"><i class="bi bi-chevron-right"></i> Executive Committee</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/events.php"><i class="bi bi-chevron-right"></i> Events</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/news.php"><i class="bi bi-chevron-right"></i> Alumni Biography</a></li>
                    <li><a href="<?= SITE_URL ?>/pages/contact.php"><i class="bi bi-chevron-right"></i> Contact Us</a></li>
                </ul>
            </div>

            <!-- Contact Column -->
            <div class="col-lg-4 col-md-6 mb-4 text-center">
                <h5>Contact Info</h5>
                <div class="footer-contact text-center">
                    <div class="contact-item">
                        <i class="bi bi-geo-alt-fill"></i>
                        <p>Kheora, Kasba, Brahmanbaria-3460</p>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-envelope-fill"></i>
                        <p><a href="mailto:anandamoyeean@gmail.com" class="footer-email">
                            anandamoyeean@gmail.com
                            </a></p>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-telephone-fill"></i>
                        <a href="tel:+8801611759094" class="footer-email">+880 1611 759094</a>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved. <br>
            Developed By -
<a href="https://ananyadutta03.github.io/my_portfolio"
   target="_blank"
   style="color: rgba(167, 173, 237, 0.5); font-size: 0.90rem; text-decoration: none; transition: color 0.3s ease, text-shadow 0.3s ease;"
   onmouseover="this.style.color='rgba(158, 165, 236, 1)'; this.style.textDecoration='underline'; this.style.textShadow='0 0 8px rgba(158, 165, 236, 0.6)';"
   onmouseout="this.style.color='rgba(158, 165, 236, 0.5)'; this.style.textDecoration='none'; this.style.textShadow='none';">
    Ananya Dutta
</a>
&
<a href="https://swarupkst.com"
   target="_blank"
   style="color: rgba(167, 173, 237, 0.5); font-size: 0.90rem; text-decoration: none; transition: color 0.3s ease, text-shadow 0.3s ease;"
   onmouseover="this.style.color='rgba(158, 165, 236, 1)'; this.style.textDecoration='underline'; this.style.textShadow='0 0 8px rgba(158, 165, 236, 0.6)';"
   onmouseout="this.style.color='rgba(158, 165, 236, 0.5)'; this.style.textDecoration='none'; this.style.textShadow='none';">
    Swarup Biswas
</a>
    </p>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button class="back-to-top" id="backToTop" title="Back to Top">
    <i class="bi bi-arrow-up"></i>
</button>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
