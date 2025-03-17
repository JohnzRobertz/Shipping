</main>
    
    <footer class="bg-[#1F2937] text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo __('app_name'); ?></h5>
                    <p class="mb-0">
                    © <?php echo date('Y'); ?> <?php echo __('app_name'); ?>. <?php echo __('all_rights_reserved'); ?>
                </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="mb-2">
                        <a href="#" class="text-white me-3" aria-label="Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="text-white me-3" aria-label="Twitter">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="#" class="text-white me-3" aria-label="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="#" class="text-white" aria-label="LinkedIn">
                            <i class="bi bi-linkedin"></i>
                        </a>
                    </div>
                    <p class="mb-0">
                        <a href="#" class="text-white me-3 text-decoration-none hover:text-gray-300">
                            <?php echo __('privacy_policy'); ?>
                        </a>
                        <a href="#" class="text-white text-decoration-none hover:text-gray-300">
                            <?php echo __('terms_of_service'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </footer>
       </div>
   </div>
   
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   <script src="assets/js/script.js"></script>
   <script>
       // Sidebar toggle functionality
       document.addEventListener('DOMContentLoaded', function() {
           const sidebarToggle = document.getElementById('sidebarToggle');
           if (sidebarToggle) {
               sidebarToggle.addEventListener('click', function() {
                   document.body.classList.toggle('sidebar-collapsed');
               });
           }
           
           // Close sidebar on mobile when clicking a nav item
           const navLinks = document.querySelectorAll('.sidebar .nav-link');
           navLinks.forEach(link => {
               link.addEventListener('click', function() {
                   if (window.innerWidth < 992) {
                       document.body.classList.add('sidebar-collapsed');
                   }
               });
           });
           
           // Set sidebar state based on screen size
           function handleResize() {
               if (window.innerWidth < 992) {
                   document.body.classList.add('sidebar-collapsed');
               } else {
                   document.body.classList.remove('sidebar-collapsed');
               }
           }
           
           // Initial call and event listener
           handleResize();
           window.addEventListener('resize', handleResize);
       });
   </script>
</body>
</html>

