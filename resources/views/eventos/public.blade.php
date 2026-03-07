<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Proticketsports')</title>
    
    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- Alpine.js (se necessário) --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Font Awesome para Ícones --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">

    {{-- Menu de Navegação Padrão --}}
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex-shrink-0">
                    <a href="/" class="text-2xl font-bold text-slate-800 flex items-center">
                        <i class="fa-solid fa-person-biking text-blue-600 mr-2"></i>
                        Proticketsports
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-6">
                    <a href="/" class="text-sm font-semibold text-gray-700 hover:text-blue-600">Home</a>
                    <a href="{{ route('eventos.public.index') }}" class="text-sm font-semibold text-gray-700 hover:text-blue-600">Eventos</a>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ auth()->user()->isOrganizador() ? route('organizador.dashboard') : route('atleta.inscricoes') }}" class="text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-md">Meu Painel</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-700 hover:text-blue-600">Login</a>
                        <a href="{{ route('register') }}" class="text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-md">Registar</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- O conteúdo específico de cada página será injetado aqui --}}
    <main>
        @yield('content')
    </main>

    {{-- Rodapé Padrão --}}
    <footer class="bg-slate-800 text-gray-400 text-sm pt-12 pb-8 mt-16">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; {{ date('Y') }} Proticketsports. Todos os direitos reservados.</p>
        </div>
    </footer>

</body>
</html>
