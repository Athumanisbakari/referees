<?php
require_once __DIR__ . '/../../includes/functions.php';
requireRole(['Admin', 'Assigner', 'Referee']);

$pageTitle = 'Venues & Map Navigation';
$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? '') && $_SESSION['role_name'] !== 'Referee') {
    $stmt = $pdo->prepare('
        INSERT INTO venues (name, address, city, latitude, longitude, capacity, contact_person, contact_phone)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $_POST['name'], $_POST['address'], $_POST['city'] ?? 'Ilala',
        $_POST['latitude'] ?: null, $_POST['longitude'] ?: null,
        $_POST['capacity'] ?: null, $_POST['contact_person'] ?? null, $_POST['contact_phone'] ?? null,
    ]);
    setFlash('success', 'Venue added.');
    redirect(APP_URL . '/modules/venues/index.php');
}

$venues = $pdo->query('SELECT * FROM venues WHERE is_active = 1 ORDER BY name')->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h4><i class="bi bi-geo-alt me-2"></i>Venues & Map Navigation</h4>
    <?php if ($_SESSION['role_name'] !== 'Referee'): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVenueModal">
            <i class="bi bi-plus-lg me-1"></i>Add Venue
        </button>
    <?php endif; ?>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Venue Map</h5></div>
            <div class="card-body p-0">
                <div id="map" class="venue-map"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Venue List</h5></div>
            <div class="list-group list-group-flush" id="venueList">
                <?php foreach ($venues as $venue): ?>
                    <a href="#" class="list-group-item list-group-item-action venue-item"
                       data-lat="<?= $venue['latitude'] ?>" data-lng="<?= $venue['longitude'] ?>"
                       data-name="<?= sanitize($venue['name']) ?>">
                        <div class="d-flex justify-content-between">
                            <strong><?= sanitize($venue['name']) ?></strong>
                            <?php if ($venue['latitude']): ?>
                                <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $venue['latitude'] ?>,<?= $venue['longitude'] ?>"
                                   target="_blank" class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation()">
                                    <i class="bi bi-signpost-2"></i> Navigate
                                </a>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted"><?= sanitize($venue['address']) ?></small>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($_SESSION['role_name'] !== 'Referee'): ?>
<div class="modal fade" id="addVenueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?= csrfField() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Add Venue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" required></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Latitude</label>
                            <input type="text" name="latitude" class="form-control" placeholder="-6.8160">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Longitude</label>
                            <input type="text" name="longitude" class="form-control" placeholder="39.2803">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">Capacity</label>
                        <input type="number" name="capacity" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Venue</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$extraScripts = '<script>
document.addEventListener("DOMContentLoaded", function() {
    var map = initMap("map", -6.8160, 39.2803, 12);
    var markers = [];
    document.querySelectorAll(".venue-item").forEach(function(item) {
        var lat = parseFloat(item.dataset.lat);
        var lng = parseFloat(item.dataset.lng);
        if (lat && lng) {
            var m = addMarker(map, lat, lng, item.dataset.name);
            markers.push(m);
            item.addEventListener("click", function(e) {
                e.preventDefault();
                map.setView([lat, lng], 15);
                m.openPopup();
            });
        }
    });
});
</script>';
require_once __DIR__ . '/../../includes/footer.php';
?>
