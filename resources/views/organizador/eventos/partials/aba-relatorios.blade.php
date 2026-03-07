<div class="bg-white p-8 rounded-lg shadow-sm border border-gray-100" 
     x-data="{
         status: 'confirmada', // Status padrão: Confirmados (Apenas pagos)
         usar_data: false,
         data_inicio: '',
         data_fim: '',
         getUrl(baseUrl) {
             try {
                 let url = new URL(baseUrl);
                 // Adiciona o status selecionado à URL
                 if (this.status) url.searchParams.append('status', this.status);
                 
                 // Apenas adiciona as datas se a opção de filtrar estiver ativada
                 if (this.usar_data) {
                     if (this.data_inicio) url.searchParams.append('data_inicio', this.data_inicio);
                     if (this.data_fim) url.searchParams.append('data_fim', this.data_fim);
                 }
                 return url.toString();
             } catch (e) {
                 console.error('Erro ao gerar URL:', e);
                 return baseUrl;
             }
         }
     }">
    
    <div class="mb-8">
        <h3 class="text-2xl font-bold text-gray-900">Central de Relatórios</h3>
        <p class="text-gray-500 mt-1">Configure os filtros e baixe os relatórios desejados.</p>
    </div>

    {{-- Filtros Globais --}}
    <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 mb-8">
        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4">Configuração dos Filtros</h4>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            {{-- Coluna 1: Filtro de Status --}}
            <div class="md:col-span-1">
                <label class="block text-xs font-bold text-gray-500 mb-2">Status da Inscrição</label>
                <div class="flex flex-col gap-3">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="filtro_status" value="confirmada" x-model="status" class="text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                        <span class="ml-2 text-sm text-gray-700 font-medium">Confirmados <span class="text-xs text-gray-400">(Apenas pagos)</span></span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="filtro_status" value="todos" x-model="status" class="text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                        <span class="ml-2 text-sm text-gray-700 font-medium">Todos <span class="text-xs text-gray-400">(Inclui pendentes)</span></span>
                    </label>
                </div>
            </div>

            {{-- Coluna 2 e 3: Filtro de Datas (Opcional) --}}
            <div class="md:col-span-2 border-l border-gray-200 pl-6">
                <div class="flex items-center mb-3">
                    <input type="checkbox" id="usar_data" x-model="usar_data" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 h-4 w-4">
                    <label for="usar_data" class="ml-2 text-sm font-bold text-gray-700 cursor-pointer select-none">
                        Filtrar por Período Específico
                    </label>
                </div>

                <div x-show="usar_data" x-transition.opacity class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-gray-400 mb-1">Data Inicial</label>
                        <input type="date" x-model="data_inicio" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-gray-400 mb-1">Data Final</label>
                        <input type="date" x-model="data_fim" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                </div>

                <div x-show="!usar_data" class="text-sm text-gray-500 italic bg-white p-3 rounded border border-dashed border-gray-300">
                    <i class="fa-regular fa-calendar-check mr-1"></i> O relatório incluirá <strong>todo o período</strong> de inscrições.
                </div>
            </div>
        </div>
    </div>

    {{-- Grid de Cartões --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        {{-- CARD 1: LISTA DE INSCRITOS (PDF) --}}
        <div class="border rounded-xl p-6 hover:shadow-md transition bg-white group relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-red-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <div class="w-12 h-12 rounded-lg bg-red-100 text-red-600 flex items-center justify-center mb-4">
                    <i class="fa-solid fa-file-pdf text-2xl"></i>
                </div>
                <h4 class="font-bold text-gray-800 text-lg">Lista de Inscritos</h4>
                <p class="text-sm text-gray-500 mt-2 mb-6">Relatório visual em PDF, organizado por categoria, ideal para impressão e check-in manual.</p>
                
                <a :href="getUrl('{{ route('organizador.eventos.relatorio-inscritos.pdf', $evento) }}')" target="_blank" 
                   class="block w-full py-2.5 text-center rounded-lg border border-red-200 text-red-700 font-bold hover:bg-red-600 hover:text-white transition-colors">
                    Baixar PDF
                </a>
            </div>
        </div>

        {{-- CARD 2: CRONOMETRAGEM (CSV) --}}
        <div class="border rounded-xl p-6 hover:shadow-md transition bg-white group relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-green-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <div class="w-12 h-12 rounded-lg bg-green-100 text-green-600 flex items-center justify-center mb-4">
                    <i class="fa-solid fa-file-csv text-2xl"></i>
                </div>
                <h4 class="font-bold text-gray-800 text-lg">Cronometragem</h4>
                <p class="text-sm text-gray-500 mt-2 mb-6">Arquivo CSV compatível com sistemas de chip. Inclui Numeral, Categoria e Dados do Atleta.</p>
                
                <a :href="getUrl('{{ route('organizador.eventos.exportarInscritos', $evento) }}')" 
                   class="block w-full py-2.5 text-center rounded-lg border border-green-200 text-green-700 font-bold hover:bg-green-600 hover:text-white transition-colors">
                    Baixar CSV
                </a>
            </div>
        </div>

        {{-- CARD 3: FINANCEIRO (PDF) --}}
        <div class="border rounded-xl p-6 hover:shadow-md transition bg-white group relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-teal-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <div class="w-12 h-12 rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center mb-4">
                    <i class="fa-solid fa-money-bill-trend-up text-2xl"></i>
                </div>
                <h4 class="font-bold text-gray-800 text-lg">Relatório Financeiro</h4>
                <p class="text-sm text-gray-500 mt-2 mb-6">Balanço completo de receitas (inscrições + manuais) e despesas no período selecionado.</p>
                
                <a :href="getUrl('{{ route('organizador.eventos.relatorio-financeiro.pdf', $evento) }}')" target="_blank" 
                   class="block w-full py-2.5 text-center rounded-lg border border-teal-200 text-teal-700 font-bold hover:bg-teal-600 hover:text-white transition-colors">
                    Baixar Relatório
                </a>
            </div>
        </div>

    </div>
</div>