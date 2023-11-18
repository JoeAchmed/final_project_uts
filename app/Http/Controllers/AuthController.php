<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request) {
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required'
            ]);
    
            // menangkap inputan
            $input = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ];
    
            // menginsert data ke table user
            User::create($input);
            $data = [
                'message' => 'User is created successfully'
            ];
    
            // mengirim response JSON
            return response()->json($data, 200);
        } catch (\Exception $err) {
            // Handle validation error
            $errorMessage = $err->getMessage();

            $errorResponse = [
                'message' => 'Validation Error',
                'errors' => $errorMessage,
            ];

            return response()->json($errorResponse, 422);
        }
    }

    public function login(Request $request) {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);
    
            // menangkap input user
            $input = [
                'email' => $request->email,
                'password' => $request->password
            ];
    
            // mengambil data user (DB)
            $user = User::where('email', $input['email'])->first();
    
            // membandingkan input user dengan data user (DB)
            $isLoginSuccessfully = (
                $input['email'] == $user->email && Hash::check($input['password'], $user->password)
            );
    
            if ($isLoginSuccessfully) {
                // membuat token
                $token = $user->createToken('auth_token');
    
                $data = [
                    'message' => 'Login successfully',
                    'token' => $token->plainTextToken
                ];
    
                // mengembalikan response JSON
                return response()->json($data, 200);
            } else {
                $data = [
                    'message' => 'Username or Password is wrong'
                ];
    
                return response()->json($data, 401);
            }
        } catch (\Exception $err) {
            // Handle validation error
            $errorMessage = $err->getMessage();

            $errorResponse = [
                'message' => 'Validation Error',
                'errors' => $errorMessage,
            ];

            return response()->json($errorResponse, 422);
        }
    }
}
