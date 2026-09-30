<?php
/**
 * REST API Endpoint untuk Frontend React & Backend UMKM Manager (AlpetBizz)
 */

// Enable CORS
if (!headers_sent()) {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Content-Type: application/json; charset=UTF-8");
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Inisialisasi Session
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Muat inisialisasi data session dan produk.json
require_once __DIR__ . '/includes/init.php';

// Inisialisasi akun default jika belum ada
if (!isset($_SESSION['users'])) {
    $_SESSION['users']['admin'] = [
        'nama' => 'Administrator',
        'email' => 'admin@gmail.com',
        'password' => 'admin123'
    ];
}

if (!isset($_SESSION['profil'])) {
    $_SESSION['profil'] = [
        'no_hp' => '08123456789',
        'nama_usaha' => 'AlpetBizz Store',
        'kategori' => 'Makanan & Minuman',
        'alamat' => 'Jl. Merdeka No. 45'
    ];
}

if (!isset($_SESSION['catatan_rekomendasi'])) {
    $_SESSION['catatan_rekomendasi'] = [];
}

if (!isset($_SESSION['laporan_tersimpan'])) {
    $_SESSION['laporan_tersimpan'] = [];
}

// Baca input data (baik JSON body maupun form-data)
$rawBody = file_get_contents('php://input');
$jsonBody = json_decode($rawBody, true) ?? [];
$input = array_merge($_GET, $_POST, $jsonBody);

$action = $input['action'] ?? $_GET['action'] ?? '';

function sendJson($data, int $statusCode = 200) {
    if (!headers_sent()) {
        http_response_code($statusCode);
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function sendError(string $message, int $statusCode = 400) {
    sendJson(['success' => false, 'error' => $message], $statusCode);
}

// Helper verifikasi login untuk endpoint terlindungi
function requireAuth() {
    if (!isset($_SESSION['user'])) {
        sendError('Sesi login telah berakhir atau belum masuk.', 401);
    }
}

switch ($action) {
    // ── AUTHENTICATION ─────────────────────────────────────────
    case 'auth_check':
        if (isset($_SESSION['user'])) {
            $u = $_SESSION['user'];
            $account = $_SESSION['users'][$u] ?? null;
            sendJson([
                'success' => true,
                'loggedIn' => true,
                'user' => [
                    'username' => $u,
                    'nama' => $_SESSION['user_nama'] ?? ($account['nama'] ?? $u),
                    'email' => $account['email'] ?? ''
                ]
            ]);
        } else {
            sendJson([
                'success' => true,
                'loggedIn' => false,
                'user' => null
            ]);
        }
        break;

    case 'login':
        $usernameOrEmail = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if (!$usernameOrEmail || !$password) {
            sendError('Username/email dan kata sandi wajib diisi.');
        }

        $found = null;
        $foundUname = null;
        foreach ($_SESSION['users'] as $uname => $data) {
            if (($uname === $usernameOrEmail || strtolower($data['email']) === strtolower($usernameOrEmail))
                && $data['password'] === $password) {
                $found = $data;
                $foundUname = $uname;
                break;
            }
        }

        if ($found) {
            $_SESSION['user'] = $foundUname;
            $_SESSION['user_nama'] = $found['nama'];
            sendJson([
                'success' => true,
                'message' => 'Login berhasil.',
                'user' => [
                    'username' => $foundUname,
                    'nama' => $found['nama'],
                    'email' => $found['email']
                ]
            ]);
        } else {
            sendError('Username/email atau kata sandi salah.', 401);
        }
        break;

    case 'register':
        $nama  = trim($input['nama'] ?? '');
        $uname = trim($input['username'] ?? '');
        $email = trim($input['email'] ?? '');
        $pw    = $input['password'] ?? '';
        $pw2   = $input['password2'] ?? '';

        if (!$nama || !$uname || !$email || !$pw) {
            sendError('Semua kolom pendaftaran wajib diisi.');
        }
        if ($pw !== $pw2) {
            sendError('Konfirmasi kata sandi tidak cocok.');
        }
        if (strlen($pw) < 6) {
            sendError('Kata sandi minimal 6 karakter.');
        }
        if (isset($_SESSION['users'][$uname])) {
            sendError('Username sudah terdaftar.');
        }

        foreach ($_SESSION['users'] as $u) {
            if (strtolower($u['email']) === strtolower($email)) {
                sendError('Email sudah terdaftar.');
            }
        }

        $_SESSION['users'][$uname] = [
            'nama' => $nama,
            'email' => $email,
            'password' => $pw
        ];

        sendJson([
            'success' => true,
            'message' => 'Akun berhasil dibuat. Silakan masuk.'
        ]);
        break;

    case 'logout':
        unset($_SESSION['user']);
        unset($_SESSION['user_nama']);
        sendJson(['success' => true, 'message' => 'Berhasil keluar.']);
        break;

    // ── DASHBOARD ──────────────────────────────────────────────
    case 'dashboard':
        requireAuth();
        $pemasukan = $pengeluaran = 0;
        foreach ($_SESSION['transaksi'] as $t) {
            if ($t['jenis'] === 'Pemasukan') $pemasukan += (int)$t['jumlah'];
            else $pengeluaran += (int)$t['jumlah'];
        }
        $laba = $pemasukan - $pengeluaran;
        $totalProduk = count($_SESSION['produk']);
        $lowStockProducts = array_values(array_filter($_SESSION['produk'], fn($p) => (int)$p['stok'] <= (int)$p['stok_min']));

        // Ambil 5 transaksi terbaru
        $allTrx = $_SESSION['transaksi'];
        usort($allTrx, fn($a, $b) => strcmp($b['tanggal'], $a['tanggal']));
        $recentTrx = array_slice($allTrx, 0, 5);

        $txMasukCount = count(array_filter($_SESSION['transaksi'], fn($t) => $t['jenis'] === 'Pemasukan'));
        $txKeluarCount = count(array_filter($_SESSION['transaksi'], fn($t) => $t['jenis'] === 'Pengeluaran'));

        sendJson([
            'success' => true,
            'stats' => [
                'pemasukan' => $pemasukan,
                'pengeluaran' => $pengeluaran,
                'laba' => $laba,
                'total_produk' => $totalProduk,
                'low_stock_count' => count($lowStockProducts),
                'tx_masuk_count' => $txMasukCount,
                'tx_keluar_count' => $txKeluarCount
            ],
            'low_stock_products' => $lowStockProducts,
            'recent_transactions' => $recentTrx
        ]);
        break;

    // ── PRODUK ────────────────────────────────────────────────
    case 'produk_list':
        requireAuth();
        sendJson(['success' => true, 'data' => array_values($_SESSION['produk'])]);
        break;

    case 'produk_save':
        requireAuth();
        $id = $input['id'] ?? '';
        $nama = trim($input['nama'] ?? '');
        $kategori = trim($input['kategori'] ?? 'Makanan & Minuman');
        $satuan = trim($input['satuan'] ?? 'pcs');
        $beli = (int)($input['harga_beli'] ?? 0);
        $jual = (int)($input['harga_jual'] ?? 0);
        $stok = (int)($input['stok'] ?? 0);
        $stokMin = (int)($input['stok_min'] ?? 0);

        if (!$nama) sendError('Nama produk wajib diisi.');
        if ($jual < $beli) sendError('Harga jual tidak boleh lebih rendah dari harga beli.');

        if ($id) {
            $updated = false;
            foreach ($_SESSION['produk'] as &$p) {
                if ($p['id'] === $id) {
                    $p['nama'] = $nama;
                    $p['kategori'] = $kategori;
                    $p['satuan'] = $satuan;
                    $p['harga_beli'] = $beli;
                    $p['harga_jual'] = $jual;
                    $p['stok'] = $stok;
                    $p['stok_min'] = $stokMin;
                    $updated = true;
                    break;
                }
            }
            unset($p);
            if (!$updated) sendError('Produk tidak ditemukan.', 404);
            simpanProduk();
            sendJson(['success' => true, 'message' => 'Produk berhasil diperbarui.']);
        } else {
            $newProduct = [
                'id' => uniqid('p'),
                'nama' => $nama,
                'kategori' => $kategori,
                'satuan' => $satuan,
                'harga_beli' => $beli,
                'harga_jual' => $jual,
                'stok' => $stok,
                'stok_min' => $stokMin
            ];
            $_SESSION['produk'][] = $newProduct;
            simpanProduk();
            sendJson(['success' => true, 'message' => 'Produk berhasil ditambahkan.', 'data' => $newProduct]);
        }
        break;

    case 'produk_delete':
        requireAuth();
        $id = $input['id'] ?? '';
        if (!$id) sendError('ID produk diperlukan.');

        $_SESSION['produk'] = array_values(array_filter($_SESSION['produk'], fn($p) => $p['id'] !== $id));
        simpanProduk();
        sendJson(['success' => true, 'message' => 'Produk berhasil dihapus.']);
        break;

    // ── TRANSAKSI ─────────────────────────────────────────────
    case 'transaksi_list':
        requireAuth();
        $list = $_SESSION['transaksi'];
        usort($list, fn($a, $b) => strcmp($b['tanggal'], $a['tanggal']));
        sendJson(['success' => true, 'data' => $list]);
        break;

    case 'transaksi_save':
        requireAuth();
        $id = $input['id'] ?? '';
        $tanggal = $input['tanggal'] ?? date('Y-m-d');
        $keterangan = trim($input['keterangan'] ?? '');
        $jenis = $input['jenis'] ?? 'Pemasukan';
        $jumlah = (int)preg_replace('/\D/', '', (string)($input['jumlah'] ?? 0));
        $produkId = !empty($input['produk_id']) ? $input['produk_id'] : null;

        if (!$keterangan) sendError('Keterangan transaksi wajib diisi.');
        if ($jumlah <= 0) sendError('Jumlah harus lebih besar dari 0.');

        if ($id) {
            foreach ($_SESSION['transaksi'] as &$t) {
                if ($t['id'] === $id) {
                    $t['tanggal'] = $tanggal;
                    $t['keterangan'] = $keterangan;
                    $t['jenis'] = $jenis;
                    $t['jumlah'] = $jumlah;
                    $t['produk_id'] = $produkId;
                    break;
                }
            }
            unset($t);
            sendJson(['success' => true, 'message' => 'Transaksi berhasil diperbarui.']);
        } else {
            $newTrx = [
                'id' => uniqid('t'),
                'tanggal' => $tanggal,
                'keterangan' => $keterangan,
                'jenis' => $jenis,
                'jumlah' => $jumlah,
                'produk_id' => $produkId
            ];
            $_SESSION['transaksi'][] = $newTrx;
            sendJson(['success' => true, 'message' => 'Transaksi berhasil dicatat.', 'data' => $newTrx]);
        }
        break;

    case 'transaksi_delete':
        requireAuth();
        $id = $input['id'] ?? '';
        if (!$id) sendError('ID transaksi diperlukan.');

        $_SESSION['transaksi'] = array_values(array_filter($_SESSION['transaksi'], fn($t) => $t['id'] !== $id));
        sendJson(['success' => true, 'message' => 'Transaksi berhasil dihapus.']);
        break;

    // ── STOK ──────────────────────────────────────────────────
    case 'stok_list':
        requireAuth();
        $logs = $_SESSION['stok_log'] ?? [];
        usort($logs, fn($a, $b) => strcmp($b['tanggal'], $a['tanggal']));
        sendJson([
            'success' => true,
            'logs' => $logs,
            'produk' => array_values($_SESSION['produk'])
        ]);
        break;

    case 'stok_catat':
        requireAuth();
        $produkId = $input['produk_id'] ?? '';
        $jenis = $input['jenis'] ?? 'Masuk';
        $jumlah = (int)($input['jumlah'] ?? 0);
        $keterangan = trim($input['keterangan'] ?? '');
        $tanggal = $input['tanggal'] ?? date('Y-m-d');

        if (!$produkId) sendError('Pilih produk.');
        if ($jumlah <= 0) sendError('Jumlah harus lebih dari 0.');

        $found = false;
        foreach ($_SESSION['produk'] as &$p) {
            if ($p['id'] === $produkId) {
                if ($jenis === 'Keluar' && $p['stok'] < $jumlah) {
                    sendError("Stok {$p['nama']} tidak mencukupi (tersedia: {$p['stok']}).");
                }
                $p['stok'] = $jenis === 'Masuk' ? $p['stok'] + $jumlah : $p['stok'] - $jumlah;
                $found = true;
                break;
            }
        }
        unset($p);

        if (!$found) sendError('Produk tidak ditemukan.', 404);

        $newLog = [
            'id' => uniqid('sl'),
            'tanggal' => $tanggal,
            'produk_id' => $produkId,
            'jenis' => $jenis,
            'jumlah' => $jumlah,
            'keterangan' => $keterangan
        ];
        $_SESSION['stok_log'][] = $newLog;
        simpanProduk();

        sendJson(['success' => true, 'message' => 'Pergerakan stok berhasil dicatat.', 'data' => $newLog]);
        break;

    case 'stok_reversal':
        requireAuth();
        $id = $input['id'] ?? '';
        if (!$id) sendError('ID log diperlukan.');

        $reversed = false;
        foreach ($_SESSION['stok_log'] as $log) {
            if ($log['id'] === $id) {
                foreach ($_SESSION['produk'] as &$p) {
                    if ($p['id'] === $log['produk_id']) {
                        $p['stok'] = $log['jenis'] === 'Masuk'
                            ? max(0, $p['stok'] - (int)$log['jumlah'])
                            : $p['stok'] + (int)$log['jumlah'];
                        $reversed = true;
                        break;
                    }
                }
                unset($p);
                break;
            }
        }

        if ($reversed) {
            $_SESSION['stok_log'] = array_values(array_filter($_SESSION['stok_log'], fn($l) => $l['id'] !== $id));
            simpanProduk();
            sendJson(['success' => true, 'message' => 'Pergerakan stok berhasil dibatalkan (reversal).']);
        } else {
            sendError('Log stok tidak ditemukan.');
        }
        break;

    // ── LAPORAN ───────────────────────────────────────────────
    case 'laporan_data':
        requireAuth();
        $bulanList = [
            '01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun',
            '07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'
        ];
        $monthly = [];
        $currentYear = date('Y');

        foreach ($bulanList as $num => $nama) {
            $monthly[$num] = [
                'bulan' => $nama,
                'pemasukan' => 0,
                'pengeluaran' => 0,
                'laba' => 0
            ];
        }

        foreach ($_SESSION['transaksi'] as $t) {
            $thn = substr($t['tanggal'], 0, 4);
            $bln = substr($t['tanggal'], 5, 2);
            if ($thn === $currentYear && isset($monthly[$bln])) {
                if ($t['jenis'] === 'Pemasukan') {
                    $monthly[$bln]['pemasukan'] += (int)$t['jumlah'];
                } else {
                    $monthly[$bln]['pengeluaran'] += (int)$t['jumlah'];
                }
            }
        }

        foreach ($monthly as &$m) {
            $m['laba'] = $m['pemasukan'] - $m['pengeluaran'];
        }
        unset($m);

        sendJson([
            'success' => true,
            'year' => $currentYear,
            'monthly' => array_values($monthly),
            'saved_reports' => $_SESSION['laporan_tersimpan'] ?? []
        ]);
        break;

    case 'laporan_save':
        requireAuth();
        $judul   = trim($input['judul'] ?? '');
        $dari    = $input['tanggal_dari'] ?? '';
        $sampai  = $input['tanggal_sampai'] ?? '';
        $catatan = trim($input['catatan'] ?? '');

        if (!$judul || !$dari || !$sampai) sendError('Judul dan rentang tanggal wajib diisi.');
        if ($dari > $sampai) sendError('Tanggal awal tidak boleh lebih besar dari tanggal akhir.');

        $p = $k = 0;
        foreach ($_SESSION['transaksi'] as $t) {
            if ($t['tanggal'] >= $dari && $t['tanggal'] <= $sampai) {
                if ($t['jenis'] === 'Pemasukan') $p += (int)$t['jumlah'];
                else $k += (int)$t['jumlah'];
            }
        }

        $newReport = [
            'id' => uniqid('l'),
            'judul' => $judul,
            'tanggal_dari' => $dari,
            'tanggal_sampai' => $sampai,
            'pemasukan' => $p,
            'pengeluaran' => $k,
            'laba' => $p - $k,
            'catatan' => $catatan,
            'dibuat' => date('Y-m-d')
        ];

        $_SESSION['laporan_tersimpan'][] = $newReport;
        sendJson(['success' => true, 'message' => 'Laporan snapshot berhasil disimpan.', 'data' => $newReport]);
        break;

    case 'laporan_delete':
        requireAuth();
        $id = $input['id'] ?? '';
        if (!$id) sendError('ID laporan diperlukan.');

        $_SESSION['laporan_tersimpan'] = array_values(
            array_filter($_SESSION['laporan_tersimpan'], fn($l) => $l['id'] !== $id)
        );
        sendJson(['success' => true, 'message' => 'Laporan berhasil dihapus.']);
        break;

    // ── REKOMENDASI ───────────────────────────────────────────
    case 'rekomendasi_data':
        requireAuth();
        // Hitung produk terlaris berdasarkan transaksi pemasukan
        $productSales = [];
        foreach ($_SESSION['transaksi'] as $t) {
            if ($t['jenis'] === 'Pemasukan' && !empty($t['produk_id'])) {
                $pid = $t['produk_id'];
                if (!isset($productSales[$pid])) {
                    $productSales[$pid] = ['count' => 0, 'total' => 0];
                }
                $productSales[$pid]['count'] += 1;
                $productSales[$pid]['total'] += (int)$t['jumlah'];
            }
        }

        $bestSellers = [];
        foreach ($_SESSION['produk'] as $p) {
            $pid = $p['id'];
            if (isset($productSales[$pid])) {
                $bestSellers[] = [
                    'id' => $pid,
                    'nama' => $p['nama'],
                    'kategori' => $p['kategori'],
                    'stok' => $p['stok'],
                    'tx_count' => $productSales[$pid]['count'],
                    'tx_total' => $productSales[$pid]['total']
                ];
            }
        }
        usort($bestSellers, fn($a, $b) => $b['tx_total'] <=> $a['tx_total']);

        // Produk yang perlu restock
        $restockAlerts = array_values(array_filter($_SESSION['produk'], fn($p) => (int)$p['stok'] <= (int)$p['stok_min']));

        // Produk belum pernah terjual (slow-moving / butuh promosi)
        $slowMoving = array_values(array_filter($_SESSION['produk'], fn($p) => !isset($productSales[$p['id']])));

        sendJson([
            'success' => true,
            'best_sellers' => $bestSellers,
            'restock_alerts' => $restockAlerts,
            'slow_moving' => $slowMoving,
            'notes' => $_SESSION['catatan_rekomendasi'] ?? []
        ]);
        break;

    case 'rekomendasi_save':
        requireAuth();
        $id = $input['id'] ?? '';
        $judul = trim($input['judul'] ?? '');
        $isi = trim($input['isi'] ?? '');
        $prioritas = $input['prioritas'] ?? 'Sedang';

        if (!$judul || !$isi) sendError('Judul dan isi catatan wajib diisi.');

        if ($id) {
            foreach ($_SESSION['catatan_rekomendasi'] as &$c) {
                if ($c['id'] === $id) {
                    $c['judul'] = $judul;
                    $c['isi'] = $isi;
                    $c['prioritas'] = $prioritas;
                    break;
                }
            }
            unset($c);
            sendJson(['success' => true, 'message' => 'Catatan berhasil diperbarui.']);
        } else {
            $newNote = [
                'id' => uniqid('cr'),
                'judul' => $judul,
                'isi' => $isi,
                'prioritas' => $prioritas,
                'dibuat' => date('Y-m-d')
            ];
            $_SESSION['catatan_rekomendasi'][] = $newNote;
            sendJson(['success' => true, 'message' => 'Catatan berhasil ditambahkan.', 'data' => $newNote]);
        }
        break;

    case 'rekomendasi_delete':
        requireAuth();
        $id = $input['id'] ?? '';
        if (!$id) sendError('ID catatan diperlukan.');

        $_SESSION['catatan_rekomendasi'] = array_values(
            array_filter($_SESSION['catatan_rekomendasi'], fn($c) => $c['id'] !== $id)
        );
        sendJson(['success' => true, 'message' => 'Catatan berhasil dihapus.']);
        break;

    // ── PROFIL ────────────────────────────────────────────────
    case 'profil_get':
        requireAuth();
        $u = $_SESSION['user'];
        $akun = $_SESSION['users'][$u] ?? ['nama' => '', 'email' => ''];
        sendJson([
            'success' => true,
            'user' => [
                'username' => $u,
                'nama' => $akun['nama'] ?? $u,
                'email' => $akun['email'] ?? ''
            ],
            'profil' => $_SESSION['profil']
        ]);
        break;

    case 'profil_save':
        requireAuth();
        $_SESSION['profil'] = [
            'no_hp' => trim($input['no_hp'] ?? ''),
            'nama_usaha' => trim($input['nama_usaha'] ?? ''),
            'kategori' => $input['kategori'] ?? '',
            'alamat' => trim($input['alamat'] ?? '')
        ];
        sendJson(['success' => true, 'message' => 'Profil usaha berhasil disimpan.', 'profil' => $_SESSION['profil']]);
        break;

    default:
        sendJson([
            'status' => 'AlpetBizz REST API active',
            'version' => '1.0.0',
            'available_endpoints' => [
                'auth_check', 'login', 'register', 'logout',
                'dashboard', 'produk_list', 'produk_save', 'produk_delete',
                'transaksi_list', 'transaksi_save', 'transaksi_delete',
                'stok_list', 'stok_catat', 'stok_reversal',
                'laporan_data', 'laporan_save', 'laporan_delete',
                'rekomendasi_data', 'rekomendasi_save', 'rekomendasi_delete',
                'profil_get', 'profil_save'
            ]
        ]);
        break;
}
