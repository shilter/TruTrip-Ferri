<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class ApiController extends Controller {

    public function register(Request $request) {

        $data = $request->only('email', 'password','name');

        $validator = Validator::make($data, [
                    'name' => 'required|string',
                    'email' => 'required|email|unique:users',
                    'password' => 'required|string|min:6|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
        ]);

        return response()->json([
                    'success' => true,
                    'message' => 'User Created Success !!!',
                    'data' => $user
                        ], Response::HTTP_OK);
    }

    public function authenticate(Request $request) {
        $credential = $request->only('email', 'password');

        $validator = Validator::make($credential, [
                    'email' => 'required|email',
                    'password' => 'required|string|min:6|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        try {
            if (!$token = JWTAuth::attempt($credential)) {
                return response()->json([
                            'success' => false,
                            'message' => 'Could not create token'
                                ], 500);
            }
        } catch (JWTException $exc) {
//            return $credential;
            return response()->json([
                        'success' => false,
                        'message' => 'Could not create token',
                            ], 500);
        }
        return response()->json([
                    'success' => true,
                    'token' => $token
        ]);
    }

    public function logout(Request $request) {
        $validator = Validator::make($request->only('token'), ['token' => 'required']);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        try {
            JWTAuth::invalidate($request->token);
            
            return response()->json([
                'success' => true,
                'message' => 'User Has been Logout'
            ]);
        } catch (JWTException $exc) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry User cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    
    public function getUser(Request $request) {
        $value = Cache::get('key');
        
        $this->validate($request, [
            'token' => 'required'
        ]);
        
        $user  = JWTAuth::authenticate($request->token);
        
        return response()->json(['user' => $user], Response::HTTP_OK);
    }

}
