<?php if (!defined('APP_START')) exit; ?>
<?php
/**
 * NOTE ON SCOPE (for whoever reviews this next):
 * adapted from included a "verification cycle"
 * picker (CY2026 Annual Physical Count, etc.) and a per-custodian progress
 * bar. Neither is backed by anything in the current schema (assets /
 * asset_custodies / personnel / offices — no cycles table), so both were
 * dropped rather than faked. Everything below (filters, custodian grouping,
 * pagination, the verify modal) is wired to real data via
 * sub=verify_worklist_json, sub=details, and sub=verify (POST).
 */
?>
<div class="card-panel mb-4">
    <div class="card-panel-header">
        <div class="flex items-center gap-3">
            <span class="page-icon"><i class="bi bi-clipboard-check"></i></span>
            <div>
                <div class="page-title">Verify Asset</div>
                <div class="text-xs text-gray-500 font-medium">NIA Regional Office IX &middot; Physical inventory verification</div>
            </div>
        </div>
    </div>
</div>

<div class="card-panel">
    <div class="card-panel-header flex-wrap gap-2">
        <div class="flex items-center gap-3">
            <span class="page-icon page-icon-sm"><i class="bi bi-list-ul"></i></span>
            <span class="font-bold text-gray-800 text-sm">Worklist</span>
        </div>
        <span id="filterIndicator" class="badge-app badge-app-info hidden"><i class="bi bi-funnel"></i> Filter active &mdash; custodian names shown</span>
        <button type="button" id="clearFiltersBtn" class="btn-app btn-app-sm btn-app-outline hidden">
            <i class="bi bi-x-circle"></i> Clear filters
        </button>
    </div>

    <div class="card-panel-body">
        <div id="ajaxMsg" class="hidden mb-3"></div>

        <!-- Filter bar -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <div class="lg:col-span-1 sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Search</label>
                <input type="text" id="filterSearch" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" placeholder="Code, name, serial, custodian, office...">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Category</label>
                <select id="filterAccount" class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm">
                    <option value="">All categories</option>
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?= $acc['asset_accounts_id'] ?>"><?= htmlspecialchars($acc['account_code'] . ' - ' . $acc['account_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">End user / Custodian</label>
                <input type="text" id="filterCustodian" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition" placeholder="Type custodian name...">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Dep't / Office</label>
                <select id="filterOffice" class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm">
                    <option value="">All offices</option>
                    <?php foreach ($offices as $o): ?>
                        <option value="<?= $o['office_id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- No-filter / empty states -->
        <div id="noFilterMsg" class="text-center text-gray-500 py-12">
            <i class="bi bi-funnel text-5xl text-gray-300"></i>
            <div class="text-lg font-semibold text-gray-800 mt-3">Apply at least one filter to see the worklist</div>
            <div class="text-sm mt-1">Use Search, Category, Custodian, or Dep't above to narrow down the list.<br>Custodian names appear once a filter is active.</div>
        </div>
        <div id="noResultsMsg" class="hidden text-center text-gray-500 py-12">
            <i class="bi bi-search text-5xl text-gray-300"></i>
            <div class="text-lg font-semibold text-gray-800 mt-3">No assets match your filters</div>
            <div class="text-sm mt-1">Try adjusting your filter criteria.</div>
        </div>
        <div id="loadingMsg" class="hidden text-center text-gray-500 py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
            <p class="mt-2">Loading worklist...</p>
        </div>

        <!-- Worklist groups (populated by JS) -->
        <div id="worklistGroups" class="hidden divide-y divide-gray-200 border border-gray-200 rounded-lg overflow-hidden"></div>

        <!-- Pagination -->
        <div id="paginationBar" class="hidden items-center justify-between flex-wrap gap-3 pt-4 mt-4 border-t border-gray-200">
            <div id="paginationInfo" class="text-sm text-gray-500"></div>
            <div class="flex items-center gap-1">
                <button type="button" id="prevPageBtn" class="btn-app btn-app-sm btn-app-outline">Previous</button>
                <span id="pageNumbers" class="flex items-center gap-1"></span>
                <button type="button" id="nextPageBtn" class="btn-app btn-app-sm btn-app-outline">Next</button>
            </div>
        </div>
    </div>
