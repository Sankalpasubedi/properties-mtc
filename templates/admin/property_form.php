<h1><?= isset($property['id']) && $property['id'] ? 'Edit' : 'Add' ?> Property</h1>

<?php if (isset($errors) && count($errors) > 0): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" action="<?= isset($property['id']) && $property['id'] ? '/admin/properties/edit/' . (int)$property['id'] : '/admin/properties/add' ?>" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="county" class="form-label">County *</label>
            <input type="text" class="form-control" id="county" name="county" value="<?= htmlspecialchars($property['county'] ?? '') ?>" required maxlength="255">
        </div>
        <div class="col-md-4 mb-3">
            <label for="country" class="form-label">Country *</label>
            <input type="text" class="form-control" id="country" name="country" value="<?= htmlspecialchars($property['country'] ?? '') ?>" required maxlength="255">
        </div>
        <div class="col-md-4 mb-3">
            <label for="town" class="form-label">Town *</label>
            <input type="text" class="form-control" id="town" name="town" value="<?= htmlspecialchars($property['town'] ?? '') ?>" required maxlength="255">
        </div>
    </div>

    <div class="mb-3">
        <label for="displayable_address" class="form-label">Displayable Address *</label>
        <input type="text" class="form-control" id="displayable_address" name="displayable_address" value="<?= htmlspecialchars($property['displayable_address'] ?? '') ?>" required maxlength="500">
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description *</label>
        <textarea class="form-control" id="description" name="description" rows="4" required><?= htmlspecialchars($property['description'] ?? '') ?></textarea>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="num_bedrooms" class="form-label">Number of Bedrooms</label>
            <select class="form-select" id="num_bedrooms" name="num_bedrooms">
                <option value="">Select...</option>
                <?php foreach (['1','2','3','4','5','6','7','8','9','10+'] as $opt): ?>
                <option value="<?= $opt ?>" <?= (isset($property['num_bedrooms']) && (string)$property['num_bedrooms'] === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label for="num_bathrooms" class="form-label">Number of Bathrooms</label>
            <select class="form-select" id="num_bathrooms" name="num_bathrooms">
                <option value="">Select...</option>
                <?php foreach (['1','2','3','4','5','6','7','8','9','10+'] as $opt): ?>
                <option value="<?= $opt ?>" <?= (isset($property['num_bathrooms']) && (string)$property['num_bathrooms'] === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label for="price" class="form-label">Price *</label>
            <input type="number" class="form-control" id="price" name="price" value="<?= htmlspecialchars($property['price'] ?? '') ?>" required min="0" step="0.01">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="latitude" class="form-label">Latitude</label>
            <input type="number" class="form-control" id="latitude" name="latitude" value="<?= htmlspecialchars($property['latitude'] ?? '') ?>" step="any" min="-90" max="90">
        </div>
        <div class="col-md-6 mb-3">
            <label for="longitude" class="form-label">Longitude</label>
            <input type="number" class="form-control" id="longitude" name="longitude" value="<?= htmlspecialchars($property['longitude'] ?? '') ?>" step="any" min="-180" max="180">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="image" class="form-label">Image <?= isset($property['id']) && $property['id'] ? '(leave empty to keep current)' : '*' ?></label>
            <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp" <?= isset($property['id']) && $property['id'] ? '' : 'required' ?>>
            <?php if (isset($property['image_url']) && $property['image_url']): ?>
            <div class="mt-2">
                <img src="<?= htmlspecialchars($property['image_url']) ?>" alt="Current image" style="max-height: 100px;">
                <small class="text-muted d-block">Current image</small>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-md-6 mb-3">
            <label for="thumbnail" class="form-label">Thumbnail <?= isset($property['id']) && $property['id'] ? '(leave empty to keep current)' : '*' ?></label>
            <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/gif,image/webp" <?= isset($property['id']) && $property['id'] ? '' : 'required' ?>>
            <?php if (isset($property['thumbnail_url']) && $property['thumbnail_url']): ?>
            <div class="mt-2">
                <img src="<?= htmlspecialchars($property['thumbnail_url']) ?>" alt="Current thumbnail" style="max-height: 100px;">
                <small class="text-muted d-block">Current thumbnail</small>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="property_description" class="form-label">Property Description</label>
            <textarea class="form-control" id="property_description" name="description" rows="1" required><?= htmlspecialchars($property['description'] ?? '') ?></textarea>
        </div>

        <div class="col-md-6 mb-3">
            <label for="property_type" class="form-label">Property Type</label>
            <input type="text" class="form-control" id="property_type" name="property_type" value="<?= htmlspecialchars($property['property_type'] ?? '') ?>" maxlength="255">
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-3">
            <label class="form-label">For Sale / For Rent *</label>
            <div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="for_sale" id="for_sale" value="sale" <?= (isset($property['for_sale']) ? (int)$property['for_sale'] : 1) === 1 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="for_sale">For Sale</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="for_sale" id="for_rent" value="rent" <?= (isset($property['for_sale']) ? (int)$property['for_sale'] : 1) === 0 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="for_rent">For Rent</label>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <button type="submit" class="btn btn-primary"><?= isset($property['id']) && $property['id'] ? 'Update' : 'Create' ?> Property</button>
        <a href="/admin" class="btn btn-secondary">Cancel</a>
    </div>
</form>
