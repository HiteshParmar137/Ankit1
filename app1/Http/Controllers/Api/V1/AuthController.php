<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserStatuses;
use App\Helper\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AuthRequest;
use App\Http\Resources\Api\V1\Auth\LoginResource;
use App\Http\Resources\Api\V1\Auth\UserProfileResource;
use App\Jobs\SendEmailVerificationLinkMailJob;
use App\Jobs\SendOtpToResetPasswordMailJob;
use App\Mail\SendEmailVerificationLinkMail;
use App\Mail\SendOtpToResetPasswordMail;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;

class AuthController extends Controller
{
    /**
     * User Registration
     * 
     * Here for user registration we didn't manage the role and permissions
     * for now as register functionality is not a part of the auth system.
     * (If any future requirement then need to update the register functionality
     * for manage the roles and permission) 
     */
    public function register(AuthRequest $request): object
    {
        return $this->errorResponse(503, "Service Unavailable");

        try {
            DB::beginTransaction();

            $request->merge([
                'password' => Hash::make($request->password),
                'status' => UserStatuses::PENDING_USER->value
            ]);

            $user = User::create(
                $request->only([
                    'first_name',
                    'last_name',
                    'email',
                    'phone_number',
                    'phone_number_country_code',
                    'password',
                    'status'
                ])
            );

            DB::commit();

            $emailVerifyUrl = env('FRONT_END_BASE_URL')."/auth/email-verify?token=".$user->email_verification_token;

            SendEmailVerificationLinkMailJob::dispatch($user, $emailVerifyUrl)->delay(now()->addSeconds(2));

            return $this->successResponse(200, "Registered successfully. Check your inbox to verify email address");
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * Verify Email
     */
    public function verifyEmail(AuthRequest $request): object
    {
        try {
            $emailVerificationToken = $request->email_verification_token;

            $user = User::select(
                    'email_verified_at',
                    'email_verification_token',
                    'status',
                )
                ->where('email_verification_token', $emailVerificationToken)->first();

            if (!empty($user)) {
                $user->email_verified_at = now();
                $user->email_verification_token = Str::random(128);
                $user->status = UserStatuses::ACTIVE_USER->value;
                $user->save();
                return $this->successResponse(200, "Email verified successfully");
            } else {
                return $this->errorResponse(400, "Either you have already verified your email address or link is expired");
            }
        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * Send OTP for reset the password
     */
    public function sendOtpToResetPassword(AuthRequest $request): object
    {
        try {
            $email = $request->email;
            $user = User::select(
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'reset_password_otp',
                    'reset_password_otp_created_at',
                )
                ->where('email', $email)
                ->first();

            $otp = mt_rand(100000, 999999);

            $user->reset_password_otp = $otp;
            $user->reset_password_otp_created_at = now();
            $user->save();

            SendOtpToResetPasswordMailJob::dispatch($user, $otp)->delay(now()->addSeconds(2));            

            return $this->successResponse(200, "Otp has been sent to your registered email address");
        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * Verify reset password otp
     */
    public function verifyResetPasswordOtp(AuthRequest $request): object
    {
        try {
            $user = User::select(
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone_number',
                    'phone_number_country_code',
                    'email',
                    'reset_password_otp',
                    'reset_password_otp_created_at',
                )
                ->where([
                    'email' => $request->email,
                    'reset_password_otp' => $request->otp
                ])
                ->first();

            if (!empty($user)) {
                $otpCreatedAt = $user->reset_password_otp_created_at;
                $currentTime = now();
                $timeDifferenceInSeconds = strtotime($currentTime) - strtotime($otpCreatedAt);
                $timeDifferenceInHours = round($timeDifferenceInSeconds / 3600, 1);

                // Otp expiration time is 24 hours after it is created.                
                if ($timeDifferenceInHours <= 24) {
                    $user->reset_password_otp = null;
                    $user->reset_password_otp_created_at = null;
                    $user->save();

                    $profileResource = new UserProfileResource($user);
                    return $this->successResponse(200, "Otp verified successfully", $profileResource);
                } else {
                    return $this->errorResponse(400, "Otp is expired, please resend otp");
                }
            } else {
                return $this->errorResponse(400, "Given data is invalid");
            }
        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword(AuthRequest $request): object
    {
        try {
            $user = User::select(
                    'id',
                    'email',
                    'password', 
                )
                ->where('email', $request->email)
                ->first();
            $user->password = Hash::make($request->password);
            $user->save();
            return $this->successResponse(200, "Password changed successfully");
        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * Change user password
     */
    public function changePassword(AuthRequest $request): object
    {
        try {
            $user = Helpers::getLoginUser();
            $user->password = Hash::make($request->new_password);
            $user->save();
            return $this->successResponse(200, "Password changed successfully");
        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * User Login
     */
    public function login(AuthRequest $request): object
    {
        try {
            $user = User::select(
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'password',
                    'phone_number',
                    'phone_number_country_code',
                    'email_verified_at',
                    'status',
                )
                ->whereEmail($request->email)
                ->first();

            if (Hash::check($request->password, $user->password)) {
                if (!is_null($user->email_verified_at)) {
                    if ($user->status == UserStatuses::ACTIVE_USER->value) {
                        $personalAccessToken = $user->createToken('auth_token')->accessToken;
                        $loginResource = new LoginResource($user, $personalAccessToken);
                        return $this->successResponse(200, "Logged in successfully", $loginResource);
                    } else {
                        if ($user->status == UserStatuses::PENDING_USER->value) {
                            return $this->errorResponse(400, "Your application is in pending");
                        } else {
                            return $this->errorResponse(400, "User is deactivated, contact administrator to activate account");
                        }
                    }
                } else {
                    return $this->errorResponse(403, "You have not verified email address, please check inbox to verify it");
                }
            } else {
                return $this->errorResponse(403, "Invalid credentials");
            }
        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * Current User info
     */
    public function me(): object
    {
        try {
            $user = Helpers::getLoginUser();
            $profileResource = new UserProfileResource($user);
            return $this->successResponse(200, "My profile data", $profileResource);
        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * User Profile update
     */
    public function update(AuthRequest $request): object
    {
        try {
            $user = Helpers::getLoginUser();

            tap($user)->update(
                $request->only(['first_name', 'last_name'])
            );

            return $this->successResponse(200, "Profile updated successfully", ['id' => $user->id]);
        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * User Logout
     */
    public function logout(AuthRequest $request): object
    {
        try {

            /**
             * Logout from all devices...
             * Ref: https://stackoverflow.com/questions/43318310/how-to-logout-a-user-from-api-using-laravel-passport
             */

            $tokens =  $request->user()->tokens->pluck('id');
            Token::whereIn('id', $tokens)
                ->update(['revoked'=> true]);

            RefreshToken::whereIn('access_token_id', $tokens)->update(['revoked' => true]);
            
            return $this->successResponse(200, "Logged out successfully");
        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }
}
