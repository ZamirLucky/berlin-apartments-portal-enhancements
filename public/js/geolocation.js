// Geolocation features for "Nearby" devices
// depends on geolib (https://www.npmjs.com/package/geolib)
// included via <script> tag in the BatteryView layout
document.addEventListener("DOMContentLoaded", () => {
    
    /* get array of all devices with coordinates */
    //let deviceIndex = window.deviceIndex || [];
    devicesArr = Array.isArray(window.deviceIndex) ? window.deviceIndex : Object.values(window.deviceIndex || {});

    // Find device by ID
    const findDeviceById = id => (devicesArr || []).find(
        d => String(d.smartlockId) === String(id)
    );

    // Location Utilities
    const fmtDistance = meters => (meters < 1000 ? `${Math.round(meters)} m` : `${(meters/1000).toFixed(1)} km`);
    const toPoint = d => ({ latitude: Number(d.latitude), longitude: Number(d.longitude) });

    // helpers to prettify fields in the modal
    function formatBatteryStatus(val) {
        if (val === true) return '<span class="badge bg-danger">Critical</span>';
        if (val === false) return '<span class="badge bg-success">Normal</span>';
        return String(val || 'Not available');
    }
    function formatCharge(val) {
        if (val === undefined || val === null || val === 'not available') return 'Not available';
        const n = Number(val);
        return Number.isFinite(n) ? `${n}%` : `${val}`;
    }
    function escapeHtml(s) {
        return String(s)
            .replaceAll('&','&amp;')
            .replaceAll('<','&lt;')
            .replaceAll('>','&gt;')
            .replaceAll('"','&quot;')
            .replaceAll("'","&#039;");
    }
    function formatStatus(val) {
        let onlineStatus;
        if (val === 0) {
            onlineStatus = '🟢 Online';
        } else if (val === 4) {
            onlineStatus = '🔴 Offline';
        } else {
            onlineStatus = '⚪ Unknown';
        }
        return onlineStatus;
    }

    // compute shortlist of nearby devices
    function computeNearby(baseDevice, { radiusMeters = 500 } = {}) {
        const center = toPoint(baseDevice);
        const kept = [];
        for (const d of devicesArr || []) {
            if (String(d.smartlockId) === String(baseDevice.smartlockId)) continue;
            const p = toPoint(d);
            if(!window.geolib) {
                console.error("geolib is not loaded. Please ensure geolib is included via a <script> tag.");
                break;
            }  
            if (geolib.isPointWithinRadius(p, center, radiusMeters)) {
            const distance = geolib.getDistance(p, center);
            kept.push({ ...d, distance });
            }

        }
        kept.sort((a, b) => a.distance - b.distance);
        return kept;
    }

    // render results into the modal
    function renderNearbyList(rows, baseDevice, radiusMeters) {
        const list = document.getElementById('nearbyDevicesList');

        if (!list) return;
        list.innerHTML = '';

        if (!rows.length) {
            list.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger py-4">
                        No devices within ${fmtDistance(radiusMeters)}. Try a larger radius.
                    </td>
                </tr>
            `;
            return;
        }

        rows.forEach(d => {
            const item = document.createElement('tr');

            // Construct Google Maps URL
            const url = new URL(`https://www.google.com/maps/dir/`);
            url.searchParams.set("api", "1");
            url.searchParams.set("origin", `${baseDevice.latitude},${baseDevice.longitude}`);
            url.searchParams.set("destination", `${d.latitude},${d.longitude}`);
            url.searchParams.set("travelmode", "walking");
            const mapUrl = url.toString();

            const isCritical = d.status === true || String(d.status).toLowerCase() === 'critical';
            const isOffline  = Number(d.isOnline) === 4; 
            const alertState = isCritical || isOffline;
            const btnClass = alertState ? 'btn btn-sm btn-outline-danger' : 'btn btn-sm btn-outline-primary';

            item.innerHTML = `
                <td>${escapeHtml(d.name)}</td>
                <td>${formatBatteryStatus(d.status)}</td>
                <td>${escapeHtml(formatCharge(d.batteryCharge))}</td>
                <td>${escapeHtml(formatStatus(d.isOnline))}</td>
                <td class="small text-muted text-secondary">${fmtDistance(d.distance)}</td>
                <td><a class="${btnClass}" 
                    target="_blank" 
                    rel="noopener noreferrer" 
                    href="${mapUrl}">Map</a>
                </td>
            `;
            list.appendChild(item);
        });
    }

    // track the last clicked base device
    let lastBaseDevice = null;

    // event delegation for dynamically created "Nearby" buttons
    document.body.addEventListener("click", function(event) {
        const getNearbyDevicesButton = event.target.closest(".getNearbyDevicesButton");
        if (getNearbyDevicesButton) {
            const smartlockId = getNearbyDevicesButton.dataset.smartlockId;
            const name = getNearbyDevicesButton.dataset.name || "Unknown";
            const modalTitle = document.getElementById("originalDeviceNameBadge");
            modalTitle.textContent = `Original: ${name}`;

            // look up Base Device in deviceArr
            const baseDevice = findDeviceById(smartlockId);
            lastBaseDevice = baseDevice;
            const list = document.getElementById('nearbyDevicesList');

            if (!baseDevice) {
                if (list) list.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger py-4">
                        Base device not found. Cannot compute nearby devices.
                    </td>
                </tr>`;
                return;
            }

            if (!Number.isFinite(baseDevice?.latitude) || !Number.isFinite(baseDevice?.longitude)) {
                if (list) list.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-warning py-4">
                        Missing coordinates for this device. Please set latitude/longitude and try again.
                    </td>
                </tr>`;
                return;
            }

            // read selected radius
            const checked = document.querySelector('input[name="radius"]:checked');
            const radiusMeters = parseInt(checked?.value || '1000', 10);

            // compute & render
            const devicesInRange = computeNearby(baseDevice, { radiusMeters});
            renderNearbyList(devicesInRange, baseDevice, radiusMeters);
        }
    });

    // Recompute nearby when radius option changes
    document.querySelectorAll('input[name="radius"]').forEach(el => {
        el.addEventListener('change', () => {
            if (!lastBaseDevice || !Number.isFinite(lastBaseDevice?.latitude) || !Number.isFinite(lastBaseDevice?.longitude)) return;
            const radiusMeters = parseInt(document.querySelector('input[name="radius"]:checked')?.value || '1000', 10);
            const devicesInRange = computeNearby(lastBaseDevice, { radiusMeters});
            renderNearbyList(devicesInRange, lastBaseDevice, radiusMeters);
        });
    });
});