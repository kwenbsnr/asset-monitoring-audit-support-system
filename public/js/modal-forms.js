/**
 * ModalForms — generic "load a form into the shared modal, submit it via
 * AJAX, show errors inline, never navigate away" controller.
 *
 * Used by every Add/Edit form in the app (Assets, Custody, Employees,
 * Users) so the trigger→load→validate→save→refresh flow is IDENTICAL
 * everywhere, including the open/close animation (handled entirely by
 * NiaModal / .modal-overlay — see public/js/modal.js and
 * public/css/style.css). One implementation, one place to fix bugs.
 *
 * Markup contract for triggers (works from ANY page, including the
 * sidebar in main.php):
 *   <button type="button" data-form-modal
 *           data-form-url="index.php?page=assets&sub=add"
 *           data-form-title="Register Asset"
 *           data-form-init="initAssetForm">...</button>
 *   data-form-init is optional — the name of a global init function
 *   (defined in e.g. public/js/asset-form.js, loaded normally via a
 *   <script> tag in main.php) to call once the fragment is injected.
 *   It's required for forms with their own JS behavior, because
 *   innerHTML does NOT execute <script> tags in injected markup — any
 *   inline <script> in a fetched fragment is silently inert.
 *
 * Markup contract for the shared shell (lives once in main.php):
 *   <div id="formModal" class="modal-overlay">
 *     <div class="modal-panel modal-panel-xl">
 *       <div class="modal-header">
 *         <h5 id="formModalTitle">...</h5>
 *         <button data-modal-close class="modal-close">&times;</button>
 *       </div>
 *       <div class="modal-body" id="formModalBody">...</div>
 *     </div>
 *   </div>
 *
 * Contract for the fetched fragment (e.g. Views/assets/form.php when
 * requested via AJAX): a single <form> element, ending in a Cancel
 * button with [data-modal-close] and a submit button. No outer
 * card-panel/page chrome — the modal shell already provides that.
 *
 * Contract for save() endpoints when called via AJAX
 * (X-Requested-With: XMLHttpRequest):
 *   Failure: JSON { success: false, errors: ["...", ...] }
 *   Success: JSON { success: true, message: "...", reload: true }
 * On success the page does a plain location.reload() — the existing
 * $_SESSION['flash'] mechanism in main.php then shows the usual toast.
 * This intentionally keeps the browser on the SAME url throughout
 * (open → fix errors → save), which is the whole point: no more
 * accidental full-page navigation to a form page that exposes sidebar
 * controls a role shouldn't be reaching mid-task.
 */
(function (window, document) {
    'use strict';

    var MODAL_ID = 'formModal';

    function modalEls() {
        return {
            overlay: document.getElementById(MODAL_ID),
            title: document.getElementById('formModalTitle'),
            body: document.getElementById('formModalBody'),
        };
    }

    function showLoading(body) {
        body.innerHTML = '<div class="text-center py-10">' +
            '<div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>' +
            '<p class="mt-2 text-gray-500">Loading form...</p></div>';
    }

    function showLoadError(body, message) {
        body.innerHTML = '<div class="alert-app alert-app-danger">' +
            '<span>' + escapeHtml(message) + '</span></div>';
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        var div = document.createElement('div');
        div.textContent = String(text);
        return div.innerHTML;
    }

    /**
     * Opens the shared modal and loads `url` into it via AJAX.
     * @param {string} url    GET endpoint returning the form fragment.
     * @param {string} title  Modal header title.
     * @param {string} [initFnName]  Name of a global function (e.g.
     *   "initAssetForm") to call once the fragment is injected — forms
     *   with their own JS behavior (SG-threshold checks, dependent
     *   dropdowns, etc.) expose one of these. Needed because innerHTML
     *   does NOT execute <script> tags in the injected markup, so a
     *   form's behavior must be (re)bound explicitly, not inline.
     */
    function openForm(url, title, initFnName) {
        var els = modalEls();
        if (!els.overlay) return;

        els.title.textContent = title || 'Form';
        showLoading(els.body);
        NiaModal.open(MODAL_ID);

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) {
                if (!r.ok) throw new Error('Server returned ' + r.status);
                return r.text();
            })
            .then(function (html) {
                els.body.innerHTML = html;
                bindForm(els.body);
                if (initFnName && typeof window[initFnName] === 'function') {
                    window[initFnName](els.body);
                }
                // Newly injected [data-modal-close] buttons (Cancel) need
                // NiaModal to re-scan for them — see public/js/modal.js.
                if (window.NiaModal && typeof NiaModal.rescan === 'function') NiaModal.rescan();
            })
            .catch(function (err) {
                showLoadError(els.body, 'Failed to load form: ' + err.message);
            });
    }

    /**
     * Wires the AJAX submit handler onto the first <form> in `container`.
     */
    function bindForm(container) {
        var form = container.querySelector('form');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            submitForm(form);
        });
    }

    function clearFormErrors(container) {
        var existing = container.querySelector('.modal-form-errors');
        if (existing) existing.remove();
    }

    function showFormErrors(container, form, errors) {
        clearFormErrors(container);
        var box = document.createElement('div');
        box.className = 'alert-app alert-app-danger alert-app-top modal-form-errors';
        var list = document.createElement('ul');
        list.className = 'list-disc list-inside';
        (errors || ['Failed to save. Please check the form and try again.']).forEach(function (msg) {
            var li = document.createElement('li');
            li.textContent = msg;
            list.appendChild(li);
        });
        box.appendChild(list);
        form.insertBefore(box, form.firstChild);
        box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function setSaving(form, saving) {
        form.querySelectorAll('button[type="submit"]').forEach(function (btn) {
            btn.disabled = saving;
            if (saving) {
                btn.dataset.originalText = btn.dataset.originalText || btn.innerHTML;
                btn.innerHTML = '<span class="inline-block animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white align-[-2px] mr-1"></span> Saving...';
            } else if (btn.dataset.originalText) {
                btn.innerHTML = btn.dataset.originalText;
            }
        });
    }

    function submitForm(form) {
        var els = modalEls();
        clearFormErrors(els.body);
        setSaving(form, true);

        fetch(form.getAttribute('action'), {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form),
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                setSaving(form, false);
                if (!res.success) {
                    showFormErrors(els.body, form, res.errors);
                    return;
                }
                NiaModal.close(MODAL_ID);
                if (res.reload !== false) {
                    window.location.reload();
                }
            })
            .catch(function (err) {
                setSaving(form, false);
                showFormErrors(els.body, form, ['Unexpected error: ' + err.message]);
            });
    }

    function init() {
        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('[data-form-modal]');
            if (!trigger) return;
            e.preventDefault();
            var url = trigger.getAttribute('data-form-url');
            var title = trigger.getAttribute('data-form-title') || '';
            var initFn = trigger.getAttribute('data-form-init') || null;
            if (!url) return;
            openForm(url, title, initFn);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.ModalForms = { open: openForm };
})(window, document);