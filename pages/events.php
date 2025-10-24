<?php
require_once '../controller/events.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Delicious Eats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/events.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="events-page">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-calendar-alt"></i> Upcoming Events</h1>
                <p class="lead">Join us for special events, workshops, and dining experiences!</p>
            </div>

            <!-- Debug Information (remove in production) -->
            <?php if (isset($_GET['debug'])): ?>
                <div class="alert alert-info">
                    <strong>Debug Info:</strong><br>
                    Current Date: <?php echo date('Y-m-d H:i:s'); ?><br>
                    Events Count: <?php echo count($events); ?><br>
                    Events Variable Type: <?php echo gettype($events); ?><br>
                    <?php if (!empty($events)): ?>
                        Events Found:
                        <ul>
                            <?php foreach ($events as $event): ?>
                                <li><?php echo htmlspecialchars($event['event_name']); ?> - <?php echo $event['event_date']; ?> - Active: <?php echo $event['is_active'] ? 'Yes' : 'No'; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($events)): ?>
                <!-- No Events -->
                <div class="no-events">
                    <div class="text-center">
                        <i class="fas fa-calendar-times display-1 text-muted mb-3"></i>
                        <h3>No Upcoming Events</h3>
                        <p class="text-muted">Check back soon for exciting new events and workshops!</p>
                        <a href="../pages/index.php" class="btn btn-primary">
                            <i class="fas fa-home"></i> Return Home
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Events Grid -->
                <div class="events-grid">
                    <?php foreach ($events as $event): ?>
                        <div class="event-card">
                            <div class="event-image">
                                <img src="<?php echo htmlspecialchars($event['image_url'] ?? '../assets/images/events/placeholder.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($event['event_name']); ?>">
                                <div class="event-price">
                                    <?php if ($event['price'] > 0): ?>
                                        <span class="price">$<?php echo number_format($event['price'], 2); ?></span>
                                    <?php else: ?>
                                        <span class="price free">FREE</span>
                                    <?php endif; ?>
                                </div>
                                <div class="event-type">
                                    <span class="badge type-<?php echo $event['event_type']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $event['event_type'])); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="event-content">
                                <h3 class="event-title"><?php echo htmlspecialchars($event['event_name']); ?></h3>
                                
                                <div class="event-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo date('M j, Y', strtotime($event['event_date'])); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($event['location']); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-users"></i>
                                        <span><?php echo $event['capacity']; ?> max guests</span>
                                    </div>
                                </div>
                                
                                <p class="event-description">
                                    <?php echo htmlspecialchars($event['description']); ?>
                                </p>
                                
                                <?php if (!empty($event['requirements'])): ?>
                                    <div class="event-requirements">
                                        <i class="fas fa-info-circle"></i>
                                        <small><?php echo htmlspecialchars($event['requirements']); ?></small>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="event-actions">
                                    <button class="btn btn-primary btn-reserve" 
                                            data-event-id="<?php echo $event['event_id']; ?>"
                                            data-event-name="<?php echo htmlspecialchars($event['event_name']); ?>"
                                            data-event-price="<?php echo $event['price']; ?>">
                                        <i class="fas fa-ticket-alt"></i> Reserve Spot
                                    </button>
                                    
                                    <?php if (!empty($event['contact_email']) || !empty($event['contact_phone'])): ?>
                                        <div class="contact-info">
                                            <?php if (!empty($event['contact_email'])): ?>
                                                <a href="mailto:<?php echo htmlspecialchars($event['contact_email']); ?>" 
                                                   class="contact-link">
                                                    <i class="fas fa-envelope"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($event['contact_phone'])): ?>
                                                <a href="tel:<?php echo htmlspecialchars($event['contact_phone']); ?>" 
                                                   class="contact-link">
                                                    <i class="fas fa-phone"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reserveButtons = document.querySelectorAll('.btn-reserve');
            
            reserveButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = this.dataset.eventId;
                    const eventName = this.dataset.eventName;
                    const eventPrice = parseFloat(this.dataset.eventPrice);
                    
                    // Check if user is logged in (this would need to be passed from PHP)
                    <?php if (is_user_logged_in()): ?>
                        // User is logged in, show reservation form
                        Swal.fire({
                            title: 'Reserve Your Spot',
                            html: `
                                <div class="reservation-form">
                                    <h5>${eventName}</h5>
                                    <p><strong>Price:</strong> ${eventPrice > 0 ? '$' + eventPrice.toFixed(2) : 'FREE'}</p>
                                    <div class="form-group mb-3">
                                        <label for="guests">Number of Guests:</label>
                                        <select id="guests" class="form-control">
                                            <option value="1">1 Guest</option>
                                            <option value="2">2 Guests</option>
                                            <option value="3">3 Guests</option>
                                            <option value="4">4 Guests</option>
                                            <option value="5">5+ Guests</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="special-requests">Special Requests:</label>
                                        <textarea id="special-requests" class="form-control" rows="3" placeholder="Any dietary restrictions or special requests..."></textarea>
                                    </div>
                                </div>
                            `,
                            showCancelButton: true,
                            confirmButtonText: 'Reserve Now',
                            cancelButtonText: 'Cancel',
                            preConfirm: () => {
                                const guests = document.getElementById('guests').value;
                                const requests = document.getElementById('special-requests').value;
                                
                                return {
                                    eventId: eventId,
                                    guests: guests,
                                    specialRequests: requests
                                };
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Here you would typically send the reservation to the server
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Reservation Submitted!',
                                    text: 'Your event reservation has been submitted. You will receive a confirmation email shortly.',
                                    confirmButtonText: 'Great!'
                                });
                            }
                        });
                    <?php else: ?>
                        // User is not logged in, prompt to login
                        Swal.fire({
                            icon: 'info',
                            title: 'Login Required',
                            text: 'Please log in to reserve a spot for this event.',
                            showCancelButton: true,
                            confirmButtonText: 'Login',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '../pages/login.php';
                            }
                        });
                    <?php endif; ?>
                });
            });
        });
    </script>
</body>
</html>