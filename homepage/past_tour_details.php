<?php
/**
 * ============================================================================
 * PAST TOUR DETAILS PAGE
 * ============================================================================
 * 
 * IMAGE STORAGE STANDARD:
 * - All images loaded from: /uploads/past_tours/
 * - Cover images: /uploads/past_tours/covers/
 * - Gallery images: /uploads/past_tours/gallery/
 * - Database stores relative paths (e.g., uploads/past_tours/gallery/image.jpg)
 * 
 * IMPORTANT: Images are managed by admin panel (admin_past_tours.php)
 * Do NOT hardcode paths - always read from database
 * 
 * ============================================================================
 */

$active = "pasttours";
include("functions.php");
include("header.php");
include("db.php");

// Get tour ID from URL
$tour_id = isset($_GET['tour_id']) ? intval($_GET['tour_id']) : 0;

if ($tour_id <= 0) {
    header("Location: pasttours.php");
    exit();
}

// Fetch tour details using prepared statement
$stmt = mysqli_prepare($con, "SELECT * FROM past_tours WHERE id = ? AND status = 1");
mysqli_stmt_bind_param($stmt, "i", $tour_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: pasttours.php");
    exit();
}

$tour = mysqli_fetch_assoc($result);

// Fetch tour media ordered by display_order
$media_stmt = mysqli_prepare($con, "SELECT * FROM past_tour_media WHERE tour_id = ? ORDER BY display_order ASC");
mysqli_stmt_bind_param($media_stmt, "i", $tour_id);
mysqli_stmt_execute($media_stmt);
$media_result = mysqli_stmt_get_result($media_stmt);

// Separate media into hero and gallery
$hero_media = null;
$gallery_media = [];
$video_media = [];

while ($media = mysqli_fetch_assoc($media_result)) {
    if ($media['display_order'] == 1 && $media['media_type'] == 'image') {
        $hero_media = $media;
    } else {
        if ($media['media_type'] == 'video') {
            $video_media[] = $media;
        } else {
            $gallery_media[] = $media;
        }
    }
}

/**
 * HERO IMAGE SELECTION:
 * Priority: 1. First media item (display_order=1)
 *           2. Tour cover image
 *           3. Default placeholder
 */
$hero_image_url = '';
if ($hero_media && !empty($hero_media['media_url']) && file_exists($hero_media['media_url'])) {
    $hero_image_url = $hero_media['media_url'];
} elseif (!empty($tour['cover_media']) && file_exists($tour['cover_media'])) {
    $hero_image_url = $tour['cover_media'];
} else {
    $hero_image_url = 'img/tours/default-past-tour.jpg';
}
?>

<head>
    <link rel="stylesheet" href="css/past_tour_details.css">
    <meta property="og:title" content="<?php echo htmlspecialchars($tour['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($tour['summary']); ?>">
</head>

<!-- Hero Section with Featured Image/Video -->
<section class="tour-hero" style="background-image: url('<?php echo htmlspecialchars($hero_image_url); ?>');">
    <div class="hero-overlay">
        <div class="hero-content">
            
            
            <h1 class="tour-hero-title"><?php echo htmlspecialchars($tour['title']); ?></h1>
            
            <div class="tour-hero-meta">
                <div class="meta-item">
                    <i class="fa fa-map-marker"></i>
                    <span><?php echo htmlspecialchars($tour['destination']); ?></span>
                </div>
                <div class="meta-item">
                    <i class="fa fa-calendar"></i>
                    <span>
                        <?php 
                        echo date('F j', strtotime($tour['start_date'])) . ' - ' . 
                             date('F j, Y', strtotime($tour['end_date'])); 
                        ?>
                    </span>
                </div>
                <?php if ($tour['participants'] > 0): ?>
                <div class="meta-item">
                    <i class="fa fa-users"></i>
                    <span><?php echo $tour['participants']; ?> Participants</span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="scroll-indicator">
                <i class="fa fa-chevron-down"></i>
                <p>Scroll to explore</p>
            </div>
        </div>
    </div>
</section>

<!-- Tour Summary Section -->
<section class="tour-summary">
    <div class="container">
        <div class="summary-content">
            <h2>About This Journey</h2>
            <p class="summary-text"><?php echo nl2br(htmlspecialchars($tour['summary'])); ?></p>
        </div>
    </div>
</section>

