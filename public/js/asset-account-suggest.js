/**
 * asset-account-suggest.js
 *
 * Progressive-enhancement feature for Views/assets/form.php.
 * As the Asset Manager types the Asset Name, suggests the most likely
 * Asset Account (from the fixed 12-account list in `asset_accounts`)
 * and auto-fills the Account dropdown if it's still blank.
 *
 * Design notes:
 * - Code-based, single source of truth for account CODES lives in the
 *   dictionary below; account NAMES are never duplicated here — they're
 *   read straight from the server-rendered <option> elements, so if the
 *   DB list ever changes, this script never goes stale on labels.
 * - Pure client-side, no network calls: cannot fail due to DB/API issues.
 * - Every DOM lookup is null-checked. If the expected fields aren't on
 *   the page, the script quietly does nothing — it can never break the
 *   form or block submission. Server-side validation in
 *   AssetController::save() remains authoritative either way.
 * - Never overwrites a value the user (or an edit-mode prefill) already
 *   set — it only offers a one-click "Apply" once a value exists.
 *
 * TUNING: The keyword lists below are a starting point based on common
 * COA/UACS equipment classification and NIA's line of work (irrigation).
 * Some equipment names are genuinely ambiguous (e.g. "water pump" could
 * be Agricultural/Forestry or Other Machinery; "photocopier" could be
 * Office Equipment or Printing Equipment). 
 */
