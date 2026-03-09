## Role Mahasiswa

### Workflow change password default
- [ ] Mahasiswa harus mengganti password default saat login pertama kali
- [ ] Tampilkan modal form untuk mengganti password beserta nomer whatsapp dan email untuk keperluan notifikasi aplikasi. 


### Fitur Pengajuan Surat
- [x] Tambah fitur pengajuan surat pemohonan Cuti/Aktif Kuliah
- [x] Tambah fitur pengajuan surat pemohonan Pindah Kelas
- [x] Tambah fitur pengajuan surat pemohonan Pindah Perguruan Tinggi
- [x] Tambah fitur pengajuan surat pemohonan Pengunduran Diri
- [x] Tambah fitur pengajuan surat pemohonan Izin Tempat PKL
- [x] Tambah fitur pengajuan surat pemohonan Perolehan Data PKL/TA

> Khusus untuk pengajuan surat permohonan izin tempat PKL, Mahasiswa diperbolehkan menginputkan data mahasiswa lain **(teman)** yang akan mengikuti PKL bersama.


### Workflow reset password
- Mahasiswa dapat mereset password melalui url yang dikirimkan ke email atau nomer whatsapp yang terdaftar.
- Admin akan mendapatkan notifikasi jika mahasiswa melakukan reset password.

### Fitur Bimbingan Konseling
- [ ] Tambah fitur bimbingan konseling (tampilkan button yang akan meredirect ke nomor whatsapp dosen bimbingan konseling atau dosen PA).


### ISSUE
- [x] Perbaiki atau realisasikan fitur akses cepat pada dashboard mahasiswa untuk akses KRS, Jadwal, dan Histori Hasil Studi.
- [x] Perbaiki atau realisasikan Pengumunan Baru pada dashboard mahasiswa untuk menampilkan pengumunan seperti periode pengambilan KRS, Pengisian Kuisioner, Klaim kartu ujian, jadwal ujian.  Durasi penampilan pengumuman menyesuaikan tanggal posting dari admin yang menset-nya. 




### Fitur Pengajuan Surat
- [x] Tambah fitur pengajuan surat pemohonan Aktif Kuliah
- Field Data yang dibutuhkan :
    1. Nama Orang Tua
    2. Alamat Orang Tua
    3. Pekerjaan Orang Tua
    4. NIP (Optional)
    5. Jabatan (Optional)
    6. Nama Instansi (Optional)
    7. Alamat Instansi (Optional)
    8. Keperluan
    9. id_semester (Tahun Akademik)

- [x] Tambah fitur pengajuan surat pemohonan Cuti Kuliah
- Field Data yang dibutuhkan :
    1. Alasan Cuti
    2. id_semester (Tahun Akademik)

- [x] Tambah fitur pengajuan surat pemohonan Pindah Kelas
- Field Data yang dibutuhkan :
    1. Kelas Tujuan (Tampilkan Kelas Saat Ini - Tujuan Kelas).
    2. id_semester (Tahun Akademik)

- [x] Tambah fitur pengajuan surat pemohonan Pindah Perguruan Tinggi
- Field Data yang dibutuhkan :
    1. Nama Instansi Saat Ini (Profile PT)
    2. Nama Perguruan Tinggi Tujuan (Select2 dengan pencarian data perguruan tinggi yang adal di table all_pt)
    3. Status Akreditasi PT tujuan.

- [x] Tambah fitur pengajuan surat pengunduran diri
- Tidak ada field khusus, hanya ada peringatan sebelum mahasiswa ingin menggajukan surat tersebut ```⚠️ Peringatan:

Anda sedang memuat Permohonan Surat Pengunduran Diri dari Perguruan Tinggi. Pastikan Anda telah mempertimbangkan keputusan ini dengan matang, karena pengunduran diri bersifat permanen dan dapat memengaruhi status akademik serta hak mahasiswa. Jika Anda yakin untuk melanjutkan, silakan lanjutkan proses permohonan.```


- [x] Tambahkan jenis pengajuan surat untuk permohonan izin penempatan lokasi untuk PKL
- Field yang dibutuhkan:
  - Nama Instansi Lokasi PKL
  - Pimpinan Instansi
  - Alamat Instansi
  - Tanggal Mulai Penempatan
  - Tanggal Selesai 
  - Teman/Partner (Jika ada)

- [x] Tambahkan jenis pengajuan surat untuk meminta data untuk keperluan Laporan PKL atau TA,
- Field yang dibutuhkan:
  - Peruntukan (PKL/TA)
  - Nama Instansi
  - Alamat 
  - Judul Laporan PKL atau TA
  - List Data yang dibutuhkan 