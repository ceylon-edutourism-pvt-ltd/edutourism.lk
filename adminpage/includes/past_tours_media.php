<?php
/**
 * PAST TOURS MEDIA MANAGEMENT VIEW
 * Add, edit, delete, and reorder media items
 */

// Fetch tour data
$stmt = mysqli_prepare($con, "SELECT * FROM past_tours WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $tour_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$media_tour = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$media_tour) {
    echo '<div class="alert alert-danger">Tour not found!</div>';
    echo '<a href="?" class="btn btn-secondary">Back to List</a>';
    return;
}

// Fetch all media for this tour
$media_sql = "SELECT * FROM past_tour_media WHERE tour_id = ? ORDER BY display_order ASC, id ASC";
$stmt = mysqli_prepare($con, $media_sql);
mysqli_stmt_bind_param($stmt, "i", $tour_id);
mysqli_stmt_execute($stmt);
$media_result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

$media_count = mysqli_num_rows($media_result);
?>

<!-- Tour Info Banner -->
<div class="card mb-3" style="border-left: 4px solid #0d6efd;">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-1"><?php echo htmlspecialchars($media_tour['title']); ?></h5>
                <p class="text-muted mb-0">
                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($media_tour['destination']); ?> | 
                    <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($media_tour['start_date'])); ?> - <?php echo date('M d, Y', strtotime($media_tour['end_date'])); ?>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <a href="?action=edit&id=<?php echo $tour_id; ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit"></i> Edit Tour Info
                </a>
                <a href="?" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Add Media Form -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Add New Media</h5>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="tour_id" value="<?php echo $tour_id; ?>">
            
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Media Type <span class="text-danger">*</span></label>
                    <select class="form-select" name="media_type" id="media_type" required onchange="toggleMediaType(this.value)">
                        <option value="image">Image</option>
                        <option value="video">Video</option>
                    </select>
                </div>
                
                <div class="col-md-2 mb-3">
                    <label class="form-label">Display Order</label>
                    <input type="number" 
                           class="form-control" 
                           name="display_order" 
                           value="<?php echo $media_count + 1; ?>" 
                           min="0">
                </div>
                
                <div class="col-md-7 mb-3">
                    <label class="form-label">Caption (English only)</label>
                    <input type="text" 
                           class="form-control" 
                           name="caption" 
                           maxlength="500"
                           placeholder="Describe this photo or video...">
                </div>
            </div>
            
            <!-- Image Upload Section -->
            <div id="image-upload-section">
                <label class="form-label">Upload Image File <span class="text-danger">*</span></label>
                <input type="file" 
                       class="form-control mb-2" 
                       name="media_file" 
                       accept="image/*">
                <div class="upload-info">
                    <i class="fas fa-info-circle"></i> Images stored in: <code>/uploads/past_tours/gallery/</code> | 
                    Allowed: JPG, PNG, GIF, WEBP | Max: 5MB
                </div>
            </div>
            
            <!-- Video URL Section -->
            <div id="video-url-section" style="display:none;">
                <label class="form-label">Video URL (YouTube or MP4) <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control mb-2" 
                       name="video_url" 
                       placeholder="https://www.youtube.com/watch?v=VIDEO_ID or path/to/video.mp4">
                <div class="upload-info">
                    <i class="fas fa-info-circle"></i> Paste YouTube link (will auto-convert to embed) or direct MP4 video URL
                </div>
            </div>
            
            <div class="text-end mt-3">
                <button type="submit" name="add_media" class="btn btn-success">
                    <i class="fas fa-upload"></i> Add Media
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Media Gallery -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-images"></i> Media Gallery 
            <span class="badge bg-primary"><?php echo $media_count; ?> items</span>
        </h5>
    </div>
    <div class="card-body">
        
        <?php if ($media_count > 0): ?>
        
        <?php while ($media = mysqli_fetch_assoc($media_result)): ?>
        <div class="card mb-3" style="border-left: 4px solid <?php echo $media['media_type'] == 'image' ? '#0d6efd' : '#dc3545'; ?>;">
            <div class="card-body">
                <div class="row align-items-center">
                    
                    <!-- Media Preview -->
                    <div class="col-md-2">
                        <?php if ($media['media_type'] == 'image'): ?>
                            <?php if (file_exists('../homepage/' . $media['media_url'])): ?>
                                <img src="../homepage/<?php echo htmlspecialchars($media['media_url']); ?>" 
                                     class="media-thumbnail" 
                                     alt="Media">
                            <?php else: ?>
                                <div class="media-thumbnail bg-secondary d-flex align-items-center justify-content-center text-white">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="media-thumbnail bg-dark d-flex align-items-center justify-content-center text-white">
                                <i class="fas fa-play-circle fa-2x"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Media Info -->
                    <div class="col-md-6">
                        <div class="mb-2">
                            <span class="badge bg-<?php echo $media['media_type'] == 'image' ? 'primary' : 'danger'; ?>">
                                <i class="fas fa-<?php echo $media['media_type'] == 'image' ? 'image' : 'video'; ?>"></i> 
                                <?php echo ucfirst($media['media_type']); ?>
                            </span>
                            <span class="badge bg-secondary">Order: <?php echo $media['display_order']; ?></span>
                        </div>
                        
                        <!-- Edit Caption Form -->
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="media_id" value="<?php echo $media['id']; ?>">
                            <input type="hidden" name="tour_id" value="<?php echo $tour_id; ?>">
                            <input type="text" 
                                   class="form-control form-control-sm" 
                                   name="new_caption" 
                                   value="<?php echo htmlspecialchars($media['caption']); ?>"
                                   placeholder="Add caption...">
                            <button type="submit" name="update_caption" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-save"></i>
                            </button>
                        </form>
                        
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-link"></i> <?php echo htmlspecialchars($media['media_url']); ?>
                        </small>
                    </div>
                    
                    <!-- Update Order -->
                    <div class="col-md-2">
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="media_id" value="<?php echo $media['id']; ?>">
                            <input type="hidden" name="tour_id" value="<?php echo $tour_id; ?>">
                            <input type="number" 
                                   class="form-control form-control-sm" 
                                   name="new_order" 
                                   value="<?php echo $media['display_order']; ?>" 
                                   min="0" 
                                   style="width: 70px;">
                            <button type="submit" name="update_order" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-sort"></i>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Delete -->
                    <div class="col-md-2 text-end">
                        <a href="?action=delete_media&media_id=<?php echo $media['id']; ?>&id=<?php echo $tour_id; ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Delete this media item?');">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                    
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        
        <?php else: ?>
        
        <div class="text-center py-5">
            <i class="fas fa-images fa-4x text-muted mb-3"></i>
            <h5>No Media Yet</h5>
            <p class="text-muted">Add photos and videos from this tour using the form above</p>
        </div>
        
        <?php endif; ?>
        
    </div>
</div>
