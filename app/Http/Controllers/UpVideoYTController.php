<?php

namespace App\Http\Controllers;

use App\Http\Service\NotificationService;
use App\Models\UpVideoYT;
use Illuminate\Http\Request;
use App\Models\GoogleToken;
use Google\Client as GoogleClient;
use Google\Service\YouTube;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Google\Http\MediaFileUpload;


class UpVideoYTController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $req)
    {
        $req->validate([
            'title' => 'required',
            'description' => 'required',
            'video' => 'required|mimes:mp4|max:20000',
        ]);

        $googleToken = GoogleToken::where('user_id', Auth::id())->first();

        if (!$googleToken) {
            return redirect()->route('auth.google');
        }

        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));

        //kiểm tra token hết hạn
        if (Carbon::now()->greaterThan($googleToken->expires_at)) {
            $client->refreshToken($googleToken->refresh_token);
            $newToken = $client->getAccessToken();
            // lưu token mới vào db
            $googleToken->update([
                'access_token' => $newToken['access_token'],
                'expires_at' => Carbon::now()->addSeconds($newToken['expires_in']),
            ]);
        } else {
            // set token hiện tại
            $client->setAccessToken($googleToken->access_token);
        }

        // tạo service youtube
        $youtube = new YouTube($client);

        // Cấu hình video upload
        $snippet = new YouTube\VideoSnippet();
        $snippet->setTitle($req->input('title'));
        $snippet->setDescription($req->input('description'));
        $snippet->setTags(['Laravel', 'YouTube']);
        $snippet->setCategoryId('22'); // Category: People & Blogs

        $status = new YouTube\VideoStatus();
        $status->setPrivacyStatus('public'); // Tùy chỉnh quyền riêng tư

        $video = new YouTube\Video();
        $video->setSnippet($snippet);
        $video->setStatus($status);

        if ($req->hasFile('video')) {
            // Xử lý file video từ input
            $videoPath = $req->file('video')->path();
        } else {
            NotificationService::sendNotification('error', 'Failed to upload video to server');
            return redirect()->route('dashboard');
        }


        // Tải lên video với chunk size 1MB
        $chunkSizeBytes = 1 * 1024 * 1024;
        $client->setDefer(true);

        // Tạo yêu cầu tải video lên
        $insertReq = $youtube->videos->insert(
            'status,snippet',
            $video,
            [
                'data' => file_get_contents($videoPath),
                'mimeType' => 'video/*',
                'uploadType' => 'resumable',
            ]
        );

        // Tạo đối tượng MediaFileUpload
        $media = new MediaFileUpload(
            $client,
            $insertReq,
            'video/*',
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize(filesize($videoPath));

        // Xử lý upload video
        $status = false;
        $handle = fopen($videoPath, 'rb');
        if ($handle === false) {
            throw new \Exception('Could not open video file: ' . $videoPath);
        }

        while (!$status && !feof($handle)) {
            $chunk = fread($handle, $chunkSizeBytes);
            $status = $media->nextChunk($chunk);
        }

        // Kiểm tra trạng thái upload
        if ($status) {
            NotificationService::sendNotification('success', 'Video uploaded successfully. Video ID: ' . $status['id']);
        } else {
            NotificationService::sendNotification('error', 'Failed to upload video');
        }

        fclose($handle);
        $client->setDefer(false);

        redirect()->route('dashboard');
    }
}
