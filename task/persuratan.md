## Persuratan

### View Cetak 
- [ ] Perbaiki view cetak KRS, KHS, dan Kartu Ujian.
- [ ] Buat view cetak untuk surat permohonan cuti/aktif kuliah.
- [ ] Buat view cetak untuk surat permohonan pengunduran diri.
- [ ] Buat view cetak untuk surat permohonan pindah kelas.
- [ ] Buat view cetak untuk surat permohonan pindah perguruan tinggi.
- [ ] Buat view cetak untuk surat permohonan izin tempat PKL.
- [ ] Buat view cetak untuk surat permohonan perolehan data PKL/TA.


### ISSUE
- [x] Perbaiki logika ACC permohonan surat, dimana surat yang masuk harus meminta persetujuan terlebih dahulu di kaprodi masing masings sebelum di ACC oleh admin.
- [x] Perbaiki logika pada fitur acc surat di role admin, surat yang masuk di role admin adalah pengajuan yang sudah diacc oleh kaprodi. Maka admin hanya perlu melakukan persetujuan untuk melakukan cetak surat dan mengirimkan notifikasi ke mahasiswa jika surat permohonan/pengajuan sudah dapat di ambil di ruang akademik. Workflow => Mahasiswa mengajukan -> Kaprodi Verifikas -> Admin Cetak Surat -> Mahasiswa ambil surat. Lakukan analisa pada proses saat ini dan berikan opini untuk melakukan perubahan dari alur logika proses persuratan tersebut. Pada fitur cetak surat template akan disediakan oleh sistem secara otomatis sesuai dengan jenis templatenya (akan saya buatkan templatenya nanti, jadi sementara gunakan view to pdf saja untuk templatenya). Berikan laporan analisa untuk melakukan implementasi pembaruan sistem tersebut. 
- [x] Perbaiki badge pemberitahuan yang tidak tampil pada menu persetujuan surat di role admin pada parent menunya yaitu Data Perkuliahan.
- [x] Saat admin menyetujui surat permohonan dari kaprodi, sistem akan mengarahkan ke halaman baru untuk melakukan cetak surat tersebut. 
- [x] Pisahkan button cetak dengan beritahu mahasiswa agar admin dapat mencetak surat kapan saja dan memberitahu mahasiswa kapan saja.