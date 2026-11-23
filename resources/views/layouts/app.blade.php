<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AlpetBizz') | AlpetBizz</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}?v={{ filemtime(public_path('img/logo.png')) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                        mono: ['Poppins', 'sans-serif']
                    }
                }
            }
        };
    </script>
    <link rel="stylesheet" href="{{ asset('assets/css/animations.css') }}?v={{ filemtime(public_path('assets/css/animations.css')) }}">
</head>
<body class="bento-body font-sans antialiased text-[#F4F7F0]">
    @include('includes.sidebar')

    <!-- Core Global Libraries (loaded once before <main> so inline page scripts work on both direct load and SPA navigation) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="{{ asset('assets/js/animations.js') }}?v={{ filemtime(public_path('assets/js/animations.js')) }}"></script>

    <main class="w-full min-h-screen lg:pl-64 flex flex-col transition-all duration-300">
        @yield('content')
    </main>

    <script src="{{ asset('assets/js/profil.js') }}?v={{ filemtime(public_path('assets/js/profil.js')) }}"></script>
    <script src="{{ asset('assets/js/spa.js') }}?v={{ filemtime(public_path('assets/js/spa.js')) }}"></script>
</body>
</html>
