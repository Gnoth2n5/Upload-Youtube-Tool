<?php

namespace App\Http\Service;
use Flasher\Toastr\Prime\ToastrInterface;

class NotificationService
{
    public static function sendNotification($type, $message)
    {
        toastr()
            ->timeOut(5000)
            ->newestOnTop(true)
            ->progressBar(true)
            ->$type($message);

    }
}