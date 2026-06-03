setTimeout(() => {
    const msg = document.getElementById('flash-msg');
    if (msg) {
        msg.style.transition = 'opacity 0.5s';
        msg.style.opacity = '0';
        setTimeout(() => msg.remove(), 500);
    }
}, 4000);

function togglePassword(fieldId, icon) {
    const field = document.getElementById(fieldId);
    field.type = field.type === 'password' ? 'text' : 'password';
}

function checkStrength(password) {
    const bar   = document.getElementById('strength-bar');
    const label = document.getElementById('strength-label');

    let score = 0;
    if (password.length >= 8)                        score++;
    if (/[A-Z]/.test(password))                      score++;
    if (/[0-9]/.test(password))                      score++;
    if (/[^A-Za-z0-9]/.test(password))               score++;

    const levels = [
        { width: '0%',   color: '#dee2e6', text: 'Enter a password',  textColor: '#aaa'     },
        { width: '25%',  color: '#dc3545', text: 'Weak',              textColor: '#dc3545'  },
        { width: '50%',  color: '#fd7e14', text: 'Fair',              textColor: '#fd7e14'  },
        { width: '75%',  color: '#ffc107', text: 'Moderate',          textColor: '#ffc107'  },
        { width: '100%', color: '#198754', text: 'Strong',            textColor: '#198754'  },
    ];

    const level = password.length === 0 ? levels[0] : levels[score];
    bar.style.width           = level.width;
    bar.style.backgroundColor = level.color;
    label.textContent         = level.text;
    label.style.color         = level.textColor;

    checkMatch();
}

function checkMatch() {
    const pw      = document.getElementById('signup-password').value;
    const confirm = document.getElementById('signup-confirm').value;
    const msg     = document.getElementById('match-msg');

    if (confirm.length === 0) {
        msg.textContent = '';
        return;
    }
    if (pw === confirm) {
        msg.textContent = '✔ Passwords match';
        msg.style.color = '#198754';
    } else {
        msg.textContent = '✘ Passwords do not match';
        msg.style.color = '#dc3545';
    }
}