</div> <!-- End Container -->

<footer class="footer mt-auto py-5 text-white">
  <div class="container">
    <div class="row gy-4">
      <!-- Branding Section -->
      <div class="col-lg-4 col-md-6">
        <div class="d-flex align-items-center mb-3">
            <img src="<?php echo $prefix; ?>assets/images/logo.png" alt="MyIELTS" height="40" class="me-2 bg-white rounded p-1">
            <h4 class="mb-0 text-white">MyIELTS</h4>
        </div>
        <p class="small text-white-50 mb-3">
            The ultimate platform for IELTS preparation in Bangladesh. Powered by Skiloholic and developed by Sharfin Hossain.
        </p>
        <div class="social-icons">
            <a href="#" class="text-white me-3 hover-effect"><i class="fab fa-facebook fa-lg"></i></a>
            <a href="#" class="text-white me-3 hover-effect"><i class="fab fa-youtube fa-lg"></i></a>
            <a href="#" class="text-white hover-effect"><i class="fab fa-whatsapp fa-lg"></i></a>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="col-lg-2 col-md-6">
        <h5 class="text-white mb-3">Quick Links</h5>
        <ul class="list-unstyled text-white-50">
            <li><a href="<?php echo $prefix; ?>index.php" class="text-white-50 text-decoration-none hover-white">Home</a></li>
            <?php if (!isLoggedIn()): ?>
                <li><a href="<?php echo $prefix; ?>register.php" class="text-white-50 text-decoration-none hover-white">Register</a></li>
                <li><a href="<?php echo $prefix; ?>login.php" class="text-white-50 text-decoration-none hover-white">Login</a></li>
            <?php else: ?>
                <li><a href="<?php echo $prefix; ?>student/index.php" class="text-white-50 text-decoration-none hover-white">Dashboard</a></li>
            <?php endif; ?>
            <li><a href="<?php echo $prefix; ?>contact.php" class="text-white-50 text-decoration-none hover-white">Contact Us</a></li>
        </ul>
      </div>

      <!-- Official Exams -->
      <div class="col-lg-3 col-md-6">
        <h5 class="text-white mb-3">Official IELTS Exams</h5>
        <ul class="list-unstyled text-white-50">
           <li class="mb-2"><a href="https://www.ielts.org" target="_blank" class="text-white-50 text-decoration-none hover-white"><i class="fas fa-external-link-alt me-2 small"></i>IELTS.org</a></li>
           <li class="mb-2"><a href="https://takeielts.britishcouncil.org" target="_blank" class="text-white-50 text-decoration-none hover-white"><i class="fas fa-external-link-alt me-2 small"></i>British Council</a></li>
           <li class="mb-2"><a href="https://www.idp.com/global/ielts/" target="_blank" class="text-white-50 text-decoration-none hover-white"><i class="fas fa-external-link-alt me-2 small"></i>IDP IELTS</a></li>
        </ul>
      </div>

      <!-- Contact Info -->
      <div class="col-lg-3 col-md-6">
        <h5 class="text-white mb-3">Get in Touch</h5>
        <ul class="list-unstyled text-white-50">
            <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> Dhaka, Bangladesh</li>
            <li class="mb-2"><i class="fas fa-envelope me-2"></i> admin@myielts.com</li>
            <li class="mb-2"><i class="fab fa-whatsapp me-2"></i> +8801724413624</li>
        </ul>
      </div>
    </div>

    <div class="border-top border-secondary mt-4 pt-4 text-center text-white-50 small">
        <div class="row">
            <div class="col-md-6 text-md-start">
                &copy; <?php echo date('Y'); ?> MyIELTS. All rights reserved.
            </div>
            <div class="col-md-6 text-md-end">
                <a href="<?php echo $prefix; ?>terms.php" class="text-white-50 text-decoration-none me-3">Terms & Policy</a>
                <a href="#" class="text-white-50 text-decoration-none">Privacy</a>
            </div>
        </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $prefix; ?>assets/js/main.js"></script>
</body>
</html>
