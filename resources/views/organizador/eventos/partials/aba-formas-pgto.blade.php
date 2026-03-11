<div class="space-y-6 animate-fade-in">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-brands fa-pix text-emerald-600"></i>
                Formas de Pagamento do Evento
            </h3>
            <p class="text-sm text-slate-500 mt-1">Configure como os atletas poderão pagar as inscrições neste evento (Mercado Pago ou PIX manual).</p>
        </div>

        <form method="POST" action="{{ route('organizador.eventos.formas-pagamento.update', $evento) }}" enctype="multipart/form-data" class="p-6 space-y-6"
              x-data="{
                  pagamentoManual: {{ json_encode((bool) ($evento->pagamento_manual ?? false)) }},
                  aceiteResponsabilidade: {{ json_encode((bool) old('aceite_responsabilidade', $evento->pagamento_manual ?? false)) }},
                  init() {
                      var self = this;
                      this.$watch('aceiteResponsabilidade', function(val) { if (!val) self.pagamentoManual = false; });
                  }
              }">
            @csrf
            @method('PATCH')

            {{-- Termo de responsabilidade (sempre visível quando vai ativar manual) --}}
            <div class="rounded-xl border-2 border-amber-200 bg-amber-50/80 p-5">
                <h4 class="text-sm font-bold text-amber-900 uppercase tracking-wide mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-scale-balanced"></i>
                    Responsabilidade pelo recebimento manual
                </h4>
                <p class="text-sm text-amber-900/90 leading-relaxed mb-4">
                    Ao optar por <strong>pagamento manual (PIX direto)</strong>, você, organizador do evento, assume <strong>total responsabilidade</strong> pelo controle de recebimento dos pagamentos. Cabe a você conferir os comprovantes, identificar as inscrições pagas e confirmar manualmente cada uma na plataforma. A ProTicket Sports não realiza cobrança nem confirmação automática nessa modalidade.
                </p>
                <div class="flex items-start gap-3">
                    <input type="hidden" name="aceite_responsabilidade" value="0">
                    <input id="aceite_responsabilidade" name="aceite_responsabilidade" type="checkbox" value="1"
                           class="mt-1 h-5 w-5 rounded border-amber-400 text-amber-600 focus:ring-amber-500"
                           x-model="aceiteResponsabilidade">
                    <label for="aceite_responsabilidade" class="text-sm font-bold text-amber-900 cursor-pointer">
                        Li e assumo integralmente a responsabilidade pelo controle de recebimento dos pagamentos quando o pagamento manual estiver ativo.
                    </label>
                </div>
                @error('aceite_responsabilidade')
                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- Opção: Receber manualmente (PIX direto) — só habilitada após aceitar o termo --}}
            <div class="flex items-start gap-3">
                <input type="hidden" name="pagamento_manual" value="0">
                <input id="pagamento_manual" name="pagamento_manual" type="checkbox" value="1"
                       class="mt-1 h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed"
                       x-model="pagamentoManual"
                       :disabled="!aceiteResponsabilidade"
                       @if(old('pagamento_manual', $evento->pagamento_manual ?? false)) checked @endif>
                <div>
                    <label for="pagamento_manual" class="font-bold text-slate-800"
                           :class="{ 'cursor-pointer': aceiteResponsabilidade, 'cursor-not-allowed text-slate-400': !aceiteResponsabilidade }">
                        Usar pagamento manual (PIX direto)
                    </label>
                    <p class="text-sm text-slate-500 mt-0.5" x-show="!aceiteResponsabilidade">
                        <span class="text-amber-600 font-medium">Aceite o termo de responsabilidade acima para habilitar esta opção.</span>
                    </p>
                    <p class="text-sm text-slate-500 mt-0.5" x-show="aceiteResponsabilidade" style="display: none;">
                        O atleta verá sua chave PIX e/ou QR Code para pagar. Você confirma o pagamento manualmente nas inscrições.
                    </p>
                </div>
            </div>

            {{-- Campos exibidos quando pagamento manual está ativo --}}
            <div x-show="pagamentoManual" x-cloak style="display: none;"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="space-y-6 pt-4 border-t border-slate-200">

                {{-- Tipo da chave PIX --}}
                <div>
                    <label for="chave_pix_tipo" class="block text-sm font-bold text-slate-700 mb-1">Tipo da chave PIX</label>
                    <select id="chave_pix_tipo" name="chave_pix_tipo" class="block w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                        <option value="">Selecione...</option>
                        <option value="cpf_cnpj" @selected(old('chave_pix_tipo', $evento->chave_pix_tipo ?? '') === 'cpf_cnpj')>CPF ou CNPJ</option>
                        <option value="email" @selected(old('chave_pix_tipo', $evento->chave_pix_tipo ?? '') === 'email')>E-mail</option>
                        <option value="telefone" @selected(old('chave_pix_tipo', $evento->chave_pix_tipo ?? '') === 'telefone')>Telefone</option>
                        <option value="aleatoria" @selected(old('chave_pix_tipo', $evento->chave_pix_tipo ?? '') === 'aleatoria')>Chave aleatória</option>
                    </select>
                    @error('chave_pix_tipo')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Chave PIX (valor) --}}
                <div>
                    <label for="chave_pix" class="block text-sm font-bold text-slate-700 mb-1">Chave PIX</label>
                    <input type="text" id="chave_pix" name="chave_pix" value="{{ old('chave_pix', $evento->chave_pix ?? '') }}"
                           class="block w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm"
                           placeholder="Ex: 12345678900, email@exemplo.com, +5541999999999 ou chave aleatória" maxlength="255">
                    <p class="text-xs text-slate-500 mt-1">Informe apenas o valor da chave (sem pontuação no CPF, se for o caso).</p>
                    @error('chave_pix')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Upload QR Code PIX --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">QR Code PIX (imagem)</label>
                    <p class="text-xs text-slate-500 mb-2">Faça upload de uma imagem do QR Code para pagamento. Ela será exibida ao atleta na tela de pagamento da inscrição.</p>
                    @if($evento->qrcode_pix_url ?? null)
                        <div class="mb-3 flex items-center gap-4">
                            <img src="{{ asset('storage/' . $evento->qrcode_pix_url) }}" alt="QR Code atual" class="h-24 w-24 object-contain border border-slate-200 rounded-lg bg-white">
                            <span class="text-sm text-green-600 font-medium"><i class="fa-solid fa-check mr-1"></i> QR Code atual cadastrado</span>
                        </div>
                    @endif
                    <input type="file" name="qrcode_pix" accept="image/png,image/jpeg,image/jpg,image/webp"
                           class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    @error('qrcode_pix')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="inline-flex items-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/30 transition-all">
                    <i class="fa-solid fa-save mr-2"></i> Salvar Formas de Pagamento
                </button>
            </div>
        </form>
    </div>
</div>
