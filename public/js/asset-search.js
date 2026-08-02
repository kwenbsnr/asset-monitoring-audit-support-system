/**
 * Live search for assets – uses AJAX with debounce.
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
                        resultsContainer.innerHTML = `<div class="bg-yellow-50 border border-yellow-300 text-yellow-800 p-3 rounded">${escapeHtml(data.error)}</div>`;
                        resultsContainer.style.display = 'block';
                        categoriesGrid.style.display = 'none';
                        return;
                    }
                    if (data.length === 0) {
                        resultsContainer.innerHTML = `<div class="bg-blue-50 border border-blue-200 text-blue-700 p-3 rounded">No assets found matching "<strong>${escapeHtml(query)}</strong>".</div>`;
                        resultsContainer.style.display = 'block';
                        categoriesGrid.style.display = 'none';
                        if (noResultsMsg) noResultsMsg.style.display = 'block';
                    } else {
                        // Build table
                        let html = `
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm border border-gray-200">
                                    <thead class="bg-gray-100 text-gray-700">
                                        <tr>
                                            <th class="px-3 py-2 border-b text-left font-medium">Asset Code</th>
                                            <th class="px-3 py-2 border-b text-left font-medium">Description</th>
                                            <th class="px-3 py-2 border-b text-left font-medium">Brand / Model</th>
                                            <th class="px-3 py-2 border-b text-left font-medium">Serial #</th>
                                            <th class="px-3 py-2 border-b text-left font-medium">Account</th>
                                            <th class="px-3 py-2 border-b text-left font-medium">Custodian</th>
                                            <th class="px-3 py-2 border-b text-left font-medium">Status</th>
                                            <th class="px-3 py-2 border-b text-left font-medium">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        data.forEach(asset => {
                            const statusClass = asset.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                            const custodianCell = asset.custodians
                                ? escapeHtml(asset.custodians)
                                : '<span class="text-gray-400">Not assigned</span>';
                            html += `
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-3 py-2 font-medium text-gray-800">${escapeHtml(asset.asset_code)}</td>
                                    <td class="px-3 py-2">${escapeHtml(asset.description)}</td>
                                    <td class="px-3 py-2">${escapeHtml(asset.brand || '')} ${escapeHtml(asset.model || '')}</td>
                                    <td class="px-3 py-2">${escapeHtml(asset.serial_number || '')}</td>
                                    <td class="px-3 py-2">${escapeHtml(asset.account_code || '')}</td>
                                    <td class="px-3 py-2">${custodianCell}</td>
                                    <td class="px-3 py-2"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${statusClass}">${escapeHtml(asset.status)}</span></td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <button type="button" class="px-2 py-1 text-blue-600 border border-blue-300 rounded hover:bg-blue-50 text-xs view-details" data-id="${asset.asset_id}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="index.php?page=assets&sub=edit&id=${asset.asset_id}" class="px-2 py-1 text-yellow-600 border border-yellow-300 rounded hover:bg-yellow-50 text-xs"><i class="bi bi-pencil"></i></a>
                                        <a href="index.php?page=assets&sub=delete&id=${asset.asset_id}" class="px-2 py-1 text-red-600 border border-red-300 rounded hover:bg-red-50 text-xs" onclick="return confirm('Delete this asset?')"><i class="bi bi-trash"></i></a>
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
                    resultsContainer.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded">Failed to search: ${escapeHtml(error.message)}</div>`;
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