<div x-data="{
    showCouponModal: false,
    editingCoupon: {},
    openCouponModal(coupon) {
        // Clona o objeto para não alterar a lista visualmente antes de salvar
        this.editingCoupon = JSON.parse(JSON.stringify(coupon));

        // Ajuste 1: Garante que o checkbox funcione (converte 1/0 para true/false)
        this.editingCoupon.ativo = !!this.editingCoupon.ativo;

        // Ajuste 2: Formata a data para o input HTML (pega apenas YYYY-MM-DD)
        if (this.editingCoupon.data_validade) {
            this.editingCoupon.data_validade = this.editingCoupon.data_validade.substring(0, 10);
        }
        
        this.showCouponModal = true;
    }
}">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        
        {{-- Coluna da Lista --}}
        <div class="lg:col-span-3 bg-white p-6 rounded-lg shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <i class="fa-solid fa-ticket text-indigo-500 mr-2"></i> Cupons Cadastrados
            </h3>
            
            <div class="space-y-3">
                @forelse ($evento->cupons as $cupom)
                    <div class="border border-gray-200 rounded-lg p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center hover:bg-gray-50 transition">
                        <div class="mb-3 sm:mb-0">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-indigo-700 bg-indigo-50 px-2 py-1 rounded text-sm tracking-wide border border-indigo-100">
                                    {{ $cupom->codigo }}
                                </span>
                                @if(!$cupom->ativo)
                                    <span class="text-[10px] uppercase font-bold text-red-600 bg-red-100 px-1.5 py-0.5 rounded">Inativo</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-700 mt-1 font-medium">
                                {{ $cupom->tipo_desconto == 'fixo' ? 'R$ '.number_format($cupom->valor, 2, ',', '.') : number_format($cupom->valor, 0) . '%' }} de desconto
                            </p>
                            <p class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                                <span><i class="fa-solid fa-users mr-1"></i> {{ $cupom->usos }} / {{ $cupom->limite_uso ?? '∞' }}</span>
                                <span>|</span>
                                <span><i class="fa-regular fa-calendar mr-1"></i> {{ $cupom->data_validade ? \Carbon\Carbon::parse($cupom->data_validade)->format('d/m/Y') : 'Indeterminado' }}</span>
                            </p>
                        </div>
                        
                        <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
                            {{-- BOTÃO DE EDITAR --}}
                            <button @click.prevent="openCouponModal({{ Js::from($cupom) }})" 
                                    class="text-gray-500 hover:text-indigo-600 transition p-2 rounded-full hover:bg-indigo-50" title="Editar">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            {{-- FORMULÁRIO DE EXCLUSÃO --}}
                            <form action="{{ route('organizador.cupons.destroy', [$evento, $cupom]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este cupom?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-500 hover:text-red-600 transition p-2 rounded-full hover:bg-red-50" title="Excluir">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                        <p class="text-gray-500 text-sm">Nenhum cupom cadastrado.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Coluna do Formulário de Adição --}}
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-sm border border-gray-100 h-fit">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <i class="fa-solid fa-plus-circle text-orange-500 mr-2"></i> Novo Cupom
            </h3>
            
            <form action="{{ route('organizador.cupons.store', $evento) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="codigo" value="Código (Ex: PROMO10)" />
                    <x-text-input id="codigo" name="codigo" type="text" class="mt-1 block w-full uppercase font-mono" placeholder="DIGITE AQUI" required />
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="tipo_desconto" value="Tipo" />
                        <select name="tipo_desconto" id="tipo_desconto" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="percentual">Percentual (%)</option>
                            <option value="fixo">Valor Fixo (R$)</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="valor_cupom" value="Valor" />
                        <x-text-input id="valor_cupom" name="valor" type="number" step="0.01" class="mt-1 block w-full" placeholder="0,00" required />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="limite_uso" value="Limite de Usos" />
                        <x-text-input id="limite_uso" name="limite_uso" type="number" class="mt-1 block w-full" placeholder="∞"/>
                    </div>
                    <div>
                        <x-input-label for="data_validade" value="Validade" />
                        <x-text-input id="data_validade" name="data_validade" type="date" class="mt-1 block w-full" />
                    </div>
                </div>

                <div class="flex items-center pt-2">
                    <input id="ativo_cupom" name="ativo" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" checked>
                    <label for="ativo_cupom" class="ml-2 text-sm text-gray-700 font-medium">Cupom ativo imediatamente</label>
                </div>

                <div class="pt-2">
                    <x-primary-button class="w-full justify-center">Criar Cupom</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL DE EDIÇÃO DE CUPOM --}}
    <div x-show="showCouponModal" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 p-4 backdrop-blur-sm" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;"
         @keydown.escape.window="showCouponModal = false" x-cloak>
         
        <div @click.away="showCouponModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900">Editar Cupom</h3>
                <button @click="showCouponModal = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark fa-lg"></i></button>
            </div>
            
            <div class="p-6">
                {{-- A URL é gerada dinamicamente via JS substituindo o placeholder pelo ID --}}
                <form x-show="editingCoupon.id" 
                      :action="'{{ route('organizador.cupons.update', [$evento, 'ID_TEMP']) }}'.replace('ID_TEMP', editingCoupon.id)" 
                      method="POST" class="space-y-5">
                    @csrf
                    @method('PATCH')
                    
                    <div>
                        <x-input-label for="edit_codigo" value="Código do Cupom" />
                        <x-text-input id="edit_codigo" name="codigo" type="text" class="mt-1 block w-full uppercase font-mono bg-gray-50" required x-model="editingCoupon.codigo" />
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <x-input-label for="edit_tipo_desconto" value="Tipo" />
                            <select name="tipo_desconto" id="edit_tipo_desconto" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" x-model="editingCoupon.tipo_desconto">
                                <option value="percentual">Percentual (%)</option>
                                <option value="fixo">Valor Fixo (R$)</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="edit_valor_cupom" value="Valor" />
                            <x-text-input id="edit_valor_cupom" name="valor" type="number" step="0.01" class="mt-1 block w-full" required x-model="editingCoupon.valor"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <x-input-label for="edit_limite_uso" value="Limite de Usos" />
                            <x-text-input id="edit_limite_uso" name="limite_uso" type="number" class="mt-1 block w-full" placeholder="Ilimitado" x-model="editingCoupon.limite_uso"/>
                        </div>
                        <div>
                            <x-input-label for="edit_data_validade" value="Validade (Opcional)" />
                            <x-text-input id="edit_data_validade" name="data_validade" type="date" class="mt-1 block w-full" x-model="editingCoupon.data_validade"/>
                        </div>
                    </div>

                    <div class="flex items-center bg-gray-50 p-3 rounded-md border border-gray-100">
                        <input id="edit_ativo_cupom" name="ativo" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" x-model="editingCoupon.ativo">
                        <label for="edit_ativo_cupom" class="ml-2 text-sm text-gray-700 font-medium cursor-pointer">Cupom Ativo</label>
                    </div>

                    <div class="flex justify-end gap-x-3 pt-2">
                        <button @click.prevent="showCouponModal = false" type="button" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">Cancelar</button>
                        <x-primary-button>Salvar Alterações</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>