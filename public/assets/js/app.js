/* BridgeCare — main application JS */
(function () {
    'use strict';

    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    /* ---------- Sidebar toggle ---------- */
    var toggleBtn = document.getElementById('bcSidebarToggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            if (window.innerWidth < 992) {
                document.body.classList.toggle('sidebar-mobile-open');
            } else {
                document.body.classList.toggle('sidebar-collapsed');
                try {
                    localStorage.setItem('bcSidebarCollapsed', document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
                } catch (e) { }
            }
        });
    }

    try {
        if (localStorage.getItem('bcSidebarCollapsed') === '1') {
            document.body.classList.add('sidebar-collapsed');
        }
    } catch (e) { }

    var overlay = document.querySelector('.bc-sidebar-mobile-overlay');
    if (overlay) {
        overlay.addEventListener('click', function () {
            document.body.classList.remove('sidebar-mobile-open');
        });
    }

    /* ---------- Public site navbar toggle ---------- */
    var pubNavToggle = document.getElementById('bcPubNavToggle');
    if (pubNavToggle) {
        pubNavToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            document.body.classList.toggle('pub-nav-open');
        });
        document.addEventListener('click', function (e) {
            if (document.body.classList.contains('pub-nav-open') && !e.target.closest('.bc-pub-nav')) {
                document.body.classList.remove('pub-nav-open');
            }
        });
    }

    /* ---------- Dropdowns (notifications + user menu) ---------- */
    document.querySelectorAll('[data-bc-dropdown]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var target = document.getElementById(btn.getAttribute('data-bc-dropdown'));
            var wasOpen = target.classList.contains('show');
            closeAllDropdowns();
            if (!wasOpen) target.classList.add('show');
        });
    });

    function closeAllDropdowns() {
        document.querySelectorAll('.bc-dropdown.show').forEach(function (d) {
            d.classList.remove('show');
        });
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.bc-dropdown') && !e.target.closest('[data-bc-dropdown]')) {
            closeAllDropdowns();
        }
    });

    /* ---------- Global AJAX search ---------- */
    var searchInput = document.getElementById('bcGlobalSearch');
    if (searchInput) {
        var resultsBox = document.getElementById('bcSearchResults');
        var debounceTimer = null;

        searchInput.addEventListener('input', function () {
            var term = this.value.trim();
            clearTimeout(debounceTimer);

            if (term.length < 2) {
                resultsBox.classList.remove('show');
                return;
            }

            debounceTimer = setTimeout(function () {
                fetch('/admin/search?q=' + encodeURIComponent(term), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        renderSearchResults(data.results || {});
                    })
                    .catch(function () {
                        resultsBox.classList.remove('show');
                    });
            }, 250);
        });

        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') resultsBox.classList.remove('show');
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.bc-search')) {
                resultsBox.classList.remove('show');
            }
        });

        var icons = {
            bridges: 'bi-signpost-split',
            roads: 'bi-signpost',
            buildings: 'bi-buildings',
            hospitals: 'bi-hospital',
            maintenance: 'bi-wrench',
            contacts: 'bi-person-lines-fill'
        };

        var labels = {
            bridges: 'Bridges',
            roads: 'Roads',
            buildings: 'Buildings',
            hospitals: 'Hospitals',
            maintenance: 'Maintenance',
            contacts: 'Contacts'
        };

        function renderSearchResults(results) {
            var html = '';
            var empty = true;

            Object.keys(labels).forEach(function (key) {
                var items = results[key];
                if (!items || !items.length) return;
                empty = false;
                html += '<div class="bc-search-group">';
                html += '<div class="bc-search-group-title">' + labels[key] + '</div>';
                items.forEach(function (item) {
                    html += '<a class="bc-search-item" href="' + item.url + '">' +
                        '<i class="' + (icons[key] || 'bi-circle') + '"></i>' +
                        '<div><strong>' + escapeHtml(item.title) + '</strong>' +
                        (item.subtitle ? '<small>' + escapeHtml(item.subtitle) + '</small>' : '') +
                        '</div></a>';
                });
                html += '</div>';
            });

            if (empty) {
                html = '<div class="bc-search-group"><div style="padding:18px;text-align:center;color:#94a3b8;font-size:13px;">No results found</div></div>';
            }

            resultsBox.innerHTML = html;
            resultsBox.classList.add('show');
        }
    }

    /* ---------- Toasts ---------- */
    window.bcToast = function (message, type) {
        type = type || 'success';
        var container = document.getElementById('bcToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'bcToastContainer';
            container.className = 'bc-toast-container';
            document.body.appendChild(container);
        }

        var icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
        var toast = document.createElement('div');
        toast.className = 'bc-toast ' + type;
        toast.innerHTML = '<i class="bi ' + icon + '"></i><div><strong>' +
            (type === 'success' ? 'Success' : 'Notice') +
            '</strong><p>' + escapeHtml(message) + '</p></div>';
        container.appendChild(toast);

        setTimeout(function () {
            toast.classList.add('hide');
            setTimeout(function () { toast.remove(); }, 350);
        }, 4200);
    };

    /* ---------- Confirm modal ---------- */
    window.bcConfirm = function (options, onConfirm) {
        options = options || {};
        var backdrop = document.getElementById('bcConfirmBackdrop');

        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.id = 'bcConfirmBackdrop';
            backdrop.className = 'bc-modal-backdrop';
            backdrop.innerHTML =
                '<div class="bc-modal" style="text-align:center;">' +
                '<div class="bc-confirm-icon"><i class="bi bi-trash-fill"></i></div>' +
                '<div class="bc-modal-body" style="text-align:center;">' +
                '<h3 id="bcConfirmTitle" style="font-size:17px;color:#1e293b;margin-bottom:8px;">Are you sure?</h3>' +
                '<p id="bcConfirmMessage" style="margin:0;">This action cannot be undone.</p>' +
                '</div>' +
                '<div class="bc-modal-footer" style="justify-content:center;">' +
                '<button type="button" class="btn-bc btn-bc-outline" id="bcConfirmCancel">Cancel</button>' +
                '<button type="button" class="btn-bc btn-bc-danger" id="bcConfirmOk">Delete</button>' +
                '</div></div>';
            document.body.appendChild(backdrop);

            backdrop.addEventListener('click', function (e) {
                if (e.target === backdrop) closeConfirm();
            });
            document.getElementById('bcConfirmCancel').addEventListener('click', closeConfirm);
        }

        document.getElementById('bcConfirmTitle').textContent = options.title || 'Are you sure?';
        document.getElementById('bcConfirmMessage').textContent = options.message || 'This action cannot be undone.';
        var okBtn = document.getElementById('bcConfirmOk');
        okBtn.textContent = options.confirmText || 'Delete';
        okBtn.className = 'btn-bc ' + (options.danger === false ? 'btn-bc-primary' : 'btn-bc-danger');

        okBtn.onclick = function () {
            closeConfirm();
            if (typeof onConfirm === 'function') onConfirm();
        };

        backdrop.classList.add('show');

        function closeConfirm() {
            backdrop.classList.remove('show');
            okBtn.onclick = null;
        }
    };

    /* ---------- Delete form buttons ---------- */
    document.querySelectorAll('[data-bc-delete]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            bcConfirm({
                title: 'Delete record?',
                message: btn.getAttribute('data-message') || 'This record will be permanently deleted.',
                confirmText: 'Delete'
            }, function () {
                var form = document.getElementById(btn.getAttribute('data-bc-delete'));
                if (form) form.submit();
            });
        });
    });

    /* ---------- Generic confirm buttons (approve / award / close) ---------- */
    document.querySelectorAll('[data-bc-confirm]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var form = btn.closest('form');
            bcConfirm({
                title: btn.getAttribute('data-bc-confirm-title') || 'Please confirm',
                message: btn.getAttribute('data-bc-confirm') || 'Are you sure you want to continue?',
                confirmText: btn.getAttribute('data-bc-confirm-text') || 'Confirm',
                danger: btn.getAttribute('data-bc-confirm-danger') !== 'no'
            }, function () {
                if (form) form.submit();
            });
        });
    });

    /* ---------- Image upload preview (single) ---------- */
    document.querySelectorAll('.bc-image-drop').forEach(function (drop) {
        var input = document.getElementById(drop.getAttribute('data-input'));
        var preview = document.getElementById(drop.getAttribute('data-preview'));
        if (!input || !preview) return;

        drop.addEventListener('click', function () { input.click(); });

        input.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="preview">' +
                    '<span class="bc-remove-img" title="Remove"><i class="bi bi-x"></i></span>';
                preview.classList.add('show');
                preview.querySelector('.bc-remove-img').addEventListener('click', function () {
                    input.value = '';
                    preview.classList.remove('show');
                });
            };
            reader.readAsDataURL(file);
        });
    });

    /* ---------- Gallery upload preview (multiple) ---------- */
    var galleryInput = document.getElementById('bcGalleryInput');
    if (galleryInput) {
        var galleryBox = document.getElementById('bcGalleryPreviews');
        galleryInput.addEventListener('change', function () {
            Array.from(this.files).forEach(function (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var item = document.createElement('div');
                    item.className = 'item';
                    item.innerHTML = '<img src="' + e.target.result + '" alt="gallery"><span class="rm"><i class="bi bi-x"></i></span>';
                    item.querySelector('.rm').addEventListener('click', function () { item.remove(); });
                    galleryBox.appendChild(item);
                };
                reader.readAsDataURL(file);
            });
        });
    }

    /* ---------- Before / After slider ---------- */
    document.querySelectorAll('.bc-before-after').forEach(function (box) {
        var handle = box.querySelector('.ba-handle');
        var after = box.querySelector('.ba-after');
        if (!handle || !after) return;

        var dragging = false;

        function setPosition(clientX) {
            var rect = box.getBoundingClientRect();
            var pct = ((clientX - rect.left) / rect.width) * 100;
            pct = Math.max(2, Math.min(98, pct));
            handle.style.left = pct + '%';
            after.style.clipPath = 'inset(0 0 0 ' + pct + '%)';
        }

        handle.addEventListener('mousedown', function (e) {
            dragging = true;
            e.preventDefault();
        });
        document.addEventListener('mousemove', function (e) {
            if (dragging) setPosition(e.clientX);
        });
        document.addEventListener('mouseup', function () { dragging = false; });

        handle.addEventListener('touchstart', function (e) { dragging = true; }, { passive: true });
        document.addEventListener('touchmove', function (e) {
            if (dragging && e.touches[0]) setPosition(e.touches[0].clientX);
        }, { passive: true });
        document.addEventListener('touchend', function () { dragging = false; });
    });

    /* ---------- Login password toggle ---------- */
    var passToggle = document.getElementById('bcPassToggle');
    if (passToggle) {
        passToggle.addEventListener('click', function () {
            var input = document.getElementById('password');
            var showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            passToggle.innerHTML = showing ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
        });
    }

    /* ---------- Form submit loading state ---------- */
    document.querySelectorAll('form[data-bc-loading]').forEach(function (form) {
        form.addEventListener('submit', function () {
            var btn = form.querySelector('button[type="submit"]');
            if (!btn) return;
            var original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="bc-spinner"></span> Please wait...';
            form.addEventListener('submit', function restore() {
                form.removeEventListener('submit', restore);
                btn.disabled = false;
                btn.innerHTML = original;
            }, { once: true });
        });
    });

    /* ---------- Sidebar submenu toggles (e.g. expandable All Infrastructure) ---------- */
    document.querySelectorAll('[data-bc-submenu]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var target = document.getElementById(btn.getAttribute('data-bc-submenu'));
            if (!target) return;
            var isOpen = target.classList.toggle('open');
            btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            btn.classList.toggle('active', isOpen);
        });
    });

    /* ---------- Helpers ---------- */
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }
})();
