<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgentLoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Session-based agent authentication via Sanctum (HTTP-only cookies).
 */
class AgentAuthController extends Controller
{
  private const MAX_ATTEMPTS = 5;

  private const ATTEMPT_WINDOW_SECONDS = 1800; // 30 minutes

  private const LOCKOUT_SECONDS = 900; // 15 minutes

  /**
   * Authenticate an agent and establish a stateful session.
   */
  public function login(AgentLoginRequest $request): JsonResponse
  {
    $email = Str::lower($request->validated('email'));
    $lockoutKey = $this->lockoutKey($email);
    $attemptKey = $this->attemptKey($email);

    if (RateLimiter::tooManyAttempts($lockoutKey, 1)) {
      $seconds = RateLimiter::availableIn($lockoutKey);

      return response()->json([
        'message' => 'Akun terkunci karena terlalu banyak percobaan login gagal.',
        'retry_after_seconds' => $seconds,
      ], Response::HTTP_TOO_MANY_REQUESTS);
    }

    if (! Auth::once([
      'email' => $email,
      'password' => $request->validated('password')
    ])) {
      RateLimiter::hit($attemptKey, self::ATTEMPT_WINDOW_SECONDS);

      if (RateLimiter::tooManyAttempts($attemptKey, self::MAX_ATTEMPTS)) {
        RateLimiter::clear($attemptKey);
        RateLimiter::hit($lockoutKey, self::LOCKOUT_SECONDS);

        return response()->json([
          'message' => 'Akun terkunci karena terlalu banyak percobaan login gagal.',
          'retry_after_seconds' => self::LOCKOUT_SECONDS,
        ], Response::HTTP_TOO_MANY_REQUESTS);
      }

      return response()->json([
        'message' => 'Email atau password salah.',
      ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    RateLimiter::clear($attemptKey);
    RateLimiter::clear($lockoutKey);

    $user = Auth::user();
    $token = $user->createToken('prime-property-web')->plainTextToken;

    return response()->json([
      'message' => 'Login berhasil.',
      'token' => $token,
      'token_type' => 'Bearer',
      'user' => [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role,
      ],
    ]);
  }

  /**
   * Destroy the authenticated session.
   */
  public function logout(): JsonResponse
  {
    $user = request()->user();

    if ($user) {
      $token = $user->currentAccessToken();
      if ($token && method_exists($token, 'delete')) {
        $token->delete();
      } else {
        $user->tokens()->delete();
      }
    }

    Auth::guard('web')->logout();

    return response()->json(['message' => 'Logout berhasil.']);
  }

  private function attemptKey(string $email): string
  {
    return 'agent-login-attempts:'.sha1($email);
  }

  private function lockoutKey(string $email): string
  {
    return 'agent-login-lockout:'.sha1($email);
  }
}
