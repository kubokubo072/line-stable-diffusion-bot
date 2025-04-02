<?php

namespace App\Services\Line;
use Illuminate\Support\Facades\DB;
use App\Services\Line\PostbackService;

class FollowService
{
    /*
     * フォロー時に以下の処理を行います
     * 画像出力 + テキスト出力 + usersテーブルにlineIdを追加
     */

    public function followUser($bot, $replyToken, $event)
    {
        $lineId = $event['source']['userId'] ?? null;
        $sendMessage = "友達登録ありがとうございます";
        $PostbackService = new PostbackService();
        // ユーザーのニックネームを取得
        $response = $bot->getProfile($lineId);
        $profile = $response->getJSONDecodedBody();
        $lineNickname = $profile['displayName'] ?? null;
        // リッチメニュー変更
        $bot->unlinkRichMenu($lineId);
        sleep(1);
        $bot->linkRichMenu($lineId, config('main.richmenu.initial')); // 新しいリッチメニューを適用
        $this->insertLineid($lineId, $lineNickname); // lineidを挿入
        $PostbackService->pushFlexMessage($bot, $replyToken, $sendMessage); // フレックスメッセージ出力
    }

    private function insertLineid($lineId, $lineNickname)
    {
        // lineidを挿入
        $isLineId = DB::table('users')
            ->where('line_id', $lineId)->exists();
        if (!$isLineId) {
            DB::table('users')
            ->insert([
                'line_id' => $lineId,
                'line_nickname' => $lineNickname,
            ]);
        }
    }
}
