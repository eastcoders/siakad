<?php

namespace Tests\Feature;

use App\Jobs\Feeder\DispatchRefNasionalSyncJob;
use App\Jobs\Feeder\ProcessRefAllProdiChunkJob;
use App\Jobs\Feeder\ProcessRefWilayahChunkJob;
use App\Services\NeoFeederService;
use App\Services\ReferensiServices\WilayahRefService;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Tests\TestCase;

class DispatchRefNasionalSyncJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function dispatcher_creates_wilayah_and_all_prodi_worker_jobs(): void
    {
        Bus::fake();

        // Mock WilayahRefService: mengembalikan 500 data di halaman pertama, 0 di probe berikutnya
        $mockWilayahService = $this->createMock(WilayahRefService::class);
        $mockWilayahService->method('getWilayah')
            ->willReturnCallback(function (string $filter, int $limit, int $offset) {
                // Halaman pertama: offset 0 dengan limit 500
                if ($offset === 0 && $limit >= 500) {
                    return array_fill(0, 500, ['id_wilayah' => 'w0']);
                }

                // Probe atau halaman kedua: data habis
                return [];
            });
        $this->app->instance(WilayahRefService::class, $mockWilayahService);

        // Mock NeoFeederService: mengembalikan 500 data di halaman pertama, 0 di probe berikutnya
        $mockFeederService = $this->createMock(NeoFeederService::class);
        $mockFeederService->method('execute')
            ->willReturnCallback(function (string $act, array $params) {
                if ($params['offset'] === 0 && $params['limit'] >= 500) {
                    return array_fill(0, 500, ['id_prodi' => 'p1', 'id_perguruan_tinggi' => 'pt1']);
                }

                // Probe atau halaman kedua: data habis
                return [];
            });
        $this->app->instance(NeoFeederService::class, $mockFeederService);

        // Act
        $job = new DispatchRefNasionalSyncJob;
        $job->handle();

        // Assert: Batch dibuat dengan Worker Jobs
        Bus::assertBatched(function (PendingBatch $batch) {
            return $batch->name === 'Sync RefNasional'
                && $batch->jobs->count() >= 2;
        });
    }

    /** @test */
    public function dispatcher_handles_empty_api_response(): void
    {
        Bus::fake();

        // Mock: kedua API mengembalikan data kosong
        $mockWilayahService = $this->createMock(WilayahRefService::class);
        $mockWilayahService->method('getWilayah')->willReturn([]);
        $this->app->instance(WilayahRefService::class, $mockWilayahService);

        $mockFeederService = $this->createMock(NeoFeederService::class);
        $mockFeederService->method('execute')->willReturn([]);
        $this->app->instance(NeoFeederService::class, $mockFeederService);

        // Act
        $job = new DispatchRefNasionalSyncJob;
        $job->handle();

        // Assert: tidak ada Batch yang di-dispatch
        Bus::assertNothingBatched();
    }

    /** @test */
    public function wilayah_worker_upserts_data_to_database(): void
    {
        // Mock API
        $mockWilayahService = $this->createMock(WilayahRefService::class);
        $mockWilayahService->method('getWilayah')
            ->willReturn([
                [
                    'id_wilayah' => '010000',
                    'nama_wilayah' => 'Prov. Aceh',
                    'id_level_wilayah' => 1,
                    'id_induk_wilayah' => null,
                    'id_negara' => 'ID',
                ],
                [
                    'id_wilayah' => '010100',
                    'nama_wilayah' => 'Kab. Aceh Selatan',
                    'id_level_wilayah' => 2,
                    'id_induk_wilayah' => '010000',
                    'id_negara' => 'ID',
                ],
            ]);
        $this->app->instance(WilayahRefService::class, $mockWilayahService);

        // Act
        [$job, $batch] = (new ProcessRefWilayahChunkJob(500, 0))->withFakeBatch();
        $job->handle();

        // Assert
        $this->assertDatabaseHas('ref_wilayah', [
            'id_wilayah' => '010000',
            'nama_wilayah' => 'Prov. Aceh',
            'id_level_wilayah' => 1,
        ]);
        $this->assertDatabaseHas('ref_wilayah', [
            'id_wilayah' => '010100',
            'nama_wilayah' => 'Kab. Aceh Selatan',
            'id_level_wilayah' => 2,
            'id_induk_wilayah' => '010000',
        ]);
        $this->assertFalse($batch->cancelled());
    }

    /** @test */
    public function wilayah_worker_handles_empty_data_gracefully(): void
    {
        $mockWilayahService = $this->createMock(WilayahRefService::class);
        $mockWilayahService->method('getWilayah')->willReturn([]);
        $this->app->instance(WilayahRefService::class, $mockWilayahService);

        [$job, $batch] = (new ProcessRefWilayahChunkJob(500, 5000))->withFakeBatch();
        $job->handle();

        // Tidak ada data yang di-insert
        $this->assertDatabaseCount('ref_wilayah', 0);
        $this->assertFalse($batch->cancelled());
    }

    /** @test */
    public function all_prodi_worker_upserts_pt_and_prodi_to_database(): void
    {
        $ptUuid = (string) Str::uuid();
        $prodiUuid1 = (string) Str::uuid();
        $prodiUuid2 = (string) Str::uuid();

        $mockFeederService = $this->createMock(NeoFeederService::class);
        $mockFeederService->method('execute')
            ->willReturn([
                [
                    'id_perguruan_tinggi' => $ptUuid,
                    'kode_perguruan_tinggi' => '001001',
                    'nama_perguruan_tinggi' => 'Universitas Test A',
                    'id_prodi' => $prodiUuid1,
                    'kode_program_studi' => '55201',
                    'nama_program_studi' => 'Teknik Informatika',
                    'status' => 'A',
                    'id_jenjang_pendidikan' => 30,
                    'nama_jenjang_pendidikan' => 'S1',
                ],
                [
                    'id_perguruan_tinggi' => $ptUuid,
                    'kode_perguruan_tinggi' => '001001',
                    'nama_perguruan_tinggi' => 'Universitas Test A',
                    'id_prodi' => $prodiUuid2,
                    'kode_program_studi' => '55202',
                    'nama_program_studi' => 'Sistem Informasi',
                    'status' => 'A',
                    'id_jenjang_pendidikan' => 30,
                    'nama_jenjang_pendidikan' => 'S1',
                ],
            ]);
        $this->app->instance(NeoFeederService::class, $mockFeederService);

        [$job, $batch] = (new ProcessRefAllProdiChunkJob(500, 0))->withFakeBatch();
        $job->handle();

        // Assert: PT di-upsert (hanya 1 karena 2 prodi dari PT yang sama)
        $this->assertDatabaseCount('ref_perguruan_tinggis', 1);
        $this->assertDatabaseHas('ref_perguruan_tinggis', [
            'id' => $ptUuid,
            'nama_perguruan_tinggi' => 'Universitas Test A',
        ]);

        // Assert: Prodi di-upsert (2 prodi)
        $this->assertDatabaseCount('ref_prodis', 2);
        $this->assertDatabaseHas('ref_prodis', [
            'id' => $prodiUuid1,
            'nama_program_studi' => 'Teknik Informatika',
            'id_perguruan_tinggi' => $ptUuid,
        ]);
        $this->assertDatabaseHas('ref_prodis', [
            'id' => $prodiUuid2,
            'nama_program_studi' => 'Sistem Informasi',
        ]);
        $this->assertFalse($batch->cancelled());
    }

    /** @test */
    public function all_prodi_worker_handles_empty_data_gracefully(): void
    {
        $mockFeederService = $this->createMock(NeoFeederService::class);
        $mockFeederService->method('execute')->willReturn([]);
        $this->app->instance(NeoFeederService::class, $mockFeederService);

        [$job, $batch] = (new ProcessRefAllProdiChunkJob(500, 0))->withFakeBatch();
        $job->handle();

        $this->assertDatabaseCount('ref_perguruan_tinggis', 0);
        $this->assertDatabaseCount('ref_prodis', 0);
        $this->assertFalse($batch->cancelled());
    }

    /** @test */
    public function dispatcher_creates_multiple_page_jobs(): void
    {
        Bus::fake();

        // Mock: Wilayah mengembalikan 500 data per halaman, 3 halaman
        $mockWilayahService = $this->createMock(WilayahRefService::class);
        $mockWilayahService->method('getWilayah')
            ->willReturnCallback(function (string $filter, int $limit, int $offset) {
                if ($limit === 1) {
                    // Cek halaman berikutnya
                    return $offset < 1500 ? [['id_wilayah' => 'w']] : [];
                }

                return $offset < 1500 ? array_fill(0, 500, ['id_wilayah' => 'w']) : [];
            });
        $this->app->instance(WilayahRefService::class, $mockWilayahService);

        // Mock: AllProdi kosong
        $mockFeederService = $this->createMock(NeoFeederService::class);
        $mockFeederService->method('execute')->willReturn([]);
        $this->app->instance(NeoFeederService::class, $mockFeederService);

        $job = new DispatchRefNasionalSyncJob;
        $job->handle();

        // 3 halaman Wilayah (offset 0, 500, 1000), 0 AllProdi
        Bus::assertBatched(function (PendingBatch $batch) {
            return $batch->name === 'Sync RefNasional'
                && $batch->jobs->count() === 3;
        });
    }
}
