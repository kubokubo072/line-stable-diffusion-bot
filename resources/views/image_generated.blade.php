<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>画像生成</title>
</head>
<body>
    <h1>画像生成を始める</h1>
    <form action="{{ route('generate.image') }}" method="GET">
        <button type="submit">画像生成</button>
    </form>
</body>
</html>
