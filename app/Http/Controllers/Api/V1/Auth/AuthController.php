<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\ProfileRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\ContactResource;
use App\Http\Resources\PlayerResource;
use App\Http\Resources\RegisterResource;
use App\Http\Resources\UserResource;
use App\Models\Admin\UserLog;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use HttpResponses;

    private const PLAYER_ROLE = 4;

    public function __construct()
    {
        $this->middleware('checkBanned');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('user_name', 'password');

        $user = User::where('user_name', $request->user_name)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error('', 'Credentail does not match!', 401);
        }

        if ($user->status == 0) {
            return $this->error('','You are banned. Please contact the administrator for more information.', 401);
        }

        if (! Auth::attempt($credentials)) {
            return $this->error('', 'Credentials do not match!', 401);
        }

        $user = User::where('user_name', $request->user_name)->first();
        if (! $user->hasRole('Player')) {
            return $this->error('', 'You are not a player!', 401);
        }

        UserLog::create([
            'ip_address' => $request->ip(),
            'user_id' => $user->id,
            'user_agent' => $request->userAgent(),
        ]);

        return $this->success(new UserResource($user), 'User login successfully.');
    }

    public function register(RegisterRequest $request)
    {
        $agent = User::where('referral_code', $request->referral_code)->first();
        if ($agent) {
            $inputs = $request->validated();

            $userPrepare = array_merge(
                $inputs,
                [
                    'user_name' => $this->generateRandomString(),
                    'password' => Hash::make($inputs['password']),
                    'agent_id' => $agent->id,
                    'type' => UserType::Player,
                ]
            );

            $player = User::create($userPrepare);
            $player->roles()->sync(self::PLAYER_ROLE);

            return $this->success(new RegisterResource($player), 'User register successfully.');
        } else {
            return $this->error('', 'Not Found Agent', 401);
        }

    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();

        return $this->success([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function getUser()
    {
        return $this->success(new PlayerResource(Auth::user()), 'User Success');
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $player = Auth::user();
        if (Hash::check($request->current_password, $player->password)) {
            $player->update([
                'password' => $request->password,
                'status' => 1,

            ]);
        } else {
            return $this->error('', 'Old Passowrd is incorrect', 401);
        }

        return $this->success($player, 'Password has been changed successfully.');
    }

    public function playerChangePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed'],
            'user_id' => ['required'],
        ]);
        $player = User::where('id', $request->user_id)->first();

        if ($player) {
            $player->update([
                'password' => Hash::make($request->password),
                'is_changed_password' => true,
            ]);

            return $this->success($player, 'Password has been changed successfully.');
        } else {
            return $this->error('', 'Not Found Player', 401);
        }
    }

    public function profile(ProfileRequest $request)
    {

        $player = Auth::user();
        $player->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        return $this->success(new PlayerResource($player), 'Update profile');
    }

    public function contact()
    {
        $player = Auth::user();

        return $this->success(new ContactResource($player->parent), 'Contact List');
    }

    private function generateRandomString()
    {
        $randomNumber = mt_rand(10000000, 99999999);

        return 'SB'.$randomNumber;
    }
}
