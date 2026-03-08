## Kuisioner AMI 

### Master Kuisioner: BPMI
- [x] BPMI membuat kuisioner Audit Mutu Internal (AMI) pada periode tertentu. 
- [x] Kuisioner ditujuankan kepada dosen/pegawai yang memiliki jabatan seperti keuangan, kemahasiswaan, sarana dan prasana serta jabatan lainnya yang ada pada sistem. 
- [x] Sistem harus mampu memberikan ringkasan responden semetara dari jawaban responden yang sudah ada. 
- [x] Setelah BPMI menerbitkan kuisioner AMI, BPMI dapat meminta admin atau secara otomatis membuat pengumuman baru yang ditunjukan kepada user selain role mahasiswa termasuk admin juga sebagai responden dari bidang akademik. 

### Responden
- [x] Sistem menampilkan kuis pada user dosen/pegawai yang memiliki jabatan (kecuali mahasiswa) sesuai dengan periode kuisioner AMI yang sudah ditentukan. 
- [x] User harus mengisi kuisioner sesuai dengan deadline yang sudah ditentukan, walaupun tidak ada ketentuan pasti terkait pengisian kuisioner tersebut.


### ISSUE
- [x] Perbaiki nav-item yang tidak active saat user mengakses detail dari kuisioner atau saat melihat laporan kuisioner.
- [x] Perbaiki target dosen pada kuisioner evaluasi kinerja dosen, setelah diuji manual data kuisioner dosen tampil berdasarkan kelas kuliah. namun ada masalah saat kelas kuliah memiliki dosen yang sama dengan kelas kuliah tersebut ini menyebabkan pengisian kinerja dosen menjadi duplikat. jadi perbaiki tampilan duplikasi data dosen sebagai target pengisian kuisionernya jika dosen ada dikelas_kuliah berbeda pada satu periode KRS maka kuisioner cukup menampilkan 1 dosen itu saja. 
- [x] Perbaiki nformasi nama kelas yang diampu oleh masing-masing dosen saat ini tidak dapat tampil alias null pada halaman kuisioner dosen baik di halaman index atau show. 
- [x] Perbaiki tampilan laporan dari kuisioner evaluasi kinerja dosen, saat ini tampilan laporan hanya menampilkan rata-rata nilai per pertanyaan. padahal seharusnya menampilkan rata-rata nilai per dosen pengampu.
- [x] Berikan tampilan detail untuk setiap kuisioner baik pelayanan akademik atau kinerja dosen untuk menampilkan jawaban yang berupa bentuk essay. ini untuk mengantisipasi jika bpmi meminta saran diakhir kuisioner. walaupun pada kuisioner tidak memiliki detail pengisi kuisioner, tambahkan juga jumlah mahasiswa yang mengisi kuisioner tersebut. 
- [x] Perbaiki total partisipasi berdasarkan peserta kelas semester aktif saat ini untuk mengetahui jumlah mahasiswa yang belum dengan yang sudah.
- [x] Hapus keterangan nama_kelas pada kuisioner evaluasi dosen cukup tampilkan nama dosennya saja baik di tampilan mahasiswa, akademik, dan bpmi.
- [x] Implementasikan export hasil kuisioner kedalam bentuk excel pada jawaban kuisioner, buatkan implementasi plan untuk struktur kolom excel dari masing-masing kuisionernya sebelum memulai kodingnya [x]