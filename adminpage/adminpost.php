<?php
session_start();

// Authentication check
if (!isset($_SESSION['user'])) {
    header('Location: adminmain.php');
    exit();
}

require_once('../homepage/db.php');

define('UPLOAD_DIR', '../homepage/uploads/tours/');
define('COVER_DIR', UPLOAD_DIR . 'covers/');
define('GALLERY_DIR', UPLOAD_DIR . 'gallery/');
define('DB_COVER_PATH', 'uploads/tours/covers/');
define('DB_GALLERY_PATH', 'uploads/tours/gallery/');

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

if ($action == 'delete' && $tour_id > 0) {
    // Get tour data
    $stmt = mysqli_prepare($con, "SELECT cover_image FROM tours WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $tour_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $tour = mysqli_fetch_assoc($result);
    
    if ($tour) {
        // Delete cover image file
        if (!empty($tour['cover_image']) && file_exists('../homepage/' . $tour['cover_image'])) {
            @unlink('../homepage/' . $tour['cover_image']);
        }
        
        // Get and delete all media files
        $media_stmt = mysqli_prepare($con, "SELECT media_url, media_type FROM tour_media WHERE tour_id = ?");
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
        $delete_stmt = mysqli_prepare($con, "DELETE FROM tours WHERE id = ?");
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_tour'])) {
    
    $edit_id = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
    
    // Get and sanitize form data (ENGLISH ONLY)
    $title = mysqli_real_escape_string($con, trim($_POST['title']));
    $destination = mysqli_real_escape_string($con, trim($_POST['destination']));
    $description = mysqli_real_escape_string($con, trim($_POST['description']));
    $start_date = mysqli_real_escape_string($con, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($con, $_POST['end_date']);
    $duration = (int)$_POST['duration'];
    $participants = isset($_POST['participants']) ? (int)$_POST['participants'] : 0;
    $tour_status = mysqli_real_escape_string($con, $_POST['tour_status']); // upcoming or past
    $status = (int)$_POST['status']; // 1=active, 0=inactive
    $price = mysqli_real_escape_string($con, trim($_POST['price']));
    $year = (int)$_POST['year'];
    
    $cover_image = '';
    
    // Handle cover image upload
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == UPLOAD_ERR_OK) {
        $file_ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_extensions) && $_FILES['cover_image']['size'] <= $max_file_size) {
            $new_filename = 'cover_' . uniqid() . '.' . $file_ext;
            $upload_path = COVER_DIR . $new_filename;
            
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_path)) {
                $cover_image = DB_COVER_PATH . $new_filename;
                
                // Delete old cover if editing
                if ($edit_id > 0) {
                    $old_stmt = mysqli_prepare($con, "SELECT cover_image FROM tours WHERE id = ?");
                    mysqli_stmt_bind_param($old_stmt, "i", $edit_id);
                    mysqli_stmt_execute($old_stmt);
                    $old_result = mysqli_stmt_get_result($old_stmt);
                    $old_tour = mysqli_fetch_assoc($old_result);
                    
                    if (!empty($old_tour['cover_image']) && file_exists('../homepage/' . $old_tour['cover_image'])) {
                        @unlink('../homepage/' . $old_tour['cover_image']);
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
            if (!empty($cover_image)) {
                $sql = "UPDATE tours SET title=?, destination=?, description=?, start_date=?, end_date=?, duration=?, cover_image=?, participants=?, tour_status=?, status=?, price=?, year=?, updated_at=NOW() WHERE id=?";
                $stmt = mysqli_prepare($con, $sql);
                mysqli_stmt_bind_param($stmt, "sssssisisssii", $title, $destination, $description, $start_date, $end_date, $duration, $cover_image, $participants, $tour_status, $status, $price, $year, $edit_id);
            } else {
                $sql = "UPDATE tours SET title=?, destination=?, description=?, start_date=?, end_date=?, duration=?, participants=?, tour_status=?, status=?, price=?, year=?, updated_at=NOW() WHERE id=?";
                $stmt = mysqli_prepare($con, $sql);
                mysqli_stmt_bind_param($stmt, "sssssiisissi", $title, $destination, $description, $start_date, $end_date, $duration, $participants, $tour_status, $status, $price, $year, $edit_id);
            }
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Tour updated successfully!";
                $action = 'list';
            } else {
                $error = "Error updating tour: " . mysqli_error($con);
            }
        } else {
            // INSERT new tour
            $sql = "INSERT INTO tours (title, destination, description, start_date, end_date, duration, cover_image, participants, tour_status, status, price, year, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "sssssissisii", $title, $destination, $description, $start_date, $end_date, $duration, $cover_image, $participants, $tour_status, $status, $price, $year);
            
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_media'])) {
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
        $sql = "INSERT INTO tour_media (tour_id, media_type, media_url, caption, display_order, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
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
    $stmt = mysqli_prepare($con, "SELECT media_url, media_type, tour_id FROM tour_media WHERE id = ?");
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
        $delete_stmt = mysqli_prepare($con, "DELETE FROM tour_media WHERE id = ?");
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_caption'])) {
    $media_id = (int)$_POST['media_id'];
    $new_caption = mysqli_real_escape_string($con, trim($_POST['new_caption']));
    
    $sql = "UPDATE tour_media SET caption = ? WHERE id = ?";
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order'])) {
    $media_id = (int)$_POST['media_id'];
    $new_order = (int)$_POST['new_order'];
    
    $sql = "UPDATE tour_media SET display_order = ? WHERE id = ?";
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
    <title>Tours Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a2b49;
            --secondary: #ff7e00;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
        }
        
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--primary) 0%, #2c3e5a 100%);
            color: white;
            padding: 25px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border: none;
        }
        
        .card-header {
            background: white;
            border-bottom: 2px solid #f0f0f0;
            font-weight: 600;
            color: var(--primary);
        }
        
        .tour-thumbnail {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .media-thumbnail {
            width: 120px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .tour-upcoming {
            background: #e3f2fd;
            color: #1565c0;
        }
        
        .tour-past {
            background: #f3e5f5;
            color: #6a1b9a;
        }
        
        .info-box {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #0d6efd;
            margin-bottom: 20px;
        }
        
        .upload-info {
            background: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            font-size: 13px;
            margin-top: 10px;
        }
        
        .filter-tabs {
            margin-bottom: 20px;
        }
        
        .filter-tabs .nav-link {
            color: #666;
            font-weight: 500;
            border-radius: 8px;
            margin-right: 10px;
        }
        
        .filter-tabs .nav-link.active {
            background: var(--primary);
            color: white;
        }
        
        .btn-action {
            padding: 5px 10px;
            font-size: 13px;
            margin: 2px;
        }
        
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table thead {
            background: var(--primary);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 8px;
            display: none;
        }
    </style>
</head>
<body>

<div class="admin-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2>Tours Management System</h2>
                
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
        All images are stored in: <code>/uploads/tours/</code><br>
        • Cover images: <code>/uploads/tours/covers/</code><br>
        • Gallery images: <code>/uploads/tours/gallery/</code><br>
        • Public pages read from this unified location
    </div>

<?php
// ============================================================================
// RENDER APPROPRIATE VIEW BASED ON ACTION
// ============================================================================

switch ($action) {
    case 'add':
    case 'edit':
        // ===== TOUR FORM VIEW =====
        $edit_tour = null;
        if ($action == 'edit' && $tour_id > 0) {
            $stmt = mysqli_prepare($con, "SELECT * FROM tours WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $tour_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $edit_tour = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
        }
        ?>
        
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-<?php echo $action == 'edit' ? 'edit' : 'plus'; ?>"></i>
                    <?php echo $action == 'edit' ? 'Edit Tour' : 'Add New Tour'; ?>
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($action == 'edit'): ?>
                        <input type="hidden" name="tour_id" value="<?php echo $tour_id; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <!-- Title -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-heading"></i> Tour Title *</label>
                            <input type="text" name="title" class="form-control" required
                                   value="<?php echo $edit_tour ? htmlspecialchars($edit_tour['title']) : ''; ?>"
                                   placeholder="e.g., Malaysia Educational Tour 2025">
                        </div>
                        
                        <!-- Destination -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-map-marker-alt"></i> Destination *</label>
                            <input type="text" name="destination" class="form-control" required
                                   value="<?php echo $edit_tour ? htmlspecialchars($edit_tour['destination']) : ''; ?>"
                                   placeholder="e.g., Kuala Lumpur, Malaysia">
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-align-left"></i> Description *</label>
                        <textarea name="description" class="form-control" rows="4" required
                                  placeholder="Enter tour description..."><?php echo $edit_tour ? htmlspecialchars($edit_tour['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="row">
                        <!-- Start Date -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="fas fa-calendar-alt"></i> Start Date *</label>
                            <input type="date" name="start_date" class="form-control" required
                                   value="<?php echo $edit_tour ? $edit_tour['start_date'] : ''; ?>">
                        </div>
                        
                        <!-- End Date -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="fas fa-calendar-check"></i> End Date *</label>
                            <input type="date" name="end_date" class="form-control" required
                                   value="<?php echo $edit_tour ? $edit_tour['end_date'] : ''; ?>">
                        </div>
                        
                        <!-- Duration -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="fas fa-clock"></i> Duration (Days) *</label>
                            <input type="number" name="duration" class="form-control" min="1" required
                                   value="<?php echo $edit_tour ? $edit_tour['duration'] : ''; ?>"
                                   placeholder="e.g., 5">
                        </div>
                    </div>
                    
                    <div class="row">
                        
                        
                        <!-- Participants -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="fas fa-users"></i> Participants</label>
                            <input type="number" name="participants" class="form-control" min="0"
                                   value="<?php echo $edit_tour ? $edit_tour['participants'] : '0'; ?>"
                                   placeholder="Number of participants">
                            <small class="text-muted">For past tours only</small>
                        </div>
                        
                        <!-- Year -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="fas fa-calendar"></i> Year *</label>
                            <input type="number" name="year" class="form-control" min="2020" max="2030" required
                                   value="<?php echo $edit_tour ? $edit_tour['year'] : date('Y'); ?>"
                                   placeholder="e.g., 2025">
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Tour Status -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-flag"></i> Tour Status *</label>
                            <select name="tour_status" class="form-control" required>
                                <option value="upcoming" <?php echo ($edit_tour && $edit_tour['tour_status'] == 'upcoming') ? 'selected' : ''; ?>>
                                    Upcoming Tour
                                </option>
                                <option value="past" <?php echo ($edit_tour && $edit_tour['tour_status'] == 'past') ? 'selected' : ''; ?>>
                                    Past Tour
                                </option>
                            </select>
                        </div>
                        
                        <!-- Active Status -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-toggle-on"></i> Active Status *</label>
                            <select name="status" class="form-control" required>
                                <option value="1" <?php echo ($edit_tour && $edit_tour['status'] == 1) ? 'selected' : ''; ?>>
                                    Active (Visible)
                                </option>
                                <option value="0" <?php echo ($edit_tour && $edit_tour['status'] == 0) ? 'selected' : ''; ?>>
                                    Inactive (Hidden)
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Cover Image -->
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-image"></i> Cover Image</label>
                        <input type="file" name="cover_image" class="form-control" accept="image/*"
                               onchange="previewImage(this, 'preview-cover')">
                        <div class="upload-info">
                            <i class="fas fa-info-circle"></i> 
                            Upload JPG, PNG, GIF, or WEBP. Max size: 5MB. Recommended: 1200x600px
                        </div>
                        <?php if ($edit_tour && !empty($edit_tour['cover_image'])): ?>
                            <div class="mt-2">
                                <img src="../homepage/<?php echo htmlspecialchars($edit_tour['cover_image']); ?>" 
                                     alt="Current cover" class="tour-thumbnail">
                                <small class="text-muted d-block">Current cover image</small>
                            </div>
                        <?php endif; ?>
                        <img id="preview-cover" class="image-preview" alt="Preview">
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" name="save_tour" class="btn btn-success">
                            <i class="fas fa-save"></i> <?php echo $action == 'edit' ? 'Update Tour' : 'Add Tour'; ?>
                        </button>
                        <a href="?action=list" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <?php
        break;
        
    case 'media':
        // ===== MEDIA GALLERY VIEW =====
        // Get tour details
        $stmt = mysqli_prepare($con, "SELECT * FROM tours WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $tour_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $tour = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if (!$tour) {
            echo '<div class="alert alert-danger">Tour not found!</div>';
            break;
        }
        
        // Get all media for this tour
        $media_stmt = mysqli_prepare($con, "SELECT * FROM tour_media WHERE tour_id = ? ORDER BY display_order ASC, id ASC");
        mysqli_stmt_bind_param($media_stmt, "i", $tour_id);
        mysqli_stmt_execute($media_stmt);
        $media_result = mysqli_stmt_get_result($media_stmt);
        ?>
        
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-0">
                            <i class="fas fa-images"></i> Media Gallery: <?php echo htmlspecialchars($tour['title']); ?>
                        </h4>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="?action=list" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Tours
                        </a>
                        <a href="?action=edit&id=<?php echo $tour_id; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit Tour
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                
                <!-- Add Media Form -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Add Media</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="tour_id" value="<?php echo $tour_id; ?>">
                            
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Media Type *</label>
                                    <select name="media_type" class="form-control" required onchange="toggleMediaType(this.value)">
                                        <option value="image">Image</option>
                                        <option value="video">Video (YouTube)</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <!-- Image Upload Section -->
                                    <div id="image-upload-section">
                                        <label class="form-label">Image File *</label>
                                        <input type="file" name="media_file" class="form-control" accept="image/*">
                                    </div>
                                    
                                    <!-- Video URL Section -->
                                    <div id="video-url-section" style="display: none;">
                                        <label class="form-label">YouTube Video URL *</label>
                                        <input type="text" name="video_url" class="form-control" 
                                               placeholder="https://www.youtube.com/watch?v=...">
                                    </div>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Display Order</label>
                                    <input type="number" name="display_order" class="form-control" 
                                           value="<?php echo mysqli_num_rows($media_result) + 1; ?>" min="0">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Caption</label>
                                <input type="text" name="caption" class="form-control" 
                                       placeholder="Optional description for this media">
                            </div>
                            
                            <button type="submit" name="add_media" class="btn btn-success">
                                <i class="fas fa-upload"></i> Add Media
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Media List -->
                <h5 class="mb-3"><i class="fas fa-th"></i> Gallery Items</h5>
                
                <?php if (mysqli_num_rows($media_result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Preview</th>
                                    <th>Type</th>
                                    <th>Caption</th>
                                    <th>Order</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($media = mysqli_fetch_assoc($media_result)): ?>
                                    <tr>
                                        <td>
                                            <?php if ($media['media_type'] == 'image'): ?>
                                                <img src="../homepage/<?php echo htmlspecialchars($media['media_url']); ?>" 
                                                     alt="Media" class="media-thumbnail">
                                            <?php else: ?>
                                                <div class="media-thumbnail bg-dark text-white d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-video fa-2x"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $media['media_type'] == 'image' ? 'primary' : 'danger'; ?>">
                                                <?php echo ucfirst($media['media_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="media_id" value="<?php echo $media['id']; ?>">
                                                <input type="hidden" name="tour_id" value="<?php echo $tour_id; ?>">
                                                <input type="text" name="new_caption" class="form-control form-control-sm d-inline-block" 
                                                       style="width: 300px;" value="<?php echo htmlspecialchars($media['caption']); ?>">
                                                <button type="submit" name="update_caption" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="media_id" value="<?php echo $media['id']; ?>">
                                                <input type="hidden" name="tour_id" value="<?php echo $tour_id; ?>">
                                                <input type="number" name="new_order" class="form-control form-control-sm d-inline-block" 
                                                       style="width: 80px;" value="<?php echo $media['display_order']; ?>" min="0">
                                                <button type="submit" name="update_order" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <?php if ($media['media_type'] == 'video'): ?>
                                                <a href="<?php echo htmlspecialchars($media['media_url']); ?>" 
                                                   target="_blank" class="btn btn-sm btn-info">
                                                    <i class="fas fa-play"></i> View
                                                </a>
                                            <?php endif; ?>
                                            <a href="?action=delete_media&media_id=<?php echo $media['id']; ?>&id=<?php echo $tour_id; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this media?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-images"></i>
                        <h5>No media added yet</h5>
                        <p>Use the form above to add images or videos to this tour</p>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
        
        <?php
        mysqli_stmt_close($media_stmt);
        break;
        
    default:
        // ===== TOURS LIST VIEW =====
        $filter = $_GET['filter'] ?? 'all';
        
        $query = "SELECT * FROM tours";
        if ($filter == 'upcoming') {
            $query .= " WHERE tour_status = 'upcoming'";
        } elseif ($filter == 'past') {
            $query .= " WHERE tour_status = 'past'";
        }
        $query .= " ORDER BY year DESC, start_date DESC";
        
        $tours_result = mysqli_query($con, $query);
        ?>
        
        <!-- Action Bar -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <a href="?action=add" class="btn btn-success">
                            <i class="fas fa-plus"></i> Add New Tour
                        </a>
                    </div>
                    <div class="col-md-6">
                        <ul class="nav nav-pills filter-tabs justify-content-end">
                            <li class="nav-item">
                                <a class="nav-link <?php echo $filter == 'all' ? 'active' : ''; ?>" 
                                   href="?action=list&filter=all">
                                    All Tours
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $filter == 'upcoming' ? 'active' : ''; ?>" 
                                   href="?action=list&filter=upcoming">
                                    <i class="fas fa-calendar-plus"></i> Upcoming
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $filter == 'past' ? 'active' : ''; ?>" 
                                   href="?action=list&filter=past">
                                    <i class="fas fa-history"></i> Past Tours
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tours Table -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-list"></i> 
                    <?php 
                    echo $filter == 'upcoming' ? 'Upcoming Tours' : 
                         ($filter == 'past' ? 'Past Tours' : 'All Tours');
                    ?>
                    (<?php echo mysqli_num_rows($tours_result); ?>)
                </h4>
            </div>
            <div class="card-body">
                <?php if (mysqli_num_rows($tours_result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Cover</th>
                                    <th>Title</th>
                                    <th>Destination</th>
                                    <th>Dates</th>
                                    <th>Duration</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Year</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($tour = mysqli_fetch_assoc($tours_result)): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($tour['cover_image']) && file_exists('../homepage/' . $tour['cover_image'])): ?>
                                                <img src="../homepage/<?php echo htmlspecialchars($tour['cover_image']); ?>" 
                                                     alt="Cover" class="tour-thumbnail">
                                            <?php else: ?>
                                                <div class="tour-thumbnail bg-light d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($tour['title']); ?></strong>
                                        </td>
                                        <td>
                                            <i class="fas fa-map-marker-alt text-danger"></i>
                                            <?php echo htmlspecialchars($tour['destination']); ?>
                                        </td>
                                        <td>
                                            <small>
                                                <?php echo date('M d', strtotime($tour['start_date'])); ?> - 
                                                <?php echo date('M d, Y', strtotime($tour['end_date'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $tour['duration']; ?> days</span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $tour['tour_status'] == 'upcoming' ? 'tour-upcoming' : 'tour-past'; ?>">
                                                <?php echo ucfirst($tour['tour_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $tour['status'] == 1 ? 'status-active' : 'status-inactive'; ?>">
                                                <?php echo $tour['status'] == 1 ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo $tour['year']; ?></strong>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="?action=edit&id=<?php echo $tour['id']; ?>" 
                                                   class="btn btn-sm btn-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?action=media&id=<?php echo $tour['id']; ?>" 
                                                   class="btn btn-sm btn-info" title="Media Gallery">
                                                    <i class="fas fa-images"></i>
                                                </a>
                                                <a href="?action=delete&id=<?php echo $tour['id']; ?>" 
                                                   class="btn btn-sm btn-danger" title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this tour? All associated media will also be deleted.')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-plane-departure"></i>
                        <h5>No tours found</h5>
                        <p>
                            <?php 
                            if ($filter == 'upcoming') {
                                echo 'No upcoming tours available. Add your first upcoming tour!';
                            } elseif ($filter == 'past') {
                                echo 'No past tours recorded yet.';
                            } else {
                                echo 'Get started by adding your first tour!';
                            }
                            ?>
                        </p>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Tour
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php
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