<?php
session_start();

// Simple login check
if (!isset($_SESSION['user'])) {
    header('Location: adminmain.php');
    exit();
}

include('../homepage/db.php');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_hero'])) {
        // Add new hero
        $type = (int)$_POST['type'];
        $title = mysqli_real_escape_string($con, $_POST['title']);
        $description = mysqli_real_escape_string($con, $_POST['description']);
        $button_text = mysqli_real_escape_string($con, $_POST['button_text']);
        $button_url = mysqli_real_escape_string($con, $_POST['button_url']);
        $button_text_2 = mysqli_real_escape_string($con, $_POST['button_text_2']);
        $button_url_2 = mysqli_real_escape_string($con, $_POST['button_url_2']);
        $media_url = mysqli_real_escape_string($con, $_POST['media_url']);
        $media_type = mysqli_real_escape_string($con, $_POST['media_type']);
        $text_bg_enabled = isset($_POST['text_bg_enabled']) ? 1 : 0;
        $text_bg_image = mysqli_real_escape_string($con, $_POST['text_bg_image']);
        $text_bg_color = mysqli_real_escape_string($con, $_POST['text_bg_color']);
        $is_active = mysqli_real_escape_string($con, $_POST['is_active']);
        $display_order = (int)$_POST['display_order'];
        
        $sql = "INSERT INTO heroes (type, title, description, button_text, button_url, button_text_2, button_url_2, media_url, media_type, text_bg_image, text_bg_enabled, text_bg_color, is_active, display_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "isssssssssissi", $type, $title, $description, $button_text, $button_url, $button_text_2, $button_url_2, $media_url, $media_type, $text_bg_image, $text_bg_enabled, $text_bg_color, $is_active, $display_order);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Hero slide added successfully!";
        } else {
            $error_message = "Error adding hero: " . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);
    }
    
    if (isset($_POST['update_hero'])) {
        // Update existing hero
        $hero_id = (int)$_POST['hero_id'];
        $type = (int)$_POST['type'];
        $title = mysqli_real_escape_string($con, $_POST['title']);
        $description = mysqli_real_escape_string($con, $_POST['description']);
        $button_text = mysqli_real_escape_string($con, $_POST['button_text']);
        $button_url = mysqli_real_escape_string($con, $_POST['button_url']);
        $button_text_2 = mysqli_real_escape_string($con, $_POST['button_text_2']);
        $button_url_2 = mysqli_real_escape_string($con, $_POST['button_url_2']);
        $media_url = mysqli_real_escape_string($con, $_POST['media_url']);
        $media_type = mysqli_real_escape_string($con, $_POST['media_type']);
        $text_bg_enabled = isset($_POST['text_bg_enabled']) ? 1 : 0;
        $text_bg_image = mysqli_real_escape_string($con, $_POST['text_bg_image']);
        $text_bg_color = mysqli_real_escape_string($con, $_POST['text_bg_color']);
        $is_active = mysqli_real_escape_string($con, $_POST['is_active']);
        $display_order = (int)$_POST['display_order'];
        
        $sql = "UPDATE heroes SET type = ?, title = ?, description = ?, button_text = ?, button_url = ?, button_text_2 = ?, button_url_2 = ?, media_url = ?, media_type = ?, text_bg_image = ?, text_bg_enabled = ?, text_bg_color = ?, is_active = ?, display_order = ? WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "isssssssssissii", $type, $title, $description, $button_text, $button_url, $button_text_2, $button_url_2, $media_url, $media_type, $text_bg_image, $text_bg_enabled, $text_bg_color, $is_active, $display_order, $hero_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Hero slide updated successfully!";
        } else {
            $error_message = "Error updating hero: " . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);
    }
    
    if (isset($_POST['delete_hero'])) {
        // Delete hero
        $hero_id = (int)$_POST['hero_id'];
        
        $sql = "DELETE FROM heroes WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $hero_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Hero slide deleted successfully!";
        } else {
            $error_message = "Error deleting hero: " . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch all heroes
$heroes_sql = "SELECT * FROM heroes ORDER BY display_order ASC, id DESC";
$heroes_result = mysqli_query($con, $heroes_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Hero Section Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            color: black;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .hero-preview {
            max-width: 150px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }
        .action-buttons {
            white-space: nowrap;
        }
        .table-responsive {
            font-size: 14px;
        }
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .status-active {
            color: #28a745;
        }
        .status-inactive {
            color: #dc3545;
        }
        .type-badge {
            font-size: 11px;
            padding: 4px 8px;
        }
        .bg-image-input {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .hero-type-info {
            background: #e7f3ff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><i class="fas fa-images"></i> Hero Section Management</h1>
                </div>
                <div class="col-md-6 text-end">
                    <a href="adminmain.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Main Admin</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-12">
                <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addHeroModal">
                    <i class="fas fa-plus"></i> Add New Hero Slide
                </button>
                <a href="../homepage/hero_section.php" target="_blank" class="btn btn-info btn-lg">
                    <i class="fas fa-eye"></i> Preview Hero Section
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3><i class="fas fa-list"></i> All Hero Slides</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Order</th>
                                <th>Preview</th>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Buttons</th>
                                <th>Media</th>
                                <th>Text BG</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($hero = mysqli_fetch_assoc($heroes_result)): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">#<?php echo $hero['display_order']; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($hero['media_type'] == 'image'): ?>
                                            <img src="<?php echo htmlspecialchars($hero['media_url']); ?>" alt="Hero Preview" class="hero-preview">
                                        <?php else: ?>
                                            <div class="bg-dark text-white p-2 hero-preview d-flex align-items-center justify-content-center">
                                                <i class="fas fa-video fa-2x"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $type_labels = [
                                            1 => 'Left Text + Right Video',
                                            2 => 'Center Overlay',
                                            3 => 'Left Image + Right Text'
                                        ];
                                        $type_colors = [1 => 'primary', 2 => 'info', 3 => 'warning'];
                                        ?>
                                        <span class="badge bg-<?php echo $type_colors[$hero['type']]; ?> type-badge">
                                            Type <?php echo $hero['type']; ?>
                                        </span><br>
                                        <small class="text-muted"><?php echo $type_labels[$hero['type']]; ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars(substr($hero['title'], 0, 30)); ?></strong>
                                        <?php echo strlen($hero['title']) > 30 ? '...' : ''; ?>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars(substr($hero['description'], 0, 40)); ?></small>
                                        <?php echo strlen($hero['description']) > 40 ? '...' : ''; ?>
                                    </td>
                                    <td>
                                        <?php if ($hero['button_text']): ?>
                                            <span class="badge bg-success">Btn 1: <?php echo htmlspecialchars($hero['button_text']); ?></span><br>
                                        <?php endif; ?>
                                        <?php if ($hero['button_text_2']): ?>
                                            <span class="badge bg-success">Btn 2: <?php echo htmlspecialchars($hero['button_text_2']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $hero['media_type'] == 'video' ? 'danger' : 'primary'; ?>">
                                            <?php echo ucfirst($hero['media_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($hero['text_bg_enabled']): ?>
                                            <span class="badge bg-info">Enabled</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Disabled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="<?php echo $hero['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                            <i class="fas fa-circle"></i> <?php echo $hero['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-primary" onclick="editHero(<?php echo htmlspecialchars(json_encode($hero)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteHero(<?php echo $hero['id']; ?>, '<?php echo htmlspecialchars($hero['title']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Hero Modal -->
    <div class="modal fade" id="addHeroModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fas fa-plus"></i> Add New Hero Slide</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="hero-type-info">
                            <strong>Hero Types:</strong><br>
                            <strong>Type 1:</strong> Left Text + Right Video/Image<br>
                            <strong>Type 2:</strong> Center Overlay (supports 2 buttons)<br>
                            <strong>Type 3:</strong> Left Video/Image + Right Text
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Hero Type *</label>
                                    <select name="type" id="add_type" class="form-control" required onchange="toggleTypeFields('add')">
                                        <option value="1">Type 1: Left Text + Right Media</option>
                                        <option value="2">Type 2: Center Overlay</option>
                                        <option value="3">Type 3: Left Media + Right Text</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Display Order *</label>
                                    <input type="number" name="display_order" class="form-control" value="0" min="0" required>
                                    <small class="text-muted">Lower numbers appear first</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Status *</label>
                                    <select name="is_active" class="form-control" required>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Title *</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Description *</label>
                                    <textarea name="description" class="form-control" rows="2" required></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Button 1 Text</label>
                                    <input type="text" name="button_text" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Button 1 URL</label>
                                    <input type="text" name="button_url" class="form-control" placeholder="#link">
                                </div>
                            </div>
                        </div>

                        <div class="row" id="add_button2_row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Button 2 Text <small class="text-muted">(Type 2 only)</small></label>
                                    <input type="text" name="button_text_2" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Button 2 URL <small class="text-muted">(Type 2 only)</small></label>
                                    <input type="text" name="button_url_2" class="form-control" placeholder="#link">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="form-label">Media URL (Image or Video) *</label>
                                    <input type="text" name="media_url" class="form-control" required placeholder="https://example.com/image.jpg">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Media Type *</label>
                                    <select name="media_type" class="form-control" required>
                                        <option value="image">Image</option>
                                        <option value="video">Video</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="bg-image-input" id="add_text_bg_section">
                            <h6>Text Area Background (Type 1 & 3 only)</h6>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="text_bg_enabled" id="add_text_bg_enabled" onchange="toggleTextBg('add')">
                                <label class="form-check-label" for="add_text_bg_enabled">
                                    Enable Text Background
                                </label>
                            </div>
                            <div id="add_text_bg_fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Background Image URL</label>
                                            <input type="text" name="text_bg_image" class="form-control" placeholder="https://example.com/bg.jpg">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Background Color/Overlay</label>
                                            <input type="text" name="text_bg_color" class="form-control" value="rgba(0, 0, 0, 0.7)" placeholder="rgba(0, 0, 0, 0.7)">
                                            <small class="text-muted">Use rgba format for transparency</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_hero" class="btn btn-success">Add Hero Slide</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Hero Modal -->
    <div class="modal fade" id="editHeroModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="hero_id" id="edit_hero_id">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Hero Slide</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="hero-type-info">
                            <strong>Hero Types:</strong><br>
                            <strong>Type 1:</strong> Left Text + Right Video/Image<br>
                            <strong>Type 2:</strong> Center Overlay (supports 2 buttons)<br>
                            <strong>Type 3:</strong> Left Video/Image + Right Text
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Hero Type *</label>
                                    <select name="type" id="edit_type" class="form-control" required onchange="toggleTypeFields('edit')">
                                        <option value="1">Type 1: Left Text + Right Media</option>
                                        <option value="2">Type 2: Center Overlay</option>
                                        <option value="3">Type 3: Left Media + Right Text</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Display Order *</label>
                                    <input type="number" name="display_order" id="edit_display_order" class="form-control" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Status *</label>
                                    <select name="is_active" id="edit_is_active" class="form-control" required>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Title *</label>
                                    <input type="text" name="title" id="edit_title" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Description *</label>
                                    <textarea name="description" id="edit_description" class="form-control" rows="2" required></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Button 1 Text</label>
                                    <input type="text" name="button_text" id="edit_button_text" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Button 1 URL</label>
                                    <input type="text" name="button_url" id="edit_button_url" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row" id="edit_button2_row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Button 2 Text <small class="text-muted">(Type 2 only)</small></label>
                                    <input type="text" name="button_text_2" id="edit_button_text_2" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Button 2 URL <small class="text-muted">(Type 2 only)</small></label>
                                    <input type="text" name="button_url_2" id="edit_button_url_2" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="form-label">Media URL (Image or Video) *</label>
                                    <input type="text" name="media_url" id="edit_media_url" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Media Type *</label>
                                    <select name="media_type" id="edit_media_type" class="form-control" required>
                                        <option value="image">Image</option>
                                        <option value="video">Video</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="bg-image-input" id="edit_text_bg_section">
                            <h6>Text Area Background (Type 1 & 3 only)</h6>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="text_bg_enabled" id="edit_text_bg_enabled" onchange="toggleTextBg('edit')">
                                <label class="form-check-label" for="edit_text_bg_enabled">
                                    Enable Text Background
                                </label>
                            </div>
                            <div id="edit_text_bg_fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Background Image URL</label>
                                            <input type="text" name="text_bg_image" id="edit_text_bg_image" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Background Color/Overlay</label>
                                            <input type="text" name="text_bg_color" id="edit_text_bg_color" class="form-control" value="rgba(0, 0, 0, 0.7)">
                                            <small class="text-muted">Use rgba format for transparency</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_hero" class="btn btn-primary">Update Hero Slide</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Hero Modal -->
    <div class="modal fade" id="deleteHeroModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="hero_id" id="delete_hero_id">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Delete Hero Slide</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this hero slide?</p>
                        <p><strong id="delete_hero_title"></strong></p>
                        <p class="text-danger"><small>This action cannot be undone.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_hero" class="btn btn-danger">Delete Hero Slide</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleTextBg(mode) {
            const checkbox = document.getElementById(mode + '_text_bg_enabled');
            const fields = document.getElementById(mode + '_text_bg_fields');
            fields.style.display = checkbox.checked ? 'block' : 'none';
        }

        function toggleTypeFields(mode) {
            const type = document.getElementById(mode + '_type').value;
            const button2Row = document.getElementById(mode + '_button2_row');
            const textBgSection = document.getElementById(mode + '_text_bg_section');
            
            // Type 2 shows button 2, Type 1 & 3 show text background
            if (type == '2') {
                button2Row.style.display = 'flex';
                textBgSection.style.display = 'none';
            } else {
                button2Row.style.display = 'none';
                textBgSection.style.display = 'block';
            }
        }

        function editHero(hero) {
            document.getElementById('edit_hero_id').value = hero.id;
            document.getElementById('edit_type').value = hero.type;
            document.getElementById('edit_title').value = hero.title;
            document.getElementById('edit_description').value = hero.description;
            document.getElementById('edit_button_text').value = hero.button_text || '';
            document.getElementById('edit_button_url').value = hero.button_url || '';
            document.getElementById('edit_button_text_2').value = hero.button_text_2 || '';
            document.getElementById('edit_button_url_2').value = hero.button_url_2 || '';
            document.getElementById('edit_media_url').value = hero.media_url;
            document.getElementById('edit_media_type').value = hero.media_type;
            document.getElementById('edit_text_bg_image').value = hero.text_bg_image || '';
            document.getElementById('edit_text_bg_color').value = hero.text_bg_color || 'rgba(0, 0, 0, 0.7)';
            document.getElementById('edit_is_active').value = hero.is_active;
            document.getElementById('edit_display_order').value = hero.display_order;
            
            // Set checkbox
            const checkbox = document.getElementById('edit_text_bg_enabled');
            checkbox.checked = hero.text_bg_enabled == 1;
            toggleTextBg('edit');
            
            // Toggle type-specific fields
            toggleTypeFields('edit');
            
            var editModal = new bootstrap.Modal(document.getElementById('editHeroModal'));
            editModal.show();
        }

        function deleteHero(id, title) {
            document.getElementById('delete_hero_id').value = id;
            document.getElementById('delete_hero_title').textContent = title;
            
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteHeroModal'));
            deleteModal.show();
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleTypeFields('add');
        });
    </script>
</body>
</html>