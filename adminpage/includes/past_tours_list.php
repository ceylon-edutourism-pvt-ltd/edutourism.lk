<?php
/**
 * PAST TOURS LIST VIEW
 * Displays all past tours in a table with actions
 */

// Fetch all tours
$tours_sql = "SELECT pt.*, 
    (SELECT COUNT(*) FROM past_tour_media WHERE tour_id = pt.id) as media_count,
    (SELECT COUNT(*) FROM past_tour_media WHERE tour_id = pt.id AND media_type = 'image') as image_count,
    (SELECT COUNT(*) FROM past_tour_media WHERE tour_id = pt.id AND media_type = 'video') as video_count
    FROM past_tours pt 
    ORDER BY pt.start_date DESC";
$tours_result = mysqli_query($con, $tours_sql);

// Calculate statistics
$total_tours = mysqli_num_rows($tours_result);
$active_tours = 0;
$total_participants = 0;
$total_media = 0;

mysqli_data_seek($tours_result, 0);
while ($row = mysqli_fetch_assoc($tours_result)) {
    if ($row['status'] == 1) $active_tours++;
    $total_participants += $row['participants'];
    $total_media += $row['media_count'];
}
mysqli_data_seek($tours_result, 0);
?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-globe fa-2x text-primary mb-2"></i>
                <h3><?php echo $total_tours; ?></h3>
                <p class="text-muted mb-0">Total Tours</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h3><?php echo $active_tours; ?></h3>
                <p class="text-muted mb-0">Active Tours</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-users fa-2x text-info mb-2"></i>
                <h3><?php echo $total_participants; ?></h3>
                <p class="text-muted mb-0">Participants</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-images fa-2x text-warning mb-2"></i>
                <h3><?php echo $total_media; ?></h3>
                <p class="text-muted mb-0">Media Items</p>
            </div>
        </div>
    </div>
</div>

<!-- Tours Table -->
<div class="card">
    <div class="card-header bg-white">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="mb-0"><i class="fas fa-list"></i> All Past Tours</h4>
            </div>
            <div class="col-md-6 text-end">
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Tour
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        
        <?php if (mysqli_num_rows($tours_result) > 0): ?>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;">Cover</th>
                        <th>Tour Title</th>
                        <th>Destination</th>
                        <th>Date Range</th>
                        <th style="width: 100px;">Participants</th>
                        <th style="width: 120px;">Media</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 280px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($tour = mysqli_fetch_assoc($tours_result)): ?>
                    <tr>
                        <td>
                            <?php if (!empty($tour['cover_media']) && file_exists('../homepage/' . $tour['cover_media'])): ?>
                                <img src="../homepage/<?php echo htmlspecialchars($tour['cover_media']); ?>" 
                                     class="tour-thumbnail" 
                                     alt="Cover">
                            <?php else: ?>
                                <div class="tour-thumbnail bg-secondary d-flex align-items-center justify-content-center text-white">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($tour['title']); ?></strong>
                            <br><small class="text-muted">ID: <?php echo $tour['id']; ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($tour['destination']); ?></td>
                        <td>
                            <small>
                                <?php echo date('M d, Y', strtotime($tour['start_date'])); ?><br>
                                to<br>
                                <?php echo date('M d, Y', strtotime($tour['end_date'])); ?>
                            </small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info"><?php echo $tour['participants']; ?></span>
                        </td>
                        <td>
                            <small>
                                <i class="fas fa-images text-primary"></i> <?php echo $tour['image_count']; ?><br>
                                <i class="fas fa-video text-danger"></i> <?php echo $tour['video_count']; ?>
                            </small>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $tour['status'] == 1 ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $tour['status'] == 1 ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="?action=edit&id=<?php echo $tour['id']; ?>" 
                               class="btn btn-sm btn-primary" 
                               title="Edit Tour">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?action=media&id=<?php echo $tour['id']; ?>" 
                               class="btn btn-sm btn-success" 
                               title="Manage Media">
                                <i class="fas fa-images"></i>
                            </a>
                            <a href="../homepage/past_tour_details.php?tour_id=<?php echo $tour['id']; ?>" 
                               class="btn btn-sm btn-info" 
                               target="_blank"
                               title="Preview">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="?action=delete&id=<?php echo $tour['id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Delete this tour and all its media? This cannot be undone.');"
                               title="Delete Tour">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <?php else: ?>
        
        <div class="text-center py-5">
            <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
            <h4>No Past Tours Yet</h4>
            <p class="text-muted">Start by adding your first completed tour</p>
            <a href="?action=add" class="btn btn-primary mt-3">
                <i class="fas fa-plus"></i> Add First Tour
            </a>
        </div>
        
        <?php endif; ?>
    </div>
</div>
