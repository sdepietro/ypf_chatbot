<?php
//app/Helpers/Envato/User.php
namespace App\Helpers;



use Illuminate\Support\Facades\Http;

if (!function_exists('wSendSlackMessage')) {
    function wSendSlackMessage($text)
    {

        $webhookUrl = config('constants.slack_notification_webhook');
        $response = Http::post($webhookUrl, [
            'text' => $text
        ]);
    }
}

