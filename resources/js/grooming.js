const MONTHS = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];
const DAY_NAMES = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

let state = {
    year: 2026,
    month: 5,
    day: null,
    service: 'Grooming',
    fee: 150,
    slot: null,
    pet: 'Dog'
};

let paymentState = {
    method: 'card'  // 'card' or 'gcash'
};

const allSlots = [
    '9:00 am', '10:00 am', '11:00 am', '12:00 pm', '1:00 pm',
    '2:00 pm', '3:00 pm', '4:00 pm', '5:00 pm', '6:00 pm'
];

function seededRandom(seed) {
    let s = seed;
    return () => { s = (s * 16807) % 2147483647; return (s - 1) / 2147483646; };
}

function getBookedSlotsForDate(y, m, d) {
    if (!d) return [];
    const rng = seededRandom(y * 10000 + m * 100 + d);
    const count = 2 + Math.floor(rng() * 4);
    return [...allSlots].sort(() => rng() - 0.5).slice(0, count);
}

function renderCalendar() {
    document.getElementById('cal-month-label').textContent =
        'Pick a date – ' + MONTHS[state.month] + ' ' + state.year;

    const grid = document.getElementById('cal-grid');
    grid.innerHTML = '';

    DAY_NAMES.forEach(d => {
        const el = document.createElement('div');
        el.className = 'cal-day-name';
        el.textContent = d;
        grid.appendChild(el);
    });

    const today = new Date();
    const todayMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());
    const firstDayOfWeek = new Date(state.year, state.month, 1).getDay();
    const daysInMonth = new Date(state.year, state.month + 1, 0).getDate();

    for (let i = 0; i < firstDayOfWeek; i++) {
        const el = document.createElement('button');
        el.className = 'cal-day empty';
        el.disabled = true;
        grid.appendChild(el);
    }

    for (let d = 1; d <= daysInMonth; d++) {
        const el = document.createElement('button');
        el.className = 'cal-day';
        el.textContent = d;

        const thisDate = new Date(state.year, state.month, d);
        const isPast = thisDate < todayMidnight;
        const isToday = thisDate.getTime() === todayMidnight.getTime();

        if (isPast) {
            el.classList.add('past');
            el.disabled = true;
        } else {
            el.addEventListener('click', () => selectDay(d));
        }

        if (isToday) el.classList.add('today');
        if (d === state.day && !isPast) el.classList.add('selected');

        grid.appendChild(el);
    }
}

function prevMonth() {
    if (state.month === 0) { state.month = 11; state.year--; }
    else state.month--;
    state.day = null;
    state.slot = null;
    renderCalendar();
    renderSlots();
    updateSummary();
}

// Fixed missing code initialization error from original booking.js call
function nextMonth() {
    if (state.month === 11) { state.month = 0; state.year++; }
    else state.month++;
    state.day = null;
    state.slot = null;
    renderCalendar();
    renderSlots();
    updateSummary();
}

function selectDay(d) {
    state.day = d;
    state.slot = null;
    renderCalendar();
    document.getElementById('slots-label').textContent =
        'Available time slots – ' + MONTHS[state.month].slice(0, 3) + ' ' + d;
    renderSlots();
    updateSummary();
}

