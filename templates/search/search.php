<div class="text-center mb-5">
    <h1>Property Search</h1>
    <p class="text-muted">Find your perfect property</p>
</div>

<div class="row mb-4">
    <div class="col-md-8 mx-auto">
        <form id="searchForm" class="card card-body shadow-sm">
            <div class="mb-3 position-relative">
                <label for="addressSearch" class="form-label fw-semibold">Search by Address</label>
                <input type="text" class="form-control form-control-lg" id="addressSearch" placeholder="Start typing an address..." autocomplete="off">
                <div id="autocompleteResults" class="list-group position-absolute w-100 d-none" style="z-index: 1000; max-height: 300px; overflow-y: auto;"></div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="filterBedrooms" class="form-label">Bedrooms</label>
                    <select class="form-select" id="filterBedrooms">
                        <option value="">Any</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5+</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filterBathrooms" class="form-label">Bathrooms</label>
                    <select class="form-select" id="filterBathrooms">
                        <option value="">Any</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5+</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filterType" class="form-label">Type</label>
                    <select class="form-select" id="filterType">
                        <option value="">All</option>
                        <option value="sale">For Sale</option>
                        <option value="rent">For Rent</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <label for="filterPriceMin" class="form-label">Min Price</label>
                    <input type="number" class="form-control" id="filterPriceMin" placeholder="0" min="0">
                </div>
                <div class="col-md-6">
                    <label for="filterPriceMax" class="form-label">Max Price</label>
                    <input type="number" class="form-control" id="filterPriceMax" placeholder="9999999" min="0">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg mt-3 w-100">Search</button>
        </form>
    </div>
</div>

<div id="results" class="row g-4"></div>
<div class="d-flex justify-content-between align-items-center mt-4">
    <div id="pagination"></div>
</div>

<script>
let currentPage = 1;
let currentPerPage = 20;

function formatPrice(price) {
    return '£' + Number(price).toLocaleString();
}

function getPropertyImage(url) {
    if (!url) return null;
    if (url.startsWith('http')) return url;
    return url;
}

