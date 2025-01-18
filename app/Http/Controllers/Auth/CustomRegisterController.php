<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class CustomRegisterController extends Controller
{
    public function register(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $this->create($request->all());

        return response()->json([
            'status' => 'success',
            'user' => $user
        ], 201);
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $this->generateFakeEmail(),
            'password' => Hash::make($data['password']),
        ]);
    }

    private function generateFakeEmail(): string
    {
        do {
            $hash = Str::random(20);
            $email = $hash . '@undefined.fakeemail';
        } while (User::where('email', $email)->exists());

        return $email;
    }
} 