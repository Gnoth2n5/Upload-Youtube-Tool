<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client as GoogleClient;
use App\Models\GoogleToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Http\Service\NotificationService;

class GoogleOAuthController extends Controller
{
    public function auth()
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->addScope('https://www.googleapis.com/auth/youtube.upload');

        // Đảm bảo yêu cầu refresh token
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return redirect($client->createAuthUrl());
    }

    public function callback(Request $request)
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));

        $token = $client->fetchAccessTokenWithAuthCode($request->input('code'));


        if (isset($token['error'])) {
            NotificationService::sendNotification('error', 'Failed to authenticate with Google');
            return redirect()->route('dashboard');
        }

        if (!isset($token['refresh_token'])) {
            $existingToken = GoogleToken::where('user_id', Auth::id())->first();
            $token['refresh_token'] = $existingToken->refresh_token ?? null;
        }

        GoogleToken::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'],
                'expires_at' => Carbon::now()->addSeconds($token['expires_in']),
            ]
        );

        NotificationService::sendNotification('success', 'Successfully authenticated with Google');
        return redirect()->route('dashboard');
    }
}
