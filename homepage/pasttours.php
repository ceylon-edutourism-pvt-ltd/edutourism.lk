<?php
/**
 * ============================================================================
 * PAST TOURS - PUBLIC LISTING PAGE
 * ============================================================================
 * 
 * IMAGE STORAGE STANDARD:
 * - All images are loaded from: /uploads/past_tours/
 * - Cover images: /uploads/past_tours/covers/
 * - These paths are set by admin panel (admin_past_tours.php)
 * - Database stores relative paths: uploads/past_tours/covers/filename.jpg
 * 
 * IMPORTANT: Do NOT hardcode image paths. Always read from database.
 * 
 * ============================================================================
 */

$active = "pasttours"; 
include("functions.php");
include("header.php");
include("db.php");

// Fetch past tours from database - ordered by most recent first
$query = "SELECT * FROM past_tours WHERE status = 1 ORDER BY start_date DESC";
$result = mysqli_query($con, $query);

// Check for query errors
if (!$result) {
    die("Database query failed: " . mysqli_error($con));
}
?>

<head>
    <link rel="stylesheet" href="css/pasttours.css">
    <link rel="stylesheet" href="css/tourcard.css">
</head>

<!-- Page Header Section -->
<section class="past-tours-header">
    <div class="header-overlay">
        <div class="header-content">
            <h1>Our Past Tours</h1>
            <p>Relive the memories and experiences from our previous educational journeys</p>
            <div class="breadcrumb">
                <a href="index.php"><i class="fa fa-home"></i> Home</a>
                <span>/</span>
                <span>Past Tours</span>
            </div>
        </div>
    </div>
</section>

<!-- Past Tours Grid Section -->
<section class="past-tours-section">
    <div class="tour-container">
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            
            <div class="tour-stats">
                <div class="stat-item">
                    <i class="fa fa-globe"></i>
                    <div>
                        <h3><?php echo mysqli_num_rows($result); ?></h3>
                        <p>Tours Completed</p>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fa fa-users"></i>
                    <div>
                        <?php
                        // Calculate total participants
                        $total_participants = 0;
                        mysqli_data_seek($result, 0);
                        while ($row = mysqli_fetch_assoc($result)) {
                            $total_participants += $row['participants'];
                        }
                        mysqli_data_seek($result, 0);
                        ?>
                        <h3><?php echo $total_participants; ?>+</h3>
                        <p>Happy Travelers</p>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fa fa-camera"></i>
                    <div>
                        <?php
                        // Count total media
                        $media_query = "SELECT COUNT(*) as total FROM past_tour_media";
                        $media_result = mysqli_query($con, $media_query);
                        $media_count = mysqli_fetch_assoc($media_result)['total'];
                        ?>
                        <h3><?php echo $media_count; ?>+</h3>
                        <p>Memories Captured</p>
                    </div>
                </div>
            </div>

            <!-- Tour Cards Grid -->
            <div class="tour-cards-wrapper">
                <?php while ($tour = mysqli_fetch_assoc($result)): ?>
                    
                    <!-- Past Tour Card -->
                    <div class="tour-card past-tour-card">
                        <a href="past_tour_details.php?tour_id=<?php echo $tour['id']; ?>" class="tour-card-link">
                            <div class="tour-card-inner">
                                
                                <!-- Card Image -->
                                <div class="tour-card-image">
                                    <div class="tour-year-badge">
                                        <?php echo date('Y', strtotime($tour['start_date'])); ?>
                                    </div>
                                    
                                    <?php 
                                    /**
                                     * IMAGE LOADING LOGIC:
                                     * - Cover images are stored in: uploads/past_tours/covers/
                                     * - Database field 'cover_media' contains relative path
                                     * - Example: "uploads/past_tours/covers/cover_abc123.jpg"
                                     * - Check if file exists before displaying
                                     */
                                    if (!empty($tour['cover_media']) && file_exists($tour['cover_media'])): 
                                    ?>
                                        <img src="<?php echo htmlspecialchars($tour['cover_media']); ?>" 
                                             alt="<?php echo htmlspecialchars($tour['title']); ?>"
                                             loading="lazy">
                                    <?php else: ?>
                                        <!-- Fallback image if cover not found -->
                                        <img src="img/tours/default-past-tour.jpg" 
                                             alt="<?php echo htmlspecialchars($tour['title']); ?>"
                                             loading="lazy"
                                             onerror="this.src='img/placeholder.jpg'">
                                    <?php endif; ?>
                                    
                                    <div class="image-overlay">
                                        <i class="fa fa-search-plus"></i>
                                        <p>View Gallery</p>
                                    </div>
                                </div>
                                
                                <!-- Card Content -->
                                <div class="tour-card-content">
                                    <div class="tour-card-header">
                                        <h3 class="tour-title"><?php echo htmlspecialchars($tour['title']); ?></h3>
                                    </div>
                                    
                                    <div class="tour-details">
                                        <div class="tour-meta">
                                            <span>
                                                <i class="fa fa-map-marker"></i> 
                                                <?php echo htmlspecialchars($tour['destination']); ?>
                                            </span>
                                            <span>
                                                <i class="fa fa-calendar"></i> 
                                                <?php 
                                                echo date('M d', strtotime($tour['start_date'])) . ' - ' . 
                                                     date('M d, Y', strtotime($tour['end_date'])); 
                                                ?>
                                            </span>
                                        </div>
                                        
                                        <p class="tour-description">
                                            <?php echo htmlspecialchars($tour['summary']); ?>
                                        </p>
                                        
                                        <?php if ($tour['participants'] > 0): ?>
                                            <div class="participants-badge">
                                                <i class="fa fa-users"></i>
                                                <span><?php echo $tour['participants']; ?> participants</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="tour-card-footer">
                                        <div class="tour-action">
                                            <span>View Memories</span>
                                            <i class="fa fa-arrow-right"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                <?php endwhile; ?>
            </div>
            
        <?php else: ?>
            
            <!-- No Tours Available Message -->
            <div class="no-tours-message">
                <i class="fa fa-calendar-times-o fa-4x"></i>
                <h3>No Past Tours Available</h3>
                <p>Check back later to see our completed educational journeys.</p>
                <a href="index.php" class="btn-primary">
                    <i class="fa fa-home"></i> Back to Home
                </a>
            </div>
            
        <?php endif; ?>
        
    </div>
</section>

<?php
mysqli_close($con);
include("footer.php");
?>
