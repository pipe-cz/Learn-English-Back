<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            //generate token
            $token = auth()->user()->createToken('Personal Access Token');
            $token->accessToken = $token->plainTextToken;
            return response()->json([
                'message' => 'Login successful',
                'token' => $token->plainTextToken,
                'user' => auth()->user()
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
    }
}
