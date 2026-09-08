const VALID_USERNAME = "admin";
const VALID_EMAIL = "admin@gmail.com";
const VALID_PASSWORD = "admin123";

document.getElementById("loginForm").addEventListener("submit", (e) => {
  e.preventDefault();

  const identifier = e.target.username.value.trim();
  const password = e.target.password.value.trim();

  const isValid = (identifier === VALID_USERNAME || identifier.toLowerCase() === VALID_EMAIL) && password === VALID_PASSWORD;
  const errorMsg = document.getElementById("errorMsg");

  errorMsg.style.color = isValid ? "#16a34a" : "#dc2626";
  errorMsg.textContent = isValid ? "Login berhasil! Mengalihkan..." : "Email / Username atau password salah.";
});