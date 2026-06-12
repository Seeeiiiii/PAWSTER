const cardNumInput = document.getElementById('card_number');
if (cardNumInput) {
    cardNumInput.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').substring(0, 19);
        this.value = v.replace(/(.{4})/g, '$1 ').trim();
    });
}

const expiryInput = document.getElementById('card_expiry');
if (expiryInput) {
    expiryInput.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').substring(0, 4);
        if (v.length >= 3) {
            this.value = v.substring(0, 2) + ' / ' + v.substring(2);
        } else {
            this.value = v;
        }
    });
}

const PH_DATA_BASE = 'https://cdn.jsdelivr.net/gh/isaacdarcilla/philippine-addresses@main/';
const phDataCache = {};

function loadPhData(file) {
    if (phDataCache[file]) return phDataCache[file];
    phDataCache[file] = fetch(PH_DATA_BASE + file).then(res => res.json());
    return phDataCache[file];
}

const regionSelect = document.getElementById('region');
const provinceSelect = document.getElementById('province');
const citySelect = document.getElementById('city');
const barangaySelect = document.getElementById('barangay');

function resetSelect(select, placeholder, disable) {
    if (!select) return;
    select.innerHTML = `<option value="">${placeholder}</option>`;
    select.disabled = !!disable;
}

if (regionSelect) {
    loadPhData('region.json').then(regions => {
        regions
            .slice()
            .sort((a, b) => a.region_name.localeCompare(b.region_name))
            .forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.region_code;
                opt.textContent = r.region_name;
                regionSelect.appendChild(opt);
            });
    }).catch(() => { });

    regionSelect.addEventListener('change', function () {
        resetSelect(provinceSelect, '-- Select Province --', true);
        resetSelect(citySelect, '-- Select City/Municipality --', true);
        resetSelect(barangaySelect, '-- Select Barangay --', true);

        const regionCode = this.value;
        if (!regionCode) return;

        loadPhData('province.json').then(provinces => {
            const filtered = provinces
                .filter(p => p.region_code === regionCode)
                .sort((a, b) => a.province_name.localeCompare(b.province_name));

            if (filtered.length === 0) {
                // Some regions (e.g. NCR) have no provinces — skip straight to cities
                resetSelect(provinceSelect, '-- N/A --', true);
                loadCitiesForRegion(regionCode);
                return;
            }

            filtered.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.province_code;
                opt.textContent = p.province_name;
                provinceSelect.appendChild(opt);
            });
            provinceSelect.disabled = false;
        }).catch(() => { });
    });
}

function loadCitiesForRegion(regionCode) {
    loadPhData('city.json').then(cities => {
        const filtered = cities
            .filter(c => c.region_desc === regionCode || c.region_code === regionCode)
            .sort((a, b) => a.city_name.localeCompare(b.city_name));

        filtered.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.city_code;
            opt.textContent = c.city_name;
            citySelect.appendChild(opt);
        });
        citySelect.disabled = false;
    }).catch(() => { });
}

if (provinceSelect) {
    provinceSelect.addEventListener('change', function () {
        resetSelect(citySelect, '-- Select City/Municipality --', true);
        resetSelect(barangaySelect, '-- Select Barangay --', true);

        const provinceCode = this.value;
        if (!provinceCode) return;

        loadPhData('city.json').then(cities => {
            const filtered = cities
                .filter(c => c.province_code === provinceCode)
                .sort((a, b) => a.city_name.localeCompare(b.city_name));

            filtered.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.city_code;
                opt.textContent = c.city_name;
                citySelect.appendChild(opt);
            });
            citySelect.disabled = false;
        }).catch(() => { });
    });
}

if (citySelect) {
    citySelect.addEventListener('change', function () {
        resetSelect(barangaySelect, '-- Select Barangay --', true);

        const cityCode = this.value;
        if (!cityCode) return;

        loadPhData('barangay.json').then(barangays => {
            const filtered = barangays
                .filter(b => b.city_code === cityCode)
                .sort((a, b) => a.brgy_name.localeCompare(b.brgy_name));

            filtered.forEach(b => {
                const opt = document.createElement('option');
                opt.value = b.brgy_code;
                opt.textContent = b.brgy_name;
                barangaySelect.appendChild(opt);
            });
            barangaySelect.disabled = false;
        }).catch(() => { });
    });
}

function resetAddressSelects() {
    resetSelect(provinceSelect, '-- Select Province --', true);
    resetSelect(citySelect, '-- Select City/Municipality --', true);
    resetSelect(barangaySelect, '-- Select Barangay --', true);
}

