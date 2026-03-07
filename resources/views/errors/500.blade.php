@extends('layouts.public')

@section('title', 'Erro no servidor')

@section('content')
<div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-200 p-8 text-center">
        <div class="w-16 h-16 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-6">
            <i class="fa-solid fa-exclamation-triangle text-2xl text-red-600"></i>
        </div>
        <h1 class="text-xl font-bold text-slate-800 mb-2">Algo deu errado</h1>
        <p class="text-slate-600 mb-6">Ocorreu um erro ao processar sua solicitação. Tente novamente ou volte mais tarde.</p>
        <a href="{{ route('welcome') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600">
            <i class="fa-solid fa-home"></i> Ir para início
        </a>
    </div>
</div>
@endsection
