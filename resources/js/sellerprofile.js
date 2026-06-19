document.addEventListener('DOMContentLoaded', function () {

    /* ── Current Listings toggle (inside modal) ── */
    var toggleCurrent = document.getElementById('toggleCurrentListings');
    var existingList = document.getElementById('existingListings');
    var currentChevron = document.getElementById('currentListingsChevron');

    if (toggleCurrent) {
        toggleCurrent.addEventListener('click', function () {
            var open = existingList.style.display !== 'none';
            existingList.style.display = open ? 'none' : '';
            currentChevron.style.transform = open ? '' : 'rotate(180deg)';
        });
    }


    var editContact = document.getElementById('editContact');
    if (editContact) {
        editContact.addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }

    /* ── Save changes (Edit Profile modal) ── */
    var saveEditBtn = document.getElementById('saveEdit');
    if (saveEditBtn) {
        saveEditBtn.addEventListener('click', function () {
            var nameInput    = document.getElementById('editName');
            var contactInput = document.getElementById('editContact');
            var addressInput = document.getElementById('editAddress');

            var bname  = nameInput.value.trim();
            var digits = contactInput.value.trim();
            var addr   = addressInput.value.trim();

            if (!bname || !addr) {
                alert('Business name and address are required.');
                return;
            }
            if (digits.length !== 10) {
                alert('Contact number must be exactly 10 digits.');
                return;
            }

            var contact = '+63' + digits;

            var originalText = saveEditBtn.textContent;
            saveEditBtn.disabled = true;
            saveEditBtn.textContent = 'Saving...';

            var formData = new FormData();
            formData.append('action', 'update_profile');
            formData.append('business_name', bname);
            formData.append('contact_num', contact);
            formData.append('address', addr);

            fetch(window.location.href, { method: 'POST', body: formData })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    saveEditBtn.disabled = false;
                    saveEditBtn.textContent = originalText;

                    if (data.success) {
                        var bannerName    = document.getElementById('bannerName');
                        var bannerContact = document.getElementById('bannerContact');
                        var bannerAddress = document.getElementById('bannerAddress');

                        if (bannerName)    bannerName.textContent    = data.business_name;
                        if (bannerContact) bannerContact.textContent = data.contact_num;
                        if (bannerAddress) bannerAddress.textContent = data.address;

                        var modalEl = document.getElementById('editModal');
                        var modalInstance = bootstrap.Modal.getInstance(modalEl);
                        if (modalInstance) modalInstance.hide();
                    } else {
                        alert(data.error || 'Failed to save changes.');
                    }
                })
                .catch(function () {
                    saveEditBtn.disabled = false;
                    saveEditBtn.textContent = originalText;
                    alert('Request failed. Please try again.');
                });
        });
    }

    var newPhotoBox = document.getElementById('new_photo_box');
    var newPhotoInput = document.getElementById('new_photo');
    var newPhotoName = document.getElementById('new_photo_name');

    if (newPhotoBox) {
        newPhotoBox.addEventListener('click', function () {
            newPhotoInput.click();
        });
        newPhotoInput.addEventListener('change', function () {
            var file = newPhotoInput.files[0];
            if (file) {
                if (file.type !== 'image/png') {
                    newPhotoName.style.color = '#c0392b';
                    newPhotoName.textContent = 'Only PNG files are allowed.';
                    newPhotoInput.value = '';
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    newPhotoName.style.color = '#c0392b';
                    newPhotoName.textContent = 'File must be under 5MB.';
                    newPhotoInput.value = '';
                    return;
                }
                newPhotoName.style.color = '#2D8C4E';
                newPhotoName.textContent = '✓ ' + file.name;
            }
        });
    }


    document.querySelectorAll('.edit-photo-box').forEach(function (box) {
        var input = box.querySelector('.edit-photo-input');
        var nameDiv = box.querySelector('.edit-photo-name');
        box.addEventListener('click', function () {
            input.click();
        });
        input.addEventListener('change', function () {
            var file = input.files[0];
            if (!file) return;
            if (file.type !== 'image/png') {
                nameDiv.style.color = '#c0392b';
                nameDiv.textContent = 'Only PNG files are allowed.';
                input.value = '';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                nameDiv.style.color = '#c0392b';
                nameDiv.textContent = 'File must be under 5MB.';
                input.value = '';
                return;
            }
            nameDiv.style.color = '#2D8C4E';
            nameDiv.textContent = '✓ ' + file.name;
        });
    });

    document.querySelectorAll('.save-listing-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var row = btn.closest('.listing-edit-row');
            var productid = row.dataset.productid;
            var brand = row.querySelector('.edit-brand').value.trim();
            var desc = row.querySelector('.edit-desc').value.trim();
            var cat = row.querySelector('.edit-cat').value;
            var price = row.querySelector('.edit-price').value.trim();
            var photoInput = row.querySelector('.edit-photo-input');
            var msg = row.querySelector('.save-listing-msg');

            if (!brand || !desc || !price) {
                msg.style.color = '#c0392b';
                msg.textContent = 'Brand, description, and price are required.';
                msg.style.display = 'inline';
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Saving...';
            msg.style.display = 'none';

            var formData = new FormData();
            formData.append('action', 'update_listing');
            formData.append('productid', productid);
            formData.append('primary_category', cat);
            formData.append('brand_name', brand);
            formData.append('product_desc', desc);
            formData.append('price', price);
            if (photoInput && photoInput.files[0]) {
                formData.append('product_photo', photoInput.files[0]);
            }

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
                .then(function (r) {
                    return r.json();
                })
                .then(function (data) {
                    if (data.success) {
                        btn.textContent = 'Saved!';
                        btn.style.background = '#2D8C4E';
                        msg.style.color = '#2D8C4E';
                        msg.textContent = 'Changes saved.';
                        msg.style.display = 'inline';
                        setTimeout(function () {
                            btn.textContent = 'Save changes';
                            btn.style.background = '';
                            btn.disabled = false;
                        }, 1500);
                    } else {
                        msg.style.color = '#c0392b';
                        msg.textContent = data.error || 'Something went wrong.';
                        msg.style.display = 'inline';
                        btn.disabled = false;
                        btn.textContent = 'Save changes';
                    }
                })
                .catch(function () {
                    msg.style.color = '#c0392b';
                    msg.textContent = 'Request failed.';
                    msg.style.display = 'inline';
                    btn.disabled = false;
                    btn.textContent = 'Save changes';
                });
        });
    });

    document.querySelectorAll('.delete-listing-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var row = btn.closest('.listing-edit-row');
            var productid = row.dataset.productid;
            var msg = row.querySelector('.save-listing-msg');

            if (!confirm('Delete this listing? This cannot be undone.')) return;

            btn.disabled = true;
            btn.textContent = 'Deleting...';

            var formData = new FormData();
            formData.append('action', 'delete_listing');
            formData.append('productid', productid);

            fetch(window.location.href, { method: 'POST', body: formData })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        /* Remove the row + its divider from the modal */
                        var divider = row.nextElementSibling;
                        if (divider && divider.classList.contains('listing-divider')) divider.remove();
                        row.remove();

                        /* Remove from the sidebar listing display */
                        var sideItem = document.querySelector('#listingDisplay [data-productid="' + productid + '"]');
                        if (sideItem) sideItem.remove();

                        /* Update the active listings count */
                        var countEl = document.getElementById('listingCount');
                        if (countEl && !isNaN(parseInt(countEl.textContent))) {
                            countEl.textContent = Math.max(0, parseInt(countEl.textContent) - 1);
                        }

                    } else {
                        msg.style.color = '#c0392b';
                        msg.textContent = data.error || 'Delete failed.';
                        msg.style.display = 'inline';
                        btn.disabled = false;
                        btn.textContent = 'Delete';
                    }
                })
                .catch(function () {
                    msg.style.color = '#c0392b';
                    msg.textContent = 'Request failed.';
                    msg.style.display = 'inline';
                    btn.disabled = false;
                    btn.textContent = 'Delete';
                });
        });
    });

});