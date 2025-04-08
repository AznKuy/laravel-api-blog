<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    </head>
    <body>
        <div class="container mx-auto text-2xl font-bold">
            <h1>Welcome to Laravel!</h1>
            <p>This is a simple Laravel application.</p>
        </div>

        @if (env('APP_ENV') === 'local')
            <style>
                body {
                    background-color: #f0f8ff;
                    color: #333;
                }
            </style>
        @else
            <style>
                body {
                    background-color: #fff;
                    color: #000;
                }
            </style>
        @endif
</html>
