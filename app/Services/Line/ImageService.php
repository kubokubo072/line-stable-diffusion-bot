<?php

namespace App\Services\Line;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;

/*
* lineでメッセージを受信した際の処理を行います
*/

class ImageService
{
    public function getImage($bot, $replyToken, $event)
    {
        $lineId = $event['source']['userId'] ?? null;
        $messageId = $event['message']['id']; // 画像のメッセージIDを取得
        $imagePath = $this->saveImageFromLine($bot, $messageId, $lineId); // 画像を保存

        if ($imagePath) {
            $sketchPath = $this->convertToSketch($imagePath, $lineId);
            if ($sketchPath) {
                // スケッチ画像のURL
                $imageUrl = url('storage/' . str_replace('storage/', '', $sketchPath));
                $previewImageUrl = str_replace("http://", "https://", $imageUrl);
                // LINEに画像を送信
                $replyMessage = new ImageMessageBuilder($previewImageUrl, $previewImageUrl);
            } else {
                $replyMessage = new TextMessageBuilder("スケッチ変換に失敗しました。");
            }
        } else {
            $replyMessage = new TextMessageBuilder("画像の保存に失敗しました。");
        }
        $bot->replyMessage($replyToken, $replyMessage);
        $bot->replyMessage($replyToken, $replyMessage);
    }

    private function saveImageFromLine($bot, $messageId, $lineId)
    {
        // LINE APIから画像データを取得
        $response = $bot->getMessageContent($messageId);
        if ($response->isSucceeded()) {
            $imageData = $response->getRawBody();  // バイナリデータを取得
            // 画像の保存先 (storage/app/public/images/ に保存)
            $fileName = 'images/origin/' . $lineId . '_' . time() . '.jpeg';
            Storage::disk('public')->put($fileName, $imageData);
            return 'storage/' . $fileName;
        }
        return false;
    }

    private function convertToSketch($imagePath, $lineId)
    {
        $fullPath = storage_path('app/public/' . str_replace('storage/', '', $imagePath));
        if (!file_exists($fullPath)) {
            return false;
        }
        $STABILITY_AUTHORIZATION = env('STABILITY_AUTHORIZATION');
        $stabilityApiUrl = config('main.const.stabilityApi.sketch');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $STABILITY_AUTHORIZATION,
            'Accept' => 'image/*',
        ])->attach('image', file_get_contents($fullPath), basename($fullPath))
          ->post($stabilityApiUrl, [
              'prompt' => 'received image make it sketched style',
              'control_strength' => 0.7,
              'output_format' => 'jpeg',
          ]);

        if ($response->successful()) {
            // スケッチ画像の保存先
            $sketchFileName = 'images/sketch/' . $lineId . '_' . time() . '.jpeg';
            Storage::disk('public')->put($sketchFileName, $response->body());
            return 'storage/' . $sketchFileName; // 公開URL
        }
        return false;
    }
}
