<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Divulgar Evento: <span class="font-normal">{{ $evento->nome }}</span>
                </h2>
            </div>
            <a href="{{ route('organizador.eventos.show', $evento) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">&larr; Voltar ao Evento</a>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ feedbackCopiado: null }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Card WhatsApp --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 flex items-center space-x-3">
                    <i class="fa-brands fa-whatsapp fa-2x text-green-500"></i>
                    <h3 class="text-lg font-medium text-gray-900">Divulgação no WhatsApp</h3>
                </div>
                <div class="p-6">
                    <label for="whatsapp-text" class="block text-sm font-medium text-gray-700 mb-1">Texto Sugerido:</label>
                    <textarea id="whatsapp-text" rows="12" readonly class="w-full border-gray-300 rounded-md shadow-sm bg-gray-50 text-sm">{{ $textoWhatsapp }}</textarea>
                    <div class="mt-4 flex justify-end space-x-3">
                        <button 
                            @click="navigator.clipboard.writeText('{{ $textoWhatsapp }}').then(() => { feedbackCopiado = 'whatsapp'; setTimeout(() => feedbackCopiado = null, 2000) })"
                            class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-md transition duration-300"
                            :class="{ 'bg-indigo-600 text-white': feedbackCopiado === 'whatsapp', 'bg-gray-600 hover:bg-gray-700 text-white': feedbackCopiado !== 'whatsapp' }">
                            <span x-show="feedbackCopiado !== 'whatsapp'"><i class="fa-solid fa-copy mr-2"></i>Copiar Texto</span>
                            <span x-show="feedbackCopiado === 'whatsapp'"><i class="fa-solid fa-check mr-2"></i>Copiado!</span>
                        </button>
                        <a href="https://wa.me/?text={{ urlencode($textoWhatsapp) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-semibold rounded-md transition">
                            <i class="fa-brands fa-whatsapp mr-2"></i>Compartilhar Agora
                        </a>
                    </div>
                </div>
            </div>

            {{-- Card Instagram (Exemplo) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 flex items-center space-x-3">
                     <i class="fa-brands fa-instagram fa-2x text-pink-500"></i>
                    <h3 class="text-lg font-medium text-gray-900">Divulgação no Instagram</h3>
                </div>
                 <div class="p-6">
                    <label for="insta-text" class="block text-sm font-medium text-gray-700 mb-1">Texto Sugerido (Adapte emojis e hashtags):</label>
                    <textarea id="insta-text" rows="8" readonly class="w-full border-gray-300 rounded-md shadow-sm bg-gray-50 text-sm">{{ $textoInstagram }}</textarea>
                    <div class="mt-4 flex justify-end space-x-3">
                         <button 
                             @click="navigator.clipboard.writeText('{{ $textoInstagram }}').then(() => { feedbackCopiado = 'instagram'; setTimeout(() => feedbackCopiado = null, 2000) })"
                             class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-md transition duration-300"
                            :class="{ 'bg-indigo-600 text-white': feedbackCopiado === 'instagram', 'bg-gray-600 hover:bg-gray-700 text-white': feedbackCopiado !== 'instagram' }">
                             <span x-show="feedbackCopiado !== 'instagram'"><i class="fa-solid fa-copy mr-2"></i>Copiar Texto</span>
                            <span x-show="feedbackCopiado === 'instagram'"><i class="fa-solid fa-check mr-2"></i>Copiado!</span>
                         </button>
                         {{-- Link direto não funciona bem pro Insta, melhor copiar e colar --}}
                    </div>
                     <p class="mt-4 text-xs text-gray-500">Dica: Use uma imagem atraente (como o banner do evento) junto com este texto no seu post ou story.</p>
                </div>
            </div>

            {{-- Você pode adicionar mais cards para Facebook, E-mail, etc. --}}

        </div>
    </div>
</x-app-layout>