<?php
/**
 * ============================================================================
 * PAST TOURS ADMIN - SINGLE FILE MANAGEMENT SYSTEM
 * ============================================================================
 * 
 * PURPOSE:
 * - Manage all past tours (CRUD operations) in ONE file
 * - Manage media gallery (images & videos) for each tour
 * - English-only interface (NO dual-language)
 * 
 * IMAGE STORAGE STANDARD:
 * - All images stored in: /uploads/past_tours/
 * - Cover images: /uploads/past_tours/covers/
 * - Gallery images: /uploads/past_tours/gallery/
 * - Database stores relative paths: uploads/past_tours/covers/filename.jpg
 * 
 * ACTIONS HANDLED:
 * - list (default) - Show all tours
 * - add - Add new tour form
 * - edit - Edit tour form
 * - save - Save new/edited tour
 * - delete - Delete tour
 * - media - Manage media for a tour
 * - add_media - Add new media item
 * - delete_media - Delete media item
 * - update_caption - Update media caption
 * - update_order - Update media display order
 * 
 * ============================================================================
 */

session_start();

// Authentication check
if (!isset($_SESSION['user'])) {
    header('Location: adminmain.php');
    exit();
}

require_once('../homepage/db.php');

// ============================================================================
// CONFIGURATION
// ============================================================================

define('UPLOAD_DIR', '../homepage/uploads/past_tours/');
define('COVER_DIR', UPLOAD_DIR . 'covers/');
define('GALLERY_DIR', UPLOAD_DIR . 'gallery/');
define('DB_COVER_PATH', 'uploads/past_tours/covers/');
define('DB_GALLERY_PATH', 'uploads/past_tours/gallery/');

// Create directories if they don't exist
if (!file_exists(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
if (!file_exists(COVER_DIR)) mkdir(COVER_DIR, 0755, true);
if (!file_exists(GALLERY_DIR)) mkdir(GALLERY_DIR, 0755, true);

// Allowed image types
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$max_file_size = 5 * 1024 * 1024; // 5MB

// Message variables
$success = '';
$error = '';

// Get action from URL
$action = $_GET['action'] ?? 'list';
$tour_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ============================================================================
// DELETE TOUR
// ============================================================================

if ($action == 'delete' && $tour_id > 0) {
    // Get tour data
    $stmt = mysqli_prepare($con, "SELECT cover_media FROM past_tours WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $tour_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $tour = mysqli_fetch_assoc($result);
    
    if ($tour) {
        // Delete cover image file
        if (!empty($tour['cover_media']) && file_exists('../homepage/' . $tour['cover_media'])) {
            @unlink('../homepage/' . $tour['cover_media']);
        }
        
        // Get and delete all media files
        $media_stmt = mysqli_prepare($con, "SELECT media_url, media_type FROM past_tour_media WHERE tour_id = ?");
        mysqli_stmt_bind_param($media_stmt, "i", $tour_id);
        mysqli_stmt_execute($media_stmt);
        $media_result = mysqli_stmt_get_result($media_stmt);
        
        while ($media = mysqli_fetch_assoc($media_result)) {
            if ($media['media_type'] == 'image' && file_exists('../homepage/' . $media['media_url'])) {
                @unlink('../homepage/' . $media['media_url']);
            }
        }
        mysqli_stmt_close($media_stmt);
        
        // Delete from database (cascade will handle media)
        $delete_stmt = mysqli_prepare($con, "DELETE FROM past_tours WHERE id = ?");
        mysqli_stmt_bind_param($delete_stmt, "i", $tour_id);
        
        if (mysqli_stmt_execute($delete_stmt)) {
            $success = "Tour deleted successfully!";
            $action = 'list';
        } else {
            $error = "Error deleting tour: " . mysqli_error($con);
        }
        mysqli_stmt_close($delete_stmt);
    }
    mysqli_stmt_close($stmt);
}

// ============================================================================
// SAVE TOUR (ADD OR EDIT)
// ============================================================================

if (['REQUEST_METHOD'] == 'POST' && isset($_POST['save_tour'])) {
    
    $edit_id = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
    
    // Get and sanitize form data (ENGLISH ONLY)
    $title = mysqli_real_escape_string($con, trim($_POST['title']));
    $destination = mysqli_real_escape_string($con, trim($_POST['destination']));
    $summary = mysqli_real_escape_string($con, trim($_POST['summary']));
    $start_date = mysqli_real_escape_string($con, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($con, $_POST['end_date']);
    $participants = (int)$_POST['participants'];
    $status = (int)$_POST['status'];
    
    $cover_media = '';
    
    // Handle cover image upload
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == UPLOAD_ERR_OK) {
        $file_ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_extensions) && $_FILES['cover_image']['size'] <= $max_file_size) {
            $new_filename = 'cover_' . uniqid() . '.' . $file_ext;
            $upload_path = COVER_DIR . $new_filename;
            
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_path)) {
                $cover_media = DB_COVER_PATH . $new_filename;
                
                // Delete old cover if editing
                if ($edit_id > 0) {
                    $old_stmt = mysqli_prepare($con, "SELECT cover_media FROM past_tours WHERE id = ?");
                    mysqli_stmt_bind_param($old_stmt, "i", $edit_id);
                    mysqli_stmt_execute($old_stmt);
                    $old_result = mysqli_stmt_get_result($old_stmt);
                    $old_tour = mysqli_fetch_assoc($old_result);
                    
                    if (!empty($old_tour['cover_media']) && file_exists('../homepage/' . $old_tour['cover_media'])) {
                        @unlink('../homepage/' . $old_tour['cover_media']);
                    }
                    mysqli_stmt_close($old_stmt);
                }
            } else {
                $error = "Failed to upload cover image.";
            }
        } else {
            $error = "Invalid image file. Must be JPG, PNG, GIF, or WEBP under 5MB.";
        }
    }
    
    // Insert or Update
    if (empty($error)) {
        if ($edit_id > 0) {
            // UPDATE existing tour
            if (!empty($cover_media)) {
                $sql = "UPDATE past_tours SET title=?, destination=?, summary=?, start_date=?, end_date=?, cover_media=?, participants=?, status=?, updated_at=NOW() WHERE id=?";
                $stmt = mysqli_prepare($con, $sql);
                mysqli_stmt_bind_param($stmt, "sssssiii", $title, $destination, $summary, $start_date, $end_date, $cover_media, $participants, $status, $edit_id);
            } else {
                $sql = "UPDATE past_tours SET title=?, destination=?, summary=?, start_date=?, end_date=?, participants=?, status=?, updated_at=NOW() WHERE id=?";
                $stmt = mysqli_prepare($con, $sql);
                mysqli_stmt_bind_param($stmt, "ssssiiii", $title, $destination, $summary, $start_date, $end_date, $participants, $status, $edit_id);
            }
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Tour updated successfully!";
                $action = 'list';
            } else {
                $error = "Error updating tour: " . mysqli_error($con);
            }
        } else {
            // INSERT new tour
            $sql = "INSERT INTO past_tours (title, destination, summary, start_date, end_date, cover_media, participants, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "ssssssii", $title, $destination, $summary, $start_date, $end_date, $cover_media, $participants, $status);
            
            if (mysqli_stmt_execute($stmt)) {
                $tour_id = mysqli_insert_id($con);
                $success = "Tour added successfully! You can now add media.";
                $action = 'media';
            } else {
                $error = "Error adding tour: " . mysqli_error($con);
            }
        }
        mysqli_stmt_close($stmt);
    }
}

