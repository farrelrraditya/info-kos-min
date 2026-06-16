/**
 * InfoKosMin - Form Validation
 *
 * Academic requirements covered:
 *   J1 - JavaScript validation before submit (min. 2 fields — we validate 6)
 *   J3 - DOM manipulation: showError() injects inline error messages
 *        clearError() toggles element visibility
 *   J4 - addEventListener types used: 'DOMContentLoaded', 'submit', 'input'
 */

'use strict';

// ─────────────────────────────────────────────
// DOM Manipulation Helpers
// ─────────────────────────────────────────────

/**
 * Show an inline validation error below a form field.
 * Manipulates the DOM by injecting text and toggling visibility.
 *
 * @param {string} fieldId  - The input element's id
 * @param {string} message  - Error message to display
 */
function showError(fieldId, message) {
    const input = document.getElementById(fieldId);
    const error = document.getElementById('error-' + fieldId);

    if (input) {
        input.classList.add('is-invalid');      // Bootstrap invalid styling
        input.classList.remove('is-valid');
    }
    if (error) {
        error.textContent = message;             // DOM manipulation: set content
        error.classList.remove('d-none');        // DOM manipulation: toggle visibility
    }
}

/**
 * Clear an inline validation error for a field.
 *
 * @param {string} fieldId
 */
function clearError(fieldId) {
    const input = document.getElementById(fieldId);
    const error = document.getElementById('error-' + fieldId);

    if (input) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    }
    if (error) {
        error.textContent = '';
        error.classList.add('d-none');           // DOM manipulation: toggle visibility
    }
}

/**
 * Clear all validation states on a form.
 *
 * @param {HTMLFormElement} form
 */
function clearAllErrors(form) {
    form.querySelectorAll('.is-invalid, .is-valid').forEach(function (el) {
        el.classList.remove('is-invalid', 'is-valid');
    });
    form.querySelectorAll('[id^="error-"]').forEach(function (el) {
        el.textContent = '';
        el.classList.add('d-none');
    });
}

// ─────────────────────────────────────────────
// Validators
// ─────────────────────────────────────────────

function validateKostForm(form) {
    let valid = true;
    clearAllErrors(form);

    // Field 1: kost_name
    const kostName = form.querySelector('#kost_name');
    if (kostName) {
        if (!kostName.value.trim()) {
            showError('kost_name', 'Nama kos tidak boleh kosong.');
            valid = false;
        } else if (kostName.value.trim().length < 3) {
            showError('kost_name', 'Nama kos minimal 3 karakter.');
            valid = false;
        } else {
            clearError('kost_name');
        }
    }

    // Field 2: monthly_price
    const price = form.querySelector('#monthly_price');
    if (price) {
        const priceVal = parseFloat(price.value);
        if (!price.value.trim() || isNaN(priceVal)) {
            showError('monthly_price', 'Harga sewa harus berupa angka.');
            valid = false;
        } else if (priceVal <= 0) {
            showError('monthly_price', 'Harga sewa harus lebih dari 0.');
            valid = false;
        } else if (priceVal > 99999999) {
            showError('monthly_price', 'Harga sewa terlalu besar.');
            valid = false;
        } else {
            clearError('monthly_price');
        }
    }

    // Field 3: address
    const address = form.querySelector('#address');
    if (address) {
        if (!address.value.trim()) {
            showError('address', 'Alamat tidak boleh kosong.');
            valid = false;
        } else if (address.value.trim().length < 10) {
            showError('address', 'Alamat terlalu pendek (minimal 10 karakter).');
            valid = false;
        } else {
            clearError('address');
        }
    }

    // Field 4: district
    const district = form.querySelector('#district');
    if (district) {
        if (!district.value.trim()) {
            showError('district', 'Kecamatan tidak boleh kosong.');
            valid = false;
        } else {
            clearError('district');
        }
    }

    // Field 5: id_owner (select)
    const owner = form.querySelector('#id_owner');
    if (owner) {
        if (!owner.value || owner.value === '') {
            showError('id_owner', 'Pilih pemilik kos.');
            valid = false;
        } else {
            clearError('id_owner');
        }
    }

    // Field 6: gender_type (select)
    const gender = form.querySelector('#gender_type');
    if (gender) {
        if (!gender.value || gender.value === '') {
            showError('gender_type', 'Pilih tipe penghuni.');
            valid = false;
        } else {
            clearError('gender_type');
        }
    }

    return valid;
}

