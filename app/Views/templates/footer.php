    </main>

    <!-- Footer Section -->
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-3">
                        <i class="fas fa-envelope me-2"></i>Generator Email
                    </h5>
                    <p class="mb-0">sinjaikab.go.id</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="mb-2">
                        <span class="badge bg-light text-dark me-2">
                            <i class="fas fa-code-branch me-1"></i>v1.0.0
                        </span>
                        <span class="badge bg-info">
                            <i class="fas fa-database me-1"></i>cPanel API
                        </span>
                    </div>
                    <p class="mb-0">
                        <small>
                            © <?php echo date('Y'); ?> Aptika Diskominfo Sinjai
                        </small>
                    </p>
                </div>
            </div>
        </div>
    </footer>

        <!-- Bootstrap 5 JS Bundle -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
        <?= $this->renderSection('scripts') ?>
    </body>
    
    </html>