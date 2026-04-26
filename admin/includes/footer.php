<?php
// admin/includes/footer.php
?>
        </main> <!-- /.main -->
    </div> <!-- /.row -->
</div> <!-- /.container-fluid -->
<script>
    // Prevent footer from overlapping content by adding bottom padding equal to footer height
    (function() {
        function adjustBodyPadding() {
            var footer = document.getElementById('site-footer');
            if (!footer) return;
            var h = footer.offsetHeight || 0;
            document.body.style.paddingBottom = h + 'px';
        }
        // Adjust on load and on resize (useful for responsive layouts)
        window.addEventListener('load', adjustBodyPadding);
        window.addEventListener('resize', adjustBodyPadding);
    })();
</script>
<footer class="bg-dark text-white text-center py-3 fixed-bottom border-top" id="site-footer">
    <div class="container">
        <p class="mb-0">
            <i class="fas fa-copyright me-1"></i><?php echo date('Y'); ?> 
            <strong>Edu Track System</strong> - All rights reserved.
        </p>
        <small class="text-muted">Powered by Edu Track System</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>

</body>
</html>