<p align="center">
  <img src="https://img.shields.io/badge/version-1.0.0-blue" alt="Version" />
  <img src="https://img.shields.io/badge/platform-Web%20App%20%2B%20ESP32-blue" alt="Platform" />
  <img src="https://img.shields.io/badge/license-MIT-green" alt="License" />
</p>

# 📋 Absensi RFID - Sistem Monitoring Kehadiran Siswa

## 📖 Deskripsi

Sistem monitoring kehadiran berbasis perangkat (hardware RFID) dan webapp sebagai antarmuka pengguna. Sistem ini dirancang untuk membantu pengelolaan data siswa terkait absensi secara terintegrasi.

Melalui webapp yang disediakan, pengguna (guru) dapat mengakses informasi kehadiran siswa secara berkala, mengelola data siswa dan kelas, mengatur sesi absensi, serta melakukan rekap kehadiran.

## ✨ Fitur Utama

- Manajemen data siswa & kelas (tambah, edit, hapus)
- Pengaturan waktu sesi absensi (Masuk, Istirahat, Kembali, Pulang)
- Rekap kehadiran siswa dengan status otomatis
- Manajemen reservasi/izin siswa
- Penentuan status kehadiran dari 16 kombinasi sesi
- Monitoring perangkat (LED/LCD) dan server
- Feedback tap kartu secara real-time
- Responsif, dapat diakses dari berbagai perangkat

## 📚 Dokumentasi

| Panduan | Target |
|---------|--------|
| [👤 Panduan Pengguna](./guides/user-guide.md) | Guru / Admin (cara login, mengelola siswa, atur sesi, rekap absensi, dll) |

## 🚀 Untuk Pengguna

1. Buka webapp Absensi melalui browser.
2. Login dengan username & password dari teknisi.
3. Kelola data siswa dan kelas, atur sesi absensi.
4. Siswa melakukan tap kartu pada perangkat (LED/LCD).
5. Lihat rekap kehadiran dan lakukan finalisasi.

📖 Selengkapnya di [Panduan Pengguna](./guides/user-guide.md)

## 🏗️ Teknologi

| Teknologi | Kegunaan |
|-----------|----------|
| **Vite** | Build tool & development server |
| **Vanilla JS (ES6 Modules)** | Frontend webapp |
| WebSocket | Komunikasi real-time dengan perangkat |
| Laravel Backend | Penyimpanan data siswa, kelas, sesi, reservasi |
| ESP32 & ESP8266 | Perangkat absensi (LED/LCD) |
| MFRC522 | Modul pembaca RFID |
| LCD 16x2 (opsional) | Tampilan pada perangkat versi LCD |

## 👥 Maintainer

- Ilham – [@kyoomik](https://instagram.com/kyoomik)
- [Techno Kreatif Solusindo](https://www.instagram.com/technokreatifsolusindo)

## 📄 Lisensi

MIT License
