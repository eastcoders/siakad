<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\KrsPeriod;
use App\Models\Semester;
use App\Models\RiwayatPendidikan;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\MataKuliah;
use App\Models\KelasKuliah;
use App\Models\PesertaKelasKuliah;
use App\Models\PembimbingAkademik;
use App\Models\KomponenBiaya;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Services\TagihanService;
use Illuminate\Support\Str;

class KeuanganModulTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $studentUser;
    protected Mahasiswa $mahasiswa;
    protected Semester $semester;
    protected ProgramStudi $prodi;
    protected Dosen $dosen;
    protected TagihanService $tagihanService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tagihanService = app(TagihanService::class);

        // ── Admin User (role: admin) ──
        $this->adminUser = User::factory()->create([
            'username' => 'testadmin_keu_' . uniqid(),
            'email' => 'admin_keu_' . uniqid() . '@example.com'
        ]);
        $this->adminUser->assignRole('admin');

        // ── Student User ──
        $this->studentUser = User::factory()->create([
            'username' => 'testmhs_keu_' . uniqid(),
            'email' => 'mhs_keu_' . uniqid() . '@example.com'
        ]);
        $this->studentUser->assignRole('Mahasiswa');

        // ── Semester Aktif ──
        $this->semester = Semester::where('a_periode_aktif', '1')->first();
        if (!$this->semester) {
            $this->semester = Semester::create([
                'id_semester' => '20241',
                'nama_semester' => '2024/2025 Ganjil',
                'id_tahun_ajaran' => '2024',
                'a_periode_aktif' => '1'
            ]);
        }

        // ── Program Studi (unique per test run for isolation) ──
        $this->prodi = ProgramStudi::create([
            'id_prodi' => (string) Str::uuid(),
            'nama_program_studi' => 'Test Prodi Keuangan ' . uniqid(),
            'kode_program_studi' => 'TK' . rand(100, 999)
        ]);

        // ── Dosen PA ──
        $dosenUser = User::factory()->create(['username' => 'dosen_keu_' . uniqid()]);
        $this->dosen = Dosen::create([
            'user_id' => $dosenUser->id,
            'nama' => 'Dosen PA Keuangan Test',
            'nidn' => 'KEU' . rand(1000, 9999),
            'email' => $dosenUser->email,
        ]);

        PembimbingAkademik::create([
            'id_dosen' => $this->dosen->id,
            'id_prodi' => $this->prodi->id_prodi,
            'id_semester' => $this->semester->id_semester
        ]);

        // ── Mahasiswa ──
        $this->mahasiswa = Mahasiswa::create([
            'user_id' => $this->studentUser->id,
            'nama_mahasiswa' => 'Test Mahasiswa Keuangan',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'id_agama' => 1,
            'nik' => '1234' . rand(100000, 999999) . str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT),
            'nisn' => '12' . rand(10000000, 99999999),
            'nama_ibu_kandung' => 'Ibu Test',
            'id_wilayah' => '000000',
            'kelurahan' => 'Test Kelurahan',
            'handphone' => '0812' . rand(10000000, 99999999),
            'email' => $this->studentUser->email,
            'sumber_data' => 'lokal',
            'status_sinkronisasi' => 'created_local'
        ]);

        RiwayatPendidikan::create([
            'id_mahasiswa' => $this->mahasiswa->id,
            'nim' => 'KEU' . rand(10000, 99999),
            'id_jenis_daftar' => '1',
            'id_periode_masuk' => $this->semester->id_semester,
            'tanggal_daftar' => now()->format('Y-m-d'),
            'id_prodi' => $this->prodi->id_prodi,
            'status_sinkronisasi' => 'lokal',
            'sumber_data' => 'lokal'
        ]);
    }

    // ═══════════════════════════════════════════════
    //  FASE 1: Master Komponen Biaya (Admin CRUD)
    // ═══════════════════════════════════════════════

    public function test_admin_can_access_komponen_biaya_index(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.keuangan-modul.komponen-biaya.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_komponen_biaya(): void
    {
        $data = [
            'kode_komponen' => 'SPP-T' . rand(100, 999),
            'nama_komponen' => 'SPP Test',
            'kategori' => 'per_semester',
            'nominal_standar' => 3000000,
            'is_wajib_krs' => 1,
            'is_wajib_ujian' => 1,
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.keuangan-modul.komponen-biaya.store'), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('komponen_biayas', [
            'kode_komponen' => $data['kode_komponen'],
            'nama_komponen' => 'SPP Test',
            'is_wajib_krs' => true,
            'is_wajib_ujian' => true,
        ]);
    }

    public function test_admin_can_update_komponen_biaya(): void
    {
        $komponen = KomponenBiaya::create([
            'kode_komponen' => 'UPD-T' . rand(100, 999),
            'nama_komponen' => 'Komponen Old',
            'kategori' => 'per_semester',
            'nominal_standar' => 1000000,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.keuangan-modul.komponen-biaya.update', $komponen->id), [
                'kode_komponen' => $komponen->kode_komponen,
                'nama_komponen' => 'Komponen Updated',
                'kategori' => 'sekali_bayar',
                'nominal_standar' => 2000000,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('komponen_biayas', [
            'id' => $komponen->id,
            'nama_komponen' => 'Komponen Updated',
            'kategori' => 'sekali_bayar',
        ]);
    }

    public function test_admin_can_deactivate_komponen_biaya(): void
    {
        $komponen = KomponenBiaya::create([
            'kode_komponen' => 'DEL-T' . rand(100, 999),
            'nama_komponen' => 'Komponen to Deactivate',
            'kategori' => 'per_semester',
            'nominal_standar' => 500000,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.keuangan-modul.komponen-biaya.destroy', $komponen->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('komponen_biayas', [
            'id' => $komponen->id,
            'is_active' => false,
        ]);
    }

    // ═══════════════════════════════════════════════
    //  FASE 2: Penerbitan Tagihan (Service + Admin)
    // ═══════════════════════════════════════════════

    public function test_service_generate_nomor_tagihan(): void
    {
        $nomor = $this->tagihanService->generateNomorTagihan();
        $this->assertStringStartsWith('INV/' . date('Y') . '/', $nomor);
    }

    public function test_service_terbitkan_tagihan_individual(): void
    {
        // Setup komponen biaya
        KomponenBiaya::create([
            'kode_komponen' => 'SVC-T' . rand(100, 999),
            'nama_komponen' => 'SPP Service Test',
            'kategori' => 'per_semester',
            'nominal_standar' => 2500000,
            'is_wajib_krs' => true,
            'is_active' => true,
            'id_prodi' => $this->prodi->id_prodi,
        ]);

        $tagihan = $this->tagihanService->terbitkanTagihan(
            $this->mahasiswa,
            $this->semester->id_semester,
            $this->prodi->id_prodi
        );

        $this->assertNotNull($tagihan);
        $this->assertEquals($this->mahasiswa->id, $tagihan->id_mahasiswa);
        $this->assertEquals($this->semester->id_semester, $tagihan->id_semester);
        $this->assertTrue($tagihan->total_tagihan >= 2500000);
        $this->assertEquals(Tagihan::STATUS_BELUM_BAYAR, $tagihan->status);

        // Cek item test komponen exists
        $this->assertTrue($tagihan->items()->whereHas('komponenBiaya', fn($q) => $q->where('nominal_standar', 2500000))->exists());
    }

    public function test_admin_can_terbitkan_tagihan_via_form(): void
    {
        KomponenBiaya::create([
            'kode_komponen' => 'ADM-T' . rand(100, 999),
            'nama_komponen' => 'SPP Admin Test',
            'kategori' => 'per_semester',
            'nominal_standar' => 3000000,
            'is_active' => true,
            'id_prodi' => $this->prodi->id_prodi,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.keuangan-modul.tagihan.store'), [
                'mode' => 'individual',
                'id_semester' => $this->semester->id_semester,
                'id_mahasiswa' => $this->mahasiswa->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('tagihans', [
            'id_mahasiswa' => $this->mahasiswa->id,
            'id_semester' => $this->semester->id_semester,
        ]);
    }

    public function test_admin_cannot_create_duplicate_tagihan(): void
    {
        KomponenBiaya::create([
            'kode_komponen' => 'DUP-T' . rand(100, 999),
            'nama_komponen' => 'SPP Dup Test',
            'kategori' => 'per_semester',
            'nominal_standar' => 1000000,
            'is_active' => true,
            'id_prodi' => $this->prodi->id_prodi,
        ]);

        // Terbitkan pertama
        $this->tagihanService->terbitkanTagihan($this->mahasiswa, $this->semester->id_semester, $this->prodi->id_prodi);

        // Coba terbitkan kedua via form
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.keuangan-modul.tagihan.store'), [
                'mode' => 'individual',
                'id_semester' => $this->semester->id_semester,
                'id_mahasiswa' => $this->mahasiswa->id,
            ]);

        $response->assertSessionHas('error');
    }

    // ═══════════════════════════════════════════════
    //  FASE 3: Upload Bukti Bayar (Mahasiswa)
    // ═══════════════════════════════════════════════

    public function test_mahasiswa_can_access_keuangan_index(): void
    {
        $response = $this->actingAs($this->studentUser)
            ->get(route('mahasiswa.keuangan.index'));

        $response->assertStatus(200);
    }

    public function test_mahasiswa_can_see_tagihan_detail(): void
    {
        KomponenBiaya::create([
            'kode_komponen' => 'DTL-T' . rand(100, 999),
            'nama_komponen' => 'SPP Detail Test',
            'kategori' => 'per_semester',
            'nominal_standar' => 2000000,
            'is_active' => true,
            'id_prodi' => $this->prodi->id_prodi,
        ]);

        $tagihan = $this->tagihanService->terbitkanTagihan($this->mahasiswa, $this->semester->id_semester, $this->prodi->id_prodi);

        $response = $this->actingAs($this->studentUser)
            ->get(route('mahasiswa.keuangan.show', $tagihan->id));

        $response->assertStatus(200);
    }

    public function test_mahasiswa_cannot_see_other_student_tagihan(): void
    {
        // Buat mahasiswa lain
        $otherUser = User::factory()->create(['username' => 'other_mhs_' . uniqid()]);
        $otherUser->assignRole('Mahasiswa');
        $otherMhs = Mahasiswa::create([
            'user_id' => $otherUser->id,
            'nama_mahasiswa' => 'Other Student',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2001-01-01',
            'id_agama' => 1,
            'nik' => '9876' . rand(100000, 999999) . str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT),
            'nisn' => '98' . rand(10000000, 99999999),
            'nama_ibu_kandung' => 'Ibu Other',
            'id_wilayah' => '000000',
            'kelurahan' => 'Kel Other',
            'handphone' => '0813' . rand(10000000, 99999999),
            'email' => $otherUser->email,
            'sumber_data' => 'lokal',
            'status_sinkronisasi' => 'created_local'
        ]);

        KomponenBiaya::create([
            'kode_komponen' => 'OTH-T' . rand(100, 999),
            'nama_komponen' => 'SPP Other Test',
            'kategori' => 'per_semester',
            'nominal_standar' => 1000000,
            'is_active' => true,
            'id_prodi' => $this->prodi->id_prodi,
        ]);

        $tagihan = $this->tagihanService->terbitkanTagihan($otherMhs, $this->semester->id_semester, $this->prodi->id_prodi);

        // Mahasiswa ini coba akses tagihan orang lain
        $response = $this->actingAs($this->studentUser)
            ->get(route('mahasiswa.keuangan.show', $tagihan->id));

        $response->assertStatus(403);
    }

    // ═══════════════════════════════════════════════
    //  FASE 4: Verifikasi Pembayaran (Admin)
    // ═══════════════════════════════════════════════

    public function test_verifikasi_approve_updates_status_and_generates_kuitansi(): void
    {
        $komponen = KomponenBiaya::create([
            'kode_komponen' => 'VRF-T' . rand(100, 999),
            'nama_komponen' => 'SPP Verifikasi Test',
            'kategori' => 'per_semester',
            'nominal_standar' => 2000000,
            'is_active' => true,
            'id_prodi' => $this->prodi->id_prodi,
        ]);

        $tagihan = $this->tagihanService->terbitkanTagihan($this->mahasiswa, $this->semester->id_semester, $this->prodi->id_prodi);

        // Buat pembayaran manual (simulate upload)
        $pembayaran = Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'jumlah_bayar' => $tagihan->total_tagihan,
            'tanggal_bayar' => now()->format('Y-m-d'),
            'bukti_bayar' => 'private/bukti-bayar/test.jpg',
            'status_verifikasi' => Pembayaran::STATUS_PENDING,
        ]);

        // Admin approve
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.keuangan-modul.verifikasi.approve', $pembayaran->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Reload
        $pembayaran->refresh();
        $tagihan->refresh();

        $this->assertEquals(Pembayaran::STATUS_DISETUJUI, $pembayaran->status_verifikasi);
        $this->assertNotNull($pembayaran->nomor_kuitansi);
        $this->assertStringStartsWith('KWT/', $pembayaran->nomor_kuitansi);
        $this->assertEquals(Tagihan::STATUS_LUNAS, $tagihan->status);
        $this->assertEquals($tagihan->total_tagihan, $tagihan->total_dibayar);
    }

    public function test_verifikasi_reject_requires_catatan(): void
    {
        $pembayaran = Pembayaran::create([
            'tagihan_id' => Tagihan::create([
                'nomor_tagihan' => 'INV/TEST/' . rand(10000, 99999),
                'id_mahasiswa' => $this->mahasiswa->id,
                'id_semester' => $this->semester->id_semester,
                'total_tagihan' => 1000000,
                'status' => 'belum_bayar',
            ])->id,
            'jumlah_bayar' => 1000000,
            'tanggal_bayar' => now()->format('Y-m-d'),
            'bukti_bayar' => 'private/bukti-bayar/test2.jpg',
            'status_verifikasi' => Pembayaran::STATUS_PENDING,
        ]);

        // Reject tanpa catatan -> wajib gagal
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.keuangan-modul.verifikasi.reject', $pembayaran->id), []);

        $response->assertSessionHasErrors('catatan_admin');
    }

    public function test_verifikasi_reject_with_catatan_success(): void
    {
        $tagihan = Tagihan::create([
            'nomor_tagihan' => 'INV/TEST/' . rand(10000, 99999),
            'id_mahasiswa' => $this->mahasiswa->id,
            'id_semester' => $this->semester->id_semester,
            'total_tagihan' => 1000000,
            'status' => 'belum_bayar',
        ]);

        $pembayaran = Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'jumlah_bayar' => 1000000,
            'tanggal_bayar' => now()->format('Y-m-d'),
            'bukti_bayar' => 'private/bukti-bayar/test3.jpg',
            'status_verifikasi' => Pembayaran::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.keuangan-modul.verifikasi.reject', $pembayaran->id), [
                'catatan_admin' => 'Bukti transfer tidak jelas'
            ]);

        $response->assertRedirect();
        $pembayaran->refresh();
        $this->assertEquals(Pembayaran::STATUS_DITOLAK, $pembayaran->status_verifikasi);
        $this->assertEquals('Bukti transfer tidak jelas', $pembayaran->catatan_admin);
    }

    public function test_cicilan_partial_payment_updates_status(): void
    {
        $komponen = KomponenBiaya::create([
            'kode_komponen' => 'CIC-T' . rand(100, 999),
            'nama_komponen' => 'SPP Cicilan Test',
            'kategori' => 'per_semester',
            'nominal_standar' => 3000000,
            'is_active' => true,
            'id_prodi' => $this->prodi->id_prodi,
        ]);

        $tagihan = $this->tagihanService->terbitkanTagihan($this->mahasiswa, $this->semester->id_semester, $this->prodi->id_prodi);
        $totalTagihan = $tagihan->total_tagihan;

        // Pembayaran pertama: 1 juta
        $p1 = Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'jumlah_bayar' => 1000000,
            'tanggal_bayar' => now()->format('Y-m-d'),
            'bukti_bayar' => 'private/bukti-bayar/cicil1.jpg',
            'status_verifikasi' => Pembayaran::STATUS_PENDING,
        ]);

        $this->tagihanService->verifikasiPembayaran($p1, true, null, $this->adminUser);
        $tagihan->refresh();

        $this->assertEquals(Tagihan::STATUS_CICIL, $tagihan->status);
        $this->assertEquals(1000000, $tagihan->total_dibayar);

        // Pembayaran kedua: sisa tagihan -> lunas
        $sisa = $totalTagihan - 1000000;
        $p2 = Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'jumlah_bayar' => $sisa,
            'tanggal_bayar' => now()->format('Y-m-d'),
            'bukti_bayar' => 'private/bukti-bayar/cicil2.jpg',
            'status_verifikasi' => Pembayaran::STATUS_PENDING,
        ]);

        $this->tagihanService->verifikasiPembayaran($p2, true, null, $this->adminUser);
        $tagihan->refresh();

        $this->assertEquals(Tagihan::STATUS_LUNAS, $tagihan->status);
        $this->assertEquals($totalTagihan, $tagihan->total_dibayar);
    }

    // ═══════════════════════════════════════════════
    //  FASE 5: Gatekeeping KRS
    // ═══════════════════════════════════════════════

    public function test_krs_blocked_when_tagihan_not_paid(): void
    {
        // 1. Buat komponen wajib KRS
        KomponenBiaya::create([
            'kode_komponen' => 'GK1-T' . rand(100, 999),
            'nama_komponen' => 'SPP Gatekeep KRS',
            'kategori' => 'per_semester',
            'nominal_standar' => 5000000,
            'is_wajib_krs' => true,
            'is_active' => true,
            'id_prodi' => $this->prodi->id_prodi,
        ]);

        // 2. Terbitkan tagihan (belum bayar)
        $this->tagihanService->terbitkanTagihan($this->mahasiswa, $this->semester->id_semester, $this->prodi->id_prodi);

        // 3. Buat KRS Period terbuka
        KrsPeriod::updateOrCreate(
            ['id_semester' => $this->semester->id_semester],
            [
                'is_active' => true,
                'tgl_mulai' => now()->subDay(),
                'tgl_selesai' => now()->addDay(),
                'nama_periode' => 'KRS Open for Gatekeep Test'
            ]
        );

        // 4. Setup kelas + enrollment
        $mk = MataKuliah::create([
            'id_matkul' => (string) Str::uuid(),
            'id_prodi' => $this->prodi->id_prodi,
            'kode_mk' => 'GK' . rand(100, 999),
            'nama_mk' => 'MK Gatekeep Test',
            'sks' => 3,
            'status_aktif' => true,
        ]);

        $kelas = KelasKuliah::create([
            'id_kelas_kuliah' => (string) Str::uuid(),
            'id_prodi' => $this->prodi->id_prodi,
            'id_semester' => $this->semester->id_semester,
            'id_matkul' => $mk->id_matkul,
            'nama_kelas_kuliah' => 'A-GK',
            'sks_mk' => 3,
            'kapasitas' => 40,
        ]);

        $riwayat = RiwayatPendidikan::where('id_mahasiswa', $this->mahasiswa->id)->first();
        PesertaKelasKuliah::create([
            'id_kelas_kuliah' => $kelas->id_kelas_kuliah,
            'riwayat_pendidikan_id' => $riwayat->id,
            'status_krs' => 'paket',
            'sumber_data' => 'lokal',
        ]);

        // 5. Coba submit KRS -> harus ditolak
        $response = $this->actingAs($this->studentUser)
            ->post(route('mahasiswa.krs.submit'), [
                'id_semester' => $this->semester->id_semester
            ]);

        $response->assertSessionHas('error');
        $this->assertTrue(str_contains(session('error'), 'Tagihan wajib KRS belum lunas'));
    }

    public function test_krs_allowed_when_tagihan_paid(): void
    {
        // 1. Buat komponen wajib KRS
        $komponen = KomponenBiaya::create([
            'kode_komponen' => 'GK2-T' . rand(100, 999),
            'nama_komponen' => 'SPP Gatekeep KRS Paid',
            'kategori' => 'per_semester',
            'nominal_standar' => 2000000,
            'is_wajib_krs' => true,
            'is_active' => true,
            'id_prodi' => $this->prodi->id_prodi,
        ]);

        // 2. Terbitkan dan BAYAR tagihan (bayar PENUH sesuai total)
        $tagihan = $this->tagihanService->terbitkanTagihan($this->mahasiswa, $this->semester->id_semester, $this->prodi->id_prodi);

        $pembayaran = Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'jumlah_bayar' => $tagihan->total_tagihan,
            'tanggal_bayar' => now()->format('Y-m-d'),
            'bukti_bayar' => 'private/bukti-bayar/gatekeep.jpg',
            'status_verifikasi' => Pembayaran::STATUS_PENDING,
        ]);
        $this->tagihanService->verifikasiPembayaran($pembayaran, true, null, $this->adminUser);

        // 3. Buat KRS Period terbuka
        KrsPeriod::updateOrCreate(
            ['id_semester' => $this->semester->id_semester],
            [
                'is_active' => true,
                'tgl_mulai' => now()->subDay(),
                'tgl_selesai' => now()->addDay(),
                'nama_periode' => 'KRS Open Paid Test'
            ]
        );

        // 4. Setup kelas
        $mk = MataKuliah::create([
            'id_matkul' => (string) Str::uuid(),
            'id_prodi' => $this->prodi->id_prodi,
            'kode_mk' => 'GP' . rand(100, 999),
            'nama_mk' => 'MK Gatekeep Paid Test',
            'sks' => 2,
            'status_aktif' => true,
        ]);

        $kelas = KelasKuliah::create([
            'id_kelas_kuliah' => (string) Str::uuid(),
            'id_prodi' => $this->prodi->id_prodi,
            'id_semester' => $this->semester->id_semester,
            'id_matkul' => $mk->id_matkul,
            'nama_kelas_kuliah' => 'B-GP',
            'sks_mk' => 2,
            'kapasitas' => 40,
        ]);

        $riwayat = RiwayatPendidikan::where('id_mahasiswa', $this->mahasiswa->id)->first();
        PesertaKelasKuliah::create([
            'id_kelas_kuliah' => $kelas->id_kelas_kuliah,
            'riwayat_pendidikan_id' => $riwayat->id,
            'status_krs' => 'paket',
            'sumber_data' => 'lokal',
        ]);

        // 5. Coba submit KRS -> harus berhasil
        $response = $this->actingAs($this->studentUser)
            ->post(route('mahasiswa.krs.submit'), [
                'id_semester' => $this->semester->id_semester
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_krs_allowed_when_no_tagihan_exists(): void
    {
        // Tanpa tagihan -> default eligible
        $eligible = $this->tagihanService->isKrsEligible($this->mahasiswa->id, $this->semester->id_semester);
        $this->assertTrue($eligible);
    }

    // ═══════════════════════════════════════════════
    //  FASE 6: Gatekeeping Ujian (Service Level)
    // ═══════════════════════════════════════════════

    public function test_ujian_not_eligible_when_tagihan_unpaid(): void
    {
        KomponenBiaya::create([
            'kode_komponen' => 'GU1-T' . rand(100, 999),
            'nama_komponen' => 'UTS Gatekeep',
            'kategori' => 'per_semester',
            'nominal_standar' => 500000,
            'is_wajib_ujian' => true,
            'is_active' => true,
            'id_prodi' => $this->prodi->id_prodi,
        ]);

        $this->tagihanService->terbitkanTagihan($this->mahasiswa, $this->semester->id_semester, $this->prodi->id_prodi);

        $eligible = $this->tagihanService->isUjianEligible($this->mahasiswa->id, $this->semester->id_semester);
        $this->assertFalse($eligible);
    }

    public function test_ujian_eligible_when_tagihan_paid(): void
    {
        $komponen = KomponenBiaya::create([
            'kode_komponen' => 'GU2-T' . rand(100, 999),
            'nama_komponen' => 'UTS Gatekeep Paid',
            'kategori' => 'per_semester',
            'nominal_standar' => 500000,
            'is_wajib_ujian' => true,
            'is_active' => true,
            'id_prodi' => $this->prodi->id_prodi,
        ]);

        $tagihan = $this->tagihanService->terbitkanTagihan($this->mahasiswa, $this->semester->id_semester, $this->prodi->id_prodi);

        $p = Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'jumlah_bayar' => $tagihan->total_tagihan,
            'tanggal_bayar' => now()->format('Y-m-d'),
            'bukti_bayar' => 'private/bukti-bayar/ujian.jpg',
            'status_verifikasi' => Pembayaran::STATUS_PENDING,
        ]);

        $this->tagihanService->verifikasiPembayaran($p, true, null, $this->adminUser);

        $eligible = $this->tagihanService->isUjianEligible($this->mahasiswa->id, $this->semester->id_semester);
        $this->assertTrue($eligible);
    }

    public function test_ujian_eligible_when_no_tagihan(): void
    {
        $eligible = $this->tagihanService->isUjianEligible($this->mahasiswa->id, $this->semester->id_semester);
        $this->assertTrue($eligible);
    }
}
