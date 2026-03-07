@extends('layouts.public')

@section('title', 'Política de Privacidade e Termos de Uso - Proticketsports')

@section('content')
<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Cabeçalho da Página --}}
        <div class="text-center mb-12">
            <h1 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                Política de Privacidade e Termos de Uso
            </h1>
            <p class="mt-4 text-lg text-gray-500">
                Transparência total sobre como cuidamos dos seus dados e as regras da nossa plataforma.
            </p>
            <p class="mt-2 text-sm text-gray-400">Última atualização: {{ date('d/m/Y') }}</p>
        </div>

        {{-- Conteúdo Principal --}}
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6 space-y-8 text-gray-700 leading-relaxed">

                {{-- Introdução --}}
                <section>
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fa-solid fa-shield-halved text-orange-500 mr-2"></i> 1. Introdução
                    </h2>
                    <p>
                        A <strong>Proticketsports</strong> (doravante referida como "Plataforma", "Nós") está comprometida em proteger a privacidade e os dados pessoais de seus usuários (atletas e organizadores). Esta política descreve como coletamos, usamos e protegemos suas informações, em conformidade com a <strong>Lei Geral de Proteção de Dados (LGPD - Lei nº 13.709/2018)</strong>.
                    </p>
                </section>

                {{-- Coleta de Dados --}}
                <section>
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fa-solid fa-database text-orange-500 mr-2"></i> 2. Dados Coletados
                    </h2>
                    <p class="mb-2">Para viabilizar a inscrição em eventos esportivos, coletamos os seguintes dados:</p>
                    <ul class="list-disc pl-5 space-y-1 bg-gray-50 p-4 rounded-md border border-gray-200">
                        <li><strong>Dados de Identificação:</strong> Nome completo, CPF, Data de Nascimento, Sexo.</li>
                        <li><strong>Dados de Contato:</strong> E-mail, Telefone/WhatsApp, Endereço completo.</li>
                        <li><strong>Dados de Saúde (Sensíveis):</strong> Tipo sanguíneo, alergias ou condições médicas (apenas quando exigido pelo Organizador do evento para segurança do atleta).</li>
                        <li><strong>Dados de Pagamento:</strong> Informações de transação (processadas de forma criptografada pelo Mercado Pago; não armazenamos números de cartão de crédito integralmente).</li>
                    </ul>
                </section>

                {{-- Finalidade --}}
                <section>
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fa-solid fa-bullseye text-orange-500 mr-2"></i> 3. Como usamos seus dados
                    </h2>
                    <p>Seus dados são utilizados estritamente para:</p>
                    <ol class="list-decimal pl-5 space-y-2 mt-2">
                        <li>Processar sua inscrição e garantir sua vaga no evento.</li>
                        <li>Emitir apólices de seguro atleta (quando aplicável ao evento).</li>
                        <li>Enviar comunicações importantes sobre o evento (alterações de horário, retirada de kits, resultados).</li>
                        <li>Processamento financeiro e emissão de comprovantes.</li>
                        <li>Divulgação de resultados (Ranking), onde seu nome, categoria e equipe podem ser listados publicamente.</li>
                    </ol>
                </section>

                {{-- Compartilhamento --}}
                <section>
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fa-solid fa-share-nodes text-orange-500 mr-2"></i> 4. Compartilhamento de Dados
                    </h2>
                    <p>
                        Nós <strong>não vendemos</strong> seus dados. As informações são compartilhadas apenas com:
                    </p>
                    <ul class="list-disc pl-5 mt-2">
                        <li><strong>Organizadores do Evento:</strong> Para gestão da prova, cronometragem e entrega de kits.</li>
                        <li><strong>Gateway de Pagamento (Mercado Pago):</strong> Para processar as transações financeiras com segurança.</li>
                        <li><strong>Autoridades Legais:</strong> Apenas se formos obrigados por lei ou ordem judicial.</li>
                    </ul>
                </section>

                {{-- Uso de Imagem --}}
                <section>
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fa-solid fa-camera text-orange-500 mr-2"></i> 5. Uso de Imagem
                    </h2>
                    <p>
                        Ao se inscrever em eventos através da Proticketsports, o atleta aceita que fotos e vídeos capturados durante o evento podem ser utilizados pelos Organizadores e pela Plataforma para fins de divulgação, publicidade e promoção do esporte, sem ônus ou compensação financeira.
                    </p>
                </section>

                {{-- Segurança --}}
                <section>
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fa-solid fa-lock text-orange-500 mr-2"></i> 6. Segurança
                    </h2>
                    <p>
                        Adotamos medidas técnicas robustas para proteger seus dados, incluindo criptografia SSL (Site Seguro), firewalls e acesso restrito aos bancos de dados. No entanto, nenhum sistema é 100% inviolável, e contamos com a colaboração dos usuários para manterem suas senhas seguras.
                    </p>
                </section>

                {{-- Contato --}}
                <section class="border-t pt-8 mt-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Dúvidas ou Solicitações?</h2>
                    <p class="mb-4">
                        Para exercer seus direitos de titular de dados (como exclusão ou correção de informações), entre em contato com nosso Encarregado de Dados (DPO):
                    </p>
                    <div class="bg-orange-50 border-l-4 border-orange-500 p-4">
                        <p class="font-bold">Equipe Proticketsports</p>
                        <p>E-mail: <a href="mailto:admin@proticketsports.com.br" class="text-orange-600 hover:underline">admin@proticketsports.com.br</a></p>
                        <p>WhatsApp: (46) 9 9130-5398</p>
                    </div>
                </section>

            </div>
        </div>

        <div class="text-center mt-8">
            <a href="/" class="text-indigo-600 hover:text-indigo-800 font-semibold flex justify-center items-center">
                <i class="fa-solid fa-arrow-left mr-2"></i> Voltar para a Página Inicial
            </a>
        </div>

    </div>
</div>
@endsection