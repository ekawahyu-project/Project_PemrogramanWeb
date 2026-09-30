function initProfilePage() {
    const form = document.getElementById("profileForm");
    if (!form) return;

    const nama = document.getElementById("nama");
    const username = document.getElementById("username");
    const email = document.getElementById("email");
    const noHp = document.getElementById("no_hp");
    const namaUsaha = document.getElementById("nama_usaha");
    const kategori = document.getElementById("kategori");
    const alamat = document.getElementById("alamat");

    const editButton = document.getElementById("editButton");
    const saveButton = document.getElementById("saveButton");
    const cancelButton = document.getElementById("cancelButton");

    const inputs = [
        nama,
        username,
        email,
        noHp,
        namaUsaha,
        kategori,
        alamat
    ];

    let originalData = {};

    function setEditMode(isEditing) {
        inputs.forEach(function(input) {
            if (input) input.disabled = !isEditing;
        });

        if (saveButton) saveButton.disabled = !isEditing;
        if (cancelButton) cancelButton.disabled = !isEditing;
        if (editButton) {
            editButton.disabled = isEditing;
            editButton.textContent = isEditing ? "Sedang Mengedit" : "Edit Profil";
        }
    }

    function saveCurrentData() {
        originalData = {
            nama: nama ? nama.value : '',
            username: username ? username.value : '',
            email: email ? email.value : '',
            noHp: noHp ? noHp.value : '',
            namaUsaha: namaUsaha ? namaUsaha.value : '',
            kategori: kategori ? kategori.value : '',
            alamat: alamat ? alamat.value : ''
        };
    }

    function restoreData() {
        if (nama) nama.value = originalData.nama;
        if (username) username.value = originalData.username;
        if (email) email.value = originalData.email;
        if (noHp) noHp.value = originalData.noHp;
        if (namaUsaha) namaUsaha.value = originalData.namaUsaha;
        if (kategori) kategori.value = originalData.kategori;
        if (alamat) alamat.value = originalData.alamat;
    }

    if (editButton) {
        editButton.onclick = function() {
            saveCurrentData();
            setEditMode(true);
            if (nama) nama.focus();
        };
    }

    if (cancelButton) {
        cancelButton.onclick = function() {
            restoreData();
            setEditMode(false);
        };
    }

    form.onsubmit = function(event) {
        if (nama && !nama.value.trim()) {
            event.preventDefault();
            alert("Nama lengkap wajib diisi.");
            nama.focus();
            return false;
        }

        if (username && !username.value.trim()) {
            event.preventDefault();
            alert("Username wajib diisi.");
            username.focus();
            return false;
        }

        if (email && !email.value.trim()) {
            event.preventDefault();
            alert("Email wajib diisi.");
            email.focus();
            return false;
        }

        if (namaUsaha && !namaUsaha.value.trim()) {
            event.preventDefault();
            alert("Nama usaha wajib diisi.");
            namaUsaha.focus();
            return false;
        }
    };

    setEditMode(false);
}

// Inisialisasi saat script pertama dimuat
initProfilePage();
