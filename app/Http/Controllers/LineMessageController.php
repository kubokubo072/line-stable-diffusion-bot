<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Log;

use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;

use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\RawMessageBuilder;

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
            $replyToken = $event['replyToken'] ?? null;

            // 友達登録時の処理（FollowEvent）
            if ($eventType === 'follow') {
                // 画像を出力 + リッチメニュー
                $userId = $event['source']['userId'] ?? null;

                // $replyToken = $event['replyToken'] ?? null;
                if ($userId && $replyToken) {
                    $bot->linkRichMenu($userId, 'richmenu-fa43e2d8bde1e1d9b6849440fae36b9a');

                    // $line_id = $event->getUserId();
                    // Log::debug($line_id);
                    Log::debug($userId);
                    $replyMessage = new TextMessageBuilder("友達登録ありがとうございます！\nこれからよろしくお願いします。");
                    $response = $bot->replyMessage($replyToken, $replyMessage);
                    if (!$response->isSucceeded()) {
                        Log::error("FollowEventで失敗しました: " . $response->getHTTPStatus() . ' ' . $response->getRawBody());
                    }
                }

            // メッセージ受信時の処理（TextMessage）
            } elseif ($eventType === 'message' && isset($event['message']['type']) && $event['message']['type'] === 'text') {
                // $replyToken = $event['replyToken'];
                // $replyMessage = new TextMessageBuilder("テキストを取得しました");
                // $response = $bot->replyMessage($replyToken, $replyMessage);
                // if (!$response->isSucceeded()) {
                //     Log::error("FollowEventで失敗しました: " . $response->getHTTPStatus() . ' ' . $response->getRawBody());
                // }

                $flexJson =
                <<<JSON
                {
                    "type": "flex",
                    "altText": "FlecMessageContents",
                    "contents":
                    {
                        "type": "carousel",
                        "contents": [
                            {
                            "type": "bubble",
                            "body": {
                                "type": "box",
                                "layout": "vertical",
                                "contents": [
                                {
                                    "type": "image",
                                    "url": "https://developers-resource.landpress.line.me/fx/clip/clip1.jpg",
                                    "size": "full",
                                    "aspectMode": "cover",
                                    "aspectRatio": "2:3",
                                    "gravity": "top"
                                },
                                {
                                    "type": "box",
                                    "layout": "vertical",
                                    "contents": [
                                    {
                                        "type": "box",
                                        "layout": "vertical",
                                        "contents": [
                                        {
                                            "type": "text",
                                            "text": "Brown's T-shirts",
                                            "size": "xl",
                                            "color": "#ffffff",
                                            "weight": "bold"
                                        }
                                        ]
                                    },
                                    {
                                        "type": "box",
                                        "layout": "baseline",
                                        "contents": [
                                        {
                                            "type": "text",
                                            "text": "¥35,800",
                                            "color": "#ebebeb",
                                            "size": "sm",
                                            "flex": 0
                                        },
                                        {
                                            "type": "text",
                                            "text": "¥75,000",
                                            "color": "#ffffffcc",
                                            "decoration": "line-through",
                                            "gravity": "bottom",
                                            "flex": 0,
                                            "size": "sm"
                                        }
                                        ],
                                        "spacing": "lg"
                                    },
                                    {
                                        "type": "box",
                                        "layout": "vertical",
                                        "contents": [
                                        {
                                            "type": "filler"
                                        },
                                        {
                                            "type": "box",
                                            "layout": "baseline",
                                            "contents": [
                                            {
                                                "type": "filler"
                                            },
                                            {
                                                "type": "icon",
                                                "url": "https://developers-resource.landpress.line.me/fx/clip/clip14.png"
                                            },
                                            {
                                                "type": "text",
                                                "text": "Add to cart",
                                                "color": "#ffffff",
                                                "flex": 0,
                                                "offsetTop": "-2px"
                                            },
                                            {
                                                "type": "filler"
                                            }
                                            ],
                                            "spacing": "sm"
                                        },
                                        {
                                            "type": "filler"
                                        }
                                        ],
                                        "borderWidth": "1px",
                                        "cornerRadius": "4px",
                                        "spacing": "sm",
                                        "borderColor": "#ffffff",
                                        "margin": "xxl",
                                        "height": "40px"
                                    }
                                    ],
                                    "position": "absolute",
                                    "offsetBottom": "0px",
                                    "offsetStart": "0px",
                                    "offsetEnd": "0px",
                                    "backgroundColor": "#03303Acc",
                                    "paddingAll": "20px",
                                    "paddingTop": "18px"
                                },
                                {
                                    "type": "box",
                                    "layout": "vertical",
                                    "contents": [
                                    {
                                        "type": "text",
                                        "text": "SALE",
                                        "color": "#ffffff",
                                        "align": "center",
                                        "size": "xs",
                                        "offsetTop": "3px"
                                    }
                                    ],
                                    "position": "absolute",
                                    "cornerRadius": "20px",
                                    "offsetTop": "18px",
                                    "backgroundColor": "#ff334b",
                                    "offsetStart": "18px",
                                    "height": "25px",
                                    "width": "53px"
                                }
                        ],
                            "paddingAll": "0px"
                        }
                    },
                    {
                    "type": "bubble",
                    "body": {
                        "type": "box",
                        "layout": "vertical",
                        "contents": [
                        {
                            "type": "image",
                            "url": "https://developers-resource.landpress.line.me/fx/clip/clip2.jpg",
                            "size": "full",
                            "aspectMode": "cover",
                            "aspectRatio": "2:3",
                            "gravity": "top"
                        },
                        {
                            "type": "box",
                            "layout": "vertical",
                            "contents": [
                            {
                                "type": "box",
                                "layout": "vertical",
                                "contents": [
                                {
                                    "type": "text",
                                    "text": "Cony's T-shirts",
                                    "size": "xl",
                                    "color": "#ffffff",
                                    "weight": "bold"
                                }
                                ]
                            },
                            {
                                "type": "box",
                                "layout": "baseline",
                                "contents": [
                                {
                                    "type": "text",
                                    "text": "¥35,800",
                                    "color": "#ebebeb",
                                    "size": "sm",
                                    "flex": 0
                                },
                                {
                                    "type": "text",
                                    "text": "¥75,000",
                                    "color": "#ffffffcc",
                                    "decoration": "line-through",
                                    "gravity": "bottom",
                                    "flex": 0,
                                    "size": "sm"
                                }
                                ],
                                "spacing": "lg"
                            },
                            {
                                "type": "box",
                                "layout": "vertical",
                                "contents": [
                                {
                                    "type": "filler"
                                },
                                {
                                    "type": "box",
                                    "layout": "baseline",
                                    "contents": [
                                    {
                                        "type": "filler"
                                    },
                                    {
                                        "type": "icon",
                                        "url": "https://developers-resource.landpress.line.me/fx/clip/clip14.png"
                                    },
                                    {
                                        "type": "text",
                                        "text": "Add to cart",
                                        "color": "#ffffff",
                                        "flex": 0,
                                        "offsetTop": "-2px"
                                    },
                                    {
                                        "type": "filler"
                                    }
                                    ],
                                    "spacing": "sm"
                                },
                                {
                                    "type": "filler"
                                }
                                ],
                                "borderWidth": "1px",
                                "cornerRadius": "4px",
                                "spacing": "sm",
                                "borderColor": "#ffffff",
                                "margin": "xxl",
                                "height": "40px"
                            }
                            ],
                            "position": "absolute",
                            "offsetBottom": "0px",
                            "offsetStart": "0px",
                            "offsetEnd": "0px",
                            "backgroundColor": "#9C8E7Ecc",
                            "paddingAll": "20px",
                            "paddingTop": "18px"
                        },
                        {
                            "type": "box",
                            "layout": "vertical",
                            "contents": [
                            {
                                "type": "text",
                                "text": "SALE",
                                "color": "#ffffff",
                                "align": "center",
                                "size": "xs",
                                "offsetTop": "3px"
                            }
                            ],
                            "position": "absolute",
                            "cornerRadius": "20px",
                            "offsetTop": "18px",
                            "backgroundColor": "#ff334b",
                            "offsetStart": "18px",
                            "height": "25px",
                            "width": "53px"
                        }
                        ],
                        "paddingAll": "0px"
                    }
                    }
                ]
                }}
                JSON;

                // $flexJson =
                // <<<JSON
                // {
                //     "type": "flex",
                //     "altText": "必要なオプションを選択してください",
                //     "contents": {
                //         "type": "carousel",
                //         "contents": [
                //             {
                //                 "type": "bubble",
                //                 "body": {
                //                     "type": "box",
                //                     "layout": "vertical",
                //                     "contents": [
                //                         {
                //                             "type": "image",
                //                             "url": "https://developers-resource.landpress.line.me/fx/clip/clip1.jpg",
                //                             "size": "full",
                //                             "aspectMode": "cover",
                //                             "aspectRatio": "2:3",
                //                             "gravity": "top"
                //                         },
                //                         {
                //                             "type": "box",
                //                             "layout": "vertical",
                //                             "contents": [
                //                                 {
                //                                     "type": "text",
                //                                     "text": "Brown's T-shirts",
                //                                     "size": "xl",
                //                                     "color": "#ffffff",
                //                                     "weight": "bold"
                //                                 }
                //                             ]
                //                         }
                //                     ],
                //                     "paddingAll": "0px"
                //                 }
                //             },
                //             {
                //                 "type": "bubble",
                //                 "body": {
                //                     "type": "box",
                //                     "layout": "vertical",
                //                     "contents": [
                //                         {
                //                             "type": "image",
                //                             "url": "https://developers-resource.landpress.line.me/fx/clip/clip2.jpg",
                //                             "size": "full",
                //                             "aspectMode": "cover",
                //                             "aspectRatio": "2:3",
                //                             "gravity": "top"
                //                         },
                //                         {
                //                             "type": "box",
                //                             "layout": "vertical",
                //                             "contents": [
                //                                 {
                //                                     "type": "text",
                //                                     "text": "Cony's T-shirts",
                //                                     "size": "xl",
                //                                     "color": "#ffffff",
                //                                     "weight": "bold"
                //                                 }
                //                             ]
                //                         }
                //                     ],
                //                     "paddingAll": "0px"
                //                 }
                //             }
                //         ]
                //     }
                // }
                // JSON;

                  $flexJsonEncode = json_decode($flexJson,true);
                  $reply_message = new RawMessageBuilder($flexJsonEncode);
                  $bot->replyMessage($replyToken, $reply_message);

            } else if ($eventType === 'postback') {

                // ポストバックidを取得
                $postback_id = 0;
                Log::debug(print_r($eventType,true));
                $postback_id = $event['postback']['data'] ?? '';

                // 時間が選択されたら
                if ($postback_id == 'manual') {
                    $reply_message = new TextMessageBuilder('ポストバックを取得しました');
                    $bot->replyMessage($replyToken, $reply_message);
                    exit();
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
