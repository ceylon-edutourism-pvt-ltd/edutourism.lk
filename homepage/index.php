<?php
$active = "home"; 
include("functions.php");
include("header.php");
include("hero_section.php");
include("db.php");

// Fetch active upcoming tours from unified tours table
$tours_query = "SELECT * FROM tours 
                WHERE status = 1 
                AND tour_status = 'upcoming' 
                ORDER BY start_date ASC";
$tours_result = mysqli_query($con, $tours_query);
?>
<head>
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/tourcard.css">
</head>

<!-- Tours Section for index.php - Upcoming Tours -->
<section class="tour-showcase">
    <div class="tour-container">
        <div class="tour-section-heading">
            <h2>Up Coming Tours</h2>
            <p>Seats are limited – don't miss this opportunity!</p>
        </div>
        
        <div class="tour-cards-wrapper">
            <?php if (mysqli_num_rows($tours_result) > 0): ?>
                <?php while ($tour = mysqli_fetch_assoc($tours_result)): ?>
                    <!-- Dynamic Tour Card -->
                    <div class="tour-card" data-destination="<?php echo htmlspecialchars($tour['destination']); ?>">
                        <a href="tour-details.php?tour=<?php echo $tour['id']; ?>" class="tour-card-link">
                            <div class="tour-card-inner">
                                <div class="tour-card-image">
                                    <!-- Display cover image -->
                                    <?php if (!empty($tour['cover_image']) && file_exists($tour['cover_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($tour['cover_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($tour['title']); ?>"
                                             loading="lazy">
                                    <?php else: ?>
                                        <img src="img/tours/default-tour.png" 
                                             alt="<?php echo htmlspecialchars($tour['title']); ?>"
                                             loading="lazy">
                                    <?php endif; ?>
                                </div>
                                <div class="tour-card-content">
                                    <div class="tour-card-header">
                                        <h3 class="tour-title"><?php echo htmlspecialchars($tour['title']); ?></h3>
                                    </div>
                                    <div class="tour-details">
                                        <div class="tour-meta">
                                            <span>
                                                <i class="fa fa-clock-o"></i> 
                                                <?php echo $tour['duration']; ?> Days
                                            </span>
                                            <span>
                                                <i class="fa fa-map-marker"></i> 
                                                <?php echo htmlspecialchars($tour['destination']); ?>
                                            </span>
                                        </div>
                                        <p class="tour-description">
                                            <?php echo htmlspecialchars($tour['description']); ?>
                                        </p>
                                    </div>
                                    <div class="tour-card-footer">
                                        
                                        <div class="tour-action">
                                            <span>View Details</span>
                                            <i class="fa fa-arrow-right"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- No tours available message -->
                <div class="col-12 text-center py-5">
                    <div class="no-tours-message">
                        <i class="fa fa-map-o fa-3x text-muted mb-3"></i>
                        <h3 class="text-muted">No upcoming tours available at the moment</h3>
                        <p class="text-muted">Please check back later for new exciting tours.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    
    </div>
</section>

<?php
mysqli_close($con);
include("footer.php");
?>

<script>
$(document).ready(function() {
    // Initialize Owl Carousel with responsive settings
    $('.hero-items').owlCarousel({
        loop: true,
        margin: 0,
        nav: true,
        dots: true,
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: true,
        smartSpeed: 1200,
        responsive: {
            0: {
                items: 1
            },
            576: {
                items: 1
            },
            992: {
                items: 1
            }
        }
    });
    
    // Apply background images from data-setbg attribute
    $('.set-bg').each(function() {
        var bg = $(this).data('setbg');
        $(this).css('background', 'url(' + bg + ')');
    });

    // Add fade-in animation to tour cards
    $('.tour-card').each(function(index) {
        $(this).css('animation-delay', (index * 0.2) + 's');
        $(this).addClass('animate-fade-in');
    });
});
</script>

<style>
/* Additional styles for dynamic tours */
.no-tours-message {
    padding: 40px 20px;
    border-radius: 10px;
    margin: 20px;
}

.animate-fade-in {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.6s ease forwards;
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.tour-card:hover {
    transform: translateY(-5px);
    transition: all 0.3s ease;
}

.tour-card-image img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px 8px 0 0;
}


</style>