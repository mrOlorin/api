<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuth\Exceptions as JWTExceptions;
use Validator;
use App\User;
use Auth;

use Illuminate\Support\Facades\Hash;

/**
 * Token authentication controller
 * 
 * @Resource("/")
 */
class TokenAuthController extends Controller
{

    /**
     * User validator
     * 
     * @param array $data
     * @return \Illuminate\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make(
            $data, 
            [
                'name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|min:6',
            ],
            [
                'required' => ':attribute is required',
                'max' => ':attribute too long',
                'unique' => ':attribute already exists',
            ]);
    }

    /**
     * Authenticate user
     * 
     * @Get("/token")
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => ['invalid_credentials']], 401);
            }
        } catch (JWTExceptions\JWTException $e) {
            return response()->json(['error' => ['could_not_create_token']], 500);
        }

        return response()->json(['success' => compact('token'),], 200);
    }

    /**
     * Get current user
     * 
     * @Middleware("jwt.auth")
     * @Get("/user")
     */
    public function getUser()
    {
        if (!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['user_not_found'], 404);
        }

        return response()->json(['success' => compact('user'),], 200);
    }

    /**
     * Create new user
     * 
     * @Post("/user")
     */
    public function store(Request $request)
    {
        $newUser = $request->all();
        $validator = $this->validator($newUser);
        $password = $request->input('password');
        $newUser['password'] = Hash::make($password);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages(),], 400);
        }

        User::create($newUser);
        $token = JWTAuth::attempt(['email' => $newUser['email'], 'password' => $password,]);
        return response()->json(['success' => compact('token'),], 200);
    }

    /**
     * Delete current user
     * 
     * @Middleware("jwt.auth")
     * @Delete("/user")
     */
    public function delete()
    {
        User::destroy(Auth::user()->id);
        return response()->json(['success' => '',], 200);
    }

}
