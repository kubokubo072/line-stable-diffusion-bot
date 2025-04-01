<?php

namespace App\Http\Controllers\Line;
use LINE\LINEBot;
use Illuminate\Http\Request;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use App\Services\Line\FollowService;
use App\Services\Line\PostbackService;
use App\Services\Line\MessagesService;
use App\Services\Line\ImageService;
use App\Http\Controllers\Controller;

class LineMessageController extends Controller
{
    public function webhook(Request $request)
    {
        $channelToken = env('LINE_CHANNEL_ACCESS_TOKEN');
        $channelSecret = env('LINE_CHANNEL_SECRET');
        $httpClient = new CurlHTTPClient($channelToken);
        $bot = new LINEBot($httpClient, ['channelSecret' => $channelSecret]);

        // 複数のユーザーが同時にメッセージを送信した場合は「$events」に複数の値が入る場合があるためループで回します
        $events = $request->input('events', []);
        foreach ($events as $event) {
            $eventType = $event['type'] ?? null;
            $replyToken = $event['replyToken'] ?? null;
            // 友達登録時
            if ($eventType === 'follow') {
                new FollowService()->followUser($bot, $replyToken, $event);
            // メッセージ受信時の処理
            } elseif ($eventType === 'message' && isset($event['message']['type']) && $event['message']['type'] === 'text') {
                new MessagesService()->getMessages($bot, $replyToken, $event);
            // リッチメニューの説明書クリック時
            } else if ($eventType === 'postback') {
                new PostbackService()->manual($bot, $replyToken, $event);
            } elseif ($eventType === 'message' && isset($event['message']['type']) && $event['message']['type'] === 'image') {
                // 画像を取得 ここから作成
                new ImageService()->getImage($bot, $replyToken, $event);
            }
        }
    }
}