function renderProperties(data) {
    const container = document.getElementById('results');
    const pagination = document.getElementById('pagination');
    container.innerHTML = '';
    pagination.innerHTML = '';

    if (!data.data || data.data.length === 0) {
        container.innerHTML = '<div class="col-12 text-center py-5"><h4>No properties found</h4></div>';
        return;
    }

    data.data.forEach(p => {
        const col = document.createElement('div');
        col.className = 'col-md-6 col-lg-4';
        col.innerHTML = `
            <a href="/property/${p.id}" class="text-decoration-none">
                <div class="card h-100 shadow-sm">
                    <img src="${getPropertyImage(p.image_url)}" class="card-img-top" alt="${p.displayable_address}" style="height: 200px; object-fit: cover;" onerror="this.onerror=null;this.src='';">
                    <div class="card-body">
                        <h5 class="card-title text-dark">${p.displayable_address}</h5>
                        <p class="card-text text-muted small">${p.town}, ${p.county}, ${p.country}</p>
                        <p class="card-text">${p.description ? p.description.substring(0, 100) + '...' : ''}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-5 fw-bold text-primary">${formatPrice(p.price)}</span>
                            <span class="badge bg-${p.for_sale == 1 ? 'success' : 'warning'}">${p.for_sale == 1 ? 'For Sale' : 'For Rent'}</span>
                        </div>
                        <div class="mt-2 text-muted small">
                            <i class="bi bi-door-open"></i> ${p.num_bedrooms || 0} beds
                            <i class="bi bi-droplet ms-2"></i> ${p.num_bathrooms || 0} baths
                        </div>
                    </div>
                </div>
            </a>
        `;
        container.appendChild(col);
    });

    if (data.last_page > 1) {
        const nav = document.createElement('nav');
        const ul = document.createElement('ul');
        ul.className = 'pagination pagination-sm mb-0';
        const c = data.page, lp = data.last_page;
        const prevus = document.createElement('li');
        prevus.className = `page-item ${c === 1 ? 'disabled' : ''}`;
        const tempprev = document.createElement('a');
        tempprev.className = 'page-link';
        tempprev.href = '#';
        tempprev.textContent = 'Previous';
        tempprev.addEventListener('click', e => { e.preventDefault(); if (c > 1) { currentPage = c - 1; doSearch(); } });
        prevus.appendChild(tempprev);
        ul.appendChild(prevus);

        let start = Math.max(1, c - 2);
        let end = Math.min(lp, c + 2);
        if (start > 1) {
            const li = document.createElement('li');
            li.className = 'page-item';
            const a = document.createElement('a'); a.className = 'page-link'; a.href = '#'; a.textContent = '1';
            a.addEventListener('click', e => { e.preventDefault(); currentPage = 1; doSearch(); });
            li.appendChild(a); ul.appendChild(li);
            if (start > 2) {
                const el = document.createElement('li'); el.className = 'page-item disabled';
                const sp = document.createElement('span'); sp.className = 'page-link'; sp.textContent = '...';
                el.appendChild(sp); ul.appendChild(el);
            }
        }
        for (let i = start; i <= end; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === c ? 'active' : ''}`;
            const a = document.createElement('a'); a.className = 'page-link'; a.href = '#'; a.textContent = i;
            a.addEventListener('click', e => { e.preventDefault(); currentPage = i; doSearch(); });
            li.appendChild(a); ul.appendChild(li);
        }
        if (end < lp) {
            if (end < lp - 1) {
                const el = document.createElement('li'); el.className = 'page-item disabled';
                const sp = document.createElement('span'); sp.className = 'page-link'; sp.textContent = '...';
                el.appendChild(sp); ul.appendChild(el);
            }
            const li = document.createElement('li'); li.className = 'page-item';
            const a = document.createElement('a'); a.className = 'page-link'; a.href = '#'; a.textContent = lp;
            a.addEventListener('click', e => { e.preventDefault(); currentPage = lp; doSearch(); });
            li.appendChild(a); ul.appendChild(li);
        }

        const nextlist = document.createElement('li');
        nextlist.className = `page-item ${c === lp ? 'disabled' : ''}`;
        const tempnext = document.createElement('a');
        tempnext.className = 'page-link';
        tempnext.href = '#';
        tempnext.textContent = 'Next';
        tempnext.addEventListener('click', e => { e.preventDefault(); if (c < lp) { currentPage = c + 1; doSearch(); } });
        nextlist.appendChild(tempnext);
        ul.appendChild(nextlist);

        nav.appendChild(ul);
        pagination.appendChild(nav);
    }
}

function doSearch() {
    const params = new URLSearchParams();
    params.append('search', document.getElementById('addressSearch').value);
    params.append('bedrooms', document.getElementById('filterBedrooms').value);
    params.append('bathrooms', document.getElementById('filterBathrooms').value);
    params.append('price_min', document.getElementById('filterPriceMin').value);
    params.append('price_max', document.getElementById('filterPriceMax').value);
    params.append('type', document.getElementById('filterType').value);
    params.append('page', currentPage);

    axios.get('/api/properties/search?' + params.toString())
        .then(res => renderProperties(res.data))
        .catch(err => console.error(err));
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('addressSearch');
    const resultsDiv = document.getElementById('autocompleteResults');
    let timeout = null;

    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        const query = this.value.trim();
        if (query.length < 2) {
            resultsDiv.classList.add('d-none');
            return;
        }
        timeout = setTimeout(() => {
            axios.get('/api/properties/autocomplete?q=' + encodeURIComponent(query))
                .then(res => {
                    resultsDiv.innerHTML = '';
                    if (res.data.length === 0) {
                        resultsDiv.classList.add('d-none');
                        return;
                    }
                    res.data.forEach(addr => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = addr;
                        item.addEventListener('click', function() {
                            searchInput.value = this.textContent;
                            resultsDiv.classList.add('d-none');
                            currentPage = 1;
                            doSearch();
                        });
                        resultsDiv.appendChild(item);
                    });
                    resultsDiv.classList.remove('d-none');
                })
                .catch(() => resultsDiv.classList.add('d-none'));
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#autocompleteResults') && e.target !== searchInput) {
            resultsDiv.classList.add('d-none');
        }
    });

    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        currentPage = 1;
        doSearch();
    });

    doSearch();
});
</script>
