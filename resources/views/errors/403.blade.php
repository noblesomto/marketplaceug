<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Unauthorized</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-100 mb-6">
            <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M18.364 5.636L5.636 18.364M5.636 5.636l12.728 12.728" />
            </svg>
        </div>
        <h1 class="text-4xl font-bold text-gray-800 mb-2">403 - Unauthorized</h1>
        <p class="text-gray-600 mb-6">You do not have permission to access this page.<br>
        If you believe this is a mistake, please contact your administrator.</p>

        <div class="flex items-center justify-center gap-4">
            <a href="{{ url()->previous() }}" 
               class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
               ⬅ Go Back
            </a>
            <a href="{{ url('/admin/index') }}"
               class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
               🏠 Dashboard
            </a>
        </div>
    </div>
</body>
</html>
