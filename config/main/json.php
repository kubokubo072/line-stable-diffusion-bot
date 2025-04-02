<?php
/*
    フレックスメッセージで使用するjsonを管理します
*/
return [
    'manual' => '{
        "type": "flex",
        "altText": "this is a flex message",
        "contents":{
        "type": "carousel",
        "contents": [
            {
            "type": "bubble",
            "size": "micro",
            "hero": {
                "type": "image",
                "url": "manualImage1",
                "aspectMode": "cover",
                "aspectRatio": "1:1.5",
                "size": "full"
            }
            },
            {
            "type": "bubble",
            "size": "micro",
            "hero": {
                "type": "image",
                "url": "manualImage2",
                "size": "full",
                "aspectMode": "cover",
                "aspectRatio": "1:1.5"
            }
            }
        ]}
    }',
    'template' => '{
        "type": "flex",
        "altText": "this is a flex message",
        "contents": ここにjsonを丸ごと記述してください(シングルクウォートなどは「\'」エスケープが必要です)
    }',
];
