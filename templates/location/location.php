<div class="text-center mb-4">
    <h1>Search by Location</h1>
    <p class="text-muted">Click on the map or enter coordinates to find nearby properties</p>
</div>

<div class="row mb-4">
    <div class="col-md-8 mx-auto">
        <form id="locationForm" class="card card-body shadow-sm">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="latInput" class="form-label">Latitude</label>
                    <input type="number" class="form-control" id="latInput" step="any" placeholder="51.5074" required>
                </div>
                <div class="col-md-4">
                    <label for="lngInput" class="form-label">Longitude</label>
                    <input type="number" class="form-control" id="lngInput" step="any" placeholder="-0.1278" required>
                </div>
                <div class="col-md-4">
                    <label for="radiusInput" class="form-label">Radius (km)</label>
                    <select class="form-select" id="radiusInput">
                        <option value="5">5 km</option>
                        <option value="10" selected>10 km</option>
                        <option value="25">25 km</option>
                        <option value="50">50 km</option>
                        <option value="100">100 km</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Search</button>
        </form>
    </div>
</div>

<div id="map"></div>
<div id="locationResults" class="row g-4 mt-4"></div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
#map { height: 500px; border-radius: 8px; }
</style>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map;
let markers = [];
let currentMarker;

function formatPrice(price) {
    return '£' + Number(price).toLocaleString();
}

function initMap() {
    map = L.map('map').setView([51.5074, -0.1278], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    map.on('click', function(e) {
        const lat = e.latlng.lat.toFixed(6);
        const lng = e.latlng.lng.toFixed(6);
        document.getElementById('latInput').value = lat;
        document.getElementById('lngInput').value = lng;
        if (currentMarker) map.removeLayer(currentMarker);
        currentMarker = L.marker([lat, lng]).addTo(map);
        doLocationSearch();
    });
}

function clearMarkers() {
    markers.forEach(m => map.removeLayer(m));
    markers = [];
}

function doLocationSearch() {
    const lat = document.getElementById('latInput').value;
    const lng = document.getElementById('lngInput').value;
    const radius = document.getElementById('radiusInput').value;
    if (!lat || !lng) return;

    clearMarkers();
    axios.get('/api/properties/location?lat=' + lat + '&lng=' + lng + '&radius=' + radius)
        .then(res => {
            const container = document.getElementById('locationResults');
            container.innerHTML = '';
            if (res.data.data.length === 0) {
                container.innerHTML = '<div class="col-12 text-center py-4"><h4>No properties found in this area</h4></div>';
                return;
            }

            res.data.data.forEach(p => {
                const distance = p.distance ? '(' + Number(p.distance).toFixed(1) + ' km)' : '';
                const marker = L.marker([p.latitude, p.longitude]).addTo(map)
                    .bindPopup(`<strong>${p.displayable_address}</strong><br>${p.town}, ${p.county}<br>${formatPrice(p.price)} - ${p.for_sale == 1 ? 'For Sale' : 'For Rent'}<br>${p.num_bedrooms || 0} beds, ${p.num_bathrooms || 0} baths`);
                markers.push(marker);

                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4';
                col.innerHTML = `
                    <a href="/property/${p.id}" class="text-decoration-none">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title text-dark">${p.displayable_address}</h5>
                                <p class="text-muted small">${p.town}, ${p.county} ${distance}</p>
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold text-primary">${formatPrice(p.price)}</span>
                                    <span class="badge bg-${p.for_sale == 1 ? 'success' : 'warning'}">${p.for_sale == 1 ? 'For Sale' : 'For Rent'}</span>
                                </div>
                                <div class="mt-2 text-muted small">
                                    <i class="bi bi-door-open"></i> ${p.num_bedrooms || 0} beds
                                    <i class="bi bi-droplet ms-2"></i> ${p.num_bathrooms || 0} baths
                                </div>
                            </div>
                        </div>
                    </a>`;
                container.appendChild(col);
            });

            if (res.data.data.length > 0) {
                const group = L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        })
        .catch(err => console.error(err));
}

document.addEventListener('DOMContentLoaded', function() {
    initMap();
    document.getElementById('locationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const lat = document.getElementById('latInput').value;
        const lng = document.getElementById('lngInput').value;
        if (lat && lng) {
            if (currentMarker) map.removeLayer(currentMarker);
            currentMarker = L.marker([lat, lng]).addTo(map);
            map.setView([lat, lng], 10);
            doLocationSearch();
        }
    });
});
</script>
