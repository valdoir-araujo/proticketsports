<x-app-layout>
    {{-- CABEÇALHO HERO --}}
    <div class="relative bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 pt-10 pb-32 overflow-hidden shadow-xl">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#fb923c 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-orange-600/20 blur-3xl pointer-events-none mix-blend-screen animate-pulse-slow"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-white z-10">
                    <div class="inline-flex items-center gap-2 mb-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/10 text-xs font-bold text-orange-200">
                        <i class="fa-solid fa-camera"></i> Card Social
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md">
                        Crie seu Avatar
                    </h1>
                    <p class="text-blue-100 mt-2 text-lg font-light opacity-90">
                        Arraste os textos e a foto para personalizar seu card.
                    </p>
                </div>
                
                <div class="z-10">
                    <a href="{{ route('atleta.inscricoes') }}" class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-white text-sm font-bold transition-all">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="relative z-20 -mt-20 pb-12" x-data="avatarGenerator()">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden flex flex-col md:flex-row">
                
                {{-- ÁREA DE EDIÇÃO (Esquerda) --}}
                <div class="p-8 md:w-2/3 border-r border-slate-100 bg-slate-50/50">
                    
                    {{-- Canvas Container --}}
                    <div class="relative w-full aspect-square bg-slate-200/50 rounded-xl overflow-hidden border-2 border-dashed border-slate-300 flex items-center justify-center mb-6 shadow-inner group cursor-move">
                        
                        {{-- Loading Indicator --}}
                        <div x-show="loadingFrame" class="absolute inset-0 flex flex-col items-center justify-center bg-white/80 z-20" style="display: none;">
                            <i class="fa-solid fa-circle-notch fa-spin text-4xl text-orange-500"></i>
                            <span class="text-xs font-bold text-slate-500 mt-2 uppercase">Carregando...</span>
                        </div>

                        {{-- Canvas Real --}}
                        <canvas x-ref="canvas" class="max-w-full max-h-full shadow-2xl z-10 bg-white"></canvas>
                        
                        {{-- Placeholder se não tiver foto --}}
                        <div x-show="!userImage" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 pointer-events-none">
                            <i class="fa-solid fa-cloud-arrow-up text-5xl mb-3"></i>
                            <span class="text-sm font-bold uppercase tracking-wider">Carregue sua foto</span>
                        </div>
                    </div>

                    {{-- Controles da Foto --}}
                    <div class="space-y-4">
                        <div class="flex gap-2">
                            <input type="file" x-ref="fileInput" @change="handleFileUpload" accept="image/*" class="hidden">
                            <button @click="$refs.fileInput.click()" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-upload"></i> Trocar Foto
                            </button>
                            <button @click="resetPositions()" class="px-4 py-3 bg-white border border-slate-300 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-all" title="Resetar Posições">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                        </div>

                        {{-- Ajustes da Foto (Só aparece se tiver foto e NENHUM texto selecionado) --}}
                        <div x-show="userImage && selectedElementIndex === null" class="grid grid-cols-2 gap-4 p-4 bg-white rounded-xl border border-slate-200 shadow-sm transition-all" x-transition>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Zoom Foto</label>
                                <input type="range" x-model="scale" @input="draw()" min="0.1" max="3" step="0.1" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Girar Foto</label>
                                <input type="range" x-model="rotation" @input="draw()" min="-180" max="180" step="1" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ÁREA DE INSTRUÇÕES E EDIÇÃO DE TEXTO (Direita) --}}
                <div class="p-8 md:w-1/3 bg-white flex flex-col justify-between">
                    
                    {{-- Painel de Edição de Texto (Aparece quando clica num texto) --}}
                    <div x-show="selectedElementIndex !== null" x-transition class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                                <i class="fa-solid fa-font text-blue-500"></i> Editar Texto
                            </h3>
                            <button @click="selectedElementIndex = null; draw()" class="text-xs text-slate-400 hover:text-slate-600 font-bold underline">Fechar</button>
                        </div>
                        
                        <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 space-y-4">
                            <div>
                                <label class="text-[10px] font-bold text-blue-400 uppercase tracking-wider mb-1 block">Tamanho da Fonte</label>
                                <input type="range" 
                                       x-model="elements[selectedElementIndex].fontSize" 
                                       @input="draw()" 
                                       min="20" max="150" step="1" 
                                       class="w-full h-2 bg-blue-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                <div class="text-right text-xs font-mono text-blue-600 mt-1" x-text="elements[selectedElementIndex].fontSize + 'px'"></div>
                            </div>
                            
                            <p class="text-xs text-blue-600/70 italic">
                                <i class="fa-solid fa-arrows-up-down-left-right mr-1"></i> Arraste o texto na imagem para mover.
                            </p>
                        </div>
                    </div>

                    {{-- Instruções (Só aparece se NADA estiver selecionado) --}}
                    <div x-show="selectedElementIndex === null">
                        <h3 class="font-bold text-slate-800 text-lg mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-hand-pointer text-orange-500"></i> Edição Livre
                        </h3>
                        <ol class="space-y-4 text-sm text-slate-600">
                            <li class="flex gap-3">
                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center font-bold text-xs text-slate-600">1</span>
                                <span><strong>Clique nos textos</strong> para selecioná-los e alterar o tamanho.</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center font-bold text-xs text-slate-600">2</span>
                                <span><strong>Arraste</strong> qualquer elemento para posicionar onde preferir.</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center font-bold text-xs text-slate-600">3</span>
                                <span>Clique no <strong>Fundo</strong> para mover ou dar zoom na sua foto.</span>
                            </li>
                        </ol>
                    </div>

                    <div class="mt-auto pt-6 border-t border-slate-100">
                        <button @click="downloadImage()" :disabled="!userImage || loadingFrame" class="w-full py-4 bg-green-600 hover:bg-green-500 text-white font-black uppercase tracking-wide rounded-xl shadow-lg shadow-green-500/30 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transform active:scale-95">
                            <i class="fa-solid fa-download"></i> Baixar Imagem
                        </button>
                        <p class="text-[10px] text-center text-slate-400 mt-2">Compartilhe no Instagram e marque o evento!</p>
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- Lógica para formatar o nome (Primeiro + Último) --}}
    @php
        $partesNome = explode(' ', $inscricao->atleta->user->name);
        $primeiroNome = $partesNome[0];
        $ultimoNome = count($partesNome) > 1 ? end($partesNome) : '';
        $nomeFormatado = trim($primeiroNome . ' ' . $ultimoNome);
    @endphp

    {{-- SCRIPT DE MANIPULAÇÃO DO CANVAS --}}
    <script>
        function avatarGenerator() {
            return {
                canvas: null,
                ctx: null,
                // Dados vindos do Blade
                frameSrc: '{{ !empty($inscricao->evento->avatar_template_url) ? asset("storage/" . $inscricao->evento->avatar_template_url) : "" }}',
                
                // Dados para os elementos de texto
                data: {
                    eventName: '{{ $inscricao->evento->nome }}',
                    categoryName: '{{ $inscricao->categoria->nome }}',
                    eventDate: '{{ $inscricao->evento->data_evento->format("d/m/Y") }}',
                    athleteName: '{{ $nomeFormatado }}',
                    teamName: '{{ $inscricao->equipe->nome ?? "" }}',
                    location: '{{ $inscricao->atleta->cidade?->nome ?? "Exterior" }}{{ $inscricao->atleta->cidade?->estado ? "/" . $inscricao->atleta->cidade->estado->sigla : "" }}',
                },

                frameImage: null,
                userImage: null,
                
                // Elementos Interativos
                elements: [],
                selectedElementIndex: null, // Índice do elemento selecionado
                draggingElementIndex: null, // Índice do elemento sendo arrastado ou 'background'

                // Variáveis de Estado
                loadingFrame: false,
                frameError: false,
                scale: 1,
                rotation: 0,
                
                // Controle de arraste
                startX: 0,
                startY: 0,
                
                // Posição da Foto de Fundo
                imgX: 0,
                imgY: 0,
                
                init() {
                    this.canvas = this.$refs.canvas;
                    this.ctx = this.canvas.getContext('2d');
                    this.canvas.width = 1080;
                    this.canvas.height = 1080;

                    this.initElements(); 

                    if (this.frameSrc) {
                        this.loadingFrame = true;
                        const img = new Image();
                        img.crossOrigin = "anonymous";
                        img.src = this.frameSrc;
                        img.onload = () => {
                            this.frameImage = img;
                            this.loadingFrame = false;
                            this.draw();
                        };
                        img.onerror = () => { this.loadingFrame = false; this.draw(); };
                    } else {
                        this.draw();
                    }

                    this.setupDragEvents();
                },

                initElements() {
                    const cx = 1080 / 2;
                    const h = 1080;
                    
                    this.elements = [
                        // Nome do Atleta
                        { type: 'text', text: this.data.athleteName.toUpperCase(), x: cx, y: h - 260, fontSize: 90, font: '900', color: '#FFFFFF', align: 'center', shadow: true },
                        // Nome do Evento
                        { type: 'text', text: this.data.eventName, x: cx, y: h - 190, fontSize: 55, font: 'bold', color: '#FEF3C7', align: 'center', shadow: true },
                        // Categoria
                        { type: 'text', text: `${this.data.categoryName} • ${this.data.eventDate}`, x: cx, y: h - 130, fontSize: 35, font: 'bold', color: '#FFFFFF', align: 'center', shadow: true },
                        // Hashtags
                        { type: 'text', text: '#EuVou #ProTicketSports', x: cx, y: h - 60, fontSize: 40, font: 'italic 800', color: '#FFFFFF', align: 'center', shadow: false }
                    ];

                    if (this.data.teamName) {
                        this.elements.push({ type: 'badge', text: this.data.teamName.toUpperCase(), x: 40, y: 50, color: '#EA580C', align: 'left', fontSize: 30 });
                    }
                    if (this.data.location && this.data.location.trim() !== "/") {
                        this.elements.push({ type: 'badge', text: this.data.location.toUpperCase(), x: 1040, y: 50, color: '#1E293B', align: 'right', fontSize: 30 });
                    }
                },

                resetPositions() {
                    this.imgX = this.canvas.width / 2;
                    this.imgY = this.canvas.height / 2;
                    this.scale = 1;
                    this.rotation = 0;
                    this.selectedElementIndex = null;
                    this.initElements();
                    this.draw();
                },

                handleFileUpload(e) {
                    const file = e.target.files[0];
                    if(!file) return;
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const img = new Image();
                        img.onload = () => {
                            this.userImage = img;
                            this.resetPositions();
                        }
                        img.src = event.target.result;
                    }
                    reader.readAsDataURL(file);
                },

                draw() {
                    const w = this.canvas.width;
                    const h = this.canvas.height;
                    
                    this.ctx.clearRect(0, 0, w, h);
                    this.ctx.fillStyle = "#ffffff";
                    this.ctx.fillRect(0, 0, w, h);

                    // 1. Foto do Usuário
                    if (this.userImage) {
                        this.ctx.save();
                        this.ctx.translate(this.imgX, this.imgY);
                        this.ctx.rotate(this.rotation * Math.PI / 180);
                        this.ctx.scale(this.scale, this.scale);
                        this.ctx.drawImage(this.userImage, -this.userImage.width / 2, -this.userImage.height / 2);
                        this.ctx.restore();
                    }

                    // 2. Fundo Degradê (se não houver moldura)
                    if (!this.frameImage) {
                         const gradient = this.ctx.createLinearGradient(0, h - 350, 0, h);
                         gradient.addColorStop(0, "rgba(234, 88, 12, 0)"); 
                         gradient.addColorStop(0.5, "rgba(234, 88, 12, 0.8)"); 
                         gradient.addColorStop(1, "rgba(154, 52, 18, 1)"); 
                         this.ctx.fillStyle = gradient;
                         this.ctx.fillRect(0, h - 350, w, 350);
                    }

                    // 3. Moldura
                    if (this.frameImage) {
                        this.ctx.drawImage(this.frameImage, 0, 0, w, h);
                    }

                    // 4. Elementos
                    this.elements.forEach((el, index) => {
                        this.ctx.save();
                        
                        // Configura Fonte com Tamanho Dinâmico
                        const fontSize = parseInt(el.fontSize) || 40; // Garante que é número
                        const fontStyle = el.font || 'bold';
                        this.ctx.font = `${fontStyle} ${fontSize}px sans-serif`;
                        
                        // Define cor e alinhamento
                        this.ctx.fillStyle = el.color;
                        this.ctx.textAlign = el.align;
                        this.ctx.textBaseline = 'middle';
                        
                        // Sombra (exceto se desativada ou badge)
                        if (el.shadow && el.type === 'text') {
                            this.ctx.shadowColor = "rgba(0,0,0,0.8)";
                            this.ctx.shadowBlur = 10;
                            this.ctx.shadowOffsetY = 3;
                        }
                        
                        // Calcula dimensões para Hit Test
                        let textMetrics = this.ctx.measureText(el.text);
                        let textWidth = textMetrics.width;
                        let textHeight = fontSize * 1.2; // Altura aproximada
                        
                        // DESENHO
                        if (el.type === 'text') {
                            this.ctx.fillText(el.text, el.x, el.y);
                            
                            // Guarda bounding box corrigido pelo alinhamento
                            let x1 = el.x;
                            if (el.align === 'center') x1 = el.x - textWidth/2;
                            else if (el.align === 'right') x1 = el.x - textWidth;
                            
                            el.hitBox = { x: x1, y: el.y - textHeight/2, w: textWidth, h: textHeight };
                            
                        } else if (el.type === 'badge') {
                            const bgWidth = textWidth + 60;
                            const bgHeight = fontSize * 2;
                            
                            let drawX = el.x;
                            if (el.align === 'right') drawX = el.x - bgWidth;
                            else if (el.align === 'center') drawX = el.x - bgWidth / 2;

                            // Sombra do badge
                            this.ctx.shadowColor = "rgba(0,0,0,0.3)";
                            this.ctx.shadowBlur = 10;
                            this.ctx.shadowOffsetY = 4;
                            this.ctx.fillStyle = el.color;
                            
                            this.roundRect(this.ctx, drawX, el.y, bgWidth, bgHeight, 10, true);

                            // Texto do Badge
                            this.ctx.shadowColor = "transparent";
                            this.ctx.fillStyle = "#FFFFFF";
                            this.ctx.textAlign = "center";
                            this.ctx.fillText(el.text, drawX + bgWidth/2, el.y + bgHeight/2);
                            
                            el.hitBox = { x: drawX, y: el.y, w: bgWidth, h: bgHeight };
                        }
                        
                        // DESENHA BORDA DE SELEÇÃO (Se selecionado)
                        if (index === this.selectedElementIndex) {
                            this.ctx.strokeStyle = "#3B82F6"; // Azul
                            this.ctx.lineWidth = 3;
                            this.ctx.setLineDash([10, 5]);
                            this.ctx.strokeRect(el.hitBox.x - 5, el.hitBox.y - 5, el.hitBox.w + 10, el.hitBox.h + 10);
                        }

                        this.ctx.restore();
                    });
                },

                roundRect(ctx, x, y, width, height, radius, fill) {
                    ctx.beginPath();
                    ctx.moveTo(x + radius, y);
                    ctx.lineTo(x + width - radius, y);
                    ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
                    ctx.lineTo(x + width, y + height - radius);
                    ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
                    ctx.lineTo(x + radius, y + height);
                    ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
                    ctx.lineTo(x, y + radius);
                    ctx.quadraticCurveTo(x, y, x + radius, y);
                    ctx.closePath();
                    if (fill) ctx.fill();
                },

                // Verifica se clicou em algum elemento
                getHitElement(x, y) {
                    // Itera ao contrário (do topo para o fundo)
                    for (let i = this.elements.length - 1; i >= 0; i--) {
                        const el = this.elements[i];
                        if (!el.hitBox) continue;
                        
                        // Padding para facilitar o toque no celular
                        const p = 20; 
                        if (x >= el.hitBox.x - p && x <= el.hitBox.x + el.hitBox.w + p &&
                            y >= el.hitBox.y - p && y <= el.hitBox.y + el.hitBox.h + p) {
                            return i;
                        }
                    }
                    return null;
                },

                setupDragEvents() {
                    const getPos = (e) => {
                        const rect = this.canvas.getBoundingClientRect();
                        const scaleX = this.canvas.width / rect.width;
                        const scaleY = this.canvas.height / rect.height;
                        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                        return { x: (clientX - rect.left) * scaleX, y: (clientY - rect.top) * scaleY };
                    };

                    const start = (e) => {
                        const pos = getPos(e);
                        
                        // 1. Verifica clique em elemento (Texto/Badge)
                        const hitIndex = this.getHitElement(pos.x, pos.y);
                        
                        if (hitIndex !== null) {
                            this.draggingElementIndex = hitIndex;
                            this.selectedElementIndex = hitIndex; // Seleciona para editar tamanho
                            // Calcula offset do clique em relação à origem do elemento
                            this.startX = pos.x - this.elements[hitIndex].x;
                            this.startY = pos.y - this.elements[hitIndex].y;
                            this.draw(); // Redesenha para mostrar seleção
                        } 
                        // 2. Se não clicou em texto, move o fundo (apenas se tiver imagem)
                        else if (this.userImage) {
                            this.draggingElementIndex = 'background';
                            this.selectedElementIndex = null; // Deseleciona texto
                            this.startX = pos.x - this.imgX;
                            this.startY = pos.y - this.imgY;
                            this.draw();
                        }
                    };

                    const move = (e) => {
                        if (this.draggingElementIndex === null) return;
                        e.preventDefault(); // Impede scroll
                        const pos = getPos(e);

                        if (this.draggingElementIndex === 'background') {
                            this.imgX = pos.x - this.startX;
                            this.imgY = pos.y - this.startY;
                        } else {
                            // Move elemento
                            const elIndex = this.draggingElementIndex;
                            this.elements[elIndex].x = pos.x - this.startX;
                            this.elements[elIndex].y = pos.y - this.startY;
                        }
                        this.draw();
                    };

                    const end = () => { this.draggingElementIndex = null; };

                    // Mouse
                    this.canvas.addEventListener('mousedown', start);
                    window.addEventListener('mousemove', move);
                    window.addEventListener('mouseup', end);

                    // Touch
                    this.canvas.addEventListener('touchstart', start, { passive: false });
                    window.addEventListener('touchmove', move, { passive: false });
                    window.addEventListener('touchend', end);
                },

                downloadImage() {
                    // Renderiza uma última vez sem a borda de seleção
                    const tempSelection = this.selectedElementIndex;
                    this.selectedElementIndex = null;
                    this.draw();
                    
                    const link = document.createElement('a');
                    link.download = 'meu-avatar-{{ Str::slug($inscricao->evento->nome) }}.jpg';
                    link.href = this.canvas.toDataURL('image/jpeg', 0.9);
                    link.click();
                    
                    // Restaura seleção
                    this.selectedElementIndex = tempSelection;
                    this.draw();
                }
            }
        }
    </script>
</x-app-layout>