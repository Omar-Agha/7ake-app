<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterNewCustomer;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function __construct(private CustomerService $customerService) {}


    public function register(RegisterNewCustomer $request)
    {
        // Validate the request data
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        \DB::beginTransaction();
        // Create a new user
        $user = User::create([
            'name' => trim($data['f_name'] . ' ' . $data['l_name']),
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $this->customerService->create($data['f_name'], $data['l_name'], $data['phone'], $data['email'], $user->id, $request->file('avatar'));
        \DB::commit();
        // Generate an API token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return the user and token in the response
        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }
    public function login(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Attempt to authenticate the user
        if (!auth()->attempt($validatedData)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Generate an API token for the authenticated user
        $token = auth()->user()->createToken('auth_token')->plainTextToken;

        // Return the user and token in the response
        return response()->json([
            'user' => auth()->user(),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
