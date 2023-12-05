<?php

namespace App\Http\Controllers;


use App\Http\Requests\AuthRequest;
use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function login()
    {
        try {
            return view('auth.login');
        } catch (Exception $e) {
            Log::error($e);
            return redirect()->back()->with(['error' => 'Somethig went wrong', 'error_msg' => $e->getMessage()]);
        }
    }
    public function loginVerify(AuthRequest $request)
    {
        try {
            $validated = $request->validated();

            $user = Auth::attempt($validated);

            if ($user) {
                $user = Auth::user();
                // Check if user has verified email or not
                // if (!empty($user->email_verified_at)) {
                return redirect()->route('dashboard')->with('success', "you're logged in successfully");
                // } else {
                //     return redirect()->back()->withErrors(['email' => 'Please verify your email address first'])->withInput();
                // }
            } else {
                return redirect()->back()->withErrors(['email' => 'Invalid credentials'])->withInput();
            }
        } catch (Exception $e) {
            Log::error($e);
            return redirect()->back()->with(['error' => 'Somethig went wrong', 'error_msg' => $e->getMessage()]);
        }
    }

    public function logout()
    {
        try {
            Session::flush();
            Auth::logout();
            return redirect()->route('login')->with('success', "You're logged out successfully");
        } catch (Exception $e) {
            Log::error($e);
            return redirect()->back()->with(['error' => 'Somethig went wrong', 'error_msg' => $e->getMessage()]);
        }
    }

    public function checkEmail(AuthRequest $request)
    {
        $email = $request->email ?? null;
        $checkUser = User::where(function ($query) use ($email) {
            $query->where('email', $email);
        })->count();

        if ($checkUser > 0) {
            return response()->json("Email address is already taken");
        } else {
            return response()->json("true");
        }
    }

    public function register($data)
    {   
        return view('auth.login');
    }

    public function userRegister(AuthRequest $request)
    {
        try {
            $data = [
                'name' => $request->first_name. ' ' .$request->last_name,
                'email' => $request->email,
                'password' =>  \Hash::make($request->password),
            ];
            User::Create($data);
            return redirect()
                ->route('login')
                ->with(['success' => 'Data store successfully.']);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()
                ->back()
                ->with([
                    'error' => 'Something is wrong',
                    'error_msg' => $e->getMessage(),
                ]);
        }
    }
}
