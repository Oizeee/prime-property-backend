<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Public contact form endpoint with strict anti-spam rate limiting.
 */
class ContactController extends Controller
{
  /**
   * Accept and log a contact form submission.
   */
  public function store(ContactRequest $request): JsonResponse
  {
    $validated = $request->validated();

    // Log for operational follow-up; extend with mail/queue as needed.
    Log::channel('single')->info('Prime Property contact form submission', [
      'nama' => $validated['nama'],
      'email' => $validated['email'],
      'nomor_hp' => $validated['nomor_hp'],
      'pesan' => $validated['pesan'],
      'ip' => $request->ip(),
    ]);

    return response()->json([
      'message' => 'Pesan Anda telah terkirim. Tim kami akan segera menghubungi Anda.',
    ], Response::HTTP_CREATED);
  }
}