// ============================================================================
// ADD MEDIA
// ============================================================================

if (['REQUEST_METHOD'] == 'POST' && isset($_POST['add_media'])) {
    $media_tour_id = (int)$_POST['tour_id'];
    $media_type = mysqli_real_escape_string($con, $_POST['media_type']);
    $caption = mysqli_real_escape_string($con, trim($_POST['caption']));
    $display_order = (int)$_POST['display_order'];
    $media_url = '';
    
    if ($media_type == 'image') {
        // Handle image upload
        if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] == UPLOAD_ERR_OK) {
            $file_ext = strtolower(pathinfo($_FILES['media_file']['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_ext, $allowed_extensions) && $_FILES['media_file']['size'] <= $max_file_size) {
                $new_filename = 'gallery_' . $media_tour_id . '_' . uniqid() . '.' . $file_ext;
                $upload_path = GALLERY_DIR . $new_filename;
                
                if (move_uploaded_file($_FILES['media_file']['tmp_name'], $upload_path)) {
                    $media_url = DB_GALLERY_PATH . $new_filename;
                } else {
                    $error = "Failed to upload media file.";
                }
            } else {
                $error = "Invalid image file.";
            }
        }
    } elseif ($media_type == 'video') {
        // Handle video URL
        $video_url = trim($_POST['video_url']);
        
        // Convert YouTube URLs to embed format
        if (strpos($video_url, 'youtube.com/watch?v=') !== false) {
            $video_url = str_replace('watch?v=', 'embed/', $video_url);
        } elseif (strpos($video_url, 'youtu.be/') !== false) {
            $video_url = str_replace('youtu.be/', 'youtube.com/embed/', $video_url);
        }
        
        $media_url = mysqli_real_escape_string($con, $video_url);
    }
    
    // Insert media
    if (!empty($media_url) && empty($error)) {
        $sql = "INSERT INTO past_tour_media (tour_id, media_type, media_url, caption, display_order, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "isssi", $media_tour_id, $media_type, $media_url, $caption, $display_order);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Media added successfully!";
        } else {
            $error = "Error adding media: " . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);
    }
    
    $action = 'media';
    $tour_id = $media_tour_id;
}

