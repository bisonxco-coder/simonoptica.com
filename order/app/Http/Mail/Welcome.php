<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Welcome extends Mailable {
    use Queueable, SerializesModels;

    private $user;

    /**
     * Create a new message instance.
     * @param User $user
     */
    public function __construct(User $user) {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        $logoUrl = config('app.url') . '/static/images/logo_full.png';

        return $this
            ->subject('Welcome to LensWare')
            ->view('emails.auth.welcome')
            ->with(['user' => $this->user, 'appUrl' => config('app.url'), 'logoUrl' => $logoUrl]);
    }
}
