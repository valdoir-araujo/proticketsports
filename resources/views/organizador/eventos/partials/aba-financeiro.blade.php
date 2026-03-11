@php
    $categoriasReceita = config('financeiro.categorias_receita', ['Patrocínio', 'Inscrições (manual)', 'Merchandising', 'Alimentação / Buffet', 'Outro']);
    $categoriasDespesa = config('financeiro.categorias_despesa', ['Almoço / Refeição', 'Premiação', 'Material de consumo', 'Locação', 'Marketing / Divulgação', 'Transporte', 'Outro']);
@endphp
{{-- Conteúdo da Aba "Financeiro" --}}
<div x-show="tab === 'financeiro'" style="display: none;" class="space-y-6" x-data="{ tipo: 'receita', categoriaSelect: '', categoriaOutra: '' }">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
       <div class="bg-green-100 p-6 rounded-lg shadow-sm">
           <p class="text-sm text-green-800">Total de Receitas</p>
           <p class="text-2xl font-bold text-green-900">R$ {{ number_format($totalReceitas ?? 0, 2, ',', '.') }}</p>
       </div>
       <div class="bg-red-100 p-6 rounded-lg shadow-sm">
           <p class="text-sm text-red-800">Total de Despesas</p>
           <p class="text-2xl font-bold text-red-900">R$ {{ number_format($totalDespesas ?? 0, 2, ',', '.') }}</p>
       </div>
       <div class="bg-blue-100 p-6 rounded-lg shadow-sm">
           <p class="text-sm text-blue-800">Saldo Final</p>
           <p class="text-2xl font-bold text-blue-900">R$ {{ number_format($saldoFinal ?? 0, 2, ',', '.') }}</p>
       </div>
   </div>
   <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
       <div class="lg:col-span-3 bg-white p-6 rounded-lg shadow-sm">
           {{-- BOTÃO DE GERAR PDF ADICIONADO AQUI --}}
           <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Histórico de Lançamentos</h3>
                <a href="{{ route('organizador.eventos.relatorio-financeiro.pdf', $evento) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-xs font-semibold rounded-md hover:bg-gray-700">
                    <i class="fa-solid fa-file-pdf mr-2"></i>
                    Gerar PDF
                </a>
           </div>
           <div class="overflow-x-auto">
               <table class="min-w-full divide-y divide-gray-200">
                   <thead class="bg-gray-50">
                       <tr>
                           <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                           <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
                           <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                           <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                       </tr>
                   </thead>
                   <tbody class="bg-white divide-y divide-gray-200">
                       @forelse ($lancamentosFinanceiros as $lancamento)
                           <tr>
                               <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $lancamento->data->format('d/m/Y') }}</td>
                               <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $lancamento->categoria ?? '—' }}</td>
                               <td class="px-4 py-3 text-sm text-gray-800">{{ $lancamento->descricao }}</td>
                               <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold {{ $lancamento->tipo == 'receita' ? 'text-green-600' : 'text-red-600' }}">
                                   {{ $lancamento->tipo == 'receita' ? '+' : '-' }} R$ {{ number_format($lancamento->valor, 2, ',', '.') }}
                               </td>
                           </tr>
                       @empty
                           <tr>
                               <td colspan="4" class="px-4 py-4 text-center text-gray-500">Nenhum lançamento financeiro registado.</td>
                           </tr>
                       @endforelse
                   </tbody>
               </table>
           </div>
           <div class="mt-4">
               {{ $lancamentosFinanceiros->withQueryString()->links('pagination::tailwind', ['pageName' => 'lancamentosPage']) }}
           </div>
       </div>
       <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-sm">
           <h3 class="text-lg font-medium text-gray-900 mb-4">Adicionar Lançamento</h3>
           <form method="POST" action="{{ route('organizador.lancamentos.store', $evento) }}" enctype="multipart/form-data" class="space-y-4">
               @csrf
               <div>
                   <x-input-label for="tipo" value="Tipo de Lançamento" />
                   <select name="tipo" id="tipo" x-model="tipo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                       <option value="receita">Receita</option>
                       <option value="despesa">Despesa</option>
                   </select>
               </div>
               <div>
                   <x-input-label for="descricao_financeiro" value="Descrição" />
                   <x-text-input id="descricao_financeiro" name="descricao" type="text" class="mt-1 block w-full" required />
               </div>
               <div>
                   <x-input-label for="lancamento_valor" value="Valor (R$)" />
                   <x-text-input id="lancamento_valor" name="valor" type="number" step="0.01" class="mt-1 block w-full" required />
               </div>
               <div>
                   <x-input-label for="data" value="Data do Lançamento" />
                   <x-text-input id="data" name="data" type="date" class="mt-1 block w-full" required value="{{ now()->format('Y-m-d') }}" />
               </div>
               <div>
                   <x-input-label for="categoria_financeiro" value="Categoria (analítica)" />
                   <select id="categoria_receita" name="categoria" x-show="tipo === 'receita'" :disabled="tipo !== 'receita'" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                       @foreach($categoriasReceita as $cat)
                           <option value="{{ $cat }}">{{ $cat }}</option>
                       @endforeach
                   </select>
                   <select id="categoria_despesa" name="categoria" x-show="tipo === 'despesa'" :disabled="tipo !== 'despesa'" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" style="display: none;" required>
                       @foreach($categoriasDespesa as $cat)
                           <option value="{{ $cat }}">{{ $cat }}</option>
                       @endforeach
                   </select>
                   <p class="mt-1 text-xs text-gray-500">Ex.: Patrocínio, Almoço, Premiação — facilita relatórios por tipo.</p>
               </div>
               <div>
                   <x-input-label for="comprovante" value="Comprovativo (Opcional)" />
                   <input id="comprovante" name="comprovante" type="file" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100"/>
               </div>
               <div>
                   <x-input-label for="observacoes" value="Observações (Opcional)" />
                   <textarea id="observacoes" name="observacoes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
               </div>
               <div class="flex items-center">
                   <x-primary-button>Salvar Lançamento</x-primary-button>
               </div>
           </form>
       </div>
   </div>
</div>

