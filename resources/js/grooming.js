const MONTHS = [
    'January','February','March','April','May','June',
    'July','August','September','October','November','December'
];
const DAY_NAMES = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

let state = {
    year: 2026,
    month: 4,
    day: null,
    service: 'Grooming',
    fee: 450,
    slot: null,
    pet: 'Dog'
};

const allSlots = [
    '9:00 am','10:00 am','11:00 am','12:00 pm','1:00 pm',
    '2:00 pm','3:00 pm','4:00 pm','5:00 pm','6:00 pm'
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
    dog:'🐶', cat:'🐱', bird:'🐦', rabbit:'🐰', fish:'🐠',
    hamster:'🐹', turtle:'🐢', snake:'🐍', lizard:'🦎', parrot:'🦜'
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

    if (state.day) {
        const dn = DAY_NAMES[new Date(state.year, state.month, state.day).getDay()];
        document.getElementById('s-datetime').textContent =
            dn + ', ' + state.day + ' | ' + (state.slot || '–');
    } else {
        document.getElementById('s-datetime').textContent = '–';
    }
}

function confirmBooking() {
    if (!state.day)  { alert('Please select a date.');      return; }
    if (!state.slot) { alert('Please select a time slot.'); return; }
    const summary = `Service: ${state.service}\nDate: ${MONTHS[state.month]} ${state.day}, ${state.year}\nTime: ${state.slot}\nPet: ${state.pet}\nFee: PHP ${state.fee}`;
    alert('Booking confirmed!\n\n' + summary);
}

document.addEventListener('DOMContentLoaded', () => {
    renderCalendar();
    renderSlots();
    updateSummary();
});