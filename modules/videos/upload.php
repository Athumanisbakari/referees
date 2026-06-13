<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['Admin', 'Assessor', 'Referee']);

$pageTitle = 'Video Upload';
$pdo = getDBConnection();

$matches = $pdo->query("
    SELECT id, home_team, away_team, match_date FROM matches
    ORDER BY match_date DESC LIMIT 50
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '')) {
    if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        setFlash('error', 'Please select a valid video file.');
        redirect(APP_URL . '/modules/videos/upload.php');
    }

    if ($_FILES['video']['size'] > MAX_VIDEO_SIZE) {
        setFlash('error', 'Video file exceeds maximum size of 500MB.');
        redirect(APP_URL . '/modules/videos/upload.php');
    }

    $allowed = ['video/mp4', 'video/avi', 'video/mov', 'video/webm', 'video/x-msvideo'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['video']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed, true)) {
        setFlash('error', 'Invalid video format. Allowed: MP4, AVI, MOV, WebM.');
        redirect(APP_URL . '/modules/videos/upload.php');
    }

    if (!is_dir(VIDEO_PATH)) {
        mkdir(VIDEO_PATH, 0755, true);
    }

    $ext = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
    $filename = 'video_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $filepath = VIDEO_PATH . $filename;

    if (move_uploaded_file($_FILES['video']['tmp_name'], $filepath)) {
        $stmt = $pdo->prepare('
            INSERT INTO match_videos (match_id, uploaded_by, title, description, file_path, file_size, video_type)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            (int) $_POST['match_id'],
            $_SESSION['user_id'],
            $_POST['title'],
            $_POST['description'] ?? null,
            'uploads/videos/' . $filename,
            $_FILES['video']['size'],
            $_POST['video_type'],
        ]);

        logActivity('Upload', 'Videos', 'Uploaded video: ' . $_POST['title']);
        setFlash('success', 'Video uploaded successfully.');
    } else {
        setFlash('error', 'Failed to upload video.');
    }
    redirect(APP_URL . '/modules/videos/upload.php');
}

$videos = $pdo->query("
    SELECT mv.*, m.home_team, m.away_team, u.full_name AS uploader
    FROM match_videos mv
    JOIN matches m ON mv.match_id = m.id
    JOIN users u ON mv.uploaded_by = u.id
    ORDER BY mv.uploaded_at DESC
")->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-camera-video me-2"></i>Video Upload</h4>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Upload New Video</h5></div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Match *</label>
                        <select name="match_id" class="form-select" required>
                            <option value="">Select match</option>
                            <?php foreach ($matches as $m): ?>
                                <option value="<?= $m['id'] ?>">
                                    <?= sanitize($m['home_team']) ?> vs <?= sanitize($m['away_team']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Video Type</label>
                        <select name="video_type" class="form-select">
                            <option value="Full Match">Full Match</option>
                            <option value="Highlights">Highlights</option>
                            <option value="Incident">Incident</option>
                            <option value="Training">Training</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="upload-zone mb-3" id="uploadZone">
                        <i class="bi bi-cloud-upload"></i>
                        <p class="mb-1">Drag & drop video here or click to browse</p>
                        <small class="text-muted">MP4, AVI, MOV, WebM (max 500MB)</small>
                        <p id="fileName" class="text-primary mt-2 fw-bold"></p>
                    </div>
                    <input type="file" name="video" id="videoFile" accept="video/*" class="d-none" required>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-upload me-1"></i>Upload Video
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Uploaded Videos</h5></div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Title</th><th>Match</th><th>Type</th><th>Size</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php if (empty($videos)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No videos uploaded</td></tr>
                        <?php else: ?>
                            <?php foreach ($videos as $v): ?>
                                <tr>
                                    <td><?= sanitize($v['title']) ?></td>
                                    <td><?= sanitize($v['home_team']) ?> vs <?= sanitize($v['away_team']) ?></td>
                                    <td><?= sanitize($v['video_type']) ?></td>
                                    <td><?= round($v['file_size'] / 1048576, 1) ?> MB</td>
                                    <td><?= formatDate($v['uploaded_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