function selectService(btn) {
    document.querySelectorAll('.svc-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    state.service = btn.dataset.service;
    state.fee = parseInt(btn.dataset.fee);
    updateSummary();
}

function renderSlots() {
    const container = document.getElementById('slots-container');
    container.innerHTML = '';

    const booked = getBookedSlotsForDate(state.year, state.month, state.day);
    if (state.slot && booked.includes(state.slot)) state.slot = null;

    allSlots.forEach(slot => {
        const isBooked = booked.includes(slot);
        const btn = document.createElement('button');
        btn.className = 'time-slot' +
            (isBooked ? ' booked' : '') +
            (slot === state.slot ? ' selected' : '');
        btn.textContent = slot;
        btn.disabled = isBooked;
        if (!isBooked) btn.addEventListener('click', () => selectSlot(slot));
        container.appendChild(btn);
    });
}

function selectSlot(slot) {
    state.slot = slot;
    renderSlots();
    updateSummary();
}

function selectPet(btn) {
    document.querySelectorAll('.pet-pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    state.pet = btn.dataset.pet;
    updateSummary();
}

function openAddPet() {
    document.getElementById('add-pet-form').classList.add('open');
    document.getElementById('pet-type-input').value = '';
    document.getElementById('pet-add-confirm').disabled = true;
}

function closeAddPet() {
    document.getElementById('add-pet-form').classList.remove('open');
}

function fillPetInput(val) {
    const input = document.getElementById('pet-type-input');
    input.value = val;
    input.focus();
    document.getElementById('pet-add-confirm').disabled = false;
}

function onPetInputChange(input) {
    document.getElementById('pet-add-confirm').disabled = input.value.trim().length === 0;
}

const PET_EMOJI = {
    dog: '🐶', cat: '🐱', bird: '🐦'
};

function confirmAddPet() {
    const raw = document.getElementById('pet-type-input').value.trim();
    if (!raw) return;
    const name = raw.charAt(0).toUpperCase() + raw.slice(1);
    const emoji = PET_EMOJI[raw.toLowerCase()] || '🐾';

    const existing = [...document.querySelectorAll('.pet-pill')].find(
        p => p.dataset.pet && p.dataset.pet.toLowerCase() === name.toLowerCase()
    );
    if (existing) { selectPet(existing); closeAddPet(); return; }

    const list = document.getElementById('pet-list');
    const btn = document.createElement('button');
    btn.className = 'pet-pill';
    btn.dataset.pet = name;
    btn.innerHTML = `<span class="pet-pill-icon">${emoji}</span> ${name}`;
    btn.addEventListener('click', () => selectPet(btn));
    list.appendChild(btn);

    selectPet(btn);
    closeAddPet();
}

function updateSummary() {
    document.getElementById('s-service').textContent = state.service;
    document.getElementById('s-fee').textContent = 'PHP ' + state.fee;
    document.getElementById('s-pet').textContent = state.pet;

    // Dynamically update the summary layout text
    if (typeof AUTH_USER_CONTEXT !== 'undefined') {
        document.getElementById('s-booked-by').textContent = AUTH_USER_CONTEXT.name;
    }

    if (state.day) {
        const dn = DAY_NAMES[new Date(state.year, state.month, state.day).getDay()];
        document.getElementById('s-datetime').textContent =
            dn + ', ' + state.day + ' | ' + (state.slot || '–');
    } else {
        document.getElementById('s-datetime').textContent = '–';
    }
}

function selectPayment(method) {
    paymentState.method = method;

    document.getElementById('pay-btn-card').classList.toggle('active', method === 'card');
    document.getElementById('pay-btn-gcash').classList.toggle('active', method === 'gcash');

    const cardDrop = document.getElementById('pay-dropdown-card');
    const gcashDrop = document.getElementById('pay-dropdown-gcash');

    if (method === 'card') {
        cardDrop.style.display = 'block';
        gcashDrop.style.display = 'none';
        // Animate open
        cardDrop.classList.remove('pay-dropdown-closing');
        cardDrop.classList.add('pay-dropdown-open');
    } else {
        gcashDrop.style.display = 'block';
        cardDrop.style.display = 'none';
        gcashDrop.classList.remove('pay-dropdown-closing');
        gcashDrop.classList.add('pay-dropdown-open');
    }
}

function formatCardNumber(input) {
    let v = input.value.replace(/\D/g, '').slice(0, 16);
    input.value = v.replace(/(.{4})/g, '$1 ').trim();
}

function formatExpiry(input) {
    let v = input.value.replace(/\D/g, '').slice(0, 4);
    if (v.length >= 3) v = v.slice(0, 2) + ' / ' + v.slice(2);
    input.value = v;
}

function formatGcash(input) {
    let v = input.value.replace(/\D/g, '').slice(0, 11);
    if (v.length > 4 && v.length <= 7) v = v.slice(0, 4) + ' ' + v.slice(4);
    else if (v.length > 7) v = v.slice(0, 4) + ' ' + v.slice(4, 7) + ' ' + v.slice(7);
    input.value = v;
}

function confirmBooking() {
    if (!state.day) { alert('Please select a date.'); return; }
    if (!state.slot) { alert('Please select a time slot.'); return; }

    // Read information dynamically from the system authentication configuration
    const userId = (typeof AUTH_USER_CONTEXT !== 'undefined') ? AUTH_USER_CONTEXT.id : 0;
    const userName = (typeof AUTH_USER_CONTEXT !== 'undefined') ? AUTH_USER_CONTEXT.name : 'Guest User';

    // Format relational date attribute structured for standard MySQL operations
    const formattedMonth = String(state.month + 1).padStart(2, '0');
    const formattedDay = String(state.day).padStart(2, '0');
    const computedDbDate = `${state.year}-${formattedMonth}-${formattedDay}`;

    if (!state.day) { alert('Please select a date.'); return; }
    if (!state.slot) { alert('Please select a time slot.'); return; }

    // Validate payment fields
    if (paymentState.method === 'card') {
        const cn = document.getElementById('card-number').value.replace(/\s/g, '');
        const ce = document.getElementById('card-expiry').value;
        const cv = document.getElementById('card-cvv').value;
        const ch = document.getElementById('card-name').value.trim();
        if (!ch || cn.length < 13 || !ce || cv.length < 3) {
            alert('Please complete your card details.'); return;
        }
    } else {
        const gn = document.getElementById('gcash-number').value.replace(/\s/g, '');
        const ga = document.getElementById('gcash-name').value.trim();
        if (gn.length < 11 || !ga) {
            alert('Please complete your GCash details.'); return;
        }
    }

    // Build functional payload object
    const booking = {
        id: Date.now(),
        userid: userId,
        user: userName,
        service: state.service,
        pet: state.pet,
        date: MONTHS[state.month] + ' ' + state.day + ', ' + state.year,
        date_raw: computedDbDate,
        datetime: MONTHS[state.month] + ' ' + state.day + ' | ' + state.slot,
        time: state.slot,
        fee: state.fee,
        payment_method: paymentState.method,
        location: 'PAWSTER',
        notes: document.getElementById('booking-notes').value.trim(),
        status: 'Pending',
        created: new Date().toISOString()
    };

    // Save configuration references to client state instances
    const existing = JSON.parse(localStorage.getItem('pawster_appointments') || '[]');
    existing.unshift(booking);
    localStorage.setItem('pawster_appointments', JSON.stringify(existing));

    // Ship data payload to backend execution pipeline handlers
    sendBookingToDatabase(booking);
}

function sendBookingToDatabase(bookingData) {
    fetch('grooming.php?action=save_appt', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(bookingData)
    })
        .then(response => response.json())
        .then(data => {
           if (data.status === 'success') {
    showBookingModal(bookingData);

    // Reset state
    state.day = null;
    state.slot = null;
    state.service = 'Grooming';
    state.fee = 150;
    state.pet = 'Dog';

    // Reset notes
    document.getElementById('booking-notes').value = '';

    // Reset card fields
    document.getElementById('card-number').value = '';
    document.getElementById('card-expiry').value = '';
    document.getElementById('card-cvv').value = '';
    document.getElementById('card-name').value = '';

    // Reset GCash fields
    document.getElementById('gcash-number').value = '';
    document.getElementById('gcash-name').value = '';

    // Reset service button
    document.querySelectorAll('.svc-btn').forEach(btn =>
        btn.classList.remove('active')
    );

    const defaultService = document.querySelector('.svc-btn');
    if (defaultService) defaultService.classList.add('active');

    // Reset pet buttons
    document.querySelectorAll('.pet-pill').forEach(btn =>
        btn.classList.remove('active')
    );

    const defaultPet = document.querySelector('.pet-pill[data-pet="Dog"]');
    if (defaultPet) defaultPet.classList.add('active');

    // Refresh UI
    renderCalendar();
    renderSlots();
    updateSummary();
}
        })
        .catch(error => {
            console.error('Asynchronous transaction processing error:', error);
        });
}



function showBookingModal(b) {
    const old = document.getElementById('booking-confirm-modal');
    if (old) old.remove();

    const modal = document.createElement('div');
    modal.id = 'booking-confirm-modal';
    modal.innerHTML = `
        <div style="position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9999;display:flex;align-items:center;justify-content:center;">
          <div style="background:#fff;border-radius:1rem;padding:2rem 2.25rem;max-width:380px;width:90%;text-align:center;box-shadow:0 8px 32px rgba(0,0,0,0.18);">
            <div style="font-size:2.5rem;margin-bottom:0.5rem;">🐾</div>
            <h5 style="font-family:'Caprasimo',cursive;color:#3D1F08;margin-bottom:0.25rem;">Booking Confirmed!</h5>
            <p style="font-size:0.82rem;color:#9B7050;margin-bottom:1.25rem;">Your appointment has been sent to admin for review.</p>
            <table style="width:100%;font-size:0.83rem;text-align:left;border-collapse:collapse;margin-bottom:1.25rem;">
              <tr><td style="color:#9B7050;padding:0.2rem 0.5rem 0.2rem 0;">Booked by</td><td style="color:#3D1F08;font-weight:700;">${b.user}</td></tr>
              <tr><td style="color:#9B7050;padding:0.2rem 0.5rem 0.2rem 0;">Service</td><td style="color:#3D1F08;font-weight:700;">${b.service}</td></tr>
              <tr><td style="color:#9B7050;padding:0.2rem 0.5rem 0.2rem 0;">Date &amp; Time</td><td style="color:#3D1F08;font-weight:700;">${b.datetime}</td></tr>
              <tr><td style="color:#9B7050;padding:0.2rem 0.5rem 0.2rem 0;">Pet</td><td style="color:#3D1F08;font-weight:700;">${b.pet}</td></tr>
              <tr><td style="color:#9B7050;padding:0.2rem 0.5rem 0.2rem 0;">Fee</td><td style="color:#3D1F08;font-weight:700;">PHP ${b.fee}</td></tr>
            </table>
            <button onclick="document.getElementById('booking-confirm-modal').remove()"
              style="background:#AB8154;color:#fff;border:none;border-radius:0.6rem;padding:0.55rem 1.5rem;font-family:'Convergence',sans-serif;font-weight:700;font-size:0.87rem;cursor:pointer;width:100%;">Done</button>
          </div>
        </div>`;
    document.body.appendChild(modal);
}


document.addEventListener('DOMContentLoaded', () => {
    renderCalendar();
    renderSlots();
    updateSummary();
});