</div>

<!-- Verify Asset Modal (standardized modal system — see public/css/style.css & public/js/modal.js) -->
<div id="verifyAssetModal" class="modal-overlay">
    <div class="modal-panel modal-panel-lg" role="dialog" aria-modal="true" aria-labelledby="verifyAssetModalTitle">
        <div class="modal-header">
            <h5 id="verifyAssetModalTitle"><i class="bi bi-clipboard-check text-green-700 mr-1"></i> Verify Asset</h5>
            <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body" id="verifyModalBody">
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                <p class="mt-2 text-gray-500">Loading asset...</p>
            </div>
        </div>
    </div>
</div>

<style>
    /* Small, view-scoped additions not already covered by the shared stylesheet. */
    .worklist-group .worklist-head { cursor: pointer; }
    .worklist-group.is-collapsed .worklist-body { display: none; }
    .worklist-group .chevron { transition: transform .15s; }
    .worklist-group.is-collapsed .chevron { transform: rotate(-90deg); }
    .custodian-avatar {
        width: 34px; height: 34px; border-radius: 50%;
        background: #eaf3ea; color: #15803d;
        display: flex; align-items: center; justify-content: center;
        font-size: .74rem; font-weight: 700; flex-shrink: 0;
    }
    .page-num {
        padding: 4px 10px; border: 1px solid transparent; border-radius: 8px;
        font-size: .8rem; font-weight: 600; background: transparent; cursor: pointer;
    }
    .page-num.active { background: #eaf3ea; border-color: #15803d; color: #15803d; }
    .page-num:hover:not(.active) { background: #f1f3f1; }
</style>

<script>
(function () {
    const pageSize = 20;
    let currentPage = 1;
    let totalRows = 0;
    let debounceTimer = null;

    const els = {
        search: document.getElementById('filterSearch'),
        account: document.getElementById('filterAccount'),
        custodian: document.getElementById('filterCustodian'),
        office: document.getElementById('filterOffice'),
        clearBtn: document.getElementById('clearFiltersBtn'),
        filterIndicator: document.getElementById('filterIndicator'),
        noFilterMsg: document.getElementById('noFilterMsg'),
        noResultsMsg: document.getElementById('noResultsMsg'),
        loadingMsg: document.getElementById('loadingMsg'),
        groups: document.getElementById('worklistGroups'),
        pagBar: document.getElementById('paginationBar'),
        pagInfo: document.getElementById('paginationInfo'),
        prevBtn: document.getElementById('prevPageBtn'),
        nextBtn: document.getElementById('nextPageBtn'),
        pageNumbers: document.getElementById('pageNumbers'),
        ajaxMsg: document.getElementById('ajaxMsg'),
        modalBody: document.getElementById('verifyModalBody'),
    };

    function hasActiveFilter() {
        return !!(els.search.value.trim() || els.account.value || els.custodian.value.trim() || els.office.value);
    }

    function buildQuery(page) {
        const params = new URLSearchParams();
        if (els.search.value.trim()) params.set('search', els.search.value.trim());
        if (els.account.value) params.set('account_id', els.account.value);
        if (els.custodian.value.trim()) params.set('custodian', els.custodian.value.trim());
        if (els.office.value) params.set('office_id', els.office.value);
        params.set('page', page);
        return params.toString();
    }

    function debounceLoad() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => loadWorklist(1), 300);
    }

    function setState(showGroups, showNoFilter, showNoResults, showLoading) {
        els.groups.classList.toggle('hidden', !showGroups);
        els.noFilterMsg.classList.toggle('hidden', !showNoFilter);
        els.noResultsMsg.classList.toggle('hidden', !showNoResults);
        els.loadingMsg.classList.toggle('hidden', !showLoading);
        els.pagBar.classList.toggle('hidden', !showGroups);
        els.pagBar.classList.toggle('flex', showGroups);
    }

    function loadWorklist(page) {
        const active = hasActiveFilter();
        els.filterIndicator.classList.toggle('hidden', !active);
        els.clearFiltersBtn.classList.toggle('hidden', !active);

        if (!active) {
            setState(false, true, false, false);
            return;
        }

        currentPage = page;
        setState(false, false, false, true);

        fetch(`index.php?page=assets&sub=verify_worklist_json&${buildQuery(page)}`)
            .then(r => r.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                totalRows = data.total;
                if (totalRows === 0) {
                    setState(false, false, true, false);
                    return;
                }
                renderGroups(data.rows);
                renderPagination(data.page, data.page_size, data.total);
                setState(true, false, false, false);
            })
            .catch(err => {
                setState(false, false, false, false);
                alert('Failed to load worklist: ' + err.message);
            });
    }

    function initials(name) {
        if (!name) return '?';
        return name.split(' ').filter(Boolean).slice(0, 2).map(p => p[0].toUpperCase()).join('');
    }

    function verificationBadge(status) {
        status = status || 'pending';
        const map = { verified: 'badge-app-success', discrepancy: 'badge-app-danger', pending: 'badge-app-neutral' };
        return `<span class="badge-app ${map[status] || 'badge-app-neutral'}">${escapeHtml(status.charAt(0).toUpperCase() + status.slice(1))}</span>`;
    }

    function statusBadge(status) {
        const map = { active: 'badge-app-success', missing: 'badge-app-danger', disposed: 'badge-app-neutral', inactive: 'badge-app-neutral' };
        return `<span class="badge-app ${map[status] || 'badge-app-neutral'}">${escapeHtml(status)}</span>`;
    }

    function renderGroups(rows) {
        // Group the current page's rows by custodian (unassigned assets bucketed together).
        const groups = new Map();
        rows.forEach(row => {
            const key = row.custodian_id || 'unassigned';
            if (!groups.has(key)) {
                groups.set(key, {
                    name: row.custodian_name || 'Unassigned',
                    position: row.position || '',
                    office: row.office_name || 'No office on record',
                    rows: [],
                });
            }
            groups.get(key).rows.push(row);
        });

        let html = '';
        groups.forEach((group, key) => {
            html += `
            <div class="worklist-group" data-group="${key}">
                <div class="worklist-head flex items-center justify-between gap-3 px-4 py-3 hover:bg-gray-50" onclick="this.closest('.worklist-group').classList.toggle('is-collapsed')">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="custodian-avatar">${escapeHtml(initials(group.name))}</div>
                        <div class="min-w-0">
                            <div class="font-semibold text-sm text-gray-800 truncate">${escapeHtml(group.name)}${group.position ? ' <span class="text-gray-400 font-normal">&middot; ' + escapeHtml(group.position) + '</span>' : ''}</div>
                            <div class="text-xs text-gray-500">${escapeHtml(group.office)}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <span class="badge-app badge-app-neutral">${group.rows.length} on this page</span>
                        <i class="bi bi-chevron-down chevron text-gray-400"></i>
                    </div>
                </div>
                <div class="worklist-body">
                    <div class="table-app-wrap" style="border:none;border-radius:0;">
                        <table class="table-app">
                            <thead><tr><th>Asset code</th><th>Name</th><th>Category</th><th>Condition</th><th>Verification</th><th>Last verified</th><th></th></tr></thead>
                            <tbody>
                                ${group.rows.map(row => `
                                    <tr>
                                        <td class="font-semibold">${escapeHtml(row.asset_code)}</td>
                                        <td>${escapeHtml(row.asset_name)}</td>
                                        <td>${escapeHtml(row.account_code || 'N/A')}</td>
                                        <td>${statusBadge(row.condition)}</td>
                                        <td>${verificationBadge(row.verification_status)}</td>
                                        <td class="text-xs text-gray-500">${row.verified_at ? new Date(row.verified_at).toLocaleDateString() : 'Never'}</td>
                                        <td><button type="button" class="btn-app btn-app-xs btn-app-primary verify-row-btn" data-id="${row.asset_id}">Verify</button></td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>`;
        });

        els.groups.innerHTML = html;
        els.groups.querySelectorAll('.verify-row-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                openVerifyModal(btn.dataset.id);
            });
        });
    }

    function renderPagination(page, size, total) {
        const totalPages = Math.max(1, Math.ceil(total / size));
        const start = (page - 1) * size + 1;
        const end = Math.min(page * size, total);
        els.pagInfo.textContent = `Showing ${start}-${end} of ${total}`;
        els.prevBtn.disabled = page <= 1;
        els.nextBtn.disabled = page >= totalPages;

        let numsHtml = '';
        const maxVisible = 5;
        let startPage = Math.max(1, page - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);
        startPage = Math.max(1, endPage - maxVisible + 1);
        for (let i = startPage; i <= endPage; i++) {
            numsHtml += `<span class="page-num ${i === page ? 'active' : ''}" data-page="${i}">${i}</span>`;
        }
        els.pageNumbers.innerHTML = numsHtml;
        els.pageNumbers.querySelectorAll('.page-num').forEach(el => {
            el.addEventListener('click', () => loadWorklist(parseInt(el.dataset.page, 10)));
        });
    }

    els.prevBtn.addEventListener('click', () => { if (currentPage > 1) loadWorklist(currentPage - 1); });
    els.nextBtn.addEventListener('click', () => loadWorklist(currentPage + 1));
    els.search.addEventListener('input', debounceLoad);
    els.custodian.addEventListener('input', debounceLoad);
    els.account.addEventListener('change', () => loadWorklist(1));
    els.office.addEventListener('change', () => loadWorklist(1));
    els.clearFiltersBtn.addEventListener('click', () => {
        els.search.value = '';
        els.account.value = '';
        els.custodian.value = '';
        els.office.value = '';
        loadWorklist(1);
    });

    // ---- Verify modal ----
    function openVerifyModal(assetId) {
        els.modalBody.innerHTML = `
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                <p class="mt-2 text-gray-500">Loading asset...</p>
            </div>`;
        NiaModal.open('verifyAssetModal');

        fetch(`index.php?page=assets&sub=details&id=${assetId}`)
            .then(r => r.json())
            .then(data => {
                if (data.error) {
                    els.modalBody.innerHTML = `<div class="alert-app alert-app-danger">${escapeHtml(data.error)}</div>`;
                    return;
                }
                renderVerifyForm(data);
            })
            .catch(err => {
                els.modalBody.innerHTML = `<div class="alert-app alert-app-danger">Failed to load asset: ${escapeHtml(err.message)}</div>`;
            });
    }

    function renderVerifyForm(data) {
        const asset = data.asset;
        const custody = data.custody || [];
        const active = custody.find(c => c.custody_status === 'active');

        const personnelOptions = <?= json_encode(array_map(fn($p) => ['id' => $p['personnel_id'], 'label' => $p['full_name'] . ' (' . $p['position'] . ')', 'office_id' => $p['office_id']], $personnel)) ?>;
        const officeOptions = <?= json_encode(array_map(fn($o) => ['id' => $o['office_id'], 'label' => $o['name']], $offices)) ?>;

        els.modalBody.innerHTML = `
            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm mb-4">
                <span class="font-bold text-green-700">${escapeHtml(asset.asset_code)}</span> &middot;
                ${escapeHtml(asset.asset_name)} &middot;
                SN: ${escapeHtml(asset.serial_number || 'N/A')}<br>
                Recorded custodian: ${active ? escapeHtml(active.custodian_name) + ', ' + escapeHtml(active.office_name) : 'Not assigned'} &middot;
                Last verified: ${asset.verified_at ? new Date(asset.verified_at).toLocaleString() : 'Never'}
            </div>

            <form id="verifyRowForm">
                <input type="hidden" name="asset_id" value="${asset.asset_id}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Condition observed</label>
                        <select name="condition" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            ${['good', 'fair', 'poor', 'damaged', 'obsolete'].map(v => `<option value="${v}" ${asset.condition === v ? 'selected' : ''}>${v.charAt(0).toUpperCase() + v.slice(1)}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Physical status</label>
                        <select name="status" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            ${[['active', 'Found / Active'], ['missing', 'Not found / Missing'], ['inactive', 'Inactive'], ['disposed', 'Disposed']].map(([v, label]) => `<option value="${v}" ${asset.status === v ? 'selected' : ''}>${label}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Verification status</label>
                        <select name="verification_status" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            ${[['pending', 'Pending'], ['verified', 'Verified'], ['discrepancy', 'Discrepancy (custodian/other mismatch)']].map(([v, label]) => `<option value="${v}" ${(asset.verification_status || 'pending') === v ? 'selected' : ''}>${label}</option>`).join('')}
                        </select>
                        <div class="text-xs text-gray-500 mt-1">Discrepancy flags this for Custodial Tracking follow-up — it won't reassign custody automatically.</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Accountable custodian</label>
                        <select name="custodian_id" id="modalCustodianSelect" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">Select custodian</option>
                            ${personnelOptions.map(p => `<option value="${p.id}" data-office-id="${p.office_id}" ${active && String(active.custodian_id) === String(p.id) ? 'selected' : ''}>${escapeHtml(p.label)}</option>`).join('')}
                        </select>
                        <select name="office_id" id="modalOfficeSelect" class="hidden">
                            ${officeOptions.map(o => `<option value="${o.id}">${escapeHtml(o.label)}</option>`).join('')}
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Inspection remarks</label>
                        <textarea name="inspection_remarks" rows="2" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Any notes for the inventory committee...">${escapeHtml(asset.inspection_remarks || '')}</textarea>
                    </div>
                </div>
            </form>
        `;

        // Keep the hidden office_id select in sync with the chosen custodian.
        const custodianSelect = document.getElementById('modalCustodianSelect');
        const officeSelect = document.getElementById('modalOfficeSelect');
        function syncOffice() {
            const opt = custodianSelect.options[custodianSelect.selectedIndex];
            const officeId = opt ? opt.getAttribute('data-office-id') : '';
            if (officeId) officeSelect.value = officeId;
        }
        custodianSelect.addEventListener('change', syncOffice);
        syncOffice();

        // Footer buttons rendered separately so they can submit the form above.
        const footer = document.createElement('div');
        footer.className = 'modal-footer';
        footer.innerHTML = `
            <span id="modalSaveMsg" class="text-sm text-green-600 mr-auto"></span>
            <button type="button" class="btn-app btn-app-outline" data-modal-close>Cancel</button>
            <button type="button" id="saveUpdatesBtn" class="btn-app btn-app-outline-primary"><i class="bi bi-save"></i> Save Updates</button>
            <button type="button" id="markVerifiedBtn" class="btn-app btn-app-primary"><i class="bi bi-check-circle"></i> Mark as Verified</button>
        `;
        els.modalBody.parentElement.querySelectorAll('.modal-footer').forEach(f => f.remove());
        els.modalBody.parentElement.appendChild(footer);
        // The Cancel button above carries data-modal-close but was added
        // after NiaModal's initial DOMContentLoaded scan, so it needs a
        // rescan to get its close listener wired up (see public/js/modal.js).
        if (window.NiaModal && typeof NiaModal.rescan === 'function') NiaModal.rescan();

        document.getElementById('saveUpdatesBtn').addEventListener('click', () => submitVerify(false));
        document.getElementById('markVerifiedBtn').addEventListener('click', () => submitVerify(true));
    }

    function submitVerify(markVerified) {
        const form = document.getElementById('verifyRowForm');
        const body = new URLSearchParams(new FormData(form));
        if (markVerified) body.set('mark_verified', '1');

        fetch('index.php?page=assets&sub=verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: body.toString(),
        })
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    const msg = document.getElementById('modalSaveMsg');
                    if (msg) { msg.className = 'text-sm text-red-600 mr-auto'; msg.textContent = res.message || 'Failed to save.'; }
                    return;
                }
                NiaModal.close('verifyAssetModal');
                showAjaxMsg(res.message || 'Asset verification updated successfully.');
                loadWorklist(currentPage);
            })
            .catch(err => alert('Failed to save: ' + err.message));
    }

    function showAjaxMsg(message) {
        els.ajaxMsg.innerHTML = `<div class="alert-app alert-app-success"><span>${escapeHtml(message)}</span></div>`;
        els.ajaxMsg.classList.remove('hidden');
        setTimeout(() => els.ajaxMsg.classList.add('hidden'), 4000);
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(text);
        return div.innerHTML;
    }

    // Nothing loads until the officer applies a filter (keeps custodian
    // names from being dumped on page load — matches the reference design).
    setState(false, true, false, false);
})();
</script>