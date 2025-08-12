    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Existing Admin JS -->
    <script src="../assets/js/admin.js"></script>

    <!-- Enhanced Bootstrap functionality -->
    <script>
        // Function to filter orders (placeholder for enhanced functionality)
        function filterOrders(status) {
            console.log('Filtering orders by:', status);
            // This can be enhanced to actually filter the orders table
            // For now, it's a placeholder that can be expanded
        }

        // Enhanced tooltips for better UX
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>

</html>
