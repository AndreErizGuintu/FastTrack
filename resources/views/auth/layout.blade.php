<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - FastTrack Courier</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-red-800 via-red-900 to-red-950 min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">
                <i class="fas fa-truck text-white mr-2"></i>FastTrack
            </h1>
            <p class="text-red-100 text-sm">Courier Management System</p>
        </div>

        <!-- Auth Card -->
        <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-red-100 text-sm">© 2026 FastTrack Courier Services. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
