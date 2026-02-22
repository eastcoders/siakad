<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\User::with(['roles', 'dosen']);

        // Filter By Role jika dipilih
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $users = $query->paginate(15);
        $allRoles = \Spatie\Permission\Models\Role::pluck('name', 'id');

        return view('admin.manajemen_dosen.index', compact('users', 'allRoles'));
    }

    public function assignRole(Request $request, \App\Models\User $user)
    {
        $request->validate(['roles' => 'required|array']);

        // Spatie otomatis melakukan sinkronisasi
        $user->syncRoles($request->roles);

        return back()->with('success', 'Jabatan (Role) berhasil diperbarui untuk user: ' . $user->name);
    }
}
