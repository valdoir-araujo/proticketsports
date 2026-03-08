<x-app-layout>
    {{-- Carrega o TinyMCE via CDNJS --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            tinymce.init({
                selector: '#descricao_completa',
                height: 500,
                menubar: false,
                promotion: false, 
                branding: false,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount', 'emoticons'
                ],
                toolbar: 'undo redo | formatselect | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | emoticons | help',
                content_style: 'body { font-family:Inter,sans-serif; font-size:14px }',
                skin: 'oxide',
                content_css: 'default'
            });
        });
    </script>

    {{-- Header "Hero" Moderno --}}
    <div class="relative bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 pt-8 pb-24 overflow-hidden shadow-xl">
        {{-- Background Effects --}}
        <div class="absolute inset-0 opacity-30 pointer-events-none" style="background-image: radial-gradient(#fb923c 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-orange-600/20 blur-3xl pointer-events-none mix-blend-screen animate-pulse-slow"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-white z-10">
                    <div class="flex items-center gap-2 mb-2 text-blue-200 text-sm font-medium">
                        <a href="{{ route('organizador.dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
                        <i class="fa-solid fa-chevron-right text-xs opacity-50"></i>
                        <span class="text-white">Criar Evento</span>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md">
                        Novo Evento
                    </h2>
                    <p class="text-blue-100 mt-2 text-lg">Preencha os detalhes abaixo para lançar sua competição.</p>
                </div>
                
                {{-- Botão de Voltar --}}
                <a href="{{ url()->previous() }}" class="inline-flex items-center px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl font-bold text-sm text-white backdrop-blur-md transition-all hover:-translate-y-0.5">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="py-12 -mt-20 relative z-20">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- BARRA DE CONTEXTO DA ORGANIZAÇÃO --}}
            <div class="bg-white border-l-4 border-indigo-500 p-6 mb-8 rounded-r-xl shadow-lg flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                        <i class="fa-solid fa-building text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Organizador Responsável</p>
                        @if(Auth::user()->organizacoes()->count() > 1)
                            <div class="relative mt-1">
                                <select onchange="window.location.href='{{ route('organizador.eventos.create') }}?org_id='+this.value" 
                                        class="appearance-none bg-indigo-50 border border-indigo-200 text-indigo-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 pr-8 font-bold cursor-pointer hover:bg-indigo-100 transition-colors">
                                    @foreach(Auth::user()->organizacoes as $org)
                                        <option value="{{ $org->id }}" {{ $org->id == $organizacao->id ? 'selected' : '' }}>
                                            {{ $org->nome_fantasia ?? $org->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-indigo-700">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        @else
                            <h3 class="text-xl font-bold text-slate-800">{{ $organizacao->nome_fantasia ?? $organizacao->nome }}</h3>
                        @endif
                    </div>
                </div>
                
                @if(Auth::user()->organizacoes()->count() > 1)
                    <div class="text-right hidden md:block">
                        <span class="text-xs text-indigo-500 bg-indigo-50 px-3 py-1 rounded-full font-medium">
                            <i class="fa-solid fa-circle-info mr-1"></i> Alternar organização carrega os campeonatos correspondentes.
                        </span>
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('organizador.eventos.store') }}" enctype="multipart/form-data" 
                  x-data="{
                      estados: {{ $estados->toJson() }},
                      cidades: [],
                      estadoSelecionado: '{{ old('estado_id') }}',
                      cidadeSelecionada: '{{ old('cidade_id') }}',
                      async getCidades() {
                          if (!this.estadoSelecionado) { this.cidades = []; return; }
                          const response = await fetch(`/api/estados/${this.estadoSelecionado}/cidades`);
                          const data = await response.json();
                          this.cidades = data;
                          if (this.cidades.findIndex(c => c.id == this.cidadeSelecionada) === -1) {
                              this.cidadeSelecionada = '';
                          }
                      }
                  }"
                  x-init="if (estadoSelecionado) { await getCidades() }">
                @csrf

                <input type="hidden" name="organizacao_id" value="{{ $organizacao->id }}">

                <div class="space-y-8">
                    
                    {{-- SEÇÃO 1: DETALHES --}}
                    <div class="bg-white overflow-hidden shadow-lg sm:rounded-2xl border border-slate-100">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center">
                                <i class="fa-solid fa-info"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Informações Básicas</h3>
                                <p class="text-xs text-slate-500">Dados principais de identificação.</p>
                            </div>
                        </div>
                        <div class="p-8 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6">
                                
                                {{-- Nome do Evento --}}
                                <div class="lg:col-span-3">
                                    <x-input-label for="nome" value="Nome do Evento" class="text-slate-700 font-bold" />
                                    <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg" :value="old('nome')" required placeholder="Ex: 3º Desafio de Mountain Bike..." autofocus />
                                    <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                                </div>

                                {{-- Modalidade --}}
                                <div class="lg:col-span-1">
                                    <x-input-label for="modalidade_id" value="Modalidade" class="text-slate-700 font-bold" />
                                    <select id="modalidade_id" name="modalidade_id" class="mt-1 block w-full border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm" required>
                                        <option value="">Selecione...</option>
                                        @foreach($modalidades as $modalidade)
                                            <option value="{{ $modalidade->id }}" @selected(old('modalidade_id') == $modalidade->id)>{{ $modalidade->nome }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('modalidade_id')" class="mt-2" />
                                </div>

                                {{-- Vincular a Campeonato --}}
                                <div class="lg:col-span-2">
                                    <x-input-label for="campeonato_id" value="Campeonato (Opcional)" class="text-slate-700 font-bold" />
                                    <select id="campeonato_id" name="campeonato_id" class="mt-1 block w-full border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm">
                                        <option value="">Nenhum (Evento Avulso)</option>
                                        @forelse($campeonatos as $campeonato)
                                            <option value="{{ $campeonato->id }}" @selected(old('campeonato_id', $selectedCampeonatoId ?? '') == $campeonato->id)>
                                                {{ $campeonato->nome }} ({{$campeonato->ano}})
                                            </option>
                                        @empty
                                            <option value="" disabled>Sem campeonatos cadastrados</option>
                                        @endforelse
                                    </select>
                                    <x-input-error :messages="$errors->get('campeonato_id')" class="mt-2" />
                                </div>

                                {{-- Peso da Etapa --}}
                                <div class="lg:col-span-1">
                                    <x-input-label for="pontos_multiplicador" value="Peso (Ranking)" class="text-slate-700 font-bold" />
                                    <select id="pontos_multiplicador" name="pontos_multiplicador" class="mt-1 block w-full border-slate-300 focus:border-orange-500 focus:ring-orange-500 rounded-lg shadow-sm">
                                        <option value="1" @selected(old('pontos_multiplicador', 1) == 1)>1x (Normal)</option>
                                        <option value="1.5" @selected(old('pontos_multiplicador', 1) == 1.5)>1.5x</option>
                                        <option value="2" @selected(old('pontos_multiplicador', 1) == 2)>2x (Dobro)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SEÇÃO 2: DATAS E PRAZOS --}}
                    <div class="bg-white overflow-hidden shadow-lg sm:rounded-2xl border border-slate-100">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                <i class="fa-regular fa-calendar-check"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Datas e Prazos</h3>
                                <p class="text-xs text-slate-500">Defina o cronograma do evento.</p>
                            </div>
                        </div>
                        <div class="p-8">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="data_evento" value="Data do Evento" class="text-slate-700 font-bold" />
                                    <x-text-input id="data_evento" name="data_evento" type="datetime-local" class="mt-1 block w-full border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" :value="old('data_evento')" min="2000-01-01T00:00" required />
                                    <x-input-error :messages="$errors->get('data_evento')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="data_inicio_inscricoes" value="Abertura das Inscrições" class="text-slate-700 font-bold" />
                                    <x-text-input id="data_inicio_inscricoes" name="data_inicio_inscricoes" type="datetime-local" class="mt-1 block w-full border-slate-300 rounded-lg focus:ring-green-500 focus:border-green-500" :value="old('data_inicio_inscricoes')" min="2000-01-01T00:00" required />
                                    <x-input-error :messages="$errors->get('data_inicio_inscricoes')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="data_fim_inscricoes" value="Encerramento das Inscrições" class="text-slate-700 font-bold" />
                                    <x-text-input id="data_fim_inscricoes" name="data_fim_inscricoes" type="datetime-local" class="mt-1 block w-full border-slate-300 rounded-lg focus:ring-red-500 focus:border-red-500" :value="old('data_fim_inscricoes')" min="2000-01-01T00:00" required />
                                    <x-input-error :messages="$errors->get('data_fim_inscricoes')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- SEÇÃO 3: LOCALIZAÇÃO --}}
                    <div class="bg-white overflow-hidden shadow-lg sm:rounded-2xl border border-slate-100">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center">
                                <i class="fa-solid fa-map-location-dot"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Localização</h3>
                                <p class="text-xs text-slate-500">Onde o evento será realizado.</p>
                            </div>
                        </div>
                        <div class="p-8">
                             <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-1">
                                    <x-input-label for="estado_id" value="Estado" class="text-slate-700 font-bold" />
                                    <select id="estado_id" name="estado_id" x-model="estadoSelecionado" @change="getCidades()" class="mt-1 block w-full border-slate-300 focus:border-teal-500 focus:ring-teal-500 rounded-lg shadow-sm" required>
                                        <option value="">Selecione...</option>
                                        <template x-for="estado in estados" :key="estado.id">
                                            <option :value="estado.id" x-text="estado.nome"></option>
                                        </template>
                                    </select>
                                    <x-input-error :messages="$errors->get('estado_id')" class="mt-2" />
                                </div>
                                 <div class="md:col-span-1">
                                    <x-input-label for="cidade_id" value="Cidade" class="text-slate-700 font-bold" />
                                    <select id="cidade_id" name="cidade_id" x-model="cidadeSelecionada" class="mt-1 block w-full border-slate-300 focus:border-teal-500 focus:ring-teal-500 rounded-lg shadow-sm bg-slate-50 disabled:opacity-50" :disabled="!estadoSelecionado || cidades.length === 0" required>
                                        <option value="">Selecione...</option>
                                        <template x-for="cidade in cidades" :key="cidade.id">
                                            <option :value="cidade.id" x-text="cidade.nome" :selected="cidade.id == cidadeSelecionada"></option>
                                        </template>
                                    </select>
                                    <x-input-error :messages="$errors->get('cidade_id')" class="mt-2" />
                                </div>
                                <div class="md:col-span-1">
                                    <x-input-label for="local" value="Local Específico (Ponto de Encontro)" class="text-slate-700 font-bold" />
                                    <x-text-input id="local" name="local" type="text" class="mt-1 block w-full border-slate-300 focus:border-teal-500 focus:ring-teal-500 rounded-lg" :value="old('local')" placeholder="Ex: Parque de Exposições" required />
                                    <x-input-error :messages="$errors->get('local')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SEÇÃO 4: MÍDIA E DESCRIÇÃO (MODERNIZADO COM UPLOAD OTIMIZADO) --}}
                    <div class="bg-white overflow-hidden shadow-lg sm:rounded-2xl border border-slate-100">
                         <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                                <i class="fa-solid fa-photo-film"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Apresentação</h3>
                                <p class="text-xs text-slate-500">Imagens e descrição detalhada do evento.</p>
                            </div>
                        </div>
                        <div class="p-8 space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                
                                {{-- Banner Upload --}}
                                <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:bg-slate-50 transition-colors group relative">
                                    <x-input-label for="banner" value="Banner Principal (1920x1080px)" class="text-slate-700 font-bold mb-2" />
                                    <div class="mt-2 flex justify-center">
                                        <i class="fa-solid fa-cloud-arrow-up text-4xl text-slate-300 group-hover:text-purple-500 transition-colors mb-3"></i>
                                    </div>
                                    <input id="banner" name="banner" type="file" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 transition-all cursor-pointer"/>
                                    <p class="text-xs text-slate-400 mt-2">Imagens grandes serão otimizadas automaticamente.</p>
                                    <x-input-error :messages="$errors->get('banner')" class="mt-2" />
                                </div>

                                {{-- Thumbnail Upload --}}
                                <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:bg-slate-50 transition-colors group relative">
                                    <x-input-label for="thumbnail" value="Miniatura / Card (400x250px)" class="text-slate-700 font-bold mb-2" />
                                    <div class="mt-2 flex justify-center">
                                        <i class="fa-regular fa-image text-4xl text-slate-300 group-hover:text-purple-500 transition-colors mb-3"></i>
                                    </div>
                                    <input id="thumbnail" name="thumbnail" type="file" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 transition-all cursor-pointer"/>
                                    <p class="text-xs text-slate-400 mt-2">Imagens grandes serão otimizadas automaticamente.</p>
                                    <x-input-error :messages="$errors->get('thumbnail')" class="mt-2" />
                                </div>
                            </div>

                            {{-- EDITOR DE TEXTO TINYMCE --}}
                             <div>
                                <x-input-label for="descricao_completa" value="Descrição Completa do Evento" class="text-slate-700 font-bold mb-2" />
                                <div class="shadow-sm rounded-lg overflow-hidden border border-slate-300">
                                    <textarea id="descricao_completa" name="descricao_completa">{{ old('descricao_completa') }}</textarea>
                                </div>
                                <p class="text-xs text-slate-500 mt-2"><i class="fa-regular fa-lightbulb mr-1"></i> Use emojis, listas e formatação para tornar seu evento atrativo.</p>
                                <x-input-error :messages="$errors->get('descricao_completa')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Botões de Ação --}}
                <div class="mt-8 flex justify-end gap-4 pb-12">
                    <a href="{{ route('organizador.dashboard', ['org_id' => $organizacao->id]) }}" class="inline-flex items-center px-6 py-3 bg-white border border-slate-300 rounded-xl font-bold text-slate-700 uppercase tracking-widest shadow-sm hover:bg-slate-50 hover:text-slate-900 transition ease-in-out duration-150">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center px-8 py-3 bg-orange-600 border border-transparent rounded-xl font-bold text-white uppercase tracking-widest shadow-lg shadow-orange-500/30 hover:bg-orange-500 hover:-translate-y-0.5 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-all duration-200">
                        <i class="fa-solid fa-check mr-2"></i> Criar Evento
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    {{-- Script de Redimensionamento de Imagem no Cliente --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Função genérica para comprimir imagem
            const handleImageUpload = (inputElement, maxWidth, quality) => {
                if (!inputElement.files || !inputElement.files[0]) return;

                const file = inputElement.files[0];
                
                // Verifica se é imagem
                if (!file.type.match(/image.*/)) return;

                const reader = new FileReader();
                reader.readAsDataURL(file);
                
                reader.onload = (event) => {
                    const img = new Image();
                    img.src = event.target.result;
                    
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        let width = img.width;
                        let height = img.height;

                        // Redimensiona mantendo proporção se for maior que o máximo
                        if (width > maxWidth) {
                            height *= maxWidth / width;
                            width = maxWidth;
                        }

                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        // Converte para Blob (JPEG com qualidade definida) e substitui no input
                        canvas.toBlob((blob) => {
                            const newFile = new File([blob], file.name, {
                                type: 'image/jpeg',
                                lastModified: Date.now()
                            });

                            // Truque para atualizar o input file com o novo arquivo menor
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(newFile);
                            inputElement.files = dataTransfer.files;
                            
                            // Feedback visual opcional (console ou UI)
                            console.log(`Imagem otimizada: de ${(file.size/1024).toFixed(2)}KB para ${(newFile.size/1024).toFixed(2)}KB`);
                            
                        }, 'image/jpeg', quality);
                    }
                }
            };

            const bannerInput = document.getElementById('banner');
            if(bannerInput) {
                bannerInput.addEventListener('change', function() {
                    // Banner: Max 1920px, Qualidade 80%
                    handleImageUpload(this, 1920, 0.8);
                });
            }

            const thumbInput = document.getElementById('thumbnail');
            if(thumbInput) {
                thumbInput.addEventListener('change', function() {
                    // Thumbnail: Max 800px, Qualidade 80%
                    handleImageUpload(this, 800, 0.8);
                });
            }
        });
    </script>
    @endpush

</x-app-layout>