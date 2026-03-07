## Kuisioner AMI 

### Master Kuisioner: BPMI
- [ ] BPMI membuat kuisioner Audit Mutu Internal (AMI) pada periode tertentu. 
- [ ] Kuisioner ditujuankan kepada dosen/pegawai yang memiliki jabatan seperti keuangan, kemahasiswaan, sarana dan prasana serta jabatan lainnya yang ada pada sistem. 
- [ ] Sistem harus mampu memberikan ringkasan responden semetara dari jawaban responden yang sudah ada. 
- [ ] Setelah BPMI menerbitkan kuisioner AMI, BPMI dapat meminta admin atau secara otomatis membuat pengumuman baru yang ditunjukan kepada user selain role mahasiswa termasuk admin juga sebagai responden dari bidang akademik. 

### Responden
- [ ] Sistem menampilkan kuis pada user dosen/pegawai yang memiliki jabatan (kecuali mahasiswa) sesuai dengan periode kuisioner AMI yang sudah ditentukan. 
- [ ] User harus mengisi kuisioner sesuai dengan deadline yang sudah ditentukan, walaupun tidak ada ketentuan pasti terkait pengisian kuisioner tersebut.


### ISSUE
- [x] Perbaiki nav-item yang tidak active saat user mengakses detail dari kuisioner atau saat melihat laporan kuisioner.
- [x] Perbaiki target dosen pada kuisioner evaluasi kinerja dosen, setelah diuji manual data kuisioner dosen tampil berdasarkan kelas kuliah. namun ada masalah saat kelas kuliah memiliki dosen yang sama dengan kelas kuliah tersebut ini menyebabkan pengisian kinerja dosen menjadi duplikat. jadi perbaiki tampilan duplikasi data dosen sebagai target pengisian kuisionernya jika dosen ada dikelas_kuliah berbeda pada satu periode KRS maka kuisioner cukup menampilkan 1 dosen itu saja. 