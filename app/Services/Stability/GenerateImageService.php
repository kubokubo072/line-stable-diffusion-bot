<?php

namespace App\Services\Stability;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;

class GenerateImageService
{
    public function hitApi($userPushText)
    {
        $STABILITY_AUTHORIZATION = env('STABILITY_AUTHORIZATION');
        $stabilityApiUrl = config('main.const.stabilityApi.generate');

        $headers = [
            "authorization: $STABILITY_AUTHORIZATION",
            "accept: image/*",
        ];
        $postData = [
            'model' => 'sd3.5-medium',
            'prompt' => $userPushText,
            'output_format' => 'jpeg',
            'width' => 512,
            'height' => 512
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $stabilityApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function keepImage($lineId, $resultImage)
    {
        // 画像を保存する
        $imagePath = public_path('images/generate/' . $lineId . '_' . time() . '.jpeg');
        file_put_contents($imagePath, $resultImage);
        // 保存した画像のURLを返す
        $imageUrl = asset($imagePath);
        $replaceImageUrl = str_replace("http://", "https://", $imageUrl);
        $replaceImageUrl = str_replace('/var/www/html/public/', '/', $replaceImageUrl);
        return $replaceImageUrl;
    }

    public function pushImage($bot, $replyToken, $resultImageUrl)
    {
        // 画像をLINEで送信
        if ($resultImageUrl) {
            // 生成した画像URLをImageMessageBuilderに渡す
            $replyMessage = new ImageMessageBuilder($resultImageUrl, $resultImageUrl);
        } else {
            // 画像が生成できなかった場合、エラーメッセージを返信
            $replyMessage = new TextMessageBuilder("画像の生成に失敗しました。");
        }
        $bot->replyMessage($replyToken, $replyMessage);
    }
}
