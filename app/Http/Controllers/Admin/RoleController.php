<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = \Spatie\Permission\Models\Role::withCount('users')->paginate(15);
        return view('admin.roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
        ]);

        \Spatie\Permission\Models\Role::create(['name' => $request->name]);

        return back()->with('success', 'Master Jabatan berhasil ditambahkan!');
    }

    public function show(string $id)
    {
        $role = \Spatie\Permission\Models\Role::findOrFail($id);

        // Memuat seluruh user (beserta profil dosen) yang punya role ini
        $users = $role->users()->with('dosen')->paginate(15);

        return view('admin.roles.show', compact('role', 'users'));
    }

    public function update(Request $request, string $id)
    {
        $role = \Spatie\Permission\Models\Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
        ]);

        $role->update(['name' => $request->name]);

        return back()->with('success', 'Nama Jabatan berhasil diubah!');
    }

    public function destroy(string $id)
    {
        $role = \Spatie\Permission\Models\Role::findOrFail($id);

        // Jangan hapus role krusial
        if (in_array(strtolower($role->name), ['admin', 'dosen'])) {
            return back()->with('error', 'Jabatan sistem (Admin / Dosen Default) tidak boleh dihapus!');
        }

        $role->delete();

        return back()->with('success', 'Master Jabatan berhasil dihapus!');
    }
}
