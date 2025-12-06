<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $email;
    public string $resetUrl;
    public int $ttlMinutes;
    public string $brand;

    public function __construct(string $email, string $resetUrl, int $ttlMinutes = 60)
    {
        $this->email      = $email;
        $this->resetUrl   = $resetUrl;
        $this->ttlMinutes = $ttlMinutes;
        $this->brand      = (string) (config('mail.from.name') ?: config('app.name', 'MSIT'));
    }

    public function build()
    {
        $subject = "Reset your password • {$this->brand}";

        return $this->from(config('mail.from.address'), $this->brand)
            ->subject($subject)
            ->view('emails.resetPassword')
            ->with([
                'brand'      => $this->brand,
                'email'      => $this->email,
                'resetUrl'   => $this->resetUrl,
                'ttlMinutes' => $this->ttlMinutes,
            ]);
    }
}
