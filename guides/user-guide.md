# HANDBOOK PROJECT ABSENSI

**Edisi: 1.0**  
**Target Pembaca: Guru / Admin (Pengguna Akhir)**

---

## 1. Pendahuluan

Project ini merupakan sistem monitoring kehadiran berbasis perangkat (hardware) dan webapp sebagai antarmuka pengguna (UI). Sistem ini dirancang untuk membantu pengelolaan data siswa terkait absensi secara terintegrasi.

Melalui webapp yang telah disediakan, pengguna—khususnya guru—dapat mengakses informasi kehadiran siswa secara berkala. Dengan adanya sistem ini, proses pencatatan dan penentuan kehadiran siswa tidak lagi dilakukan secara manual.

Project ini bertujuan untuk meningkatkan efisiensi, akurasi, dan kemudahan dalam pengelolaan kehadiran siswa.

---

## 2. Web App

### 2.1. Preview

Webapp absensi berbasis RFID yang menyediakan berbagai fitur untuk mendukung manajemen kehadiran siswa serta sistem penentuan status kehadiran siswa. Webapp ini juga bersifat responsif, sehingga tampilan antarmuka dapat menyesuaikan perangkat yang digunakan oleh pengguna.

Selain itu, webapp dilengkapi:
- Fitur filter pada tabel dan daftar data
- Optimasi performa untuk kecepatan tampilan data
- Pengaturan state perangkat berdasarkan halaman yang diakses

### 2.2. Gambaran Umum

Webapp ini membantu Anda:

- Memantau jumlah status kehadiran (total siswa, hadir, tidak hadir) secara berkala
- Mengelola data siswa dan kelas
- Mengatur waktu tiap sesi (Masuk, Istirahat, Kembali, Pulang)
- Merekap kehadiran siswa berdasarkan tiap sesi dan penilaian
- Mengelola reservasi/izin siswa
- Mengatur bagaimana sistem menentukan status kehadiran siswa
- Melihat feedback dari tap kartu siswa dari tiap alat
- Memonitoring alat yang terdaftar dan server

---

## 3. Login

- Masukkan **Username** dan **Password** yang telah didaftarkan oleh teknisi.
- Klik tombol **Login**.
- Jika berhasil, Anda akan masuk ke halaman Dashboard.

---

## 4. Antarmuka & Fitur tiap Halaman

### 4.1. Navbar (Menu Atas)

| Menu | Fungsi |
|------|--------|
| Home | Menampilkan status kehadiran siswa |
| Data Siswa | Mengelola kelas dan data seluruh siswa |
| Absensi | Mengatur waktu tiap sesi dan finalisasi kehadiran |
| Reservasi | Mengelola data reservasi/izin siswa |
| Penilaian | Mengatur kombinasi untuk finalisasi kehadiran |
| Monitoring | Menampilkan sidebar status alat yang terdaftar |
| Setting | Menampilkan sidebar status server |
| Role Pengguna | Dropdown untuk logout |

### 4.2. Halaman Home

- Menampilkan nama dan visi misi sekolah
- Tombol **Mulai dengan Scan** (arah ke halaman Scan Kartu) dan **Analisis** (arah ke halaman Absensi)
- 3 card:
  - **Total students** – jumlah siswa terdaftar
  - **Present today** – siswa yang melakukan tap hari ini
  - **Absent today** – siswa yang tidak melakukan tap hari ini

### 4.3. Halaman Data Siswa

Terbagi menjadi dua bagian: **Manage Kelas** dan **Manage Siswa**.

#### Manage Kelas

| Elemen | Fungsi |
|--------|--------|
| Tombol delete | Menghapus kelas (setelah diklik, muncul konfirmasi) |
| Daftar kelas | Klik untuk edit nama kelas |
| Tombol + | Menambah kelas baru |

