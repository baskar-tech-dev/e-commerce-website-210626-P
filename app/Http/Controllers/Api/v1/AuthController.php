<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\CustomerProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Customer registration.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'accepted',
        ], [
            'terms.accepted' => 'You must agree to the Terms & Privacy Policy to create an account.',
            'email.unique' => 'An account with this email address already exists. Please sign in.',
            'phone.unique' => 'An account with this mobile number already exists. Please sign in.',
            'password.confirmed' => 'Passwords do not match.',
            'password.min' => 'Please choose a stronger password (minimum 8 characters).',
        ]);

        $nameParts = explode(' ', trim($validated['name']), 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        // Assign default customer role if available
        $customerRole = Role::where('name', 'customer')
            ->orWhere('name', 'Customer')
            ->first();

        try {
            $user = User::create([
                'name' => trim($validated['name']),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower(trim($validated['email'])),
                'phone' => !empty($validated['phone']) ? trim($validated['phone']) : null,
                'password' => Hash::make($validated['password']),
                'role_id' => $customerRole?->id,
                'is_active' => true,
            ]);

            // Auto-create customer profile
            CustomerProfile::create([
                'user_id' => $user->id,
                'email_subscribed' => true,
            ]);

            // Update last login metadata
            $user->last_login_at = now();
            $user->last_login_ip = $request->ip();
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Account created successfully',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user->load('customerProfile'),
            ], 201);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'phone' => ['An account with this mobile number or email already exists. Please sign in.'],
            ]);
        } catch (\Throwable $e) {
            Log::error('AuthController@register error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during registration. Please try again.',
            ], 500);
        }
    }

    /**
     * Authenticate a user with email or phone and return a Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginId = trim($request->email);
        $cleanedDigits = preg_replace('/[^0-9]/', '', $loginId);

        try {
            // Find user by email or flexible phone number match
            $user = User::where('email', strtolower($loginId))
                ->orWhere('phone', $loginId)
                ->when(!empty($cleanedDigits) && strlen($cleanedDigits) >= 8, function ($query) use ($cleanedDigits) {
                    $lastDigits = substr($cleanedDigits, -10);
                    $query->orWhere('phone', 'LIKE', "%{$lastDigits}%");
                })
                ->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['No account found with this email address or phone number. Please click "Create Account" to register.'],
                ]);
            }

            if (!Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'password' => ['The password you entered is incorrect. Please try again or click "Forgot Password?".'],
                ]);
            }

            if (!$user->is_active) {
                throw ValidationException::withMessages([
                    'email' => ['Your account is disabled. Please contact customer support.'],
                ]);
            }

            // Update last login details
            $user->last_login_at = now();
            $user->last_login_ip = $request->ip();
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            // Load user relationships safely
            $user->loadMissing(['roles.permissions', 'customerProfile']);

            return response()->json([
                'success' => true,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('AuthController@login error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to sign in at this time. Please check your credentials and try again.',
            ], 500);
        }
    }

    /**
     * Log the user out (Invalidate token).
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Initiate forgot password flow.
     */
    public function forgotPassword(Request $request, \App\Services\OrderNotificationService $notificationService): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Return safe success message to prevent user enumeration
            return response()->json([
                'success' => true,
                'message' => 'If an account exists with that email, password reset instructions have been sent.',
            ]);
        }

        try {
            // Generate a secure reset token
            $token = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ]
            );

            $resetUrl = url("/reset-password?token={$token}&email=" . urlencode($email));

            // Configure SMTP settings dynamically and dispatch email
            $notificationService->configureDynamicMailer();
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\PasswordResetMail($user, $resetUrl));

            Log::info("Password reset email sent to {$email}");

            return response()->json([
                'success' => true,
                'message' => 'Password reset instructions have been sent to your email address.',
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to send password reset email to {$email}: " . $e->getMessage());
            return response()->json([
                'success' => true,
                'message' => 'Password reset instructions have been sent to your email address.',
            ]);
        }
    }

    /**
     * Reset password.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'nullable|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password reset request.',
            ], 400);
        }

        if ($request->filled('token')) {
            $record = DB::table('password_reset_tokens')->where('email', $email)->first();
            if (!$record || !Hash::check($request->token, $record->token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The password reset token is invalid or has expired.',
                ], 400);
            }
            DB::table('password_reset_tokens')->where('email', $email)->delete();
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully. You can now sign in with your new password.',
        ]);
    }
}