(function () {
    'use strict';

    // Map: account_code -> list of keywords/phrases that suggest it.
    // Longer/more specific phrases win ties, so
    // it's fine to list both a specific phrase and a generic word.
    var ACCOUNT_KEYWORDS = [
        {
            code: '05-030', // Information and Communications Technology Equipment
            keywords: [
                'laptop', 'notebook computer', 'notebook', 'desktop computer', 'desktop',
                'computer', 'cpu unit', 'cpu', 'all in one pc', 'workstation',
                'monitor', 'lcd monitor', 'led monitor', 'server', 'network switch',
                'switch', 'router', 'modem', 'access point', 'wifi router',
                'tablet', 'ipad', 'external hard drive', 'hard drive', 'ssd',
                'flash drive', 'usb drive', 'ups', 'uninterruptible power supply',
                'webcam', 'document scanner', 'scanner', 'multimedia projector',
                'projector', 'keyboard', 'mouse', 'graphics card', 'nas', 'firewall'
            ]
        },
        {
            code: '05-120', // Printing Equipment
            keywords: [
                'printer', 'inkjet printer', 'laser printer', 'dot matrix printer',
                'plotter', 'printing press', 'offset printing machine',
                'riso machine', 'risograph', 'duplicator', 'label printer'
            ]
        },
        {
            code: '05-020', // Office Equipment
            keywords: [
                'photocopier', 'copier', 'photocopy machine', 'fax machine', 'fax',
                'shredder', 'paper shredder', 'typewriter', 'safe', 'vault',
                'time clock', 'biometric', 'biometric scanner', 'folding machine',
                'binding machine', 'laminator', 'paper cutter', 'calculator',
                'cash register', 'bundy clock'
            ]
        },
        {
            code: '05-040', // Agricultural and Forestry Equipment
            keywords: [
                'tractor', 'hand tractor', 'plow', 'harvester', 'planter',
                'sprayer', 'cultivator', 'mower', 'grass cutter', 'chainsaw',
                'brush cutter', 'farm equipment', 'irrigation pump', 'sprinkler',
                'threshing machine', 'thresher', 'rice mill', 'seeder'
            ]
        },
        {
            code: '05-070', // Communication Equipment
            keywords: [
                'two way radio', 'handheld radio', 'radio transceiver', 'radio',
                'walkie talkie', 'base radio', 'telephone', 'landline',
                'pabx', 'intercom', 'satellite phone', 'cctv camera', 'cctv',
                'ip camera', 'dvr', 'nvr'
            ]
        },
        {
            code: '05-080', // Construction and Heavy Equipment
            keywords: [
                'excavator', 'backhoe', 'bulldozer', 'crane', 'forklift',
                'payloader', 'wheel loader', 'road grader', 'grader',
                'road roller', 'compactor', 'concrete mixer', 'welding machine',
                'air compressor', 'jackhammer', 'dump truck', 'boom truck'
            ]
        },
        {
            code: '05-140', // Technical and Scientific Equipment
            keywords: [
                'microscope', 'oscilloscope', 'multimeter', 'gps unit', 'gps',
                'total station', 'theodolite', 'survey equipment',
                'laboratory equipment', 'weighing scale', 'precision scale',
                'spectrometer', 'water quality meter', 'flow meter',
                'current meter', 'rain gauge', 'water level recorder',
                'water level logger', 'barometer', 'anemometer'
            ]
        },
        {
            code: '05-170', // Electrical Equipment
            keywords: [
                'generator set', 'genset', 'generator', 'voltage regulator',
                'transformer', 'electrical panel', 'circuit breaker',
                'stabilizer', 'air conditioner', 'aircon', 'air conditioning unit',
                'exhaust fan', 'electric fan'
            ]
        },
        {
            code: '05-990', // Other Machinery and Equipment
            keywords: [
                'water pump', 'pump', 'sewing machine', 'washing machine',
                'refrigerator', 'freezer', 'water dispenser', 'vacuum cleaner',
                'machine', 'equipment'
            ]
        },
        {
            code: '06-010', // Motor Vehicles
            keywords: [
                'service vehicle', 'sedan', 'pickup truck', 'pickup', 'van',
                'suv', 'motorcycle', 'motorbike', 'bus', 'truck', 'car'
            ]
        },
        {
            code: '06-990', // Other Transportation Equipment
            keywords: [
                'motor boat', 'pump boat', 'banca', 'boat', 'trailer',
                'bicycle', 'atv', 'all terrain vehicle', 'watercraft'
            ]
        },
        {
            code: '07-010', // Furniture and Fixtures
            keywords: [
                'office chair', 'chair', 'office table', 'table', 'desk',
                'filing cabinet', 'cabinet', 'shelf', 'shelving', 'bookshelf',
                'locker', 'sofa', 'bench', 'partition', 'cubicle', 'wardrobe',
                'conference table', 'workstation table'
            ]
        }
    ];

    function escapeRegex(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // Pick the best-matching account code for a given asset name.
    // Longer keyword matches win (more specific beats generic), and
    // matches are whole-word/whole-phrase to avoid false positives).
    function suggestAccountCode(assetName) {
        if (!assetName || !assetName.trim()) return null;
        var best = null;
        for (var i = 0; i < ACCOUNT_KEYWORDS.length; i++) {
            var entry = ACCOUNT_KEYWORDS[i];
            for (var j = 0; j < entry.keywords.length; j++) {
                var kw = entry.keywords[j];
                var re = new RegExp('\\b' + escapeRegex(kw) + '\\b', 'i');
                if (re.test(assetName)) {
                    if (!best || kw.length > best.matchLength) {
                        best = { code: entry.code, matchLength: kw.length, keyword: kw };
                    }
                }
            }
        }
        return best;
    }

    function debounce(fn, wait) {
        var t = null;
        return function () {
            var args = arguments, ctx = this;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(ctx, args); }, wait);
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        initAssetAccountSuggest(document);
    });

    // Exposed so public/js/asset-form.js can (re)run this after
    // modal-forms.js injects the Asset form fragment into the shared
    // modal — DOMContentLoaded only fires once per real page load, not
    // after an AJAX injection, so a demand-callable entry point is
    // needed for the modal case. `root` scopes every lookup so calling
    // this again after a fresh fragment load never double-binds
    // listeners from a previous modal open.
    window.initAssetAccountSuggest = function (root) {
        root = root || document;
        var nameInput = root.querySelector('#asset_name');
        var accountSelect = root.querySelector('#asset_accounts_id');
        if (!nameInput || !accountSelect) return; // form markup not as expected — bail out quietly

        // Build code -> <option> map from the server-rendered dropdown.
        // Account names/labels are never duplicated in this script.
        var codeToOption = {};
        Array.prototype.forEach.call(accountSelect.options, function (opt) {
            var code = opt.getAttribute('data-code');
            if (code) codeToOption[code] = opt;
        });

        // Hint element (created if the view didn't already provide one).
        var hint = document.getElementById('accountSuggestionHint');
        if (!hint) {
            hint = document.createElement('p');
            hint.id = 'accountSuggestionHint';
            hint.className = 'mt-1 text-xs text-gray-500';
            nameInput.insertAdjacentElement('afterend', hint);
        }

        var applyingSuggestion = false; // true only while this script sets the select itself
        var manualOverride = false;     // true once the user has explicitly picked an account

        accountSelect.addEventListener('change', function () {
            if (applyingSuggestion) {
                applyingSuggestion = false;
                return;
            }
            manualOverride = accountSelect.value !== '';
        });

        function clearHint() {
            hint.textContent = '';
        }

        function showApplyHint(match) {
            var option = codeToOption[match.code];
            if (!option) { clearHint(); return; } // code not present in current dropdown — no-op

            hint.textContent = '';
            var label = document.createElement('span');
            label.textContent = 'Suggested account: ' + option.textContent.trim() + '. ';
            hint.appendChild(label);

            var applyBtn = document.createElement('button');
            applyBtn.type = 'button';
            applyBtn.className = 'text-green-700 hover:text-green-800 underline font-medium';
            applyBtn.textContent = 'Apply suggestion';
            applyBtn.addEventListener('click', function () {
                applySuggestion(match);
            });
            hint.appendChild(applyBtn);
        }

        function showAutoAppliedHint(match) {
            var option = codeToOption[match.code];
            if (!option) return;
            hint.textContent = 'Auto-suggested: ' + option.textContent.trim() + ' — change the dropdown anytime if this isn\u2019t right.';
        }

        function applySuggestion(match) {
            var option = codeToOption[match.code];
            if (!option) return;
            applyingSuggestion = true;
            accountSelect.value = option.value;
            accountSelect.dispatchEvent(new Event('change'));
            manualOverride = false;
            clearHint();
        }

        var handleInput = debounce(function () {
            var match = suggestAccountCode(nameInput.value);

            if (!match) { clearHint(); return; }

            var currentValue = accountSelect.value;

            if (currentValue === '' && !manualOverride) {
                // Nothing selected yet — safe to auto-fill. Still 100% editable.
                var option = codeToOption[match.code];
                if (!option) { clearHint(); return; }
                applyingSuggestion = true;
                accountSelect.value = option.value;
                accountSelect.dispatchEvent(new Event('change'));
                manualOverride = false; // this was us, not the user
                showAutoAppliedHint(match);
                return;
            }

            var selectedOption = codeToOption[match.code];
            if (selectedOption && accountSelect.value === selectedOption.value) {
                // Already matches the suggestion — nothing to show, avoid clutter.
                clearHint();
                return;
            }

            // A value already exists (edit mode, prior manual choice, or
            // repopulated after a validation error) — never overwrite it
            // automatically.  offer a one-click way to apply instead.
            showApplyHint(match);
        }, 300);

        nameInput.addEventListener('input', handleInput);

        // If the name field is prefilled on load (e.g. after a validation
        // error round-trip), run once so the hint reflects that immediately.
        if (nameInput.value.trim()) handleInput();
    };
})();/**
 * asset-account-suggest.js
 *
 * Progressive-enhancement feature for Views/assets/form.php.
 * As the Asset Manager types the Asset Name, suggests the most likely
 * Asset Account (from the fixed 12-account list in `asset_accounts`)
 * and auto-fills the Account dropdown if it's still blank.
 *
 * Design notes:
 * - Code-based, single source of truth for account CODES lives in the
 *   dictionary below; account NAMES are never duplicated here — they're
 *   read straight from the server-rendered <option> elements, so if the
 *   DB list ever changes, this script never goes stale on labels.
 * - Pure client-side, no network calls: cannot fail due to DB/API issues.
 * - Every DOM lookup is null-checked. If the expected fields aren't on
 *   the page, the script quietly does nothing — it can never break the
 *   form or block submission. Server-side validation in
 *   AssetController::save() remains authoritative either way.
 * - Never overwrites a value the user (or an edit-mode prefill) already
 *   set — it only offers a one-click "Apply" once a value exists.
 *
 * TUNING: The keyword lists below are a starting point based on common
 * COA/UACS equipment classification and NIA's line of work (irrigation).
 * Some equipment names are genuinely ambiguous (e.g. "water pump" could
 * be Agricultural/Forestry or Other Machinery; "photocopier" could be
 * Office Equipment or Printing Equipment). 
 */
