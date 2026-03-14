<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class FirstLoginController extends Controller
{
    /**
     * Show the first login update form.
     */
    public function edit(): View
    {
        $user = Auth::user();
        $contactData = null;

        if ($user->hasRole('Mahasiswa')) {
            $contactData = $user->mahasiswa;
        } elseif ($user->hasRole('Dosen')) {
            $contactData = $user->dosen;
        }

        return view('auth.first-login', [
            'user' => $user,
            'contactData' => $contactData,
        ]);
    }

    /**
     * Update the user's password and contact information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'whatsapp' => ['required', 'string', 'max:20'],
            'password' => [
                'required',
                'confirmed',
                Password::defaults(),
                function ($attribute, $value, $fail) use ($user) {
                    if (Hash::check($value, $user->password)) {
                        $fail('Password baru tidak boleh sama dengan password lama.');
                    }
                },
            ],
        ]);

        // Update User
        $user->update([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_first_login' => false,
        ]);

        // Update Mahasiswa/Dosen WhatsApp
        if ($user->hasRole('Mahasiswa') && $user->mahasiswa) {
            $user->mahasiswa->update(['whatsapp' => $request->whatsapp]);
        } elseif ($user->hasRole('Dosen') && $user->dosen) {
            $user->dosen->update(['whatsapp' => $request->whatsapp]);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Data berhasil diperbarui. Selamat datang!');
    }
}
