@extends('layouts.public')

@section('title', 'Acesso não autorizado')

@section('content')
<div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-200 p-8 text-center">
        <div class="w-16 h-16 mx-auto rounded-full bg-amber-100 flex items-center justify-center mb-6">
            <i class="fa-solid fa-lock text-2xl text-amber-600"></i>
        </div>
        <h1 class="text-xl font-bold text-slate-800 mb-2">Acesso não autorizado</h1>
        <p class="text-slate-600 mb-6">{{ isset($exception) ? $exception->getMessage() : 'Você não tem permissão para acessar esta página.' }}</p>
        <a href="{{ url()->previous() ?: route('welcome') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>
@endsection
