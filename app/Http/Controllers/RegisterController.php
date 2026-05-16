<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function view()
    {
        return view('auth.register');
    }

    public function regist(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect('/login')->with('success', 'Resgistration successfull! Please login to continue.');
    }
}
