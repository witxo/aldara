<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaV3 implements ValidationRule
{
    protected string $action;
    protected ?float $threshold;

    public function __construct(string $action, ?float $threshold = null)
    {
        $this->action = $action;
        $this->threshold = $threshold ?? config('recaptcha.threshold');
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!config('recaptcha.enabled')) {
            return;
        }

        $secret = config('recaptcha.secret_key');
        if (empty($secret)) {
            Log::warning('reCAPTCHA v3 validation skipped: secret key not configured', [
                'action' => $this->action,
            ]);
            return;
        }

        try {
            $response = Http::timeout(config('recaptcha.timeout'))
                ->asForm()
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secret,
                    'response' => $value,
                ]);

            if (!$response->successful()) {
                $this->handleFailure($fail, 'reCAPTCHA service unavailable');
                return;
            }

            $data = $response->json();

            if (!($data['success'] ?? false)) {
                $errorCodes = $data['error-codes'] ?? ['unknown'];
                Log::warning('reCAPTCHA v3 verification failed', [
                    'action' => $this->action,
                    'errors' => $errorCodes,
                ]);
                $this->handleFailure($fail, 'Verificación de reCAPTCHA fallida');
                return;
            }

            $score = (float) ($data['score'] ?? 0.0);
            $action = $data['action'] ?? '';

            if ($action !== $this->action) {
                Log::warning('reCAPTCHA v3 action mismatch', [
                    'expected' => $this->action,
                    'received' => $action,
                ]);
                $this->handleFailure($fail, 'Acción de reCAPTCHA no válida');
                return;
            }

            if ($score < $this->threshold) {
                Log::warning('reCAPTCHA v3 score below threshold', [
                    'action' => $this->action,
                    'score' => $score,
                    'threshold' => $this->threshold,
                ]);
                $this->handleFailure($fail, 'Puntuación de reCAPTCHA insuficiente');
                return;
            }

        } catch (\Throwable $e) {
            Log::error('reCAPTCHA v3 validation exception', [
                'action' => $this->action,
                'error' => $e->getMessage(),
            ]);

            if (!config('recaptcha.fail_open')) {
                $this->handleFailure($fail, 'Error al verificar reCAPTCHA');
            }
        }
    }

    protected function handleFailure(Closure $fail, string $message): void
    {
        if (config('recaptcha.fail_open')) {
            Log::warning('reCAPTCHA v3 fail-open: allowing request despite failure', [
                'action' => $this->action,
                'message' => $message,
            ]);
            return;
        }
        $fail($message);
    }
}