// ============================================================================
// DELETE MEDIA
// ============================================================================

if ($action == 'delete_media' && isset($_GET['media_id'])) {
    $media_id = (int)$_GET['media_id'];
    
    // Get media info
    $stmt = mysqli_prepare($con, "SELECT media_url, media_type, tour_id FROM past_tour_media WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $media_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $media = mysqli_fetch_assoc($result);
    
    if ($media) {
        // Delete file if it's an image
        if ($media['media_type'] == 'image' && file_exists('../homepage/' . $media['media_url'])) {
            @unlink('../homepage/' . $media['media_url']);
        }
        
        // Delete from database
        $delete_stmt = mysqli_prepare($con, "DELETE FROM past_tour_media WHERE id = ?");
        mysqli_stmt_bind_param($delete_stmt, "i", $media_id);
        
        if (mysqli_stmt_execute($delete_stmt)) {
            $success = "Media deleted successfully!";
        }
        mysqli_stmt_close($delete_stmt);
        
        $tour_id = $media['tour_id'];
        $action = 'media';
    }
    mysqli_stmt_close($stmt);
}

// ============================================================================
// UPDATE MEDIA CAPTION
// ============================================================================

if (['REQUEST_METHOD'] == 'POST' && isset($_POST['update_caption'])) {
    $media_id = (int)$_POST['media_id'];
    $new_caption = mysqli_real_escape_string($con, trim($_POST['new_caption']));
    
    $sql = "UPDATE past_tour_media SET caption = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "si", $new_caption, $media_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success = "Caption updated!";
    }
    mysqli_stmt_close($stmt);
    
    $tour_id = (int)$_POST['tour_id'];
    $action = 'media';
}

// ============================================================================
// UPDATE MEDIA ORDER
// ============================================================================

if (['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order'])) {
    $media_id = (int)$_POST['media_id'];
    $new_order = (int)$_POST['new_order'];
    
    $sql = "UPDATE past_tour_media SET display_order = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $new_order, $media_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success = "Display order updated!";
    }
    mysqli_stmt_close($stmt);
    
    $tour_id = (int)$_POST['tour_id'];
    $action = 'media';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Past Tours Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a2b49;
            --secondary: #ff7e00;
        }
        .admin-header {
            background: linear-gradient(135deg, var(--primary) 0%, #2c3e5a 100%);
            color: white;
            padding: 25px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .tour-thumbnail { width: 80px; height: 60px; object-fit: cover; border-radius: 5px; }
        .media-thumbnail { width: 120px; height: 80px; object-fit: cover; border-radius: 5px; }
        .status-badge { padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight: 600; }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .info-box { background: #e7f3ff; padding: 15px; border-radius: 8px; border-left: 4px solid #0d6efd; margin-bottom: 20px; }
        .upload-info { background: #fff3cd; padding: 10px; border-radius: 5px; font-size: 13px; margin-top: 10px; }
    </style>
</head>
<body style="background: #f5f5f5;">

<div class="admin-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2><i class="fas fa-history"></i> Past Tours Management</h2>
                <p class="mb-0">Single-file admin system | English only | Unified image storage</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="adminmain.php" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- STORAGE PATH INFO -->
    <div class="info-box">
        <strong><i class="fas fa-info-circle"></i> Image Storage Standard:</strong><br>
        All images are stored in: <code>/uploads/past_tours/</code><br>
        • Cover images: <code>/uploads/past_tours/covers/</code><br>
        • Gallery images: <code>/uploads/past_tours/gallery/</code><br>
        • Public pages (pasttours.php, past_tour_details.php) read from this location
    </div>

<?php
// ============================================================================
// RENDER APPROPRIATE VIEW BASED ON ACTION
// ============================================================================

switch ($action) {
    case 'add':
    case 'edit':
        include 'includes/past_tours_form.php';
        break;
    case 'media':
        include 'includes/past_tours_media.php';
        break;
    default:
        include 'includes/past_tours_list.php';
        break;
}
?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Image preview
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
            document.getElementById(previewId).style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Toggle media input type
function toggleMediaType(type) {
    document.getElementById('image-upload-section').style.display = (type === 'image') ? 'block' : 'none';
    document.getElementById('video-url-section').style.display = (type === 'video') ? 'block' : 'none';
}
</script>

</body>
</html>

<?php
mysqli_close($con);
?>
