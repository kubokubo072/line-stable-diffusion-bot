<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageController extends Controller
{
    // public function generateImage()
    // {
    //     // 画像を保存するパス
    //     $imageDir = public_path('images');
    //     if (!file_exists($imageDir)) {
    //         mkdir($imageDir, 0777, true);
    //     }
    //     $imagePath = $imageDir . '/generated_image.jpeg';

    //     // APIリクエスト（Stable Diffusion API を呼び出す）
    //     $apiKey = "sk-xxxx"; // 実際のAPIキーを設定
    //     $curl = curl_init();
    //     curl_setopt_array($curl, [
    //         CURLOPT_URL => "https://api.stability.ai/v2beta/stable-image/generate/core",
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_POST => true,
    //         CURLOPT_HTTPHEADER => [
    //             "Authorization: $apiKey",
    //             "Accept: image/*"
    //         ],
    //         CURLOPT_POSTFIELDS => [
    //             "model" => "sd3.5-medium",
    //             "prompt" => "日本 中国 仲良し",
    //             "output_format" => "jpeg",
    //             "width" => "512",
    //             "height" => "512"
    //         ]
    //     ]);

    //     $imageData = curl_exec($curl);
    //     $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    //     $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
    //     $error = curl_error($curl);
    //     curl_close($curl);

    //     // エラーチェック
    //     if ($httpCode !== 200) {
    //         return response()->json([
    //             'error' => "API request failed with status code $httpCode",
    //             'contentType' => $contentType,
    //             'curlError' => $error
    //         ], 500);
    //     }

    //     if (strpos($contentType, 'image') === false) {
    //         return response()->json([
    //             'error' => "Invalid response from API. Expected an image, but received $contentType"
    //         ], 500);
    //     }

    //     // 画像を保存
    //     if ($imageData) {
    //         file_put_contents($imagePath, $imageData);
    //     } else {
    //         return response()->json(['error' => 'Failed to write image data'], 500);
    //     }

    //     return view('image_generated', ['imagePath' => asset('images/generated_image.jpeg')]);
    // }

    public function generateImage()
    {
        $response = $this->callApiToGenerateImage();

        // 画像を保存する
        $imagePath = public_path('images/generated_image.jpeg');
        file_put_contents($imagePath, $response);

        return view('image_generated', ['imagePath' => 'images/generated_image.jpeg']);
    }

    private function callApiToGenerateImage()
    {
        // APIリクエストの設定
        $url = "https://api.stability.ai/v2beta/stable-image/generate/core";
        $headers = [
            "authorization: sk-xxxx",
            // "authorization: sk-sAfYu17HOA7ebzmGg9ICPvd7tO8EE6Dm8pQ1LAS30BZrYIiu",
            "accept: image/*",
        ];

        $postData = [
            'model' => 'sd3.5-medium',
            'prompt' => '日本 中国 仲良し',
            'output_format' => 'jpeg',
            'width' => 512,
            'height' => 512
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
