<?php
$page_title = "Event Management";
include 'includes/header.php';
require_once "../models/db_Model.php"; // Now includes universal table display function

// Handle delete request
if (isset($_GET['deleteid'])) {
    $delete_id = $_GET['deleteid'];
    
    global $connection;
    $get_image_sql = "SELECT image_url FROM events WHERE event_id = '$delete_id'";
    $image_result = mysqli_query($connection, $get_image_sql);
    $image_row = mysqli_fetch_array($image_result);
    
    // Delete the record
    $delete_sql = "DELETE FROM events WHERE event_id = '$delete_id'";
    mysqli_query($connection, $delete_sql);
    
    // Remove the image file if it exists and is not a placeholder
    if ($image_row && $image_row['image_url'] && strpos($image_row['image_url'], 'placeholder.jpg') === false) {
        $image_path = $image_row['image_url'];
        
        // Handle both relative and absolute paths
        if (!file_exists($image_path)) {
            // Try relative path from current directory
            $image_path = "../" . $image_row['image_url'];
        }
        
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    redirect_to("event_list.php");
}

if (isset($_POST['event_name'])) {
    $data = array(
        'event_name' => $_POST['event_name'],
        'description' => $_POST['description'],
        'event_date' => $_POST['event_date'],
        'event_time' => $_POST['event_time'],
        'location' => $_POST['location'],
        'capacity' => intval($_POST['capacity']),
        'price' => floatval($_POST['price']),
        'event_type' => $_POST['event_type'],
        'contact_email' => $_POST['contact_email'],
        'contact_phone' => $_POST['contact_phone'],
        'requirements' => $_POST['requirements'],
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    );

    if (save('events', $data)) {
        $success_message = "Event '{$_POST['event_name']}' has been successfully created!";
    } else {
        $error_message = "Failed to create event. Please try again.";
    }
}
?>

<body>
    <div class="admin-container">
        <!-- Admin Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Event Management Section -->
            <section id="event-management" class="admin-section active">
                <div class="section-header">
                    <h1>Event Management</h1>
                    <button class="btn btn-primary" onclick="showAddEventForm()">
                        <i class="fas fa-plus"></i> Add New Event
                    </button>
                </div>

                <!-- Add Event Form -->
                <div id="add-event-form" class="form-container" style="display: none;">
                    <h3>Add New Event</h3>
                    <form action="event_list.php" method="post" enctype="multipart/form-data" id="eventForm">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="event-name">Event Name *</label>
                                <input type="text" id="event-name" name="event_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="event-type">Event Type *</label>
                                <select id="event-type" name="event_type" class="form-select" required>
                                    <option value="">Select Event Type</option>
                                    <option value="workshop">Workshop</option>
                                    <option value="tasting">Tasting</option>
                                    <option value="party">Party</option>
                                    <option value="cooking_class">Cooking Class</option>
                                    <option value="special_dinner">Special Dinner</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="event-date">Event Date *</label>
                                <input type="date" id="event-date" name="event_date" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="event-time">Event Time *</label>
                                <input type="time" id="event-time" name="event_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="event-description">Description</label>
                            <textarea id="event-description" name="description" class="form-control" rows="3" placeholder="Describe the event details, activities, and what attendees can expect..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="event-location">Location *</label>
                            <input type="text" id="event-location" name="location" class="form-control" required placeholder="e.g., Main Restaurant, Private Dining Room, Outdoor Patio">
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="event-capacity">Capacity *</label>
                                <input type="number" id="event-capacity" name="capacity" class="form-control" min="1" max="500" value="50" required>
                            </div>
                            <div class="form-group">
                                <label for="event-price">Price *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" id="event-price" name="price" class="form-control" step="0.01" min="0" value="0.00" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="contact-email">Contact Email</label>
                                <input type="email" id="contact-email" name="contact_email" class="form-control" placeholder="events@deliciouseats.com">
                            </div>
                            <div class="form-group">
                                <label for="contact-phone">Contact Phone</label>
                                <input type="tel" id="contact-phone" name="contact_phone" class="form-control" placeholder="(555) 123-4567">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="event-requirements">Special Requirements</label>
                            <textarea id="event-requirements" name="requirements" class="form-control" rows="2" placeholder="Age restrictions, dress code, dietary considerations, etc."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="fileField">Upload Image (Optional)</label>
                            <input type="file" id="fileField" name="fileField" class="form-control" accept="image/*">
                            <small class="form-text text-muted">Supported formats: JPG, JPEG, PNG, GIF, WEBP. Recommended size: 800x600px</small>
                        </div>
                        <div class="form-group checkbox-group">
                            <label class="form-check-label">
                                <input type="checkbox" id="event-active" name="is_active" class="form-check-input" checked>
                                Active event (visible to customers)
                            </label>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Save Event
                            </button>
                            <button type="button" class="btn btn-secondary ms-2" onclick="hideAddEventForm()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Events List -->
                <div class="table-container">
                    <?php
                    $sql = "SELECT * FROM events ORDER BY event_date ASC, event_time ASC";
                    display_events_table($sql);
                    ?>
                </div>
            </section>

        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Existing Admin JS -->
    <script src="../assets/js/admin.js"></script>

    <!-- Enhanced Bootstrap functionality -->
    <script>
        function showAddEventForm() {
            document.getElementById('add-event-form').style.display = 'block';
            document.getElementById('event-name').focus();
        }

        function hideAddEventForm() {
            document.getElementById('add-event-form').style.display = 'none';
            // Reset form when hiding
            document.getElementById('eventForm').reset();
        }

        // Auto-hide form after page load if there's no error
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (!urlParams.has('error')) {
                hideAddEventForm();
            }
            
            // Set minimum date to today
            const eventDateInput = document.getElementById('event-date');
            const today = new Date().toISOString().split('T')[0];
            eventDateInput.min = today;

            // Show success/error messages using Bootstrap alerts
            <?php if (isset($success_message)): ?>
                showBootstrapAlert('<?php echo addslashes($success_message); ?>', 'success', 4000);
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                showBootstrapAlert('<?php echo addslashes($error_message); ?>', 'error', 6000);
            <?php endif; ?>
        });

        // Form validation
        document.getElementById('eventForm').addEventListener('submit', function(e) {
            const eventDate = new Date(document.getElementById('event-date').value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (eventDate < today) {
                e.preventDefault();
                showBootstrapAlert('Event date cannot be in the past. Please select a valid date.', 'error', 5000);
                return false;
            }
        });
    </script>

    <?php include 'includes/footer.php'; ?>

</body>
</html>
