<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

class LineMessageController extends Controller
{
    public function webhook(Request $request)
    {
        $channelToken = env('LINE_CHANNEL_ACCESS_TOKEN');
        $channelSecret = env('LINE_CHANNEL_SECRET');

        // LINE SDKに必要な設定
        $httpClient = new CurlHTTPClient($channelToken);
        $bot = new LINEBot($httpClient, ['channelSecret' => $channelSecret]);

        $events = $request->input('events', []);

        foreach ($events as $event) {
            if ($event['type'] === 'message' && $event['message']['type'] === 'text') {
                $replyToken = $event['replyToken'];
                $messageText = $event['message']['text'];

                // 送信されたメッセージをそのまま返す
                $replyMessage = new TextMessageBuilder("あなたは「{$messageText}」と言いましたね！");

                // メッセージを返信
                $bot->replyMessage($replyToken, $replyMessage);
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
}
