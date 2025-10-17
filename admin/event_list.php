<?php
$page_title = "Event Management";
include 'includes/header.php';
require_once 'controller/event_list.php';
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

                <!-- Search and Filter Controls -->
                <div class="filters-container">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="search-filter" class="form-label">
                                <i class="fas fa-search"></i> Search
                            </label>
                            <input type="text" id="search-filter" class="form-control" placeholder="Search by name, location, description..." onkeyup="applyEventFilters()">
                        </div>
                        <div class="col-md-3">
                            <label for="event-type-filter" class="form-label">
                                <i class="fas fa-tags"></i> Event Type
                            </label>
                            <select id="event-type-filter" class="form-select" onchange="applyEventFilters()">
                                <option value="">All Types</option>
                                <option value="workshop">Workshop</option>
                                <option value="tasting">Tasting</option>
                                <option value="party">Party</option>
                                <option value="cooking_class">Cooking Class</option>
                                <option value="special_dinner">Special Dinner</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status-filter" class="form-label">
                                <i class="fas fa-toggle-on"></i> Status
                            </label>
                            <select id="status-filter" class="form-select" onchange="applyEventFilters()">
                                <option value="">All Events</option>
                                <option value="1">Active Only</option>
                                <option value="0">Inactive Only</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date-filter" class="form-label">
                                <i class="fas fa-calendar"></i> Date
                            </label>
                            <select id="date-filter" class="form-select" onchange="applyEventFilters()">
                                <option value="">All Dates</option>
                                <option value="upcoming">Upcoming</option>
                                <option value="past">Past Events</option>
                                <option value="today">Today</option>
                                <option value="this-week">This Week</option>
                                <option value="this-month">This Month</option>
                            </select>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearEventFilters()">
                            <i class="fas fa-times"></i> Clear Filters
                        </button>
                        <div id="results-count" class="results-count ms-3"></div>
                    </div>
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

    <!-- Event View Details Modal -->
    <div class="modal fade" id="eventViewModal" tabindex="-1" aria-labelledby="eventViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventViewModalLabel">
                        <i class="fas fa-calendar-alt"></i> Event Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="eventViewContent">
                        <!-- Event details will be loaded here -->
                        <div class="text-center modal-loading">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading event details...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Existing Admin JS -->
    <script src="../assets/js/admin.js"></script>
    <!-- Event Management JS -->
    <script src="../assets/js/event_management.js"></script>

    <!-- Handle PHP messages -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show success/error messages using Bootstrap alerts
            <?php if (isset($success_message)): ?>
                showEventMessages('<?php echo addslashes($success_message); ?>', null);
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                showEventMessages(null, '<?php echo addslashes($error_message); ?>');
            <?php endif; ?>

            <?php if (isset($info_message)): ?>
                showEventInfoMessage('<?php echo addslashes($info_message); ?>');
            <?php endif; ?>
        });
    </script>

    <?php include 'includes/footer.php'; ?>

</body>
</html>
