<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    protected function normalizePhone(string $value): string
    {
        // keep digits only
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        // If user typed 25261xxxxxxx => convert to 61xxxxxxx
        if (Str::startsWith($digits, '25261')) {
            $digits = Str::replaceFirst('252', '', $digits); // 25261 -> 61
        }

        return $digits;
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = trim((string) $this->input('login'));
        $password = (string) $this->input('password');

        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL) !== false;

        if ($isEmail) {
            $credentials = ['email' => $login, 'password' => $password];
        } else {
            $phone = $this->normalizePhone($login);

            // ✅ Enforce your format: 61XXXXXXXX  (61 + 7 digits)
            if (!preg_match('/^61\d{7}$/', $phone)) {
                throw ValidationException::withMessages([
                    'login' => 'Phone must be like 61XXXXXXXX (example: 6151234567).',
                ]);
            }

            $credentials = ['phone' => $phone, 'password' => $password];
        }

        if (!Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')).'|'.$this->ip());
    }
}