function validateOwnerForm(form) {
    let valid = true;
    clearAllErrors(form);

    // Field 1: owner_name
    const name = form.querySelector('#owner_name');
    if (name) {
        if (!name.value.trim()) {
            showError('owner_name', 'Nama pemilik tidak boleh kosong.');
            valid = false;
        } else if (name.value.trim().length < 2) {
            showError('owner_name', 'Nama pemilik minimal 2 karakter.');
            valid = false;
        } else {
            clearError('owner_name');
        }
    }

    // Field 2: phone_number
    const phone = form.querySelector('#phone_number');
    if (phone) {
        const phoneVal = phone.value.trim().replace(/[\s\-]/g, '');
        if (!phoneVal) {
            showError('phone_number', 'Nomor telepon tidak boleh kosong.');
            valid = false;
        } else if (!/^[0-9+]{10,15}$/.test(phoneVal)) {
            showError('phone_number', 'Nomor telepon tidak valid (10-15 digit angka).');
            valid = false;
        } else {
            clearError('phone_number');
        }
    }

    // Field 3: email (optional but validate format if filled)
    const email = form.querySelector('#email');
    if (email && email.value.trim()) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.value.trim())) {
            showError('email', 'Format email tidak valid.');
            valid = false;
        } else {
            clearError('email');
        }
    }

    return valid;
}

function validateFacilityForm(form) {
    let valid = true;
    clearAllErrors(form);

    const name = form.querySelector('#facility_name');
    if (name) {
        if (!name.value.trim()) {
            showError('facility_name', 'Nama fasilitas tidak boleh kosong.');
            valid = false;
        } else if (name.value.trim().length < 2) {
            showError('facility_name', 'Nama fasilitas minimal 2 karakter.');
            valid = false;
        } else {
            clearError('facility_name');
        }
    }

    return valid;
}

function validateLoginForm(form) {
    let valid = true;
    clearAllErrors(form);

    // Field 1: username
    const username = form.querySelector('#username');
    if (username) {
        if (!username.value.trim()) {
            showError('username', 'Username tidak boleh kosong.');
            valid = false;
        } else {
            clearError('username');
        }
    }

    // Field 2: password
    const password = form.querySelector('#password');
    if (password) {
        if (!password.value) {
            showError('password', 'Password tidak boleh kosong.');
            valid = false;
        } else if (password.value.length < 6) {
            showError('password', 'Password minimal 6 karakter.');
            valid = false;
        } else {
            clearError('password');
        }
    }

    return valid;
}

// ─────────────────────────────────────────────
// addEventListener Type 1: DOMContentLoaded
// Wraps all form event binding so DOM is ready
// ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {

    // ─────────────────────────────────────────
    // addEventListener Type 2: submit
    // Intercepts form submission for validation
    // ─────────────────────────────────────────

    // Kost form (create + edit)
    const kostForm = document.getElementById('kostForm');
    if (kostForm) {
        kostForm.addEventListener('submit', function (e) {
            if (!validateKostForm(this)) {
                e.preventDefault();
                // Scroll to first error
                const firstInvalid = this.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
            } else if (this.action.includes('edit.php')) {
                if (!confirm('Apakah Anda yakin ingin menyimpan perubahan data kos ini?')) {
                    e.preventDefault();
                }
            }
        });

        // Live validation on input (clear error as user types)
        kostForm.querySelectorAll('input, select, textarea').forEach(function (el) {
            el.addEventListener('input', function () {
                const errorEl = document.getElementById('error-' + this.id);
                if (errorEl && !errorEl.classList.contains('d-none')) {
                    clearError(this.id);
                }
            });
        });
    }

    // Owner form
    const ownerForm = document.getElementById('ownerForm');
    if (ownerForm) {
        ownerForm.addEventListener('submit', function (e) {
            if (!validateOwnerForm(this)) {
                e.preventDefault();
                const firstInvalid = this.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
            } else if (this.action.includes('edit.php')) {
                if (!confirm('Apakah Anda yakin ingin menyimpan perubahan data pemilik ini?')) {
                    e.preventDefault();
                }
            }
        });
    }

    // Facility form
    const facilityForm = document.getElementById('facilityForm');
    if (facilityForm) {
        facilityForm.addEventListener('submit', function (e) {
            if (!validateFacilityForm(this)) {
                e.preventDefault();
            } else if (this.action.includes('edit.php')) {
                if (!confirm('Apakah Anda yakin ingin menyimpan perubahan data fasilitas ini?')) {
                    e.preventDefault();
                }
            }
        });
    }

    // Login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            if (!validateLoginForm(this)) {
                e.preventDefault();
            }
        });
    }

    // Survey edit form - basic required field check
    const surveyForm = document.getElementById('surveyForm');
    if (surveyForm) {
        surveyForm.addEventListener('submit', function (e) {
            const dateField = this.querySelector('#survey_date');
            if (dateField && !dateField.value) {
                showError('survey_date', 'Tanggal survei tidak boleh kosong.');
                e.preventDefault();
            } else if (this.action.includes('edit.php')) {
                if (!confirm('Apakah Anda yakin ingin menyimpan perubahan log survei ini?')) {
                    e.preventDefault();
                }
            }
        });
    }

});
