<?php

namespace Tests\Feature\Auth;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\RiwayatPendidikan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FirstLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup roles
        if (! Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin']);
        }
        if (! Role::where('name', 'Mahasiswa')->exists()) {
            Role::create(['name' => 'Mahasiswa']);
        }
        if (! Role::where('name', 'Dosen')->exists()) {
            Role::create(['name' => 'Dosen']);
        }
    }

    public function test_student_on_first_login_sees_modal(): void
    {
        $user = User::factory()->create(['is_first_login' => true]);
        $user->assignRole('Mahasiswa');

        $prodi = ProgramStudi::create([
            'id_prodi' => (string) \Illuminate\Support\Str::uuid(),
            'kode_program_studi' => 'TI',
            'nama_program_studi' => 'Teknik Informatika',
            'status' => 'A',
        ]);

        $mahasiswa = Mahasiswa::create([
            'user_id' => $user->id,
            'id_feeder' => (string) \Illuminate\Support\Str::uuid(),
            'nama_mahasiswa' => $user->name,
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '2000-01-01',
            'id_agama' => 1,
            'nama_ibu_kandung' => 'Mother',
            'email' => $user->email,
        ]);

        RiwayatPendidikan::create([
            'id_mahasiswa' => $mahasiswa->id,
            'id_prodi' => $prodi->id_prodi,
            'id_perguruan_tinggi' => (string) \Illuminate\Support\Str::uuid(),
            'id_jenis_daftar' => 1,
            'id_periode_masuk' => '20231',
            'nim' => '123456',
            'tanggal_daftar' => '2023-09-01',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        // Check for modal presence on the dashboard
        if ($response->isRedirect()) {
            $response = $this->followRedirects($response);
        }

        $response->assertStatus(200);
        $response->assertSee('id="firstLoginModal"', false);
    }

    public function test_first_login_data_can_be_updated(): void
    {
        $user = User::factory()->create(['is_first_login' => true, 'password' => Hash::make('old-password')]);
        $user->assignRole('Mahasiswa');

        $mahasiswa = Mahasiswa::create([
            'user_id' => $user->id,
            'id_feeder' => (string) \Illuminate\Support\Str::uuid(),
            'nama_mahasiswa' => $user->name,
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '2000-01-01',
            'id_agama' => 1,
            'nama_ibu_kandung' => 'Mother',
            'email' => $user->email,
        ]);

        $response = $this->actingAs($user)->post('/first-login', [
            'email' => 'newemail@example.com',
            'whatsapp' => '08123456789',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success', 'Data berhasil diperbarui. Selamat datang!');

        $user->refresh();
        $this->assertFalse($user->is_first_login);
        $this->assertEquals('newemail@example.com', $user->email);
        $this->assertTrue(Hash::check('new-password', $user->password));

        $mahasiswa->refresh();
        $this->assertEquals('08123456789', $mahasiswa->whatsapp);
    }

    public function test_new_password_cannot_be_same_as_old_password(): void
    {
        $user = User::factory()->create([
            'is_first_login' => true,
            'password' => Hash::make('secret-password'),
        ]);
        $user->assignRole('Mahasiswa');

        $response = $this->actingAs($user)->post('/first-login', [
            'email' => $user->email,
            'whatsapp' => '08123456789',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_lecturer_first_login_data_can_be_updated(): void
    {
        $user = User::factory()->create(['is_first_login' => true]);
        $user->assignRole('Dosen');

        $dosen = Dosen::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'email' => $user->email,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/first-login', [
            'email' => 'dosen.new@example.com',
            'whatsapp' => '08987654321',
            'password' => 'dosen-password',
            'password_confirmation' => 'dosen-password',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertFalse($user->is_first_login);

        $dosen->refresh();
        $this->assertEquals('08987654321', $dosen->whatsapp);
    }
}
