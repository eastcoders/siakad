## Ujian Tengah dan Akhir Semester

### ISSUE 

- [x] Tampilkan notifikasi saat ada mahasiswa yang meminta cetak kartu ujian, baik UTS/UAS.
- [x] Berikan gap antar icon dikolom aksi pada menu manajemen jadwal ujian.
- [x] Kelompokan menu pengaturan ujian dan manajemen jadwal ujian agar user tidak kesulitan memanajemen data ujiannya.
- [x] Perbaiki logika pada aksi `selesai` di menu permintaan cetak agar dapat mengirimkan notifikasi berupa pesan bahwa kartu ujian sudah dapat diambil di Ruang Akademik dengan membawa informasi NIM yang bersangkutan.
- [x] Berikan gap pada button aksi di menu Permintaan Cetak Kartu. 
- [x] Jika jadwal ujian sudah dibuat, sistem otomatis menjalankan fungsi `generate peserta`.
- [x] Perbaiki tampilan parent nav-items saat membuat items pada child didalam childnya agar tercollapse saat ada childnya yang aktif.

- [x] Perbaiki tampilan alert error saat melakukan pengajuan cetak kartu ujian jika belum mengisi kuisioner. Serta disable akses cetak jika mahasiswa belum menyelesaikan kuisionernya. 
- [x] Perbaiki akses cetak kartu ujian, terjadi bug/kesalahan dimana ujian dengan tipe UAS hanya mengisi kuisioner yang ditujukan untuk UAS saja. setelah dilakukan testing manual, mahasiswa sudah mengisi kuisioner untuk UAS yaitu evaluasi kinerja dosen. kuisioner sudah diisi hingga statusnya menjadi 100% namun akses cetak kartu ujian masih belum terbuka.
- [x] Analisa dan perbaiki error pada saat mencoba cetak kartu ujian setelah mengisi kuisioner evaluasi dosen, tidak ada pesan yang spesifik mengenai kesalahan yang terjadi. hanya muncul alert berisi "Terjadi Kesalahan Sistem".
- [x] Terjadi kesalahan atau bug pada logika `generate peserta` pada kondisi mahasiswa belum melunasi tagihan saat admin/akademik menambahkan generate peserta. setelah saya mencoba testing dengan melunasi tagihan. saya tidak dapat mengupdate peserta ujian. lakukan analisa logika saat ini dan berikan best practices untuk mengatasi kasus seperti ini. 
- [x] Tambahkan peringatan pada menu Kartu Ujian di Role Mahasiswa untuk memberitahu jika mahasiswa tersebut belum mengambil atau mengajukan KRS. Dimana jika mahasiswa belum mengambil KRS mahasiswa tersebut tidak dapat mengikuti ujian dan ikut presensi kelas. Kemudian rapihkan UI table peserta ujian di tampilan admin dengan memperbaiki ui button `cetak kartu` dengan `tandai dicetak` karena terlalu nempel. tambahkan juga aksi sinkronisasi data peserta ujian pada halaman manajemen jadwal ujian. [x]
- [ ] 