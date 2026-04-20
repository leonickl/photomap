<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('title') }}</title>

    <link rel="stylesheet" href="/css/media">
    <link rel="stylesheet" href="/css/colors">
    <link rel="stylesheet" href="/css/base">
    <link rel="stylesheet" href="/css/snippets">
    <link rel="stylesheet" href="/css/button">
    <link rel="stylesheet" href="/css/form">
    <link rel="stylesheet" href="/css/notification">
</head>

<body>
    <main>
        {{ $slot }}
    </main>
</body>

</html>