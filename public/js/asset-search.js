/**
 * Live search for assets – uses AJAX with debounce.
 * Injected result markup uses the app's shared design-system classes
 * (table-app, badge-app, btn-app, alert-app — see public/css/style.css)
 * so search results render identically to every other table in the app.
 */
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('liveSearchInput');
    const resultsContainer = document.getElementById('searchResultsContainer');
    const categoriesGrid = document.getElementById('categoriesGrid');
    const noResultsMsg = document.getElementById('noResultsMessage');
    let debounceTimer;

    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(debounceTimer);

        if (query.length < 2) {
            // Hide results, show categories
            resultsContainer.style.display = 'none';
            categoriesGrid.style.display = 'flex';
            if (noResultsMsg) noResultsMsg.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(function() {
            fetch(`index.php?page=assets&sub=search_json&q=${encodeURIComponent(query)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        resultsContainer.innerHTML = `<div class="alert-app alert-app-warning">${escapeHtml(data.error)}</div>`;
                        resultsContainer.style.display = 'block';
                        categoriesGrid.style.display = 'none';
                        return;
                    }
                    if (data.length === 0) {
                        resultsContainer.innerHTML = `<div class="alert-app alert-app-info">No assets found matching "<strong>${escapeHtml(query)}</strong>".</div>`;
                        resultsContainer.style.display = 'block';
                        categoriesGrid.style.display = 'none';
                        if (noResultsMsg) noResultsMsg.style.display = 'block';
                    } else {
                        // Build table
                        let html = `
                            <div class="table-app-wrap">
                                <table class="table-app">
                                    <thead>
                                        <tr>
                                            <th>Asset Code</th>
                                            <th>Description</th>
                                            <th>Brand / Model</th>
                                            <th>Serial #</th>
                                            <th>Account</th>
                                            <th>Custodian</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        data.forEach(asset => {
                            const statusClass = asset.status === 'active' ? 'badge-app-success' : 'badge-app-neutral';
                            const custodianCell = asset.custodians
                                ? escapeHtml(asset.custodians)
                                : '<span class="text-gray-400">Not assigned</span>';
                            html += `
                                <tr>
                                    <td class="font-medium text-gray-800">${escapeHtml(asset.asset_code)}</td>
                                    <td>${escapeHtml(asset.description)}</td>
                                    <td>${escapeHtml(asset.brand || '')} ${escapeHtml(asset.model || '')}</td>
                                    <td>${escapeHtml(asset.serial_number || '')}</td>
                                    <td>${escapeHtml(asset.account_code || '')}</td>
                                    <td>${custodianCell}</td>
                                    <td><span class="badge-app ${statusClass}">${escapeHtml(asset.status)}</span></td>
                                    <td class="whitespace-nowrap">
                                        <button type="button" class="btn-app btn-app-sm btn-app-outline-primary view-details" data-id="${asset.asset_id}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="index.php?page=assets&sub=edit&id=${asset.asset_id}" class="btn-app btn-app-sm btn-app-outline-warning"><i class="bi bi-pencil"></i></a>
                                        <a href="index.php?page=assets&sub=delete&id=${asset.asset_id}" class="btn-app btn-app-sm btn-app-outline-danger" onclick="return confirm('Delete this asset?')"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            `;
                        });
                        html += `</tbody></table></div>`;
                        resultsContainer.innerHTML = html;
                        resultsContainer.style.display = 'block';
                        categoriesGrid.style.display = 'none';
                        if (noResultsMsg) noResultsMsg.style.display = 'none';
                    }
                })
                .catch(error => {
                    resultsContainer.innerHTML = `<div class="alert-app alert-app-danger">Failed to search: ${escapeHtml(error.message)}</div>`;
                    resultsContainer.style.display = 'block';
                    categoriesGrid.style.display = 'none';
                });
        }, 300); // debounce delay (ms)
    });

    // Helper to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});