const VALID_USERNAME = "admin";
const VALID_EMAIL = "admin@gmail.com";
const VALID_PASSWORD = "admin123";

const form = document.getElementById("loginForm");
const errorMsg = document.getElementById("errorMsg");

form.addEventListener("submit", function (e) {
  e.preventDefault();

  const identifier = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value.trim();

  if ((identifier === VALID_USERNAME || identifier.toLowerCase() === VALID_EMAIL.toLowerCase()) && password === VALID_PASSWORD) {
    errorMsg.style.color = "#16a34a";
    errorMsg.textContent = "Login berhasil! Mengalihkan...";
  } else {
    errorMsg.style.color = "#dc2626";
    errorMsg.textContent = "Email / Username atau password salah.";
  }
});