> 💡 **Catatan:** Nama kelas tidak boleh sama. Perubahan (tambah, edit, delete) hanya memperbarui tampilan. Klik tombol **Save Changes** untuk menyimpan ke server.

#### Manage Siswa

| Elemen | Fungsi |
|--------|--------|
| Tombol Tambah Siswa | Arah ke halaman form pendaftaran siswa |
| Filter | Filter berdasarkan nama siswa dan kelas |
| Tabel siswa | Menampilkan data diri & kehadiran siswa, dengan tombol ✎ (edit) dan 🗑️ (hapus) |

> 💡 **Catatan:**
> - Pada halaman edit/tambah siswa, state perangkat otomatis berubah menjadi **register/update**. Untuk mengembalikan ke state login, buka halaman **Scan Kartu**.
> - Isi inputan ID kartu dengan melakukan **tap kartu pada alat**.
> - ID kartu tidak boleh sama.
> - Tabel memiliki pagination (10 siswa per halaman).

### 4.4. Halaman Absensi

#### Manage Sesi Absensi

- Terdiri dari 4 sesi: **Masuk**, **Istirahat**, **Kembali**, **Pulang**.
- Tentukan batas mulai dan akhir tiap sesi.
- Klik **Simpan Pengaturan** untuk menerapkan.

> 💡 Rentang waktu yang sama tidak diizinkan.

#### Rekap Absensi Siswa

- Filter berdasarkan nama siswa, tanggal, dan kelas.
- Tombol **Rekaptulasi** → memproses penentuan status dan keterangan tiap siswa. Setelah selesai, tombol berubah menjadi "Rekaptulasi Berhasil".
- Tabel menampilkan nama siswa, waktu tap tiap sesi, status, dan keterangan.

> 💡 Rekaptulasi dapat dilakukan kapan saja, cukup dengan filter yang diinginkan.

### 4.5. Halaman Reservasi

#### Manage Reservasi

- Inputan: **Waktu Mulai**, **Waktu Akhir**, **Keterangan**.
- Filter berdasarkan kelas dan siswa.
- Daftar siswa dalam bentuk grid. Klik siswa (bisa lebih dari satu) untuk memilih, lalu klik **Simpan Status**.

> 💡 Daftar siswa muncul bertahap saat scroll. Tombol **Pilih Semua** membantu memilih semua siswa (atau berdasarkan filter kelas).

#### Aktivitas Terbaru

- Menampilkan 10 reservasi terbaru (nama siswa, waktu, keterangan).
- Klik tombol **X** untuk membatalkan reservasi.

### 4.6. Halaman Penilaian

- Tombol **Panduan** untuk menjelaskan maksud tiap kolom tabel.
- Tabel **16 Kombinasi** dari 4 sesi (setiap sesi: Masuk atau Alpa). User dapat menentukan status untuk setiap kombinasi.

> 💡 Penentuan ini digunakan saat proses rekaptulasi.

### 4.7. Halaman Scan Kartu

- Menampilkan keterangan saat siswa melakukan tap kartu pada alat.
- Halaman ini secara otomatis mengganti state perangkat menjadi **login**.
- Berguna untuk mereset state setelah sebelumnya mengakses halaman edit/tambah.
- Contoh keterangan: *Kartu tidak dikenal*, *Keterlambatan*, *Berhasil Masuk*, *Diluar Sesi*.

### 4.8. Sidebar Monitoring

- Muncul saat mengklik menu **Monitoring** pada navbar.
- Menampilkan daftar alat terdaftar: nama, IP, dan status perangkat.

### 4.9. Sidebar Setting

- Muncul saat mengklik menu **Setting** pada navbar.
- Menampilkan status server (yang menjembatani komunikasi) dan jumlah device terhubung.

---

## 5. Perangkat (Alat Absensi)

Alat Absensi terdiri dari dua versi: **LED** dan **LCD**. Keduanya memiliki fungsi utama yang sama: menerima state dari sistem dan media input tap kartu.

