document.addEventListener('DOMContentLoaded', function () {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const sidebar = document.getElementById('sidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');

    function setSidebarOpen(isOpen) {
        if (!sidebar) {
            return;
        }

        sidebar.classList.toggle('show', isOpen);

        if (sidebarBackdrop) {
            sidebarBackdrop.classList.toggle('show', isOpen);
        }

        document.body.classList.toggle('sidebar-open', isOpen);
    }

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            setSidebarOpen(!sidebar.classList.contains('show'));
        });
    }

    if (mobileMenuButton && sidebar) {
        mobileMenuButton.addEventListener('click', function () {
            setSidebarOpen(!sidebar.classList.contains('show'));
        });
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', function () {
            setSidebarOpen(false);
        });
    }

    document.querySelectorAll('.sidebar-nav .nav-link, .sidebar-footer .nav-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.matchMedia('(max-width: 768px)').matches) {
                setSidebarOpen(false);
            }
        });
    });

    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });

    // Confirm delete actions
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // File upload drag and drop
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('videoFile');

    if (uploadZone && fileInput) {
        uploadZone.addEventListener('click', function () { fileInput.click(); });

        uploadZone.addEventListener('dragover', function (e) {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', function () {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', function (e) {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            fileInput.files = e.dataTransfer.files;
            updateFileName();
        });

        fileInput.addEventListener('change', updateFileName);

        function updateFileName() {
            const nameEl = document.getElementById('fileName');
            if (nameEl && fileInput.files.length > 0) {
                nameEl.textContent = fileInput.files[0].name;
            }
        }
    }

    // Geolocation for arrival confirmation
    const confirmArrivalBtn = document.getElementById('confirmArrival');
    if (confirmArrivalBtn) {
        confirmArrivalBtn.addEventListener('click', function () {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                return;
            }

            confirmArrivalBtn.disabled = true;
            confirmArrivalBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Getting location...';

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    document.getElementById('latitude').value = position.coords.latitude;
                    document.getElementById('longitude').value = position.coords.longitude;
                    document.getElementById('arrivalForm').submit();
                },
                function () {
                    alert('Unable to get your location. Please enable GPS.');
                    confirmArrivalBtn.disabled = false;
                    confirmArrivalBtn.innerHTML = '<i class="bi bi-pin-map me-2"></i>Confirm Arrival';
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });
    }
});

function initMap(elementId, lat, lng, zoom) {
    lat = lat || -6.8160;
    lng = lng || 39.2803;
    zoom = zoom || 13;

    const map = L.map(elementId).setView([lat, lng], zoom);

    if (navigator.onLine) {
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
    } else {
        map.getContainer().style.background = '#e8ecef';
    }

    return map;
}

function addMarker(map, lat, lng, popup) {
    const marker = L.marker([lat, lng]).addTo(map);
    if (popup) marker.bindPopup(popup);
    return marker;
}
