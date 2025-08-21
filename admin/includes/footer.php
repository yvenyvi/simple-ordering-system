    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.27/dist/sweetalert2.all.min.js"></script>
    <!-- Existing Admin JS -->
    <script src="../assets/js/admin.js"></script>

    <!-- Enhanced Bootstrap functionality -->
    <script>
        // Enhanced Delete Confirmation Function using SweetAlert
        function confirmDelete(id, name, page) {
            showSweetConfirmation(
                'Are you sure?',
                `You won't be able to recover "${name}"!`,
                function() {
                    // Show loading state
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait while we delete the item.',
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Redirect to delete URL
                    window.location.href = page + '?deleteid=' + id;
                },
                {
                    confirmButtonText: 'Yes, delete it!',
                    confirmButtonColor: '#dc3545'
                }
            );
        }

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
