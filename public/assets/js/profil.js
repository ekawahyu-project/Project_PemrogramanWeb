/**
 * profil.js — Validasi dan interaktivitas halaman profil AlpetBizz
 */

function initProfilePage() {
    const form = document.getElementById("profileForm");

    if (!form) return;

    const nama = document.getElementById("nama");
    const email = document.getElementById("email");
    const foto = document.getElementById("foto_profil");

    // Validasi form
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

    // ==================================================
    // OTOMATIS SIMPAN SAAT FOTO DIPILIH
    // ==================================================

    if (foto) {

        foto.addEventListener("change", function () {

            if (!foto.files || foto.files.length === 0) {
                return;
            }

            const file = foto.files[0];

            // Validasi ukuran maksimal 2 MB
            if (file.size > 2 * 1024 * 1024) {
                alert("Ukuran foto maksimal 2 MB.");
                foto.value = "";
                return;
            }

            // Validasi format
            const allowedTypes = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];

            if (!allowedTypes.includes(file.type)) {
                alert("Format foto harus JPG, JPEG, atau WEBP.");
                foto.value = "";
                return;
            }

            // Submit form secara otomatis
            form.submit();

        });
    }
}


// Inisialisasi
initProfilePage();