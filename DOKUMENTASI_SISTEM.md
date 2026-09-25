# Dokumentasi Sistem Informasi Klinik Gigi

## Gambaran Umum

Sistem Informasi Klinik Gigi adalah aplikasi web untuk membantu proses pelayanan pasien di klinik gigi. Sistem digunakan mulai dari pendaftaran pasien, pengambilan antrean, pemeriksaan dokter, pencatatan rekam medis, hingga pembuatan laporan.

Sistem melibatkan tiga pengguna utama, yaitu admin atau petugas, dokter, dan pasien. Setiap pengguna memperoleh menu sesuai tugasnya.

## Tujuan Sistem

- Mempermudah pengelolaan data pasien.
- Mempercepat proses pendaftaran dan antrean.
- Membantu dokter mencatat hasil pemeriksaan gigi.
- Memudahkan pasien memantau antrean dan jadwal kontrol.
- Menyediakan laporan pelayanan klinik.

## Teknologi yang Digunakan

| Komponen | Teknologi |
|---|---|
| Bahasa pemrograman | PHP |
| Framework | Laravel |
| Tampilan | Blade, Tailwind CSS, JavaScript |
| Basis data | SQLite |
| Grafik laporan | Chart.js |
| Pengelolaan aset | Vite |

## Pengguna Sistem

| Pengguna | Peran dalam Sistem |
|---|---|
| Admin/Petugas | Mengelola pasien, antrean, pengguna, ruangan, jadwal, laporan, dan pengaturan klinik |
| Dokter | Melakukan pemeriksaan dan mengisi rekam medis pasien |
| Pasien | Mengambil antrean, memantau antrean, melihat riwayat pemeriksaan, serta jadwal kontrol |

## Menu Utama

### Admin atau Petugas

- Dashboard
- Data pasien
- Antrean
- Rekam medis
- Jadwal kontrol
- Laporan
- Data pengguna
- Data ruangan
- Pengaturan sistem
- Pengaturan layar antrean

### Dokter

- Dashboard
- Data pasien
- Antrean
- Rekam medis
- Jadwal kontrol
- Laporan

### Pasien

- Dashboard pasien
- Antrean saya
- Riwayat kunjungan
- Odontogram
- Jadwal kontrol
- Notifikasi
- Profil

## Alur Sistem

### 1. Alur Pendaftaran Pasien

Pasien dapat didaftarkan oleh petugas atau melakukan registrasi secara mandiri.

```text
Pasien baru
  -> Mengisi data diri
  -> Sistem membuat nomor rekam medis
  -> Data pasien tersimpan
  -> Pasien dapat mengambil antrean
```

Untuk pasien lama yang belum memiliki akun, pasien melakukan verifikasi menggunakan NIK dan nomor telepon. Setelah data sesuai, pasien dapat membuat akun untuk masuk ke portal pasien.

### 2. Alur Antrean oleh Petugas

Petugas membuat antrean ketika pasien datang ke klinik.

```text
Petugas mencari data pasien
  -> Memilih pasien
  -> Mengisi keluhan dan ruangan bila diperlukan
  -> Sistem membuat nomor antrean
  -> Nomor antrean dapat dicetak
  -> Pasien menunggu panggilan
```

Petugas dapat melakukan tiga tindakan terhadap antrean:

- Memanggil pasien.
- Memanggil ulang pasien.
- Melewati pasien yang tidak hadir.

### 3. Alur Antrean Mandiri Pasien

Pasien yang telah memiliki akun dapat mengambil antrean melalui portal pasien atau check-in QR code di klinik.

```text
Pasien login
  -> Memilih layanan/ruangan bila tersedia
  -> Mengisi keluhan
  -> Mengambil nomor antrean
  -> Memantau posisi antrean
  -> Menunggu dipanggil
```

Pasien dapat melihat nomor antrean, jumlah pasien di depan, dan perkiraan waktu tunggu.

### 4. Alur Layar Antrean

Layar antrean ditempatkan di ruang tunggu dan dapat dibuka tanpa login.

```text
Petugas memanggil antrean
  -> Status antrean berubah
  -> Layar menampilkan nomor dan ruangan
  -> Sistem memutar suara panggilan
  -> Pasien menuju ruang pemeriksaan
```

Layar juga menampilkan antrean berikutnya dan QR code untuk check-in pasien.

### 5. Alur Pemeriksaan Dokter

Dokter memulai pemeriksaan setelah pasien dipanggil oleh petugas.

```text
Pasien dipanggil
  -> Dokter memulai pemeriksaan
  -> Sistem membuat data kunjungan
  -> Dokter mengisi hasil pemeriksaan
  -> Dokter mencatat tindakan dan kondisi gigi
  -> Dokter menyelesaikan pemeriksaan
  -> Antrean berubah menjadi selesai
```

