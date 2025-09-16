<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events | Delicious Eats</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body>
    <?php
    include '../includes/header.php';
    include '../includes/sidebar.php';
    require_once '../models/db_Model.php';
    ?>

    <main>
        <section class="products">
            <div class="container">
                <h2>Upcoming Events</h2>
                <p>Join us for special dining experiences, cooking classes, and exclusive events!</p>
                
                <div class="events-grid">
                    <?php
                    // Display events using the helper function from db_Model
                    display_upcoming_events();
                    ?>
                </div>
            </div>
        </section>
    </main>

    <?php
    include '../includes/footer.php';
    ?>

    <script src="../assets/js/sidebar.js"></script>
</body>

</html>
