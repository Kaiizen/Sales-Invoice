<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class ProfileController extends Controller
{
    public function edit()
    {
        $userId = auth()->id();
        $user = User::find($userId);
        
        if (!$user) {
            return redirect()->route('home')->with('error', 'User not found');
        }
        
        return view('profile.edit', [
            'user' => $user
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'f_name' => 'required|string|max:255',
            'l_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
        ]);

        $userId = auth()->id();
        $user = User::find($userId);
        
        if ($user) {
            $user->f_name = $request->f_name;
            $user->l_name = $request->l_name;
            $user->email = $request->email;
            $user->save();
            
            return back()->with('success', 'Profile updated successfully');
        }
        
        return back()->with('error', 'Unable to update profile');
    }
}
