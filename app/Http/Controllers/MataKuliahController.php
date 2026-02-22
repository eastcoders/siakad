<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\MataKuliah;

class MataKuliahController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mataKuliah = MataKuliah::with('prodi')->orderBy('kode_mk')->get();
        return view('mata-kuliah.index', compact('mataKuliah'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $prodi = \App\Models\ProgramStudi::all();
        return view('mata-kuliah.create', compact('prodi'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\StoreMataKuliahRequest $request)
    {
        $validated = $request->validated();

        // Add default values for local data
        $validated['sumber_data'] = 'lokal';
        $validated['status_sinkronisasi'] = MataKuliah::STATUS_CREATED_LOCAL;
        $validated['status_aktif'] = true;
        $validated['sync_action'] = 'insert';
        $validated['is_local_change'] = true;
        $validated['is_deleted_server'] = false;
        $validated['is_deleted_local'] = false;

        MataKuliah::create($validated);

        return redirect()->route('admin.mata-kuliah.index')
            ->with('success', 'Mata Kuliah berhasil ditambahkan secara lokal.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $mataKuliah = MataKuliah::with('prodi')->findOrFail($id);
        return view('mata-kuliah.show', compact('mataKuliah'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $mataKuliah = MataKuliah::findOrFail($id);

        // Allowed to edit locally based on Offline-First rules

        $prodi = \App\Models\ProgramStudi::all();
        return view('mata-kuliah.edit', compact('mataKuliah', 'prodi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\App\Http\Requests\UpdateMataKuliahRequest $request, string $id)
    {
        $mataKuliah = MataKuliah::findOrFail($id);

        // Allowed to edit locally based on Offline-First rules

        $validated = $request->validated();

        // Update status for push possibility
        $isSyncedBefore = in_array($mataKuliah->status_sinkronisasi, [
            MataKuliah::STATUS_SYNCED,
            MataKuliah::STATUS_PUSH_SUCCESS,
        ], true);

        if ($mataKuliah->sumber_data === 'server') {
            $validated['status_sinkronisasi'] = MataKuliah::STATUS_UPDATED_LOCAL;
            $validated['sync_action'] = 'update';
            $validated['is_local_change'] = true;
        } else {
            if ($isSyncedBefore) {
                $validated['status_sinkronisasi'] = MataKuliah::STATUS_UPDATED_LOCAL;
                $validated['sync_action'] = 'update';
            }
            $validated['is_local_change'] = true;
        }

        $mataKuliah->update($validated);

        return redirect()->route('admin.mata-kuliah.index')
            ->with('success', 'Mata Kuliah berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $mataKuliah = MataKuliah::findOrFail($id);

        if ($mataKuliah->sumber_data === 'server') {
            $mataKuliah->update([
                'is_deleted_local' => true,
                'status_sinkronisasi' => MataKuliah::STATUS_DELETED_LOCAL,
                'sync_action' => 'delete',
                'is_local_change' => true,
                'sync_error_message' => null,
            ]);
        } else {
            $hasEverSynced = $mataKuliah->last_push_at !== null
                || in_array($mataKuliah->status_sinkronisasi, [
                    MataKuliah::STATUS_SYNCED,
                    MataKuliah::STATUS_UPDATED_LOCAL,
                    MataKuliah::STATUS_DELETED_LOCAL,
                    MataKuliah::STATUS_PUSH_SUCCESS,
                ], true);

            if (!$hasEverSynced) {
                $mataKuliah->delete();
            } else {
                $mataKuliah->update([
                    'is_deleted_local' => true,
                    'status_sinkronisasi' => MataKuliah::STATUS_DELETED_LOCAL,
                    'sync_action' => 'delete',
                    'is_local_change' => true,
                    'sync_error_message' => null,
                ]);
            }
        }

        return redirect()->route('admin.mata-kuliah.index')
            ->with('success', 'Mata Kuliah berhasil dihapus.');
    }
}
