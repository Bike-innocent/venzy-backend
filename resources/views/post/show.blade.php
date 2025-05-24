{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $title }}" />
    <meta property="og:description" content="{{ $description }}" />
    <meta property="og:image" content="{{ url($image) }}" />
    <meta property="og:type" content="article" />
    <meta property="og:url" content="{{ url()->current() }}" />
    
    <!-- Standard Meta Tags -->
    <title>{{ $title }}</title>
</head>
<body>
    <!-- Root for React app -->
    <div id="root"></div>
    <p>{{ $title }}</p>
    <p>{{ $description }}</p>
    <img src="{{ $image }}" alt="{{ $title }}">

    
    <!-- React JS bundle -->
    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html> --}}


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title" content="{{ $title }}" />
    <meta property="og:description" content="{{ $description }}" />
    <meta property="og:image" content="{{ $image }}" />
    <meta property="og:type" content="article" />
    <meta property="og:url" content="{{ $url }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="description" content="{{ $description }}" />
    <title>{{ $title }}</title>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>{{ $description }}</p>
    <img src="{{ $image }}" alt="{{ $title }}">
</body>
</html>
