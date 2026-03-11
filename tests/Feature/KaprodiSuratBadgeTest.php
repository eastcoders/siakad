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
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KaprodiSuratBadgeTest extends TestCase
{
    use DatabaseTransactions;

    protected User $kaprodiUser;

    protected ProgramStudi $prodi;

    protected Semester $semester;

    protected Mahasiswa $mahasiswa;

    protected function setUp(): void
    {
        parent::setUp();

        $dosenRole = Role::firstOrCreate(['name' => 'Dosen']);
        $kaprodiRole = Role::firstOrCreate(['name' => 'Kaprodi']);
        Role::firstOrCreate(['name' => 'Mahasiswa']);

        $this->prodi = ProgramStudi::create([
            'id_prodi' => (string) Str::uuid(),
            'nama_program_studi' => 'Prodi Badge Test '.uniqid(),
            'status' => 'A',
        ]);

        $this->semester = Semester::create([
            'id_semester' => '99992',
            'nama_semester' => 'Semester Badge Test '.uniqid(),
            'id_tahun_ajaran' => '2025',
            'a_periode_aktif' => 1,
        ]);

        $nip = 'badge_test_'.uniqid();
        $this->kaprodiUser = User::factory()->create([
            'name' => 'Kaprodi Badge Test',
            'username' => $nip,
        ]);
        $this->kaprodiUser->assignRole($dosenRole);
        $this->kaprodiUser->assignRole($kaprodiRole);

        $dosen = Dosen::create([
            'user_id' => $this->kaprodiUser->id,
            'nama' => 'Kaprodi Badge Test',
            'nip' => $nip,
            'is_struktural' => true,
            'status_sinkronisasi' => 'lokal',
        ]);

        Kaprodi::create([
            'dosen_id' => $dosen->id,
            'id_prodi' => $this->prodi->id_prodi,
            'sumber_data' => 'lokal',
        ]);

        $mahasiswaUser = User::factory()->create([
            'name' => 'Mahasiswa Badge Test',
            'username' => 'mhs_badge_'.uniqid(),
        ]);
        $mahasiswaUser->assignRole('Mahasiswa');

        $this->mahasiswa = Mahasiswa::create([
            'user_id' => $mahasiswaUser->id,
            'nama_mahasiswa' => 'Mahasiswa Badge Test',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '2000-01-01',
            'id_agama' => 1,
            'status_sinkronisasi' => 'lokal',
        ]);

        RiwayatPendidikan::create([
            'id_mahasiswa' => $this->mahasiswa->id,
            'nim' => 'BADGE001',
            'id_prodi' => $this->prodi->id_prodi,
            'id_riwayat_pendidikan' => (string) Str::uuid(),
            'id_periode_masuk' => $this->semester->id_semester,
            'id_jenis_daftar' => '1',
            'tanggal_daftar' => now(),
            'sumber_data' => 'lokal',
            'status_sinkronisasi' => 'created_local',
        ]);
    }

    /** @test */
    public function test_sidebar_shows_badge_for_pending_surat(): void
    {
        SuratPermohonan::create([
            'id_mahasiswa' => $this->mahasiswa->id,
            'id_semester' => $this->semester->id_semester,
            'nomor_tiket' => 'TKT-BADGE-'.uniqid(),
            'tipe_surat' => 'aktif_kuliah',
            'status' => 'pending',
        ]);

        $this->actingAs($this->kaprodiUser);
        session(['active_role' => 'Dosen']);

        $response = $this->get(route('kaprodi.surat.index'));
        $response->assertStatus(200);
        $response->assertSee('badge bg-danger rounded-pill', false);
    }

    /** @test */
    public function test_sidebar_hides_badge_when_no_pending_surat(): void
    {
        SuratPermohonan::create([
            'id_mahasiswa' => $this->mahasiswa->id,
            'id_semester' => $this->semester->id_semester,
            'nomor_tiket' => 'TKT-BADGE-'.uniqid(),
            'tipe_surat' => 'aktif_kuliah',
            'status' => 'validasi',
        ]);

        $this->actingAs($this->kaprodiUser);
        session(['active_role' => 'Dosen']);

        $response = $this->get(route('kaprodi.surat.index'));
        $response->assertStatus(200);
        $response->assertDontSee('badge bg-danger rounded-pill', false);
    }

    /** @test */
    public function test_badge_excludes_surat_from_other_prodi(): void
    {
        $otherProdi = ProgramStudi::create([
            'id_prodi' => (string) Str::uuid(),
            'nama_program_studi' => 'Prodi Lain '.uniqid(),
            'status' => 'A',
        ]);

        $otherMhsUser = User::factory()->create([
            'name' => 'Mhs Lain',
            'username' => 'mhs_lain_'.uniqid(),
        ]);
        $otherMhsUser->assignRole('Mahasiswa');

        $otherMhs = Mahasiswa::create([
            'user_id' => $otherMhsUser->id,
            'nama_mahasiswa' => 'Mhs Lain',
            'jenis_kelamin' => 'P',
            'tanggal_lahir' => '2000-06-15',
            'id_agama' => 1,
            'status_sinkronisasi' => 'lokal',
        ]);

        RiwayatPendidikan::create([
            'id_mahasiswa' => $otherMhs->id,
            'nim' => 'OTHER001',
            'id_prodi' => $otherProdi->id_prodi,
            'id_riwayat_pendidikan' => (string) Str::uuid(),
            'id_periode_masuk' => $this->semester->id_semester,
            'id_jenis_daftar' => '1',
            'tanggal_daftar' => now(),
            'sumber_data' => 'lokal',
            'status_sinkronisasi' => 'created_local',
        ]);

        SuratPermohonan::create([
            'id_mahasiswa' => $otherMhs->id,
            'id_semester' => $this->semester->id_semester,
            'nomor_tiket' => 'TKT-OTHER-'.uniqid(),
            'tipe_surat' => 'aktif_kuliah',
            'status' => 'pending',
        ]);

        $this->actingAs($this->kaprodiUser);
        session(['active_role' => 'Dosen']);

        $response = $this->get(route('kaprodi.surat.index'));
        $response->assertStatus(200);
        $response->assertDontSee('badge bg-danger rounded-pill', false);
    }
}
