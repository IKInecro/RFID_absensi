# Kerangka Laporan / Makalah: Sistem Absensi RFID Berbasis IoT & Web

Gunakan kerangka ini untuk mempercepat pembuatan laporanmu. Bagian-bagian teknis sudah saya sesuaikan dengan codingan kita.

---

## BAB 1: PENDAHULUAN

### 1.1 Latar Belakang
*   Jelaskan masalah absensi manual (lambat, antre, potensi titip absen, rekap data susah).
*   Solusi teknologi: Menggunakan RFID (Radio Frequency Identification) yang terintegrasi dengan database terpusat secara real-time.
*   Keunggulan sistem ini: Cepat (tap & go), data langsung masuk server, bisa dipantau via web dashboard.

### 1.2 Rumusan Masalah
1.  Bagaimana merancang alat absensi berbasis IoT menggunakan ESP8266 dan RFID RC522?
2.  Bagaimana membangun sistem informasi berbasis web untuk mengelola data absensi siswa secara real-time?
3.  Bagaimana mengintegrasikan perangkat keras dan perangkat lunak agar sinkronisasi data berjalan lancar?

### 1.3 Tujuan Penelitian
1.  Membuat prototipe alat absensi otomatis.
2.  Mempermudah pihak sekolah (admin/guru) dalam merekap data kehadiran.
3.  Menyediakan antarmuka (interface) yang modern dan responsif untuk monitoring kehadiran.

---

## BAB 2: LANDASAN TEORI

### 2.1 Internet of Things (IoT)
*   Penjelasan singkat konsep IoT (benda fisik yang terkoneksi internet).

### 2.2 Perangkat Keras (Hardware)
1.  **NodeMCU ESP8266**: Mikrokontroler dengan fitur WiFi built-in sebagai otak pemrosesan data.
2.  **RFID MFRC522**: Sensor pembaca kartu/tag RFID frekuensi 13.56 MHz.
3.  **Piezo Buzzer**: Indikator suara (beep) saat berhasil/gagal absen.
4.  **LED Indikator**: Visualisasi status koneksi (Merah: Disconnect, Hijau: Connected).

### 2.3 Perangkat Lunak (Software)
1.  **Bahasa C++ (Arduino IDE)**: Untuk memprogram logika di ESP8266 (`mrc.ino`).
2.  **PHP (Hypertext Preprocessor)**: Bahasa server-side untuk menangani API dan logika backend (`api/attendance.php`).
3.  **MySQL / MariaDB**: Database management system untuk menyimpan data siswa dan log absensi.
4.  **Tailwind CSS**: Framework CSS untuk desain antarmuka yang modern dan responsif.
5.  **XAMPP**: Paket web server lokal (Apache + MySQL) untuk menjalankan sistem.

---

## BAB 3: PERANCANGAN SISTEM

### 3.1 Perancangan Perangkat Keras
*   *Jelaskan skema kabel:*
    *   SDA (SS) -> D8 (GPIO15)
    *   SCK -> D5 (GPIO14)
    *   MOSI -> D7 (GPIO13)
    *   MISO -> D6 (GPIO12)
    *   RST -> D3 (GPIO0)
    *   Buzzer -> D1 (GPIO5)

### 3.2 Perancangan Database (ERD)
Jelaskan tabel-tabel utama:
1.  **`students`**: `id`, `card_id` (UID), `name`, `class`, `profile_pic`.
2.  **`attendance_log`**: `id`, `student_id`, `timestamp`, `status` (On Time/Late), `device_id`.
3.  **`schedules`**: `day`, `time_in`, `time_out`, `grace_period` (untuk logika terlambat).
4.  **`settings`**: `mode` (Normal/Register/Tester).

### 3.3 Alur Program (Flowchart)
*(Gunakan deskripsi dari prompt Boardmix tadi)*
1.  Siswa tap kartu.
2.  ESP8266 baca UID -> Kirim HTTP POST ke Server.
3.  Server cek UID di database.
4.  Server hitung jam masuk vs jadwal.
5.  Server simpan data -> Kirim respon balik.
6.  ESP8266 bunyikan buzzer.

---

## BAB 4: IMPLEMENTASI DAN PEMBAHASAN

### 4.1 Implementasi Hardware
*   (Foto alat yang sudah dirakit).
*   Jelaskan fitur "Multi-SSID Failover" di kode `mrc.ino` (Alat otomatis pindah WiFi jika yang satu mati).

### 4.2 Implementasi Software
1.  **Halaman Dashboard**: Menampilkan statistik hadir, terlambat, alpha (Tampilkan screenshot dashboard).
2.  **Fitur Live Feed**: Data muncul otomatis tanpa refresh halaman (menggunakan AJAX/JSON polling).
3.  **Fitur Tester Mode**: Untuk mengecek kartu rusak/baru tanpa mengotori database absensi utama.
4.  **API Backend**: Jelaskan sedikit kode `api/attendance.php` bagian logika penentuan status "Late" atau "On Time".

### 4.3 Pengujian Sistem
*   **Skenario 1: Kartu Terdaftar**. Hasil: Data masuk, status "Hadir", Buzzer bunyi 1x.
*   **Skenario 2: Kartu Tidak Terdaftar**. Hasil: Data ditolak, respon "Unknown", Buzzer bunyi panjang/beda nada (jika diatur).
*   **Skenario 3: Absen Terlambat**. Hasil: Status di database tercatat "Late".
*   **Skenario 4: Koneksi Putus**. Hasil: LED Merah menyala, alat mencoba reconnecting.

---

## BAB 5: PENUTUP

### 5.1 Kesimpulan
1.  Sistem berhasil mencatat kehadiran dalam waktu < 2 detik.
2.  Integrasi IoT dan Web Server berjalan stabil menggunakan protokol HTTP.
3.  Fitur kustomisasi domain lokal (`absen.smapij`) memudahkan akses pengguna tanpa menghafal IP.

### 5.2 Saran
1.  Pengembangan ke depan bisa menggunakan protokol MQTT agar lebih ringan dibanding HTTP.
2.  Penambahan fitur notifikasi WhatsApp ke orang tua saat siswa absen.
3.  Implementasi HTTPS untuk keamanan data yang lebih baik.