<!-- Journey Timeline - Photo Gallery -->
<?php if (count($gallery_media) > 0): ?>
<section class="journey-timeline">
    <div class="container">
        <h2 class="section-title">
            <span>Tour Highlights</span>
            <div class="title-underline"></div>
        </h2>
        
        <div class="timeline-grid">
            <?php foreach ($gallery_media as $index => $media): ?>
                <div class="timeline-item" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                    <div class="timeline-media">
                        <?php 
                        /**
                         * GALLERY IMAGE LOADING:
                         * - Images stored in: uploads/past_tours/gallery/
                         * - Check file existence before displaying
                         * - Show placeholder if file not found
                         */
                        if (file_exists($media['media_url'])): 
                        ?>
                            <img src="<?php echo htmlspecialchars($media['media_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($media['caption']); ?>"
                                 loading="lazy"
                                 onclick="openLightbox(<?php echo $index; ?>)">
                        <?php else: ?>
                            <img src="img/placeholder.jpg" 
                                 alt="Image not found"
                                 loading="lazy">
                        <?php endif; ?>
                        <div class="media-overlay">
                            <i class="fa fa-search-plus"></i>
                        </div>
                    </div>
                    <?php if (!empty($media['caption'])): ?>
                        <div class="timeline-caption">
                            <p><?php echo htmlspecialchars($media['caption']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Video Section -->
<?php if (count($video_media) > 0): ?>
<section class="video-section">
    <div class="container">
        <h2 class="section-title">
            <span>Video Highlights</span>
            <div class="title-underline"></div>
        </h2>
        
        <div class="video-grid">
            <?php foreach ($video_media as $video): ?>
                <div class="video-item">
                    <div class="video-wrapper">
                        <?php if (strpos($video['media_url'], 'youtube.com') !== false || strpos($video['media_url'], 'youtu.be') !== false): ?>
                            <iframe src="<?php echo htmlspecialchars($video['media_url']); ?>" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen
                                    loading="lazy">
                            </iframe>
                        <?php else: ?>
                            <video controls preload="metadata">
                                <source src="<?php echo htmlspecialchars($video['media_url']); ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($video['caption'])): ?>
                        <p class="video-caption"><?php echo htmlspecialchars($video['caption']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Call to Action Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Ready for Your Own Adventure?</h2>
            <p>Join us on our next educational journey and create unforgettable memories</p>
            <div class="cta-buttons">
                <a href="index.php" class="btn-primary">
                    <i class="fa fa-compass"></i> View Upcoming Tours
                </a>
                <a href="pasttours.php" class="btn-secondary">
                    <i class="fa fa-history"></i> More Past Tours
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div id="lightbox" class="lightbox">
    <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
    <div class="lightbox-content">
        <img id="lightbox-img" src="" alt="">
        <div id="lightbox-caption"></div>
    </div>
    <a class="lightbox-prev" onclick="changeImage(-1)">&#10094;</a>
    <a class="lightbox-next" onclick="changeImage(1)">&#10095;</a>
</div>

<script>
// Lightbox functionality
let currentImageIndex = 0;
const images = <?php echo json_encode(array_map(function($m) { 
    return ['url' => $m['media_url'], 'caption' => $m['caption']]; 
}, $gallery_media)); ?>;

function openLightbox(index) {
    currentImageIndex = index;
    document.getElementById('lightbox').style.display = 'flex';
    updateLightboxImage();
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function changeImage(direction) {
    currentImageIndex += direction;
    if (currentImageIndex >= images.length) currentImageIndex = 0;
    if (currentImageIndex < 0) currentImageIndex = images.length - 1;
    updateLightboxImage();
}

function updateLightboxImage() {
    const img = document.getElementById('lightbox-img');
    const caption = document.getElementById('lightbox-caption');
    img.src = images[currentImageIndex].url;
    caption.textContent = images[currentImageIndex].caption || '';
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (document.getElementById('lightbox').style.display === 'flex') {
        if (e.key === 'ArrowLeft') changeImage(-1);
        if (e.key === 'ArrowRight') changeImage(1);
        if (e.key === 'Escape') closeLightbox();
    }
});

// Smooth scroll for scroll indicator
document.querySelector('.scroll-indicator')?.addEventListener('click', function() {
    window.scrollTo({
        top: window.innerHeight,
        behavior: 'smooth'
    });
});

// Simple fade-in animation on scroll
document.addEventListener('DOMContentLoaded', function() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.timeline-item').forEach(item => {
        observer.observe(item);
    });
});
</script>

<?php
mysqli_close($con);
include("footer.php");
?>
