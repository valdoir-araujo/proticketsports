<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ProTicket Sports') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

        <!-- TRIX Editor -->
        <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
        <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js" defer></script>

        <!-- Scripts e Estilos (VITE) -->
        <!-- O Vite já carrega o Alpine.js e o Tailwind nativamente no Laravel atual. -->
        <!-- NÃO adicione CDNs do Alpine ou Tailwind aqui para evitar conflitos. -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
        <div class="min-h-screen">
            
            {{-- MENU DE NAVEGAÇÃO --}}
            @include('layouts.navigation')

            {{-- CABEÇALHO DA PÁGINA --}}
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow relative z-30">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            {{-- ÁREA DE MENSAGENS (SUCESSO/ERRO) --}}
            {{-- Adicionei isso para o usuário saber o que aconteceu --}}
            @if (session('success') || session('error') || session('status'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                    @if (session('success'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm" role="alert">
                            <p class="font-bold">Sucesso!</p>
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm" role="alert">
                            <p class="font-bold">Atenção!</p>
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif
                    @if (session('status'))
                        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded shadow-sm" role="alert">
                            <p>{{ session('status') }}</p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- CONTEÚDO PRINCIPAL --}}
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>