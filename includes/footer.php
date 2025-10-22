<?php
// Get user details for contact form pre-fill
$contact_user = null;
if (function_exists('is_user_logged_in') && is_user_logged_in()) {
    $contact_user = get_logged_in_user();
}
$contact_name = $contact_user ? htmlspecialchars($contact_user['first_name'] . ' ' . $contact_user['last_name']) : '';
$contact_email = $contact_user ? htmlspecialchars($contact_user['email']) : '';
?>

<footer class="main-footer">
    <div class="container">
        <div class="footer-content">
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
            <p>&copy; All rights reserved. Simple Ordering System 2025</p>
        </div>
    </div>
</footer>

<!-- Contact Us Modal -->
<div id="contactModal" class="contact-modal">
    <div class="contact-modal-content">
        <span class="contact-close">&times;</span>
        <h2><i class="fas fa-envelope"></i> Contact Us</h2>
        <p class="modal-subtitle">We'd love to hear from you! Send us a message and we'll respond as soon as possible.</p>
        
        <form id="contactForm" class="contact-form">
            <div class="form-group">
                <label for="contactName"><i class="fas fa-user"></i> Name *</label>
                <input type="text" id="contactName" name="name" required placeholder="Your Name" value="<?php echo $contact_name; ?>">
            </div>
            
            <div class="form-group">
                <label for="contactEmail"><i class="fas fa-envelope"></i> Email *</label>
                <input type="email" id="contactEmail" name="email" required placeholder="your.email@example.com" value="<?php echo $contact_email; ?>">
            </div>
            
            <div class="form-group">
                <label for="contactSubject"><i class="fas fa-tag"></i> Subject *</label>
                <input type="text" id="contactSubject" name="subject" required placeholder="Message Subject">
            </div>
            
            <div class="form-group">
                <label for="contactMessage"><i class="fas fa-comment"></i> Message *</label>
                <textarea id="contactMessage" name="message" rows="5" required placeholder="Your message here..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
                <button type="button" class="btn btn-secondary" id="cancelContactBtn">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Contact Modal Styles */
.contact-modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.6);
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.contact-modal-content {
    background: white;
    margin: 3% auto;
    padding: 30px;
    border-radius: 15px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    animation: slideDown 0.4s ease;
    position: relative;
}

@keyframes slideDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.contact-modal-content h2 {
    color: #3a6073;
    margin-bottom: 10px;
    font-size: 1.8rem;
}

.contact-modal-content h2 i {
    color: #f9ed69;
    margin-right: 10px;
}

.modal-subtitle {
    color: #6c757d;
    margin-bottom: 25px;
    font-size: 0.95rem;
}

.contact-close {
    color: #aaa;
    float: right;
    font-size: 32px;
    font-weight: bold;
    line-height: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.contact-close:hover,
.contact-close:focus {
    color: #e74c3c;
    transform: rotate(90deg);
}

.contact-form .form-group {
    margin-bottom: 20px;
}

.contact-form label {
    display: block;
    margin-bottom: 8px;
    color: #3a6073;
    font-weight: 600;
    font-size: 0.95rem;
}

.contact-form label i {
    margin-right: 5px;
    color: #f9ed69;
}

.contact-form input,
.contact-form textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    font-family: inherit;
}

.contact-form input:focus,
.contact-form textarea:focus {
    outline: none;
    border-color: #3a6073;
    box-shadow: 0 0 0 3px rgba(58, 96, 115, 0.1);
}

.contact-form textarea {
    resize: vertical;
    min-height: 120px;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 25px;
}

.form-actions .btn {
    flex: 1;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #3a6073 0%, #16222a 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(58, 96, 115, 0.4);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .contact-modal-content {
        width: 95%;
        margin: 10% auto;
        padding: 20px;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
// Contact Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('contactModal');
    const contactNavLink = document.getElementById('contactNavLink');
    const span = document.querySelector('.contact-close');
    const cancelBtn = document.getElementById('cancelContactBtn');
    const form = document.getElementById('contactForm');
    
    // Open modal when clicking the Contact link in header
    if (contactNavLink) {
        contactNavLink.onclick = function(e) {
            e.preventDefault();
            modal.style.display = 'block';
        }
    }
    
    // Close modal
    if (span) {
        span.onclick = function() {
            modal.style.display = 'none';
        }
    }
    
    if (cancelBtn) {
        cancelBtn.onclick = function() {
            modal.style.display = 'none';
            form.reset();
        }
    }
    
    // Close when clicking outside
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
            form.reset();
        }
    }
    
    // Handle form submission
    if (form) {
        form.onsubmit = function(e) {
            e.preventDefault();
            
            // Get form values
            const name = document.getElementById('contactName').value;
            const email = document.getElementById('contactEmail').value;
            const subject = document.getElementById('contactSubject').value;
            const message = document.getElementById('contactMessage').value;
            
            // Show success message using SweetAlert2
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Message Sent!',
                    html: `
                        <div style="text-align: left;">
                            <p><strong>Thank you, ${name}!</strong></p>
                            <p>Your message has been successfully sent. We'll get back to you at <strong>${email}</strong> as soon as possible.</p>
                            <hr>
                            <p style="color: #6c757d; font-size: 0.9rem;">
                                <i class="fas fa-info-circle"></i> This is a demo message. No actual email has been sent.
                            </p>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonColor: '#3a6073',
                    confirmButtonText: '<i class="fas fa-check"></i> OK'
                });
            } else {
                alert('Message sent successfully! (This is a mock-up demo)');
            }
            
            // Close modal and reset form
            modal.style.display = 'none';
            form.reset();
        }
    }
});
</script>