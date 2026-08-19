/**
 * Behavior for the Asset Add/Edit form (app/Views/assets/form.php).
 * Exposed as window.initAssetForm() so it can be called both on a normal
 * full-page load (non-AJAX fallback) AND after public/js/modal-forms.js
 * injects the form fragment into the shared modal — see that file's
 * docs for why this can't just be an inline <script> in the fragment.
 *
 * Safe to call more than once: every listener is attached to elements
 * freshly queried from `root`, so re-running it after a fresh fragment
 * load never double-binds anything from a previous modal open.
 */
function initAssetForm(root) {
    root = root || document;

    const toggle = root.getElementById ? root.getElementById('assignCustodianToggle') : root.querySelector('#assignCustodianToggle');
    const section = root.querySelector ? root.querySelector('#custodianSection') : null;
    const propertyNumberInput = root.querySelector('#property_number');

    function syncCustodianRequirement() {
        const on = toggle.checked;
        section.style.display = on ? 'block' : 'none';
        if (propertyNumberInput) {
            propertyNumberInput.required = on;
        }
    }
    if (toggle && section) {
        toggle.addEventListener('change', syncCustodianRequirement);
        syncCustodianRequirement(); // correct state on load (e.g. after a failed-validation re-render)
    }

    // ===== Acquisition date sanity check (client-side heads-up; server-side is authoritative) =====
    const acquisitionDateInput = root.querySelector('#acquisition_date');
    const dateWarning = root.querySelector('#dateWarning');
    if (acquisitionDateInput && dateWarning) {
        acquisitionDateInput.addEventListener('input', function() {
            if (!this.value) { dateWarning.textContent = ''; return; }
            const year = parseInt(this.value.split('-')[0], 10);
            const currentYear = new Date().getFullYear();
            if (year < 1990 || year > currentYear) {
                dateWarning.textContent = 'Year must be between 1990 and ' + currentYear + '.';
                dateWarning.classList.add('text-red-600');
            } else {
                dateWarning.textContent = '';
                dateWarning.classList.remove('text-red-600');
            }
        });
    }

    // ===== Custodian autocomplete (Office <-> Custodian linkage) =====
    // Replaces the old text-input-that-filters-a-giant-<select> pattern.
    // custodian_id is a hidden input now — the visible custodianSearch box
    // is search text only, and is never treated as the source of truth.
    const custodianSearch = root.querySelector('#custodianSearch');
    const custodianIdInput = root.querySelector('#custodian_id');
    const custodianDropdown = root.querySelector('#custodianDropdown');
    const custodianDataEl = root.querySelector('#custodianData');
    const officeSelect = root.querySelector('#office_id');
    const acquisitionCostInput = root.querySelector('#acquisition_cost');
    const sgWarning = root.querySelector('#sgWarning');

    // Fixed SG -> threshold table, mirrors app/Helpers/SalaryGradeHelper.php.
    // Server-side validation (AssetController::save) is authoritative;
    // this is a client-side heads-up only, same as custody/form.php and verify.php.
    function sgThreshold(sg) {
        if (sg >= 1 && sg <= 7) return 70000;
        if (sg >= 8 && sg <= 10) return 500000;
        if (sg >= 11 && sg <= 17) return 1000000;
        if (sg >= 18 && sg <= 21) return 10000000;
        if (sg >= 22 && sg <= 30) return Infinity;
        return 0;
    }

    // ===== External-office handling =====
    // When the selected office is external, the accountable officer is
    // always that office's head — auto-filled, read-only, no SG check.
    // Server-side (AssetController::save()) re-resolves this independently,
    // so this is a UX convenience, not the source of truth.
    const custodianWrap = root.querySelector('.custodian-ac-wrap');
    const externalHeadNotice = root.querySelector('#externalHeadNotice');
    const externalHeadName = root.querySelector('#externalHeadName');
    const externalNoHeadWarning = root.querySelector('#externalNoHeadWarning');

    let custodianWasAutoFilled = false;

    function syncOfficeTypeUI() {
        if (!officeSelect || !custodianWrap) return;
        const opt = officeSelect.options[officeSelect.selectedIndex];
        const officeType = opt ? opt.getAttribute('data-office-type') : null;

        if (officeType === 'external') {
            const headId = opt.getAttribute('data-head-id');
            const headName = opt.getAttribute('data-head-name');

            custodianWrap.style.display = 'none';
            if (sgWarning) sgWarning.textContent = '';

            if (headId) {
                custodianIdInput.value = headId;
                if (custodianSearch) custodianSearch.value = headName;
                custodianWasAutoFilled = true;
                if (externalHeadNotice) externalHeadNotice.style.display = 'block';
                if (externalHeadName) externalHeadName.textContent = headName;
                if (externalNoHeadWarning) externalNoHeadWarning.style.display = 'none';
            } else {
                // No head on file — block the client from silently submitting
                // a blank/invalid custodian; server-side rejects this too.
                custodianIdInput.value = '';
                custodianWasAutoFilled = false;
                if (externalHeadNotice) externalHeadNotice.style.display = 'none';
                if (externalNoHeadWarning) externalNoHeadWarning.style.display = 'block';
            }
        } else {
            custodianWrap.style.display = '';
            if (externalHeadNotice) externalHeadNotice.style.display = 'none';
            if (externalNoHeadWarning) externalNoHeadWarning.style.display = 'none';
            // Clear a head auto-fill left over from a previous external pick —
            // never touch a real manually-selected custodian.
            if (custodianWasAutoFilled) {
                custodianIdInput.value = '';
                if (custodianSearch) custodianSearch.value = '';
                custodianWasAutoFilled = false;
            }
        }
    }

    if (custodianSearch && custodianIdInput && custodianDropdown && custodianDataEl) {
        const people = JSON.parse(custodianDataEl.textContent || '[]');
        const byId = new Map(people.map(p => [String(p.id), p]));
        const MAX_RESULTS = 8; // keep the list short — never dump the full roster on-screen
        let currentMatches = [];
        let activeIndex = -1;

        function personLabel(p) {
            return p.name + (p.position ? ' (' + p.position + ')' : '');
        }

        function checkSgThreshold() {
            if (!sgWarning) return;
            const selected = byId.get(custodianIdInput.value);
            const costRaw = acquisitionCostInput ? acquisitionCostInput.value : null;
            if (!selected || !costRaw) {
                sgWarning.textContent = '';
                return;
            }
            const cost = parseFloat(costRaw);
            if (!cost) {
                sgWarning.textContent = '';
                return;
            }
            const threshold = sgThreshold(selected.sg);
            if (cost > threshold) {
                sgWarning.textContent = 'Warning: this asset (₱' + cost.toLocaleString(undefined, {minimumFractionDigits: 2}) +
                    ') exceeds SG ' + selected.sg + '\'s threshold' + (isFinite(threshold) ? ' of ₱' + threshold.toLocaleString() : '') + '. This assignment will be rejected on save.';
                sgWarning.classList.add('text-red-600');
                sgWarning.classList.remove('text-gray-500');
            } else {
                sgWarning.textContent = '';
                sgWarning.classList.remove('text-red-600');
            }
        }

        // Simple, predictable relevance ranking: a name that STARTS WITH the
        // typed text ranks above one that contains it at a word boundary,
        // which in turn ranks above any other substring match. Position/title
        // is only a fallback so e.g. typing "clerk" still finds someone even
        // when it matches no name.
        function scoreText(text, term) {
            const t = (text || '').toLowerCase();
            const idx = t.indexOf(term);
            if (idx === -1) return -1;
            if (idx === 0) return 3;
            if (/\s/.test(t[idx - 1])) return 2;
            return 1;
        }

        function findMatches(term, officeId) {
            const q = (term || '').trim().toLowerCase();
            let pool = people;
            if (officeId) pool = pool.filter(p => String(p.office) === String(officeId));

            const scored = pool.map(p => {
                let score = q === '' ? 0 : scoreText(p.name, q);
                if (q !== '' && score < 0) {
                    score = scoreText(p.position, q) > 0 ? 0.5 : -1;
                }
                return { p, score };
            }).filter(x => x.score >= 0);

            scored.sort((a, b) => b.score - a.score || a.p.name.localeCompare(b.p.name));
            return scored.map(x => x.p);
        }

        function closeDropdown() {
            custodianDropdown.hidden = true;
            custodianSearch.setAttribute('aria-expanded', 'false');
            custodianSearch.removeAttribute('aria-activedescendant');
            activeIndex = -1;
        }

        function highlightActive() {
            Array.from(custodianDropdown.children).forEach((el, i) => {
                el.classList.toggle('is-active', i === activeIndex);
            });
            const activeEl = currentMatches[activeIndex] ? custodianDropdown.querySelector('#custodian-opt-' + currentMatches[activeIndex].id) : null;
            if (activeEl) {
                activeEl.scrollIntoView({ block: 'nearest' });
                custodianSearch.setAttribute('aria-activedescendant', activeEl.id);
            } else {
                custodianSearch.removeAttribute('aria-activedescendant');
            }
        }

        function renderDropdown(matches, term) {
            currentMatches = matches.slice(0, MAX_RESULTS);
            custodianDropdown.innerHTML = '';
            activeIndex = -1;

            if (!currentMatches.length) {
                const li = document.createElement('li');
                li.className = 'custodian-ac-empty';
                li.textContent = term ? 'No matching custodians.' : 'No custodians available.';
                custodianDropdown.appendChild(li);
            } else {
                currentMatches.forEach((p) => {
                    const li = document.createElement('li');
                    li.className = 'custodian-ac-item';
                    li.id = 'custodian-opt-' + p.id;
                    li.setAttribute('role', 'option');
                    li.dataset.id = String(p.id);
                    if (String(p.id) === custodianIdInput.value) li.classList.add('is-selected');

                    const nameSpan = document.createElement('span');
                    nameSpan.className = 'custodian-ac-name';
                    nameSpan.textContent = p.name;

                    // Extra detail (position, SG) lives in a hover/focus tooltip
                    // rather than cluttering every row of the list.
                    const info = document.createElement('span');
                    info.className = 'custodian-ac-info';
                    info.tabIndex = -1;
                    info.innerHTML = '<i class="bi bi-info-circle"></i>';
                    const tip = document.createElement('span');
                    tip.className = 'custodian-ac-tooltip';
                    tip.textContent = (p.position || 'No position on file') + ' — SG ' + p.sg;
                    info.appendChild(tip);

                    li.appendChild(nameSpan);
                    li.appendChild(info);
                    custodianDropdown.appendChild(li);
                });

                if (matches.length > MAX_RESULTS) {
                    const more = document.createElement('li');
                    more.className = 'custodian-ac-more';
                    const remaining = matches.length - MAX_RESULTS;
                    more.textContent = remaining + ' more match' + (remaining === 1 ? '' : 'es') + ' — keep typing to narrow it down.';
                    custodianDropdown.appendChild(more);
                }
            }

            custodianDropdown.hidden = false;
            custodianSearch.setAttribute('aria-expanded', 'true');
        }

        function openDropdownWithCurrentInput() {
            renderDropdown(findMatches(custodianSearch.value, officeSelect ? officeSelect.value : ''), custodianSearch.value.trim());
        }

        function selectPerson(p) {
            custodianIdInput.value = String(p.id);
            custodianSearch.value = personLabel(p);
            if (officeSelect && p.office) {
                officeSelect.value = p.office;
            }
            closeDropdown();
            checkSgThreshold();
        }

        custodianSearch.addEventListener('input', function () {
            // Typing invalidates any prior selection until the user explicitly
            // picks a result again — the field never silently keeps a stale ID
            // just because the visible text happens to still resemble a name.
            if (custodianIdInput.value) custodianIdInput.value = '';
            openDropdownWithCurrentInput();
        });

        custodianSearch.addEventListener('focus', openDropdownWithCurrentInput);

        custodianSearch.addEventListener('keydown', function (e) {
            if (custodianDropdown.hidden && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
                e.preventDefault();
                openDropdownWithCurrentInput();
                return;
            }
            if (custodianDropdown.hidden) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (currentMatches.length) {
                    activeIndex = (activeIndex + 1) % currentMatches.length;
                    highlightActive();
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (currentMatches.length) {
                    activeIndex = (activeIndex - 1 + currentMatches.length) % currentMatches.length;
                    highlightActive();
                }
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0 && currentMatches[activeIndex]) {
                    e.preventDefault();
                    selectPerson(currentMatches[activeIndex]);
                }
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });

        // THE BUG FIX: bind selection on 'mousedown', not 'click'. Clicking a
        // dropdown option first fires 'mousedown' on the option, which (by
        // default) blurs the search input — and blur was what used to hide/
        // remove the dropdown, so the 'click' that followed landed on an
        // element that was already gone, and nothing ever got selected.
        // preventDefault() here stops that focus change from happening at
        // all, so the input never blurs and selection completes reliably.
        custodianDropdown.addEventListener('mousedown', function (e) {
            const item = e.target.closest('.custodian-ac-item');
            if (!item) return;
            e.preventDefault();
            const person = byId.get(item.dataset.id);
            if (person) selectPerson(person);
        });

        // Mouse-hover highlight stays in sync with keyboard highlight so the
        // "active" option is always whichever one Enter would pick.
        custodianDropdown.addEventListener('mousemove', function (e) {
            const item = e.target.closest('.custodian-ac-item');
            if (!item) return;
            const idx = currentMatches.findIndex(p => String(p.id) === item.dataset.id);
            if (idx !== -1 && idx !== activeIndex) {
                activeIndex = idx;
                highlightActive();
            }
        });

        // Click outside closes the dropdown WITHOUT touching the current
        // selection — only an explicit pick (click or Enter on an option)
        // ever changes custodian_id.
        document.addEventListener('click', function (e) {
            if (!custodianSearch.isConnected) return; // stale listener from a previous modal open
            const wrap = custodianSearch.closest('.custodian-ac-wrap');
            if (wrap && !wrap.contains(e.target)) {
                closeDropdown();
            }
        });

        if (officeSelect) {
            officeSelect.addEventListener('change', function () {
                if (!custodianDropdown.hidden) openDropdownWithCurrentInput();
                syncOfficeTypeUI();
            });
        }

        if (acquisitionCostInput) {
            acquisitionCostInput.addEventListener('input', checkSgThreshold);
        }

        checkSgThreshold();
    }

    // Correct state on load — e.g. edit mode with an external office
    // already selected, or a failed-validation re-render.
    syncOfficeTypeUI();

    // Progressive-enhancement account suggestion (public/js/asset-account-suggest.js)
    if (typeof window.initAssetAccountSuggest === 'function') {
        window.initAssetAccountSuggest(root);
    }
}

// Non-AJAX fallback: the controller can still render this same fragment
// inside the full page layout (JS-disabled / direct link access).
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('assetForm')) {
        initAssetForm(document);
    }
});