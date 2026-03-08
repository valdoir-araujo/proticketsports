<div class="space-y-6">
    <div class="flex items-center gap-3 text-slate-700">
        <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 shrink-0">
            <i class="fa-solid fa-file-contract text-xl"></i>
        </div>
        <div>
            <h3 class="text-lg font-bold text-slate-800">Regulamento do Evento</h3>
            <p class="text-sm text-slate-500">Exiba o regulamento em PDF (upload para download) ou em texto direto na página do evento.</p>
        </div>
    </div>

    <form action="{{ route('organizador.eventos.regulamento.update', $evento) }}" method="POST" enctype="multipart/form-data" class="space-y-6"
        x-data="{
            tipo: '{{ old('regulamento_tipo', $evento->regulamento_tipo) ?? 'pdf' }}',
            editorInited: false,
            initEditor() {
                if (typeof tinymce === 'undefined' || this.editorInited) return;
                if (!document.getElementById('regulamento_texto')) return;
                tinymce.init({
                    selector: '#regulamento_texto',
                    height: 400,
                    menubar: false,
                    promotion: false,
                    branding: false,
                    plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table wordcount help',
                    toolbar: 'undo redo | formatselect | bold italic underline backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | removeformat | code | help',
                    content_style: 'body { font-family:Inter,sans-serif; font-size:14px }',
                    skin: 'oxide',
                    content_css: 'default',
                    block_formats: 'Parágrafo=p; Título 1=h1; Título 2=h2; Título 3=h3; Pré-formatado=pre'
                });
                this.editorInited = true;
            }
        }"
        x-init="
            const tryInit = () => { if (this.tipo === 'texto') this.$nextTick(() => this.initEditor()); };
            $watch('tipo', value => { if (value === 'texto') this.$nextTick(() => this.initEditor()); });
            window.addEventListener('regulamento-tab-visible', () => tryInit());
        "
        onsubmit="if (typeof tinymce !== 'undefined' && tinymce.get('regulamento_texto')) tinymce.triggerSave();">
        @csrf
        @method('PATCH')

        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-sm font-bold text-slate-700 mb-3">Como deseja exibir o regulamento?</p>
            <div class="flex flex-wrap gap-4">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="regulamento_tipo" value="pdf" x-model="tipo" class="rounded-full border-slate-300 text-amber-600 focus:ring-amber-500" {{ old('regulamento_tipo', $evento->regulamento_tipo) === 'pdf' ? 'checked' : '' }}>
                    <span class="ml-2 font-medium text-slate-700">Arquivo PDF (visitante baixa o arquivo)</span>
                </label>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="regulamento_tipo" value="texto" x-model="tipo" class="rounded-full border-slate-300 text-amber-600 focus:ring-amber-500" {{ old('regulamento_tipo', $evento->regulamento_tipo) === 'texto' ? 'checked' : '' }}>
                    <span class="ml-2 font-medium text-slate-700">Texto no site (exibido na página)</span>
                </label>
            </div>
        </div>

        {{-- Opção PDF --}}
        <div class="bg-slate-50 rounded-xl border border-slate-200 p-5">
            <div x-show="tipo === 'pdf'" x-cloak style="display: none;">
                <p class="text-sm font-bold text-slate-700 mb-3">Arquivo PDF</p>
                @if($evento->regulamento_arquivo)
                    <div class="flex flex-wrap items-center gap-4 mb-4 p-3 bg-white rounded-lg border border-slate-200">
                        <i class="fa-solid fa-file-pdf text-3xl text-red-500"></i>
                        <div>
                            <p class="font-medium text-slate-800">PDF atual disponível</p>
                            <a href="{{ asset('storage/' . $evento->regulamento_arquivo) }}" target="_blank" class="text-sm text-amber-600 hover:underline">Abrir / Baixar</a>
                        </div>
                        <label class="inline-flex items-center gap-2 px-4 py-2 bg-slate-200 hover:bg-slate-300 rounded-lg text-sm font-medium cursor-pointer">
                            <i class="fa-solid fa-upload"></i> Substituir PDF
                            <input type="file" name="regulamento_arquivo" accept=".pdf" class="hidden">
                        </label>
                        <label class="inline-flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-medium cursor-pointer">
                            <input type="checkbox" name="remover_pdf" value="1" class="rounded border-slate-300">
                            Remover PDF
                        </label>
                    </div>
                @else
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Enviar arquivo PDF (máx. 10 MB)</label>
                        <input type="file" name="regulamento_arquivo" accept=".pdf" class="block w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:font-semibold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200">
                        @error('regulamento_arquivo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                @endif
            </div>

            {{-- Opção Texto (editor rico: negrito, listas, títulos, links, etc.) --}}
            <div x-show="tipo === 'texto'" x-cloak style="display: none;">
                <p class="text-sm font-bold text-slate-700 mb-3">Texto do regulamento (exibido na página do evento com a mesma formatação)</p>
                <textarea id="regulamento_texto" name="regulamento_texto" rows="14" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm" placeholder="Use o editor para formatar títulos, listas, negrito, links...">{{ old('regulamento_texto', $evento->regulamento_texto) }}</textarea>
                <p class="mt-2 text-xs text-slate-500">Use a barra de ferramentas para formatar o texto. A exibição na página pública manterá a mesma formatação.</p>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow-md transition-all">
                <i class="fa-solid fa-save mr-2"></i> Salvar regulamento
            </button>
        </div>
    </form>
</div>
