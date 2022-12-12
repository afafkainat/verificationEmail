<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegisteredUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $user;

    public function __construct($user)
    {
$this->user=$user;
    }

    public function via($notifiable){
        return ['mail'];
    }


    public function toMail($notifiable){
        return (new MailMessage)
        ->line('Hi! '. $notifiable->name)
        ->line('New user registered')
        ->action('verify', url('/'));
    }
}
