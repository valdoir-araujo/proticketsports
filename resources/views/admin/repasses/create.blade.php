<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Criar Novo Lote de Repasse
            </h2>
            <a href="{{ route('admin.relatorios.financeiros.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                &larr; Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ 
        inscricoes: {{ Js::from($inscricoes) }},
        selectedInscricoes: [],
        selectedOrgId: '',
        selectedEventoId: '',
        
        get organizacoes() {
            const map = new Map();
            this.inscricoes.forEach(i => {
                const o = i.evento?.organizacao;
                if (o && !map.has(o.id)) map.set(o.id, { id: o.id, nome_fantasia: o.nome_fantasia });
            });
            return Array.from(map.values()).sort((a,b) => (a.nome_fantasia || '').localeCompare(b.nome_fantasia || ''));
        },
        get eventosFiltrados() {
            const map = new Map();
            this.inscricoes.forEach(i => {
                const e = i.evento;
                if (!e) return;
                if (this.selectedOrgId && e.organizacao_id != this.selectedOrgId) return;
                if (!map.has(e.id)) map.set(e.id, { id: e.id, nome: e.nome, organizacao_id: e.organizacao_id });
            });
            return Array.from(map.values()).sort((a,b) => (a.nome || '').localeCompare(b.nome || ''));
        },
        get inscricoesFiltradas() {
            return this.inscricoes.filter(i => {
                if (this.selectedEventoId && i.evento?.id != this.selectedEventoId) return false;
                if (this.selectedOrgId && i.evento?.organizacao_id != this.selectedOrgId) return false;
                return true;
            });
        },
        get totalAPagar() {
            return this.inscricoes
                .filter(inscricao => this.selectedInscricoes.includes(inscricao.id))
                .reduce((total, inscricao) => total + (parseFloat(inscricao.valor_pago) - parseFloat(inscricao.taxa_plataforma)), 0);
        },
        toggleAll(event) {
            if (event.target.checked) {
                this.selectedInscricoes = this.inscricoesFiltradas.map(i => i.id);
            } else {
                this.selectedInscricoes = [];
            }
        },
        toggleAllFiltered() {
            const filteredIds = this.inscricoesFiltradas.map(i => i.id);
            const allSelected = filteredIds.length && filteredIds.every(id => this.selectedInscricoes.includes(id));
            if (allSelected) {
                this.selectedInscricoes = this.selectedInscricoes.filter(id => !filteredIds.includes(id));
            } else {
                const toAdd = filteredIds.filter(id => !this.selectedInscricoes.includes(id));
                this.selectedInscricoes = [...this.selectedInscricoes, ...toAdd];
            }
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="mb-4 p-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            <form action="{{ route('admin.repasses.store') }}" method="POST">
                @csrf
                {{-- Envio dos IDs selecionados (todos, mesmo fora do filtro) --}}
                <div class="hidden">
                    <template x-for="id in selectedInscricoes" :key="id">
                        <input type="hidden" name="inscricao_ids[]" :value="id">
                    </template>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg">
                    {{-- Filtros: Organizador e Evento --}}
                    <div class="p-4 border-b bg-white flex flex-wrap items-end gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Organizador</label>
                            <select x-model="selectedOrgId" @change="selectedEventoId = ''" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Todos</option>
                                <template x-for="org in organizacoes" :key="org.id">
                                    <option :value="org.id" x-text="org.nome_fantasia"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Evento</label>
                            <select x-model="selectedEventoId" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Todos</option>
                                <template x-for="ev in eventosFiltrados" :key="ev.id">
                                    <option :value="ev.id" x-text="ev.nome"></option>
                                </template>
                            </select>
                        </div>
                        <p class="text-sm text-gray-500" x-show="inscricoesFiltradas.length < inscricoes.length" x-transition>
                            <span x-text="inscricoesFiltradas.length"></span> de <span x-text="inscricoes.length"></span> inscrições
                        </p>
                    </div>

                    {{-- Cabeçalho com Ações e Totais --}}
                    <div class="p-6 border-b flex flex-col md:flex-row justify-between items-center bg-gray-50">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Inscrições Pendentes de Repasse</h3>
                            <p class="text-sm text-gray-600">Selecione as inscrições que deseja incluir neste lote de pagamento.</p>
                        </div>
                        <div class="mt-4 md:mt-0 text-right">
                            <p class="text-sm text-gray-500">Valor a Repassar</p>
                            <p class="text-3xl font-bold text-blue-700" x-text="`R$ ${totalAPagar.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`"></p>
                            <button type="submit" :disabled="selectedInscricoes.length === 0" class="mt-2 w-full md:w-auto inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white font-semibold text-sm rounded-md hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-check mr-2"></i>
                                Criar Lote de Repasse (<span x-text="selectedInscricoes.length"></span>)
                            </button>
                        </div>
                    </div>

                    {{-- Tabela de Inscrições (filtrada por organizador/evento) --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="p-4 text-center">
                                        <input type="checkbox" @click="toggleAllFiltered" class="rounded" :checked="inscricoesFiltradas.length > 0 && inscricoesFiltradas.every(i => selectedInscricoes.includes(i.id))">
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Evento</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Organizador</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Atleta</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Valor Bruto</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Taxa</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Valor a Repassar</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="inscricao in inscricoesFiltradas" :key="inscricao.id">
                                    <tr>
                                        <td class="p-4 text-center">
                                            <input type="checkbox" :value="inscricao.id" x-model="selectedInscricoes" class="rounded">
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900" x-text="inscricao.evento?.nome"></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500" x-text="inscricao.evento?.organizacao?.nome_fantasia"></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700" x-text="inscricao.atleta?.user?.name"></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500" x-text="'R$ ' + (parseFloat(inscricao.valor_pago || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}))"></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-red-600" x-text="'- R$ ' + (parseFloat(inscricao.taxa_plataforma || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}))"></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-800" x-text="'R$ ' + ((parseFloat(inscricao.valor_pago || 0) - parseFloat(inscricao.taxa_plataforma || 0)).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}))"></td>
                                    </tr>
                                </template>
                                <tr x-show="inscricoesFiltradas.length === 0">
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">Nenhuma inscrição pendente de repasse para o filtro selecionado.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
