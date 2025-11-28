# Panduan Konfigurasi Domain Lokal (absen.smapij)

Panduan ini akan membantu Anda mengatur agar website bisa diakses melalui `http://absen.smapij` di komputer ini.

## Langkah 1: Edit File Hosts (Agar komputer mengenali nama domain)

1.  Buka **Notepad** sebagai **Administrator** (Klik kanan Notepad -> Run as Administrator).
2.  Buka file: `C:\Windows\System32\drivers\etc\hosts`
    *   *Catatan: Anda mungkin perlu memilih "All Files (*.*)" di jendela Open agar file hosts terlihat.*
3.  Tambahkan baris berikut di bagian paling bawah file:
    ```
    127.0.0.1 absen.smapij
    ```
4.  Simpan file (Ctrl+S).

## Langkah 2: Konfigurasi Apache (Agar XAMPP mengarahkan ke folder yang benar)

1.  Buka file konfigurasi Virtual Hosts XAMPP. Biasanya ada di:
    `e:\xam\apache\conf\extra\httpd-vhosts.conf`
2.  Tambahkan konfigurasi berikut di bagian paling bawah:

    ```apache
    <VirtualHost *:80>
        ServerAdmin webmaster@absen.smapij
        DocumentRoot "e:/xam/htdocs/RFID"
        ServerName absen.smapij
        ErrorLog "logs/absen.smapij-error.log"
        CustomLog "logs/absen.smapij-access.log" common
        <Directory "e:/xam/htdocs/RFID">
            Options Indexes FollowSymLinks Includes ExecCGI
            AllowOverride All
            Require all granted
        </Directory>
    </VirtualHost>
    ```
3.  Simpan file tersebut.

## Langkah 3: Restart Apache

1.  Buka **XAMPP Control Panel**.
2.  Klik **Stop** pada module Apache.
3.  Tunggu sebentar, lalu klik **Start** lagi.

## Selesai!

Sekarang Anda bisa mengakses website melalui browser di: [http://absen.smapij](http://absen.smapij)

---

### ⚠️ Catatan Penting untuk Alat RFID (Arduino/ESP8266)

Alat RFID Anda (`mrc.ino`) **TIDAK BISA** menggunakan nama `absen.smapij` ini karena alat tersebut tidak membaca file hosts di komputer Anda.

Untuk alat RFID, **tetap gunakan alamat IP** komputer Anda (seperti yang sudah ada di codingan `mrc.ino`):
`http://192.168.100.184/RFID/api/attendance.php`

Jadi:
- **Di Browser (Laptop/PC)**: Bisa pakai `absen.smapij` (lebih mudah diingat).
- **Di Alat RFID**: Tetap pakai IP `192.168.100.184`.
