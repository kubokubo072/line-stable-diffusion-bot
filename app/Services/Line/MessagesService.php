<?php

namespace App\Services\Line;
use Illuminate\Support\Facades\DB;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use App\Services\Line\PostbackService;
use App\Http\Controllers\Stability\StabilityController;

/*
* lineでメッセージを受信した際の処理を行います
*/

class MessagesService
{
    public function getMessages($bot, $replyToken, $event)
    {
        $userPushText = $event['message']['text'];
        $lineId = $event['source']['userId'] ?? null;
        $featureStatus = $this->hasCurrentFeature($lineId); // 機能フラグを取得

        // リッチメニューから「キーワードから画像を作成」が選択された場合
        if ($userPushText == 'キーワードから画像を作成') {
            $featureStatus = 'generete';
            $sendMessage = '生成したい画像のキーワードを入力してください';
            $richmenuUrl = config('main.richmenu.generate');
            $this->updateFeatureStatus($lineId, $featureStatus); // フラグを更新
            $this->updateRichmenu($bot, $lineId, $richmenuUrl); // リッチメニューを変更する
            $this->pushMessage($bot, $replyToken, $sendMessage); // メッメージを出力

        // リッチメニューから「画像をスケッチする」が選択された場合
        } else if ($userPushText == '画像をスケッチする') {
            $featureStatus = 'sketch';
            $sendMessage = 'スケッチしたい画像を選択してください';
            $richmenuUrl = config('main.richmenu.sketch');
            $this->updateFeatureStatus($lineId, $featureStatus); // フラグを更新
            $this->updateRichmenu($bot, $lineId, $richmenuUrl); // リッチメニューを変更する
            $this->pushMessage($bot, $replyToken, $sendMessage); // メッメージを出力

        // 機能がすでに選択されている場合
        } else if ($featureStatus) {
            if ($featureStatus == 'generete') {
                // 画像生成します
                $StabilityController = new StabilityController();
                $StabilityController->generateImage($bot, $replyToken, $lineId, $userPushText);

            } else if ($featureStatus == 'sketch') {
                $sendMessage = '現在、「画像をスケッチ」が選択されています';
                $replyMessage = new TextMessageBuilder($sendMessage);
                $multiMessage = new MultiMessageBuilder();
                $multiMessage->add($replyMessage);
                $bot->replyMessage($replyToken, $multiMessage);
            }

        // 機能が選択されていない場合はmanualを出力
        } else {
            $PostbackService = new PostbackService();
            $PostbackService->manual($bot, $replyToken, $event);
        }
    }

    // 機能フラグを取得
    private function hasCurrentFeature($lineId)
    {
        return DB::table('users')
            ->where('line_id', $lineId)
            ->value('current_feature');
    }

    // 機能ステータス更新
    private function updateFeatureStatus($lineId, $featureStatus)
    {
        DB::table('users')
            ->where('line_id', $lineId)
            ->update([
                'current_feature' => $featureStatus,
        ]);
    }

    // リッチメニュー更新
    private function updateRichmenu($bot, $lineId, $richmenuUrl)
    {
        $bot->unlinkRichMenu($lineId);
        sleep(1);
        $bot->linkRichMenu($lineId, $richmenuUrl);
    }

    // メッセージ出力
    private function pushMessage($bot, $replyToken, $sendMessage)
    {
        $replyMessage = new TextMessageBuilder($sendMessage);
        $bot->replyMessage($replyToken, $replyMessage);
    }
}
