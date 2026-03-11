<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Kaprodi;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\RiwayatPendidikan;
use App\Models\Semester;
use App\Models\SuratPermohonan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuratWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;

    protected $kaprodiUser;

    protected $mahasiswaUser;

    protected $prodi;

    protected $semester;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $kaprodiRole = Role::firstOrCreate(['name' => 'Kaprodi']);
        $mahasiswaRole = Role::firstOrCreate(['name' => 'Mahasiswa']);
        $dosenRole = Role::firstOrCreate(['name' => 'Dosen']);

        // 2. Setup Prodi & Semester
        $this->prodi = ProgramStudi::create([
            'id_prodi' => (string) \Illuminate\Support\Str::uuid(),
            'nama_program_studi' => 'Prodi Test '.uniqid(),
            'status' => 'A',
        ]);

        $this->semester = Semester::create([
            'id_semester' => '99991',
            'nama_semester' => 'Semester Test '.uniqid(),
            'id_tahun_ajaran' => '2025',
            'a_periode_aktif' => 1,
        ]);

        // 3. Setup Users
        $this->adminUser = User::factory()->create([
            'name' => 'Admin Test',
            'username' => 'admin_test_'.uniqid(),
        ]);
        $this->adminUser->assignRole($adminRole);

        $nip = '12345678';
        $this->kaprodiUser = User::factory()->create([
            'name' => 'Kaprodi Test',
            'username' => $nip,
        ]);
        $this->kaprodiUser->assignRole($kaprodiRole);
        $this->kaprodiUser->assignRole($dosenRole);

        $this->mahasiswaUser = User::factory()->create([
            'name' => 'Mahasiswa Test',
            'username' => 'mahasiswa_test_'.uniqid(),
        ]);
        $this->mahasiswaUser->assignRole($mahasiswaRole);

        // 4. Setup Dosen & Kaprodi Record
        $dosen = Dosen::create([
            'user_id' => $this->kaprodiUser->id,
            'nama' => 'Kaprodi Test',
            'nip' => $nip,
            'is_struktural' => true,
            'status_sinkronisasi' => 'lokal',
        ]);

        // Ensure role is assigned to the CORRECT user if generateUserIfNotExists ran
        // Actually, with same username it should find the existing one.

        Kaprodi::create([
            'dosen_id' => $dosen->id,
            'id_prodi' => $this->prodi->id_prodi,
            'sumber_data' => 'lokal',
        ]);

        // 5. Setup Mahasiswa & Riwayat
        $mahasiswa = Mahasiswa::create([
            'user_id' => $this->mahasiswaUser->id,
            'nama_mahasiswa' => 'Mahasiswa Test',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '2000-01-01',
            'id_agama' => 1,
            'status_sinkronisasi' => 'lokal',
        ]);

        RiwayatPendidikan::create([
            'id_mahasiswa' => $mahasiswa->id,
            'nim' => '20250001',
            'id_prodi' => $this->prodi->id_prodi,
            'id_riwayat_pendidikan' => (string) \Illuminate\Support\Str::uuid(),
            'id_periode_masuk' => $this->semester->id_semester,
            'id_jenis_daftar' => '1',
            'tanggal_daftar' => now(),
            'sumber_data' => 'lokal',
            'status_sinkronisasi' => 'created_local',
        ]);
    }

    /** @test */
    public function test_full_hierarchical_surat_approval_workflow()
    {
        Storage::fake('public');

        // --- PHASE 1: STUDENT SUBMISSION ---
        $this->actingAs($this->mahasiswaUser);

        $response = $this->post(route('mahasiswa.surat.store'), [
            'tipe_surat' => 'aktif_kuliah',
            'id_semester' => $this->semester->id_semester,
            'keperluan' => 'BPJS Kesehatan',
            'nama_ortu' => 'Bapak Test',
            'pekerjaan_ortu' => 'Swasta',
            'alamat_ortu' => 'Jl. Test No. 1',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('surat_permohonans', [
            'id_mahasiswa' => $this->mahasiswaUser->mahasiswa->id,
            'tipe_surat' => 'aktif_kuliah',
            'status' => 'pending',
        ]);

        $surat = SuratPermohonan::latest()->first();

        // Verify notification for Kaprodi
        $notifKaprodi = DB::table('notifications')
            ->where('notifiable_id', $this->kaprodiUser->id)
            ->where('data', 'like', '%"status":"pending"%')
            ->first();
        $this->assertNotNull($notifKaprodi, 'Kaprodi should receive a notification');
        $dataKaprodi = json_decode($notifKaprodi->data, true);
        $this->assertStringContainsString('kaprodi/surat', $dataKaprodi['url'], 'Kaprodi notification should point to kaprodi route');

        // --- PHASE 2: KAPRODI VALIDATION ---
        $this->kaprodiUser->refresh()->load('dosen');
        $this->actingAs($this->kaprodiUser);

        $response = $this->post(route('kaprodi.surat.validate', $surat->id), [
            'status' => 'validasi',
            'catatan_kaprodi' => 'Lengkap!',
        ]);

        $response->assertRedirect(route('kaprodi.surat.index'));
        $this->assertEquals('validasi', $surat->fresh()->status);

        // Verify notification for Admin
        $notifAdmin = DB::table('notifications')
            ->where('notifiable_id', $this->adminUser->id)
            ->where('data', 'like', '%"status":"validasi"%')
            ->first();
        $this->assertNotNull($notifAdmin, 'Admin should receive a notification');
        $dataAdmin = json_decode($notifAdmin->data, true);
        $this->assertStringContainsString('admin/surat-approval', $dataAdmin['url'], 'Admin notification should point to admin route');

        $this->adminUser->refresh();
        $this->actingAs($this->adminUser);

        // --- PHASE 3: ADMIN APPROVAL (NEW) ---
        $response = $this->post(route('admin.surat-approval.approve', $surat->id), [
            'status' => 'disetujui',
        ]);

        $response->assertRedirect();
        $this->assertEquals('disetujui', $surat->fresh()->status);

        // --- PHASE 4: ADMIN PRINT & FINALIZATION (DOCX GENERATION) ---
        $response = $this->post(route('admin.surat-approval.finalize', $surat->id));

        $response->assertRedirect();

        $surat = $surat->fresh();
        $this->assertEquals('selesai', $surat->status, "Status should be 'selesai' after admin prints");
        $this->assertNotNull($surat->file_final, 'Final file path should be saved in DB');
        $this->assertStringContainsString('.docx', $surat->file_final, 'Final file should be a DOCX');
        $this->assertFileExists(storage_path('app/public/'.$surat->file_final), 'DOCX file should actually exist in storage');

        // --- PHASE 5: STUDENT VERIFICATION ---
        $notifMhs = DB::table('notifications')
            ->where('notifiable_id', $this->mahasiswaUser->id)
            ->where('data', 'like', '%"status":"selesai"%')
            ->first();
        $this->assertNotNull($notifMhs, 'Student should receive a final notification');
        $dataMhs = json_decode($notifMhs->data, true);
        $this->assertStringContainsString('mahasiswa/surat', $dataMhs['url'], 'Student notification should point to mahasiswa route');
    }

    /** @test */
    public function test_admin_can_reject_surat()
    {
        $this->actingAs($this->mahasiswaUser);

        $response = $this->post(route('mahasiswa.surat.store'), [
            'tipe_surat' => 'aktif_kuliah',
            'id_semester' => $this->semester->id_semester,
            'keperluan' => 'BPJS Kesehatan',
            'nama_ortu' => 'Bapak Test Reject',
            'pekerjaan_ortu' => 'Swasta',
            'alamat_ortu' => 'Jl. Test Reject No. 2',
        ]);

        $surat = SuratPermohonan::latest()->first();

        $this->actingAs($this->kaprodiUser);
        $this->post(route('kaprodi.surat.validate', $surat->id), [
            'status' => 'validasi',
        ]);

        $this->actingAs($this->adminUser);

        // Attempt to print without approval should fail
        $response = $this->post(route('admin.surat-approval.finalize', $surat->id));
        $response->assertSessionHas('error');
        $this->assertEquals('validasi', $surat->fresh()->status);

        // Reject the surat
        $response = $this->post(route('admin.surat-approval.approve', $surat->id), [
            'status' => 'ditolak',
            'catatan' => 'Format surat tidak sesuai.',
        ]);

        $response->assertRedirect();

        $surat = $surat->fresh();
        $this->assertEquals('ditolak', $surat->status);
        $this->assertEquals('Format surat tidak sesuai.', $surat->catatan_admin);

        // Ensure student gets rejection notification
        $notifMhs = DB::table('notifications')
            ->where('notifiable_id', $this->mahasiswaUser->id)
            ->where('data', 'like', '%"status":"ditolak"%')
            ->first();
        $this->assertNotNull($notifMhs, 'Student should receive a rejection notification');
    }
    /** @test */
    public function test_admin_can_print_surat_pindah_kelas()
    {
        Storage::fake('public');

        // Create a dummy template file
        $templatePath = storage_path('app/templates/pindah_kelas.docx');
        if (! file_exists(dirname($templatePath))) {
            mkdir(dirname($templatePath), 0755, true);
        }
        
        // We just create an empty file so file_exists($templatePath) passes in Controller.
        // Actually PhpWord TemplateProcessor might fail if it's not a valid zip,
        $templatePath = storage_path('app/templates/pindah_kelas.docx');
        if (! file_exists($templatePath)) {
            $this->markTestSkipped('Template pindah_kelas.docx not found in storage/app/templates');
        }

        $this->actingAs($this->mahasiswaUser);

        $response = $this->post(route('mahasiswa.surat.store'), [
            'tipe_surat' => 'pindah_kelas',
            'id_semester' => $this->semester->id_semester,
            'keperluan' => 'Pindah Kelas karena bentrok jadwal kerja',
            'kelas_tujuan' => 'Sore', // Karyawan
        ]);
        $response->assertRedirect();

        $surat = SuratPermohonan::latest()->first();
        $this->assertEquals('pending', $surat->status);

        $this->actingAs($this->kaprodiUser);
        $response = $this->post(route('kaprodi.surat.validate', $surat->id), [
            'status' => 'validasi',
        ]);
        $response->assertRedirect();
        $this->assertEquals('validasi', $surat->fresh()->status);

        $this->actingAs($this->adminUser);

        // Approve it first
        $response = $this->post(route('admin.surat-approval.approve', $surat->id), [
            'status' => 'disetujui',
        ]);
        $response->assertRedirect();
        $this->assertEquals('disetujui', $surat->fresh()->status);

        // Attempt to print
        $response = $this->post(route('admin.surat-approval.finalize', $surat->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $surat = $surat->fresh();
        $this->assertEquals('selesai', $surat->status);
        $this->assertNotNull($surat->file_final);
    }
}