### 5.1. State Perangkat

- **State default saat pertama kali dijalankan:** `login` → setiap tap kartu diproses sebagai absensi.
- Perubahan state dilakukan melalui webapp (misal saat admin membuka halaman edit/tambah siswa).
- Setelah selesai, **kembalikan state ke login** melalui halaman **Scan Kartu** pada webapp.

### 5.2. Versi LED

- Feedback tap kartu menggunakan **LED** (hijau = berhasil, kuning = error/koneksi putus).
- **Tidak ada indikator state** perangkat. Admin harus rajin mereset ke login melalui halaman Scan Kartu.

### 5.3. Versi LCD

- Menggunakan layar LCD 16x2 sebagai tampilan dan feedback.
- **Menu utama:**
  - **Menu Absensi** → state login, tampilan "AREA ABSENSI". Setelah tap, hasil tampil beberapa detik lalu kembali ke AREA ABSENSI.
  - **Menu Reservasi** → state reservasi, tampilan "AREA RESERVASI". Tap kartu akan mengambil data reservasi siswa dari server. Siswa bisa membatalkan reservasi langsung dari sini.
- **Navigasi tombol:** ATAS, BAWAH, KEMBALI, OKE.
- Saat admin mengakses halaman tambah/edit siswa, perangkat menampilkan **"AREA Admin"** sebagai indikator mode administrasi.

> 💡 **Disarankan** untuk kembali ke menu utama setelah berpindah state (reservasi atau mode administrasi) untuk mereset state dan menjaga keamanan.

---

## 6. Perawatan & Troubleshooting Sederhana untuk Pengguna

### A. Indikator LED (versi LED)

| Kejadian | Arti |
|----------|------|
| LED hijau berkedip 2x | Tap kartu berhasil |
| LED kuning berkedip 3x | Koneksi ke server terputus. Tunggu, hubungi teknisi jika terus terjadi. |
| LED kuning berkedip terus-menerus saat boot | Perangkat dalam mode pengaturan awal (lihat bagian B). |

### B. Jika Perangkat Baru atau Tidak Bisa Connect WiFi

1. Perangkat akan memancarkan WiFi bernama `ESP_RFID_Config`.
2. Hubungkan HP/laptop ke WiFi tersebut.
3. Buka browser, akses `192.168.4.1`.
4. Isi **SSID** dan **password** WiFi sekolah, serta **Secret Key** (dari teknisi).
5. Klik **Save**. Perangkat akan restart dan siap digunakan.

### C. Cara Reset Perangkat ke Pengaturan Pabrik (khusus versi LCD)

- Saat perangkat dinyalakan, **tahan tombol OK** hingga layar menampilkan "WiFi Reset".
- Lepas tombol, perangkat akan restart dan masuk mode AP (seperti langkah B).

> 💡 Reset pabrik menghapus semua konfigurasi WiFi dan Secret Key. Anda perlu mengatur ulang seperti langkah B.

### D. Kartu tidak terbaca

- Pastikan kartu ditempelkan dengan benar pada area baca RFID.
- Coba kartu lain.
- Jika tetap tidak terbaca, hubungi teknisi.

---

## 7. Troubleshooting Umum Webapp

| Masalah | Solusi |
|---------|--------|
| Tidak bisa login | Cek username/password, hubungi teknisi jika lupa. |
| Data siswa tidak muncul | Refresh halaman, cek koneksi internet. |
| Tombol Save Changes tidak berfungsi | Pastikan tidak ada nama kelas duplikat. |
| Rekap tidak berubah setelah rekaptulasi | Coba filter ulang, pastikan waktu sesi sudah diatur. |
| Perangkat tidak terdaftar di sidebar Monitoring | Hubungi teknisi untuk pendaftaran perangkat. |

---

**Dokumen ini untuk pengguna akhir. Untuk panduan teknis (jika tersedia), hubungi tim teknisi.**