Data yang dapat dicatat dokter:

- Keluhan pasien.
- Diagnosis.
- Kode ICD-10 bila diperlukan.
- Catatan pemeriksaan.
- Tindakan perawatan.
- Jadwal kontrol berikutnya.
- Lampiran seperti foto, rontgen, atau dokumen pendukung.

### 6. Alur Odontogram

Odontogram digunakan untuk mencatat kondisi gigi pasien. Dokter memilih gigi pada tampilan odontogram, kemudian mengisi kondisi dan catatan gigi tersebut.

```text
Dokter membuka odontogram
  -> Memilih nomor gigi
  -> Mengisi kondisi dan bagian gigi
  -> Data tersimpan pada rekam medis
  -> Pasien dapat melihat riwayat odontogram
```

Pasien dapat membandingkan odontogram dari pemeriksaan yang berbeda untuk mengetahui perubahan kondisi gigi.

### 7. Alur Jadwal Kontrol

Jika pasien memerlukan pemeriksaan lanjutan, dokter membuat jadwal kontrol saat pemeriksaan.

```text
Dokter menentukan tanggal kontrol
  -> Jadwal tersimpan
  -> Pasien melihat jadwal pada portal
  -> Petugas menandai selesai atau dibatalkan
```

Sistem menampilkan pengingat kontrol yang terlambat, kontrol hari ini, atau kontrol esok hari.

### 8. Alur Riwayat Pasien

Pasien dapat melihat data pelayanan sendiri melalui portal.

```text
Pasien login
  -> Membuka riwayat kunjungan
  -> Melihat diagnosis dan tindakan
  -> Membuka odontogram
  -> Melihat jadwal kontrol
```

Data identitas utama pasien tidak dapat diubah melalui portal. Pasien hanya dapat mengubah data kontak dan akun, seperti alamat, nomor telepon, email, dan kata sandi.

### 9. Alur Laporan

Admin atau dokter dapat membuka laporan untuk melihat hasil pelayanan klinik.

```text
Pengguna memilih jenis laporan
  -> Memilih periode atau filter
  -> Sistem mengolah data
  -> Sistem menampilkan tabel dan grafik
```

Jenis laporan:

- Laporan kunjungan pasien.
- Laporan antrean.
- Laporan data pasien.
- Laporan tindakan perawatan.
- Laporan waktu pelayanan.

## Basis Data

Basis data menyimpan data utama pelayanan klinik dalam beberapa tabel.

| Tabel | Kegunaan |
|---|---|
| `users` | Menyimpan akun admin, dokter, dan pasien |
| `patients` | Menyimpan identitas pasien |
| `patient_medical_histories` | Menyimpan riwayat kesehatan pasien |
| `queues` | Menyimpan nomor dan status antrean |
| `visits` | Menyimpan data kunjungan dan pemeriksaan |
| `treatments` | Menyimpan tindakan perawatan pasien |
| `odontograms` | Menyimpan data pemeriksaan kondisi gigi |
| `odontogram_teeth` | Menyimpan detail kondisi setiap gigi |
| `attachments` | Menyimpan data lampiran pemeriksaan |
| `control_schedules` | Menyimpan jadwal kontrol pasien |
| `rooms` | Menyimpan data ruang pemeriksaan |
| `room_schedules` | Menyimpan jadwal praktik ruangan |

Hubungan data utama:

```text
Pasien
  -> memiliki riwayat medis
  -> memiliki antrean
  -> memiliki kunjungan
  -> memiliki odontogram
  -> memiliki jadwal kontrol

Kunjungan
  -> memiliki tindakan
  -> memiliki odontogram
  -> memiliki lampiran

Dokter
  -> menangani kunjungan pasien
  -> dapat ditempatkan pada ruangan pemeriksaan
```

## Dashboard

Dashboard memberikan ringkasan pelayanan klinik pada hari berjalan, seperti:

- Jumlah antrean.
- Jumlah antrean menunggu, dipanggil, diperiksa, dan selesai.
- Jumlah pasien.
- Rata-rata waktu tunggu dan pemeriksaan.
- Grafik antrean harian.
- Data antrean dan kunjungan terbaru.

## Kesimpulan

Sistem Informasi Klinik Gigi membantu klinik menjalankan pelayanan pasien secara lebih teratur. Sistem menghubungkan proses pendaftaran, antrean, pemeriksaan, rekam medis, odontogram, jadwal kontrol, dan laporan. Pasien juga dapat mengakses informasi pelayanan secara mandiri melalui portal pasien.
