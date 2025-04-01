<?php

namespace App\Http\Controllers\Stability;
use App\Http\Controllers\Controller;
use App\Services\Stability\GenerateImageService;

class StabilityController extends Controller
{
    //「テキストから画像を生成」が選択されている時にテキストを受信したら画像を生成します
    public function generateImage($bot, $replyToken, $lineId, $userPushText)
    {
        $GenerateImageService = new GenerateImageService();
        $resultImage = $GenerateImageService->hitApi($userPushText);
        $resultImageUrl = $GenerateImageService->keepImage($lineId, $resultImage);
        $GenerateImageService->pushImage($bot, $replyToken, $resultImageUrl);
    }
}
