<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActiveRoleController extends Controller
{
    /**
     * Switch the active role in the session and redirect back.
     */
    public function switchRole(Request $request)
    {
        $request->validate([
            'role' => 'required|string',
        ]);

        $roleToSwitch = $request->role;
        $user = Auth::user();

        // Security check: Only allow switching to roles the user actually has
        if ($user->hasRole($roleToSwitch)) {
            session(['active_role' => $roleToSwitch]);
            return redirect()->route('dashboard')->with('success', "Active role switched to {$roleToSwitch}.");
        }

        return redirect()->back()->with('error', 'Unauthorized role switch attempt.');
    }
}