const addressList = document.getElementById('address-list');
const addressForm = document.getElementById('address-form');
const toggleBtn = document.getElementById('toggle-address-form');
const cancelBtn = document.getElementById('cancel-address-form');
const addressErrEl = document.getElementById('address-form-error');

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

function renderAddresses(data) {
    if (!addressList) return;

    if (!data.addresses || data.addresses.length === 0) {
        addressList.innerHTML = '<p class="address-empty">No saved addresses yet. Add one below.</p>';
        return;
    }

    addressList.innerHTML = data.addresses.map(addr => {
        const checked = (String(data.selected) === String(addr.addressid)) ? 'checked' : '';
        return `
            <label class="address-option">
                <input type="radio" name="selected_address" value="${addr.addressid}" ${checked} />
                <span class="address-text">
                    <strong>${escapeHtml(addr.secondary_address)}</strong><br>
                    Brgy. ${escapeHtml(addr.barangay)}, ${escapeHtml(addr.city)}, ${escapeHtml(addr.province)}, ${escapeHtml(addr.region)}
                </span>
                <button type="button" class="delete-address-btn" data-id="${addr.addressid}" aria-label="Delete address">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </label>`;
    }).join('');

    addressList.querySelectorAll('input[name="selected_address"]').forEach(input => {
        input.addEventListener('change', function () {
            fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=select_address&addressid=' + encodeURIComponent(this.value)
            })
                .then(res => res.json())
                .then(resp => {
                    if (!resp.success) {
                        alert(resp.message || 'Could not select address.');
                    }
                });
        });
    });

    addressList.querySelectorAll('.delete-address-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            if (!confirm('Remove this address?')) return;
            fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=delete_address&addressid=' + encodeURIComponent(id)
            })
                .then(res => res.json())
                .then(resp => {
                    if (resp.success) {
                        loadAddresses();
                    } else {
                        alert('Could not delete address.');
                    }
                });
        });
    });
}

function loadAddresses() {
    if (!addressList) return;
    fetch('cart.php?action=list_addresses')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderAddresses(data);
            } else {
                addressList.innerHTML = '<p class="address-empty">' + escapeHtml(data.message || 'Unable to load addresses.') + '</p>';
            }
        })
        .catch(() => {
            addressList.innerHTML = '<p class="address-empty">Unable to load addresses.</p>';
        });
}

if (toggleBtn && addressForm) {
    toggleBtn.addEventListener('click', function () {
        addressForm.style.display = (addressForm.style.display === 'none') ? 'block' : 'none';
        if (addressErrEl) addressErrEl.style.display = 'none';
    });
}

if (cancelBtn && addressForm) {
    cancelBtn.addEventListener('click', function () {
        addressForm.reset();
        resetAddressSelects();
        addressForm.style.display = 'none';
        if (addressErrEl) addressErrEl.style.display = 'none';
    });
}

function selectedText(select) {
    if (!select || select.selectedIndex < 0) return '';
    return select.options[select.selectedIndex].text.trim();
}

if (addressForm) {
    addressForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const secondaryAddress = document.getElementById('secondary_address').value.trim();
        const region = selectedText(regionSelect);
        const province = (provinceSelect.value === '' && provinceSelect.disabled) ? '' : selectedText(provinceSelect);
        const city = selectedText(citySelect);
        const barangay = selectedText(barangaySelect);

        if (!secondaryAddress || !regionSelect.value || !citySelect.value || !barangaySelect.value) {
            if (addressErrEl) {
                addressErrEl.textContent = 'Please complete the address fields (Region, City/Municipality, and Barangay are required).';
                addressErrEl.style.display = 'block';
            }
            return;
        }

        const body = new URLSearchParams({
            action: 'add_address',
            secondary_address: secondaryAddress,
            region: region,
            province: province || 'N/A',
            city: city,
            barangay: barangay
        });

        fetch('cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
            .then(res => res.json())
            .then(resp => {
                if (resp.success) {
                    addressForm.reset();
                    resetAddressSelects();
                    addressForm.style.display = 'none';
                    if (addressErrEl) addressErrEl.style.display = 'none';
                    loadAddresses();
                } else if (addressErrEl) {
                    addressErrEl.textContent = resp.message || 'Could not save address.';
                    addressErrEl.style.display = 'block';
                }
            });
    });
}

loadAddresses();