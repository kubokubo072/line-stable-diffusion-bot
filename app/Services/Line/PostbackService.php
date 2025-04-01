<?php

namespace App\Services\Line;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\RawMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use Illuminate\Support\Facades\DB;

/*
    * リッチメニューの説明書クリック時 + 機能フラグリセット時に呼ばれます
    * リッチメニューをリセット + フラグをリセット + 画像/テキストを出力
*/

class PostbackService
{
    public function manual($bot, $replyToken, $event)
    {
        $sendMessage = "希望の機能を選択して画像を生成してください";
        $lineId = $event['source']['userId'] ?? null;
        $this->restRichmenu($bot, $lineId); // リッチメニューリセット
        $this->resetFeatureStatus($lineId); // db値リセット
        $this->pushFlexMessage($bot, $replyToken, $sendMessage); // フレックスメッセージ出力
    }

    public function restRichmenu($bot, $lineId)
    {
        $initialRichmenuUrl = config('main.richmenu.initial');
        $bot->unlinkRichMenu($lineId);
        sleep(1);
        $bot->linkRichMenu($lineId, $initialRichmenuUrl);
    }

    private function resetFeatureStatus($lineId)
    {
        DB::table('users')
            ->where('line_id', $lineId)
            ->update(['current_feature' => null]);
    }

    public function pushFlexMessage($bot, $replyToken, $sendMessage)
    {
        $replyMessage = new TextMessageBuilder($sendMessage);
        // マニュアルとして出力するフレックスmsgのjsonテキストを取得
        $flexManualJson = config('main.json.manual');
        $manualUrl1 = config('main.imageUrl.manual1'); // 1つ目の画像
        $manualUrl2 = config('main.imageUrl.manual2'); // 2つ目の画像
        // 画像urlを置換
        $flexReplacedJson = strtr($flexManualJson, [
            'manualImage1' => asset($manualUrl1),
            'manualImage2' => asset($manualUrl2)
        ]);
        $flexReplacedJson = str_replace('http://', 'https://', $flexReplacedJson);
        $flexJsonDecode = json_decode($flexReplacedJson, true);
        $flexMessageBuild = new RawMessageBuilder($flexJsonDecode);
        $multiMessage = new MultiMessageBuilder();
        $multiMessage->add($replyMessage);
        $multiMessage->add($flexMessageBuild);
        $bot->replyMessage($replyToken, $multiMessage);
    }
}
