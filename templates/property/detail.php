<div class="row">
    <div class="col-lg-8 mx-auto">
        <a href="javascript:history.back()" class="btn btn-outline-secondary mb-3">&larr; Back</a>

        <div class="card shadow-sm">
            <?php if (!empty($property['image_url'])): ?>
            <img src="<?= htmlspecialchars($property['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($property['displayable_address']) ?>" style="max-height: 400px; object-fit: cover;">
            <?php endif; ?>
            <div class="card-body">
                <h2 class="card-title"><?= htmlspecialchars($property['displayable_address']) ?></h2>
                <p class="text-muted mb-1"><?= htmlspecialchars($property['town']) ?>, <?= htmlspecialchars($property['county']) ?>, <?= htmlspecialchars($property['country']) ?></p>

                <div class="d-flex justify-content-between align-items-center my-3">
                    <span class="fs-3 fw-bold text-primary">£<?= number_format((float)$property['price']) ?></span>
                    <span class="badge fs-6 bg-<?= $property['for_sale'] == 1 ? 'success' : 'warning' ?>">
                        <?= $property['for_sale'] == 1 ? 'For Sale' : 'For Rent' ?>
                    </span>
                </div>

                <hr>

                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="fs-4 fw-bold"><?= (int)$property['num_bedrooms'] ?></div>
                        <div class="text-muted small">Bedrooms</div>
                    </div>
                    <div class="col-4">
                        <div class="fs-4 fw-bold"><?= (int)$property['num_bathrooms'] ?></div>
                        <div class="text-muted small">Bathrooms</div>
                    </div>
                    <div class="col-4">
                        <div class="fs-4 fw-bold"><?= htmlspecialchars($property['property_type'] ?? 'N/A') ?></div>
                        <div class="text-muted small">Type</div>
                    </div>
                </div>

                <?php if (!empty($property['description'])): ?>
                <hr>
                <h5>Description</h5>
                <p class="card-text"><?= nl2br(htmlspecialchars($property['description'])) ?></p>
                <?php endif; ?>

                <?php if (!empty($property['latitude']) && !empty($property['longitude'])): ?>
                <hr>
                <h5>Location</h5>
                <p class="text-muted small">Lat: <?= $property['latitude'] ?>, Lng: <?= $property['longitude'] ?></p>
                <div id="detailMap" style="height: 300px; border-radius: 8px;"></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($property['latitude']) && !empty($property['longitude'])): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('detailMap').setView([<?= $property['latitude'] ?>, <?= $property['longitude'] ?>], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    L.marker([<?= $property['latitude'] ?>, <?= $property['longitude'] ?>]).addTo(map)
        .bindPopup('<?= htmlspecialchars($property['displayable_address']) ?>');
});
</script>
<?php endif; ?>
