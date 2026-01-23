
const form = document.getElementById('registerForm');

const email = document.getElementById('email');
const password = document.getElementById('password');
const passwordRepeat = document.getElementById('passwordRepeat');

form.addEventListener('submit', function (e) {
  let isValid = true;

  clearErrors();

  // EMAIL
  if (email.value.trim() === '') {
    showError(email, 'Email jest wymagany');
    isValid = false;
  } else if (!isValidEmail(email.value)) {
    showError(email, 'Nieprawidłowy format email');
    isValid = false;
  }

  // HASŁO
  if (password.value.length < 8) {
    showError(password, 'Hasło musi mieć minimum 8 znaków');
    isValid = false;
  }

  // POWTÓRZ HASŁO
  if (passwordRepeat.value !== password.value) {
    showError(passwordRepeat, 'Hasła nie są takie same');
    isValid = false;
  }

  if (!isValid) {
    e.preventDefault(); // blokuje wysłanie formularza
  }
});

function showError(input, message) {
  const formGroup = input.parentElement;
  const error = formGroup.querySelector('.error');

  error.innerText = message;
  input.classList.add('error-input');
}

function clearErrors() {
  document.querySelectorAll('.error').forEach(el => el.innerText = '');
  document.querySelectorAll('input').forEach(input =>
    input.classList.remove('error-input')
  );
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}
