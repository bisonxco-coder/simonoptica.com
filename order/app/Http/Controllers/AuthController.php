<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller {
    /**
     * Create user
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'agree_terms' => 'required|accepted'
        ], []);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 400);

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'started' => false
        ]);

        if ($user->save()) {
            $tokenResult = $user->createToken('personal');
            $user->sendWelcomeNotification();

            return response()->json([
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
                'user' => $user
            ]);
        } else {
            return response()->json(['error' => 'There was an error saving the user']);
        }
    }

    /**
     * Login user and create token
     *
     * @param  Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ], []);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 400);

        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = $request->user();
        $tokenResult = $user->createToken('personal');
        $token = $tokenResult->token;

        if (!$request->remember_me) $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
            'user' => $user
        ]);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse [string] message
     */
    public function logout(Request $request) {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    /**
     * Forgot password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse [string] message
     */
    public function forgot(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ], []);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 400);

        Password::sendResetLink($request->only('email'));
        return response()->json(['message' => 'Reset link sent'], 200);
    }

    /**
     * Reset password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse [string] message
     */
    public function reset(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed'
        ], []);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 400);

        $resetStatus = Password::reset($request->all(), function ($user, $password) {
            $user->password = bcrypt($password);
            $user->save();
        });

        if ($resetStatus == Password::INVALID_TOKEN) {
            return response()->json(['errors' => ['token' => ['Invalid token']]], 400);
        }

        return response()->json(['message' => 'Password has been changed'], 200);
    }
}
