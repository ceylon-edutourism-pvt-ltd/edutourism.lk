<?php
/**
 * PAST TOURS FORM VIEW
 * Add or Edit tour form (ENGLISH ONLY)
 */

$edit_mode = ($action == 'edit' && $tour_id > 0);
$form_tour = null;

if ($edit_mode) {
    // Fetch tour data for editing
    $stmt = mysqli_prepare($con, "SELECT * FROM past_tours WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $tour_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $form_tour = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$form_tour) {
        echo '<div class="alert alert-danger">Tour not found!</div>';
        echo '<a href="?" class="btn btn-secondary">Back to List</a>';
        return;
    }
}
?>

<div class="card">
    <div class="card-header bg-white">
        <h4 class="mb-0">
            <i class="fas fa-<?php echo $edit_mode ? 'edit' : 'plus-circle'; ?>"></i>
            <?php echo $edit_mode ? 'Edit Tour' : 'Add New Tour'; ?>
        </h4>
    </div>
    <div class="card-body">
        
        <form method="POST" enctype="multipart/form-data">
            <?php if ($edit_mode): ?>
                <input type="hidden" name="tour_id" value="<?php echo $tour_id; ?>">
            <?php endif; ?>
            
            <!-- Basic Information -->
            <div class="mb-4">
                <h5 class="border-bottom pb-2"><i class="fas fa-info-circle text-primary"></i> Basic Information</h5>
                
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Tour Title <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               name="title" 
                               maxlength="255"
                               value="<?php echo $edit_mode ? htmlspecialchars($form_tour['title']) : ''; ?>"
                               placeholder="e.g., Japan Cultural & Educational Tour 2025"
                               required>
                        <small class="text-muted">Enter tour title in English only</small>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" name="status" required>
                            <option value="1" <?php echo ($edit_mode && $form_tour['status'] == 1) ? 'selected' : ''; ?>>Active (Visible)</option>
                            <option value="0" <?php echo ($edit_mode && $form_tour['status'] == 0) ? 'selected' : ''; ?>>Inactive (Hidden)</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Destination <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control" 
                           name="destination" 
                           maxlength="255"
                           value="<?php echo $edit_mode ? htmlspecialchars($form_tour['destination']) : ''; ?>"
                           placeholder="e.g., Tokyo & Kyoto, Japan"
                           required>
                    <small class="text-muted">City, country, or region</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Tour Summary <span class="text-danger">*</span></label>
                    <textarea class="form-control" 
                              name="summary" 
                              rows="5" 
                              maxlength="1000"
                              placeholder="Describe the tour experience, highlights, and memorable moments..."
                              required><?php echo $edit_mode ? htmlspecialchars($form_tour['summary']) : ''; ?></textarea>
                    <small class="text-muted">English only - Maximum 1000 characters</small>
                </div>
            </div>
            
            <!-- Dates & Participants -->
            <div class="mb-4">
                <h5 class="border-bottom pb-2"><i class="fas fa-calendar-alt text-primary"></i> Dates & Participants</h5>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="form-control" 
                               name="start_date" 
                               value="<?php echo $edit_mode ? $form_tour['start_date'] : ''; ?>"
                               required>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="form-control" 
                               name="end_date" 
                               value="<?php echo $edit_mode ? $form_tour['end_date'] : ''; ?>"
                               required>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Number of Participants</label>
                        <input type="number" 
                               class="form-control" 
                               name="participants" 
                               min="0" 
                               value="<?php echo $edit_mode ? $form_tour['participants'] : '0'; ?>"
                               placeholder="e.g., 42">
                    </div>
                </div>
            </div>
            
            <!-- Cover Image -->
            <div class="mb-4">
                <h5 class="border-bottom pb-2"><i class="fas fa-image text-primary"></i> Cover Image</h5>
                
                <?php if ($edit_mode && !empty($form_tour['cover_media'])): ?>
                    <div class="mb-3">
                        <label class="form-label">Current Cover Image</label><br>
                        <?php if (file_exists('../homepage/' . $form_tour['cover_media'])): ?>
                            <img src="../homepage/<?php echo htmlspecialchars($form_tour['cover_media']); ?>" 
                                 style="max-width: 300px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;"
                                 alt="Current cover">
                            <br><small class="text-muted"><?php echo htmlspecialchars($form_tour['cover_media']); ?></small>
                        <?php else: ?>
                            <p class="text-danger">Image file not found: <?php echo htmlspecialchars($form_tour['cover_media']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <label class="form-label">
                        <?php echo $edit_mode ? 'Upload New Cover Image (optional)' : 'Upload Cover Image'; ?>
                    </label>
                    <input type="file" 
                           class="form-control" 
                           name="cover_image" 
                           accept="image/*"
                           onchange="previewImage(this, 'cover-preview')">
                    <img id="cover-preview" style="display:none; max-width: 300px; margin-top: 10px; border-radius: 8px;">
                </div>
                
                <div class="upload-info">
                    <i class="fas fa-info-circle"></i> <strong>Image Storage:</strong> 
                    Images will be uploaded to <code>/uploads/past_tours/covers/</code><br>
                    <strong>Allowed:</strong> JPG, PNG, GIF, WEBP | <strong>Max Size:</strong> 5MB
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="text-end">
                <a href="?" class="btn btn-secondary me-2">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" name="save_tour" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo $edit_mode ? 'Update Tour' : 'Save Tour'; ?>
                </button>
            </div>
        </form>
        
    </div>
</div>

<?php if ($edit_mode): ?>
<div class="card mt-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-0"><i class="fas fa-images text-success"></i> Manage Tour Media</h5>
                <p class="text-muted mb-0">Add photos and videos to this tour's gallery</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="?action=media&id=<?php echo $tour_id; ?>" class="btn btn-success">
                    <i class="fas fa-images"></i> Go to Media Gallery
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
