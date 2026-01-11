<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('frontend/images/favicon.png') }}">

<meta name="csrf-token" content="{{ csrf_token() }}">


<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4998736645213032" crossorigin="anonymous"></script>

@auth
    <script>
        window.Laravel = {
            userId: {{ auth()->id() }},
            soundUrl: "{{ asset('sound/new-message.mp3') }}",
            iconUrl: "{{ asset('images/logo.png') }}",
            unreadUrl: "{{ route('unread.messages.count') }}"
        };
    </script>
@endauth
</head>

<body class="bg-body text-gray-700 text-sm">
    <main id="main-content">
