<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Confirmado! - Proticketsports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">

    @include('layouts.public-navigation')

    <div class="container mx-auto p-4 md:p-8">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md text-center">
            
            <div class="mx-auto bg-green-100 text-green-600 w-24 h-24 rounded-full flex items-center justify-center border-4 border-green-200">
                <i class="fa-solid fa-check-double text-5xl"></i>
            </div>

            <h1 class="text-3xl font-bold text-slate-800 mt-6">Pagamento Confirmado!</h1>
            <p class="text-gray-600 mt-2">Parabéns, {{ $inscricao->atleta->user->name }}! Sua inscrição está confirmada e sua vaga no evento está garantida.</p>

            <div class="text-left bg-gray-50 p-6 rounded-lg mt-8 border">
                <h2 class="text-lg font-semibold mb-4 border-b pb-2">Resumo da Inscrição</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Evento:</span>
                        <span class="font-medium text-gray-800 text-right">{{ $inscricao->evento->nome }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Categoria:</span>
                        <span class="font-medium text-gray-800">{{ $inscricao->categoria->nome }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Código da Inscrição:</span>
                        <span class="font-medium text-gray-800 font-mono">{{ $inscricao->codigo_inscricao }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Status:</span>
                        <span class="px-3 py-1 text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            <i class="fa-solid fa-check mr-1"></i> {{ $inscricao->status === 'confirmada' ? 'Confirmada' : $inscricao->status }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <p class="text-gray-600 mt-2">Enviamos um e-mail com os detalhes da sua inscrição. Você pode gerenciar todas as suas inscrições no seu painel.</p>
                <a href="{{ route('atleta.inscricoes') }}" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg text-lg transition duration-300">
                    Ver Minhas Inscrições
                </a>
            </div>

        </div>
    </div>

</body>
</html>
