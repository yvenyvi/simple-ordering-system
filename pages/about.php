<?php
// Start session at the very beginning before any output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Delicious Eats</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/about_us.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="../assets/js/header.js"></script>
</head>
<body>
    <?php
    include '../includes/header.php';
    include '../includes/sidebar.php';
    ?>

    <main>
        <!-- Hero Section -->
        <section class="about-hero">
            <div class="container">
                <h1><i class="fas fa-store"></i> About Delicious Eats</h1>
                <p>Where passion for food meets exceptional service. We've been serving our community with love, dedication, and the finest culinary experiences since our inception.</p>
            </div>
        </section>

        <!-- Our Story Section -->
        <section class="about-section">
            <div class="container">
                <h2 class="section-title">Our Story</h2>
                <div class="about-content">
                    <p>
                        Delicious Eats began with a simple dream: to bring people together through exceptional food and warm hospitality. 
                        What started as a small family kitchen has grown into a beloved destination for food lovers across the region.
                    </p>
                    <p>
                        Our journey is rooted in authenticity, quality, and a genuine passion for creating memorable dining experiences. 
                        Every dish we serve tells a story, crafted with carefully selected ingredients and time-honored techniques passed down through generations.
                    </p>
                    <p>
                        Today, we continue to honor our founding principles while embracing innovation, ensuring that every meal we serve 
                        reflects our commitment to excellence and our love for what we do.
                    </p>
                </div>

                <div class="story-grid">
                    <div class="story-card">
                        <i class="fas fa-lightbulb"></i>
                        <h3>Our Vision</h3>
                        <p>To be the premier destination for food lovers, known for our exceptional quality, innovative menu, and unwavering commitment to customer satisfaction.</p>
                    </div>
                    <div class="story-card">
                        <i class="fas fa-bullseye"></i>
                        <h3>Our Mission</h3>
                        <p>To create unforgettable culinary experiences that bring joy to our customers' lives, using the finest ingredients and time-honored cooking traditions.</p>
                    </div>
                    <div class="story-card">
                        <i class="fas fa-heart"></i>
                        <h3>Our Passion</h3>
                        <p>We are driven by a deep love for food and hospitality, constantly striving to exceed expectations and create moments that matter.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Core Values Section -->
        <section class="about-section">
            <div class="container">
                <h2 class="section-title">Our Core Values</h2>
                <div class="values-grid">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <h3>Quality First</h3>
                        <p>We never compromise on the quality of our ingredients or the excellence of our preparation.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h3>Freshness</h3>
                        <p>Every dish is prepared fresh to order, using locally sourced ingredients whenever possible.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Community</h3>
                        <p>We believe in giving back and supporting our local community through various initiatives.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h3>Innovation</h3>
                        <p>While respecting tradition, we constantly innovate to bring you exciting new flavors and experiences.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="container">
                <h2 class="section-title" style="color: white;">Our Journey in Numbers</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">10+</div>
                        <div class="stat-label">Years of Excellence</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">50K+</div>
                        <div class="stat-label">Happy Customers</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">200+</div>
                        <div class="stat-label">Menu Items</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">5★</div>
                        <div class="stat-label">Average Rating</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="team-section">
            <div class="container">
                <h2 class="section-title">Meet Our Team</h2>
                <div class="team-grid">
                    <div class="team-member">
                        <div class="team-avatar">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h3>John Anderson</h3>
                        <p class="team-role">Founder & CEO</p>
                        <p>With over 20 years of culinary experience, John's vision and leadership continue to drive our success.</p>
                    </div>
                    <div class="team-member">
                        <div class="team-avatar">
                            <i class="fas fa-user-chef"></i>
                        </div>
                        <h3>Maria Rodriguez</h3>
                        <p class="team-role">Executive Chef</p>
                        <p>Maria brings creativity and passion to every dish, leading our talented kitchen team with excellence.</p>
                    </div>
                    <div class="team-member">
                        <div class="team-avatar">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <h3>Sarah Chen</h3>
                        <p class="team-role">Operations Manager</p>
                        <p>Sarah ensures every aspect of our service runs smoothly, creating exceptional experiences for our guests.</p>
                    </div>
                    <div class="team-member">
                        <div class="team-avatar">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <h3>Michael Thompson</h3>
                        <p class="team-role">Customer Experience Lead</p>
                        <p>Michael leads our commitment to outstanding customer service and satisfaction.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action Section -->
        <section class="about-section">
            <div class="container">
                <div class="about-content" style="text-align: center;">
                    <h2 class="section-title">Join Our Journey</h2>
                    <p style="font-size: 1.2rem; margin-bottom: 30px;">
                        Experience the Delicious Eats difference today. Whether dining in or ordering online, 
                        we're committed to making every meal memorable.
                    </p>
                    <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                        <a href="products.php" class="btn" style="background: linear-gradient(135deg, #3a6073 0%, #16222a 100%); color: white; padding: 15px 30px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                            <i class="fas fa-utensils"></i> View Our Menu
                        </a>
                        <a href="#" id="contactFromAbout" class="btn" style="background: #f9ed69; color: #3a6073; padding: 15px 30px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                            <i class="fas fa-envelope"></i> Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/sidebar.js"></script>
    <script>
        // Trigger contact modal from About page
        document.addEventListener('DOMContentLoaded', function() {
            const contactBtn = document.getElementById('contactFromAbout');
            const modal = document.getElementById('contactModal');
            
            if (contactBtn && modal) {
                contactBtn.onclick = function(e) {
                    e.preventDefault();
                    modal.style.display = 'block';
                }
            }
        });
    </script>
</body>
</html>