(function () {
    'use strict';

    // Map: account_code -> list of keywords/phrases that suggest it.
    // Longer/more specific phrases win ties, so
    // it's fine to list both a specific phrase and a generic word.
    var ACCOUNT_KEYWORDS = [
        {
            code: '05-030', // Information and Communications Technology Equipment
            keywords: [
                'laptop', 'notebook computer', 'notebook', 'desktop computer', 'desktop',
                'computer', 'cpu unit', 'cpu', 'all in one pc', 'workstation',
                'monitor', 'lcd monitor', 'led monitor', 'server', 'network switch',
                'switch', 'router', 'modem', 'access point', 'wifi router',
                'tablet', 'ipad', 'external hard drive', 'hard drive', 'ssd',
                'flash drive', 'usb drive', 'ups', 'uninterruptible power supply',
                'webcam', 'document scanner', 'scanner', 'multimedia projector',
                'projector', 'keyboard', 'mouse', 'graphics card', 'nas', 'firewall'
            ]
        },
        {
            code: '05-120', // Printing Equipment
            keywords: [
                'printer', 'inkjet printer', 'laser printer', 'dot matrix printer',
                'plotter', 'printing press', 'offset printing machine',
                'riso machine', 'risograph', 'duplicator', 'label printer'
            ]
        },
        {
            code: '05-020', // Office Equipment
            keywords: [
                'photocopier', 'copier', 'photocopy machine', 'fax machine', 'fax',
                'shredder', 'paper shredder', 'typewriter', 'safe', 'vault',
                'time clock', 'biometric', 'biometric scanner', 'folding machine',
                'binding machine', 'laminator', 'paper cutter', 'calculator',
                'cash register', 'bundy clock'
            ]
        },
        {
            code: '05-040', // Agricultural and Forestry Equipment
            keywords: [
                'tractor', 'hand tractor', 'plow', 'harvester', 'planter',
                'sprayer', 'cultivator', 'mower', 'grass cutter', 'chainsaw',
                'brush cutter', 'farm equipment', 'irrigation pump', 'sprinkler',
                'threshing machine', 'thresher', 'rice mill', 'seeder'
            ]
        },
        {
            code: '05-070', // Communication Equipment
            keywords: [
                'two way radio', 'handheld radio', 'radio transceiver', 'radio',
                'walkie talkie', 'base radio', 'telephone', 'landline',
                'pabx', 'intercom', 'satellite phone', 'cctv camera', 'cctv',
                'ip camera', 'dvr', 'nvr'
            ]
        },
        {
            code: '05-080', // Construction and Heavy Equipment
            keywords: [
                'excavator', 'backhoe', 'bulldozer', 'crane', 'forklift',
                'payloader', 'wheel loader', 'road grader', 'grader',
                'road roller', 'compactor', 'concrete mixer', 'welding machine',
                'air compressor', 'jackhammer', 'dump truck', 'boom truck'
            ]
        },
        {
            code: '05-140', // Technical and Scientific Equipment
            keywords: [
                'microscope', 'oscilloscope', 'multimeter', 'gps unit', 'gps',
                'total station', 'theodolite', 'survey equipment',
                'laboratory equipment', 'weighing scale', 'precision scale',
                'spectrometer', 'water quality meter', 'flow meter',
                'current meter', 'rain gauge', 'water level recorder',
                'water level logger', 'barometer', 'anemometer'
            ]
        },
        {
            code: '05-170', // Electrical Equipment
            keywords: [
                'generator set', 'genset', 'generator', 'voltage regulator',
                'transformer', 'electrical panel', 'circuit breaker',
                'stabilizer', 'air conditioner', 'aircon', 'air conditioning unit',
                'exhaust fan', 'electric fan'
            ]
        },
        {
            code: '05-990', // Other Machinery and Equipment
            keywords: [
                'water pump', 'pump', 'sewing machine', 'washing machine',
                'refrigerator', 'freezer', 'water dispenser', 'vacuum cleaner',
                'machine', 'equipment'
            ]
        },
        {
            code: '06-010', // Motor Vehicles
            keywords: [
                'service vehicle', 'sedan', 'pickup truck', 'pickup', 'van',
                'suv', 'motorcycle', 'motorbike', 'bus', 'truck', 'car'
            ]
        },
        {
            code: '06-990', // Other Transportation Equipment
            keywords: [
                'motor boat', 'pump boat', 'banca', 'boat', 'trailer',
                'bicycle', 'atv', 'all terrain vehicle', 'watercraft'
            ]
        },
        {
            code: '07-010', // Furniture and Fixtures
            keywords: [
                'office chair', 'chair', 'office table', 'table', 'desk',
                'filing cabinet', 'cabinet', 'shelf', 'shelving', 'bookshelf',
                'locker', 'sofa', 'bench', 'partition', 'cubicle', 'wardrobe',
                'conference table', 'workstation table'
            ]
        }
    ];

    function escapeRegex(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // Pick the best-matching account code for a given asset name.
    // Longer keyword matches win (more specific beats generic), and
    // matches are whole-word/whole-phrase to avoid false positives).
    function suggestAccountCode(assetName) {
        if (!assetName || !assetName.trim()) return null;
        var best = null;
        for (var i = 0; i < ACCOUNT_KEYWORDS.length; i++) {
            var entry = ACCOUNT_KEYWORDS[i];
            for (var j = 0; j < entry.keywords.length; j++) {
                var kw = entry.keywords[j];
                var re = new RegExp('\\b' + escapeRegex(kw) + '\\b', 'i');
                if (re.test(assetName)) {
                    if (!best || kw.length > best.matchLength) {
                        best = { code: entry.code, matchLength: kw.length, keyword: kw };
                    }
                }
            }
        }
        return best;
    }

    function debounce(fn, wait) {
        var t = null;
        return function () {
            var args = arguments, ctx = this;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(ctx, args); }, wait);
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        initAssetAccountSuggest(document);
    });

    // Exposed so public/js/asset-form.js can (re)run this after
    // modal-forms.js injects the Asset form fragment into the shared
    // modal — DOMContentLoaded only fires once per real page load, not
    // after an AJAX injection, so a demand-callable entry point is
    // needed for the modal case. `root` scopes every lookup so calling
    // this again after a fresh fragment load never double-binds
    // listeners from a previous modal open.
    window.initAssetAccountSuggest = function (root) {
        root = root || document;
        var nameInput = root.querySelector('#asset_name');
        var accountSelect = root.querySelector('#asset_accounts_id');
        if (!nameInput || !accountSelect) return; // form markup not as expected — bail out quietly

        // Build code -> <option> map from the server-rendered dropdown.
        // Account names/labels are never duplicated in this script.
        var codeToOption = {};
        Array.prototype.forEach.call(accountSelect.options, function (opt) {
            var code = opt.getAttribute('data-code');
            if (code) codeToOption[code] = opt;
        });

        // Hint element (created if the view didn't already provide one).
        var hint = document.getElementById('accountSuggestionHint');
        if (!hint) {
            hint = document.createElement('p');
            hint.id = 'accountSuggestionHint';
            hint.className = 'mt-1 text-xs text-gray-500';
            nameInput.insertAdjacentElement('afterend', hint);
        }

        var applyingSuggestion = false; // true only while this script sets the select itself
        var manualOverride = false;     // true once the user has explicitly picked an account

        accountSelect.addEventListener('change', function () {
            if (applyingSuggestion) {
                applyingSuggestion = false;
                return;
            }
            manualOverride = accountSelect.value !== '';
        });

        function clearHint() {
            hint.textContent = '';
        }

        function showApplyHint(match) {
            var option = codeToOption[match.code];
            if (!option) { clearHint(); return; } // code not present in current dropdown — no-op

            hint.textContent = '';
            var label = document.createElement('span');
            label.textContent = 'Suggested account: ' + option.textContent.trim() + '. ';
            hint.appendChild(label);

            var applyBtn = document.createElement('button');
            applyBtn.type = 'button';
            applyBtn.className = 'text-green-700 hover:text-green-800 underline font-medium';
            applyBtn.textContent = 'Apply suggestion';
            applyBtn.addEventListener('click', function () {
                applySuggestion(match);
            });
            hint.appendChild(applyBtn);
        }

        function showAutoAppliedHint(match) {
            var option = codeToOption[match.code];
            if (!option) return;
            hint.textContent = 'Auto-suggested: ' + option.textContent.trim() + ' — change the dropdown anytime if this isn\u2019t right.';
        }

        function applySuggestion(match) {
            var option = codeToOption[match.code];
            if (!option) return;
            applyingSuggestion = true;
            accountSelect.value = option.value;
            accountSelect.dispatchEvent(new Event('change'));
            manualOverride = false;
            clearHint();
        }

        var handleInput = debounce(function () {
            var match = suggestAccountCode(nameInput.value);

            if (!match) { clearHint(); return; }

            var currentValue = accountSelect.value;

            if (currentValue === '' && !manualOverride) {
                // Nothing selected yet — safe to auto-fill. Still 100% editable.
                var option = codeToOption[match.code];
                if (!option) { clearHint(); return; }
                applyingSuggestion = true;
                accountSelect.value = option.value;
                accountSelect.dispatchEvent(new Event('change'));
                manualOverride = false; // this was us, not the user
                showAutoAppliedHint(match);
                return;
            }

            var selectedOption = codeToOption[match.code];
            if (selectedOption && accountSelect.value === selectedOption.value) {
                // Already matches the suggestion — nothing to show, avoid clutter.
                clearHint();
                return;
            }

            // A value already exists (edit mode, prior manual choice, or
            // repopulated after a validation error) — never overwrite it
            // automatically.  offer a one-click way to apply instead.
            showApplyHint(match);
        }, 300);

        nameInput.addEventListener('input', handleInput);

        // If the name field is prefilled on load (e.g. after a validation
        // error round-trip), run once so the hint reflects that immediately.
        if (nameInput.value.trim()) handleInput();
    };
})();