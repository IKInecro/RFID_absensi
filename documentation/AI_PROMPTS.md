# Prompt AI untuk Dokumentasi Sistem RFID

Gunakan prompt mendetail ini dengan alat AI (seperti ChatGPT, Claude, Boardmix, dll.) untuk menghasilkan Flowchart dan Mind Map yang komprehensif untuk Sistem Absensi RFID.

---

## Prompt 1: Flowchart Algoritma Mendetail

**Salin dan tempel prompt ini untuk membuat Flowchart:**

> Buatkan flowchart yang sangat mendetail untuk Sistem Absensi RFID berbasis IoT. Sistem ini terdiri dari perangkat keras ESP8266 dan server web PHP/MySQL.
>
> **Node dan Logika yang Harus Disertakan:**
>
> 1.  **Mulai**: Pengguna menempelkan Kartu RFID pada pembaca MFRC522 yang terhubung ke ESP8266.
> 2.  **Logika Perangkat Keras**:
>     *   Perangkat membaca UID kartu.
>     *   Perangkat terhubung ke WiFi (Logika: Coba SSID Utama -> Gagal -> Coba SSID Cadangan -> Gagal -> Restart).
>     *   Perangkat mengirim permintaan HTTP POST ke URL Server (`/api/attendance.php`) dengan data JSON: `{ "device_id": "...", "uid": "..." }`.
> 3.  **Logika Sisi Server (Masuk API)**:
>     *   Terima data POST.
>     *   **Cek Database**: Ambil `reg_mode` (Mode Daftar) dan `test_mode` (Mode Tester) dari tabel `settings`.
> 4.  **Keputusan: Cek Mode**:
>     *   **Jalur A: Mode Daftar (`reg_mode = 1`)**:
>         *   Cek apakah UID Kartu ada di tabel `students`.
>         *   **Jika Tidak**: Masukkan data baru ke tabel `students` dengan nama default "Baru". Kembalikan JSON "Kartu Terdaftar".
>         *   **Jika Ya**: Kembalikan JSON "Kartu Sudah Terdaftar".
>     *   **Jalur B: Mode Tester (`test_mode = 1`)**:
>         *   *Catatan: Tidak menyimpan ke database, hanya untuk debugging.*
>         *   Ambil Jadwal untuk Hari ini (Senin-Minggu).
>         *   **Hitung Status**:
>             *   Jika Libur: Status = "Libur".
>             *   Jika Waktu <= Jam Masuk: Status = "On Time".
>             *   Jika Waktu <= Batas Toleransi: Status = "Toleransi".
>             *   Jika Waktu <= Jam Pulang: Status = "Late" (Terlambat).
>             *   Lainnya: Status = "Out of Schedule" (Di Luar Jadwal).
>         *   **Aksi**: Tambahkan data ke file `test_data.json`.
>         *   Kembalikan JSON dengan Status dan Nama Siswa (jika terdaftar).
>     *   **Jalur C: Mode Absensi Normal (Default)**:
>         *   Cek apakah UID Kartu ada di tabel `students`.
>         *   **Jika Tidak**: Kembalikan Error JSON "Kartu Tidak Terdaftar".
>         *   **Jika Ya**: Ambil Data Siswa.
>         *   **Ambil Jadwal**: Dapatkan `time_in`, `time_out`, `grace_period`, `is_holiday` untuk hari ini.
>         *   **Hitung Status** (Logika sama dengan Mode Tester: On Time / Toleransi / Late / Out of Schedule / Libur).
>         *   **Cek Duplikat**: Query tabel `attendance_log` untuk ID Siswa ini + Tanggal Hari Ini.
>         *   **Jika Ada**: Kembalikan JSON "Sudah Absen" (Lewati Insert).
>         *   **Jika Baru**:
>             *   Masukkan data ke tabel `attendance_log`.
>             *   Perbarui `live_feed.json` untuk dashboard real-time.
>             *   Kembalikan JSON "Absensi Tercatat" dengan Nama Siswa dan Status.
> 5.  **Selesai**: Perangkat menerima respons JSON dan berbunyi beep (Beep panjang untuk sukses, beep cepat untuk error).

---

## Prompt 2: Mind Map Struktur Sistem

**Salin dan tempel prompt ini untuk membuat Mind Map:**

> Buatkan Mind Map yang komprehensif untuk "Sistem Absensi RFID Pintar" yang merinci seluruh arsitektur dari perangkat keras hingga perangkat lunak.
>
> **Node Pusat**: Sistem Absensi RFID Pintar
>
> **Cabang 1: Lapisan Perangkat Keras (Hardware)**
> *   **Mikrokontroler**: ESP8266 (NodeMCU) - Menangani WiFi dan permintaan HTTP.
> *   **Sensor**: Pembaca RFID MFRC522 (Antarmuka SPI).
> *   **Output**: Buzzer (Aktif Low/High), Indikator LED (Status).
> *   **Daya**: 5V USB / Power Supply Eksternal.
> *   **Konektivitas**: WiFi (2.4GHz) dengan logika Failover Multi-SSID.
>
> **Cabang 2: Lapisan Backend (Server)**
> *   **Bahasa**: PHP 7.4+ (Native).
> *   **Database**: MySQL / MariaDB.
>     *   *Tabel `students`*: id, nama, kelas, card_id, foto_profil.
>     *   *Tabel `attendance_log`*: id, student_id, timestamp, status (On Time/Late), device_id.
>     *   *Tabel `schedules`*: hari, jam_masuk, jam_pulang, batas_toleransi, is_holiday.
>     *   *Tabel `settings`*: reg_mode, test_mode.
> *   **Endpoint API**:
>     *   `api/attendance.php`: Penerima utama untuk perangkat IoT.
>     *   `api/updates.php`: Endpoint long-polling untuk pembaruan UI Real-time.
>     *   `api/get_feed.php`: Pengambilan data awal.
>
> **Cabang 3: Lapisan Frontend (Web UI)**
> *   **Tech Stack**: HTML5, Tailwind CSS (CDN), Vanilla JavaScript.
> *   **Halaman**:
>     *   *Dashboard*: Statistik (Hadir, Terlambat, Absen), Grafik.
>     *   *Live Feed*: Daftar scrolling real-time dari tap yang masuk.
>     *   *Siswa*: Manajemen CRUD, Upload Foto, Pemetaan Kartu ID.
>     *   *Jadwal*: Pengaturan dinamis untuk setiap hari dalam seminggu.
>     *   *Mode Tester*: Alat simulasi, Konsol debug, Indikator Sistem Aktif.
>     *   *Ekspor*: Pemfilteran rentang tanggal, Ekspor ke CSV/Excel.
>
> **Cabang 4: Fitur Utama**
> *   **Pembaruan Real-Time**: Menggunakan Long-Polling (AJAX) untuk mendorong data ke browser tanpa refresh.
> *   **Anti-Duplikat**: Mencegah tap ganda untuk siswa yang sama pada hari yang sama.
> *   **Status Pintar**: Menghitung otomatis "Toleransi" vs "Terlambat".
> *   **Mode Ganda**:
>     *   *Mode Daftar*: Untuk menambahkan kartu baru dengan mudah.
>     *   *Mode Tester*: Untuk debugging tanpa mengotori database utama.
>
> **Cabang 5: Aktor (Pengguna)**
> *   **Administrator**: Akses penuh ke pengaturan, laporan, dan data siswa.
> *   **Siswa**: Interaksi pasif (Tap kartu), Lihat konfirmasi di layar (jika mode kiosk).
> *   **Sistem**: Pencatatan otomatis, Perhitungan waktu, Sinkronisasi data.
