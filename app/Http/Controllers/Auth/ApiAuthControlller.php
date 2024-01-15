<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ApiAuthControlller extends Controller
{
    
    public function register (Request $req)
    {
        $validator = Validator::make($req->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|unique:users,email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $req['password'] = Hash::make($req['password']);
        $req['remember_token'] = Str::random(10);
        $user = User::create($req->toArray());
        $token = $user->createToken('Laravel Paswword Grant Client')->plainTextToken;
        // $response = ['token' => $token];
        // $data = User::where('name','=', $user->name)->first();
        
        return response() -> json([
           'success' => true,
           'message' => 'Berhasil menambahkan akun',
           'token' => $token
        ]);
    }

    public function login (Request $req)
    {
        $validator = Validator::make($req->all(), [
            'email' => 'required|string|unique:users,email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) 
        {
            return response(['errors' => $validator -> errors() -> all()], 422);
        }
        $user = User::where('email', $req -> email) -> first();
        if ($user)
        {
            if (Hash::check($req -> password, $user -> password)) 
            {
                $token = $user -> createToken('Laravel Password Grant Client') -> accessToken;
                $response = ['token' => $token];
                return response($response, 200);
            }
            else 
            {
                $response = ["message" => "Password Tidak Cocok!"];
                return response ($response, 422);
            }
        }
        else 
        {
            $response = ["message" => 'User Tidak Ditemukan'];
            return response($response,422);
        }
    }

    public function logout (Request $req)
    {
        $token = $req -> user() -> token();
        $token -> revoke();
        $response = ['message' => 'Anda telah logout'];
        return response($response, 200);
    }

}
