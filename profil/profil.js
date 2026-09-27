const form = document.getElementById("profileForm");

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
        input.disabled = !isEditing;
    });

    saveButton.disabled = !isEditing;
    cancelButton.disabled = !isEditing;

    editButton.disabled = isEditing;

    if (isEditing) {
        editButton.textContent = "Sedang Mengedit";
    } else {
        editButton.textContent = "Edit Profil";
    }
}

function saveCurrentData() {
    originalData = {
        nama: nama.value,
        username: username.value,
        email: email.value,
        noHp: noHp.value,
        namaUsaha: namaUsaha.value,
        kategori: kategori.value,
        alamat: alamat.value
    };
}

function restoreData() {
    nama.value = originalData.nama;
    username.value = originalData.username;
    email.value = originalData.email;
    noHp.value = originalData.noHp;
    namaUsaha.value = originalData.namaUsaha;
    kategori.value = originalData.kategori;
    alamat.value = originalData.alamat;
}

editButton.addEventListener("click", function() {
    saveCurrentData();
    setEditMode(true);
    nama.focus();
});

cancelButton.addEventListener("click", function() {
    restoreData();
    setEditMode(false);
});

form.addEventListener("submit", function(event) {
    if (!nama.value.trim()) {
        event.preventDefault();
        alert("Nama lengkap wajib diisi.");
        nama.focus();
        return;
    }

    if (!username.value.trim()) {
        event.preventDefault();
        alert("Username wajib diisi.");
        username.focus();
        return;
    }

    if (!email.value.trim()) {
        event.preventDefault();
        alert("Email wajib diisi.");
        email.focus();
        return;
    }

    if (!namaUsaha.value.trim()) {
        event.preventDefault();
        alert("Nama usaha wajib diisi.");
        namaUsaha.focus();
        return;
    }
});

setEditMode(false);