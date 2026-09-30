/**
 * profil.js — Validasi dan interaktivitas halaman profil AlpetBizz
 */
function initProfilePage() {
    const form = document.getElementById("profileForm");
    if (!form) return;

    const nama = document.getElementById("nama");
    const email = document.getElementById("email");

    form.onsubmit = function(event) {
        if (nama && !nama.value.trim()) {
            event.preventDefault();
            alert("Nama lengkap wajib diisi.");
            nama.focus();
            return false;
        }

        if (email && !email.value.trim()) {
            event.preventDefault();
            alert("Alamat email wajib diisi.");
            email.focus();
            return false;
        }
    };
}

// Inisialisasi saat file dimuat
initProfilePage();
