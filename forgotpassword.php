<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Forgot Password — PAWSTER</title>
<link href="https://fonts.googleapis.com/css2?family=Convergence&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; }

  body {
    font-family: 'Convergence', sans-serif;
    background-color: #F5E6D3;
    margin: 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
  }

  .custom-card {
    max-width: 28rem;
    width: 100%;
    background-color: #FAF0E8;
    border-radius: 1rem;
    padding: 2.5rem 2.25rem;
    box-shadow: 0 8px 30px rgba(155, 98, 64, 0.15);
    position: relative;
    overflow: hidden;
  }

  .paw-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    background-color: #AB8154;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    margin: 0 auto 1.25rem;
  }

  h2.title {
    color: #3D1F08;
    font-size: 1.5rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 0.5rem;
  }

  p.subtitle {
    color: #9B7050;
    font-size: 0.9rem;
    text-align: center;
    margin-bottom: 1.75rem;
    line-height: 1.5;
  }

  label.field-label {
    color: #6b5320;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 0.4rem;
    display: block;
  }

  .custom-input {
    background-color: #F9F3EB;
    border: 1px solid #D6C5B3;
    border-radius: 12px;
    padding: 10px 15px;
    width: 100%;
    font-family: 'Convergence', sans-serif;
    color: #3D1F08;
    font-size: 0.95rem;
  }

  .custom-input::placeholder {
    color: #b8a690;
  }

  .custom-input:focus {
    outline: none;
    border-color: #A67C52;
    box-shadow: 0 0 0 0.25rem rgba(166, 124, 82, 0.25);
  }

  .field-error {
    color: #c0392b;
    font-size: 0.78rem;
    margin-top: 0.35rem;
    display: none;
  }

  .btn-login {
    background-color: #AB8154;
    color: #F5E6D3;
    border: none;
    border-radius: 1rem;
    padding: 0.7rem 1rem;
    font-family: 'Convergence', sans-serif;
    font-weight: 700;
    font-size: 0.95rem;
    width: 100%;
    margin-top: 0.5rem;
    cursor: pointer;
    transition: background-color 0.2s ease;
  }

  .btn-login:hover {
    background-color: #9B6240;
  }

  .btn-login:disabled {
    opacity: 0.6;
    cursor: default;
  }

  .back-link {
    display: block;
    text-align: center;
    margin-top: 1.5rem;
    color: #AB8154;
    font-size: 0.85rem;
    text-decoration: none;
  }

  .back-link:hover {
    text-decoration: underline;
  }

  .form-step,
  .success-step {
    transition: opacity 0.25s ease;
  }

  .success-step {
    display: none;
    text-align: center;
  }

  .success-icon {
    width: 3.75rem;
    height: 3.75rem;
    border-radius: 50%;
    background-color: #2D8C4E;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.25rem;
    font-size: 1.75rem;
  }

  .success-email {
    color: #3D1F08;
    font-weight: 700;
    word-break: break-word;
  }

  .resend-row {
    margin-top: 1.5rem;
    font-size: 0.85rem;
    color: #9B7050;
  }

  .resend-btn {
    background: none;
    border: none;
    color: #AB8154;
    font-weight: 700;
    cursor: pointer;
    font-family: 'Convergence', sans-serif;
    font-size: 0.85rem;
    padding: 0;
  }

  .resend-btn:hover {
    text-decoration: underline;
  }

  .resend-btn:disabled {
    color: #c8b69e;
    cursor: default;
    text-decoration: none;
  }

  @media (max-width: 480px) {
    .custom-card {
      padding: 2rem 1.5rem;
      border-radius: 0.85rem;
    }

    h2.title {
      font-size: 1.3rem;
    }
  }
</style>
</head>
<body>

  <div class="custom-card">

    <div class="form-step" id="formStep">
      <div class="paw-icon">🐾</div>
      <h2 class="title">Forgot your password?</h2>
      <p class="subtitle">Put your email below and we'll send you a link to reset it.</p>

      <form id="resetForm" novalidate>
        <label class="field-label" for="emailInput">Email address</label>
        <input
          class="custom-input"
          type="email"
          id="emailInput"
          placeholder="you@example.com"
          autocomplete="email"
          required
        />
        <p class="field-error" id="emailError">Enter a valid email address.</p>

        <button type="submit" class="btn-login" id="submitBtn">Send reset link</button>
      </form>

      <a href="#" class="back-link">Back to login</a>
    </div>

    <div class="success-step" id="successStep">
      <div class="success-icon">✓</div>
      <h2 class="title">Email has been sent</h2>
      <p class="subtitle">
        We sent a password reset link to<br>
        <span class="success-email" id="sentToEmail"></span>
      </p>
      <p class="subtitle" style="margin-bottom: 0;">
        Check your inbox and spam folder. The link expires in 30 minutes.
      </p>

      <div class="resend-row">
        Didn't get it?
        <button type="button" class="resend-btn" id="resendBtn">Resend email</button>
      </div>

      <a href="login.php" class="back-link">Back to login</a>
    </div>

  </div>

  <script>
    const form = document.getElementById('resetForm');
    const emailInput = document.getElementById('emailInput');
    const emailError = document.getElementById('emailError');
    const submitBtn = document.getElementById('submitBtn');
    const formStep = document.getElementById('formStep');
    const successStep = document.getElementById('successStep');
    const sentToEmail = document.getElementById('sentToEmail');
    const resendBtn = document.getElementById('resendBtn');

    function isValidEmail(value) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const value = emailInput.value.trim();

      if (!isValidEmail(value)) {
        emailError.style.display = 'block';
        emailInput.style.borderColor = '#c0392b';
        return;
      }

      emailError.style.display = 'none';
      emailInput.style.borderColor = '#D6C5B3';

      submitBtn.disabled = true;
      submitBtn.textContent = 'Sending...';

      setTimeout(function () {
        sentToEmail.textContent = value;
        formStep.style.display = 'none';
        successStep.style.display = 'block';
      }, 700);
    });

    emailInput.addEventListener('input', function () {
      emailError.style.display = 'none';
      emailInput.style.borderColor = '#D6C5B3';
    });

    resendBtn.addEventListener('click', function () {
      resendBtn.disabled = true;
      resendBtn.textContent = 'Sent again';
      setTimeout(function () {
        resendBtn.disabled = false;
        resendBtn.textContent = 'Resend email';
      }, 4000);
    });
  </script>

</body>
</html>