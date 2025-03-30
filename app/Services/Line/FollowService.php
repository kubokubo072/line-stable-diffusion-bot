<?php

namespace App\Services\Line;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // dbファサード
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\RawMessageBuilder;

class FollowService
{
    /*
     * フォロー時に以下の処理を行います
     * 画像出力 + テキスト出力 + usersテーブルにlineIdを追加
     */
    public function followUser($bot, $replyToken, $event)
    {
        $lineId = $event['source']['userId'] ?? null;
        if ($lineId && $replyToken) {
            $bot->linkRichMenu($lineId, 'richmenu-fa43e2d8bde1e1d9b6849440fae36b9a');
            $isLineId = DB::table('users')
                ->where('line_id', $lineId)->exists();
            if (!$isLineId) {
                DB::table('users')
                    ->insert(values: ['line_id' => $lineId]);
            }
            // テキストを出力
            $replyMessage = new TextMessageBuilder("友達登録ありがとうございます");
            // 画像を出力
            $flexMessageJson = config('json.follow');
            $flexMessageJsonDecode = json_decode($flexMessageJson, true);
            $flexMessage = new RawMessageBuilder($flexMessageJsonDecode);
            $multiMessage = new MultiMessageBuilder();
            $multiMessage->add($replyMessage);
            $multiMessage->add($flexMessage);
            $bot->replyMessage($replyToken, $multiMessage);
        }
    }
}
