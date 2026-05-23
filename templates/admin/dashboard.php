<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Properties (<?= (int)($total ?? 0) ?>)</h1>
    <div>
        <a href="/admin/properties/add" class="btn btn-primary">Add Property</a>
        <a href="/admin/api/sync" class="btn btn-info" onclick="return confirm('Sync properties from API? This will update existing API properties and add new ones.')">Sync from API</a>
    </div>
</div>

<form method="GET" action="/admin" class="row g-2 mb-3">
    <div class="col-auto flex-grow-1">
        <input type="text" class="form-control" name="search" placeholder="Search by address..." value="<?= htmlspecialchars($search ?? '') ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-primary">Search</button>
        <?php if (!empty($search)): ?>
        <a href="/admin" class="btn btn-outline-secondary">Clear</a>
        <?php endif; ?>
    </div>
</form>

<?php if (isset($error) && $error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Address</th>
                <th>Town</th>
                <th>County</th>
                <th>Bedrooms</th>
                <th>Price</th>
                <th>Type</th>
                <th>Source</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($properties) && count($properties) > 0): ?>
            <?php $rowNum = ((int)($page ?? 1) - 1) * (int)($per_page ?? 10); ?>
            <?php foreach ($properties as $i => $p): ?>
            <tr>
                <td><?= $rowNum + $i + 1 ?></td>
                <td><?= htmlspecialchars($p['displayable_address'] ?? '') ?></td>
                <td><?= htmlspecialchars($p['town'] ?? '') ?></td>
                <td><?= htmlspecialchars($p['county'] ?? '') ?></td>
                <td><?= htmlspecialchars($p['num_bedrooms'] ?? '') ?></td>
                <td>&pound;<?= number_format((float)($p['price'] ?? 0)) ?></td>
                <td>
                    <?php if ($p['for_sale'] == 1): ?>
                    <span class="badge bg-success">For Sale</span>
                    <?php else: ?>
                    <span class="badge bg-warning">For Rent</span>
                    <?php endif; ?>
                </td>
                <td><span class="badge bg-<?= ($p['source'] ?? 'admin') === 'admin' ? 'primary' : 'secondary' ?>"><?= htmlspecialchars($p['source'] ?? '') ?></span></td>
                <td>
                    <a href="/admin/properties/edit/<?= (int)$p['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <form method="POST" action="/admin/properties/delete/<?= (int)$p['id'] ?>" style="display:inline" onsubmit="return confirm('Delete this property?')">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr>
                <td colspan="9" class="text-center">No properties found. <a href="/admin/properties/add">Add one</a> or <a href="/admin/api/sync">sync from API</a>.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <?php if (isset($last_page) && $last_page > 1): $current = (int)($page ?? 1); $pp = 20; ?>
    <nav>
        <ul class="pagination mb-0 pagination-sm flex-wrap">
            <li class="page-item <?= $current === 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="/admin?page=<?= max(1, $current-1) ?>&per_page=<?= $pp ?>">Previous</a>
            </li>
            <?php
            $start = max(1, $current - 2);
            $end = min($last_page, $current + 2);
            if ($start > 1): ?>
            <li class="page-item"><a class="page-link" href="/admin?page=1&per_page=<?= $pp ?>">1</a></li>
            <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
            <?php endif; ?>
            <?php for ($i = $start; $i <= $end; $i++): ?>
            <li class="page-item <?= $i === $current ? 'active' : '' ?>">
                <a class="page-link" href="/admin?page=<?= $i ?>&per_page=<?= $pp ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
            <?php if ($end < $last_page): ?>
            <?php if ($end < $last_page - 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
            <li class="page-item"><a class="page-link" href="/admin?page=<?= $last_page ?>&per_page=<?= $pp ?>"><?= $last_page ?></a></li>
            <?php endif; ?>
            <li class="page-item <?= $current === $last_page ? 'disabled' : '' ?>">
                <a class="page-link" href="/admin?page=<?= min($last_page, $current+1) ?>&per_page=<?= $pp ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>
