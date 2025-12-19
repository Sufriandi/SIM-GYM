<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules.
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'], // username/email/no_hp
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = $this->input('login');

        // Tentukan tipe login (email / username / no_hp)
        $field = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : (is_numeric($login) ? 'no_hp' : 'username');

        // 1. Cek apakah user ada (termasuk yang sudah di-soft delete)
        $user = \App\Models\User::withTrashed()->where($field, $login)->first();

        if (! $user || ! \Illuminate\Support\Facades\Hash::check($this->password, $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        // 2. Cek apakah user sedang di-soft delete (Non-aktif)
        if ($user->trashed()) {
            throw ValidationException::withMessages([
                'login' => 'Akun Anda telah dinonaktifkan. Silakan hubungi admin BETA GYM.',
            ]);
        }

        // 3. Jika semua aman, lakukan login
        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure login not rate limited.
     */
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
        return Str::lower($this->input('login')) . '|' . $this->ip();
    }
}
