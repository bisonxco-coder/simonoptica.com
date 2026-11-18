<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordReset extends Mailable {
    use Queueable, SerializesModels;

    private $user;
    private $token;

    /**
     * Create a new message instance.
     * @param User $user
     * @param string $token
     */
    public function __construct(User $user, string $token) {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        $resetUrl = config('app.url') . '/forgot-password/reset-password?email=' . $this->user->email . '&token=' . $this->token;
        $logoUrl = config('app.url') . '/static/images/logo_full.png';

        return $this
            ->subject('LensWare password reset')
            ->view('emails.auth.reset_password')
            ->with(['user' => $this->user, 'appUrl' => config('app.url'), 'resetUrl' => $resetUrl, 'logoUrl' => $logoUrl]);
    }
}
