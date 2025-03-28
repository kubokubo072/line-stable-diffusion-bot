<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Log;

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

            // 友達登録時の処理（FollowEvent）
            if ($eventType === 'follow') {
                $userId = $event['source']['userId'] ?? null;
                $replyToken = $event['replyToken'] ?? null;
                if ($userId && $replyToken) {
                    $replyMessage = new TextMessageBuilder("友達登録ありがとうございます！\nこれからよろしくお願いします。");
                    $response = $bot->replyMessage($replyToken, $replyMessage);
                    if (!$response->isSucceeded()) {
                        Log::error("FollowEventで失敗しました: " . $response->getHTTPStatus() . ' ' . $response->getRawBody());
                    }
                }

            // メッセージ受信時の処理（TextMessage）
            } elseif ($eventType === 'message' && isset($event['message']['type']) && $event['message']['type'] === 'text') {
                $replyToken = $event['replyToken'];
                $replyMessage = new TextMessageBuilder("テキストを取得しました");
                $response = $bot->replyMessage($replyToken, $replyMessage);
                if (!$response->isSucceeded()) {
                    Log::error("FollowEventで失敗しました: " . $response->getHTTPStatus() . ' ' . $response->getRawBody());
                }
            }

            //【bk】
            // if ($event['type'] === 'message' && $event['message']['type'] === 'text') {
            //     $replyToken = $event['replyToken'];
            //     $messageText = $event['message']['text'];
            //     $imageUrl = $this->generateImageFromPrompt($messageText);
            //     $imageUrl = str_replace("http://", "https://", $imageUrl);
            //     // 画像をLINEで送信
            //     if ($imageUrl) {
            //         // 生成した画像URLをImageMessageBuilderに渡す
            //         $replyMessage = new ImageMessageBuilder($imageUrl, $imageUrl);
            //     } else {
            //         // 画像が生成できなかった場合、エラーメッセージを返信
            //         $replyMessage = new TextMessageBuilder("画像の生成に失敗しました。");
            //     }
            //     // メッセージを返信
            //     $bot->replyMessage($replyToken, $replyMessage);
            // }
        }
        // return response()->json(['status' => 'success'], 200);
    }

    //【bk】
    // 画像生成APIの呼び出し
    // private function generateImageFromPrompt($prompt)
    // {
    //     $imageController = new ImageController();
    //     return $imageController->generateImageFromPrompt($prompt);
    // }
}
