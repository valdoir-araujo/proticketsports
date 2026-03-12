<?php

use Illuminate\Support\Facades\Route;
use App\Models\Banner;
use App\Models\Evento;
use App\Models\Modalidade;

// Controladores
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\PublicEventController; 
use App\Http\Controllers\CampeonatoPublicoController; // <-- NOVO CONTROLADOR DE CAMPEONATOS
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AtletaController;
use App\Http\Controllers\EquipeController;
use App\Http\Controllers\InscricaoController;
use App\Http\Controllers\InscricaoGrupoController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\StravaController;
use App\Http\Controllers\LojaController; 
use App\Http\Controllers\CarrinhoController;
use App\Http\Controllers\LojaCheckoutController; // Controlador de Checkout
use App\Http\Controllers\Organizador\CampeonatoController;
use App\Http\Controllers\Organizador\CategoriaController;
use App\Http\Controllers\Organizador\DashboardController as OrganizadorDashboardController;
use App\Http\Controllers\Organizador\EventoOrganizadorController;
use App\Http\Controllers\Organizador\LancamentoFinanceiroController;
use App\Http\Controllers\Organizador\LoteController;
use App\Http\Controllers\Organizador\LoteInscricaoGeralController;
use App\Http\Controllers\Organizador\OrganizacaoController;
use App\Http\Controllers\Organizador\PercursoController;
use App\Http\Controllers\Organizador\RegraPontuacaoController;
use App\Http\Controllers\Organizador\RankingController;
use App\Http\Controllers\Organizador\ProdutoOpcionalController;
use App\Http\Controllers\Organizador\CupomController;
use App\Http\Controllers\Organizador\PercursoModeloController;
use App\Http\Controllers\Organizador\CategoriaModeloController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\EventoController as AdminEventoController;
use App\Http\Controllers\Admin\UsuarioController as AdminUsuarioController;
use App\Http\Controllers\Admin\RelatorioFinanceiroController;
use App\Http\Controllers\Admin\ConfiguracaoController;
use App\Http\Controllers\Admin\ModalidadeController;
use App\Http\Controllers\Admin\ParceiroController as AdminParceiroController;
use App\Http\Controllers\Admin\ContatoController as AdminContatoController;
use App\Http\Controllers\ParceiroPublicoController;
use App\Http\Controllers\ContatoController;
use App\Http\Controllers\SitemapController;

// Importações para as rotas de autenticação
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

/*
|--------------------------------------------------------------------------
| ROTAS PÚBLICAS GERAIS
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $banners = Banner::where('ativo', true)->orderBy('ordem')->get()->map(function ($banner) {
        return ['id' => $banner->id, 'url' => asset('storage/' . $banner->imagem_url), 'link' => $banner->link_url, 'titulo' => $banner->titulo, 'subtitulo' => $banner->subtitulo];
    });
    $eventosDestaque = Evento::with(['cidade.estado', 'modalidade'])->where('status', 'publicado')->where('data_evento', '>=', now())->orderBy('data_evento', 'asc')->take(8)->get();
    
    $modalidades = \App\Models\Modalidade::orderBy('nome')->get(); 
    
    return view('welcome', compact('eventosDestaque', 'banners', 'modalidades')); 
})->name('welcome');

Route::get('/eventos', [PublicEventController::class, 'index'])->name('eventos.public.index');
Route::get('/eventos/{evento:slug}', [PublicEventController::class, 'show'])->name('eventos.public.show');
Route::get('/eventos/{evento:slug}/inscritos', [PublicEventController::class, 'showInscritos'])->name('eventos.public.inscritos');
Route::get('/eventos/{evento:slug}/resultados', [PublicEventController::class, 'showResultados'])->name('eventos.public.resultados');

// --- ROTAS DE CAMPEONATOS PÚBLICOS ---
Route::get('/campeonatos', [CampeonatoPublicoController::class, 'index'])->name('campeonatos.index');
Route::get('/campeonatos/{campeonato}/ranking', [CampeonatoPublicoController::class, 'ranking'])->name('campeonatos.ranking');
Route::get('/campeonatos/{campeonato}', [CampeonatoPublicoController::class, 'show'])->name('campeonatos.show');

Route::get('/parceiros', [ParceiroPublicoController::class, 'index'])->name('parceiros.index');
Route::get('/contato', [ContatoController::class, 'index'])->name('contato.index');
Route::get('/para-organizadores', fn () => view('para-organizadores'))->name('para-organizadores');

// SEO: sitemap e robots (Google e outros buscadores)
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', function () {
    $url = rtrim(config('app.url'), '/');
    $body = "User-agent: *\nDisallow:\nSitemap: {$url}/sitemap.xml\n";
    return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('robots');

// --- INSCRIÇÃO: IDENTIFICAÇÃO (CPF/email + data nascimento), sem login ---
Route::get('/evento/{evento}/identificacao', [InscricaoController::class, 'identificacao'])->name('inscricao.identificacao');
Route::post('/evento/{evento}/identificacao', [InscricaoController::class, 'verificarIdentificacao'])->name('inscricao.identificacao.verificar');
// Inscrição create/store/pagamento: aceitam usuário logado OU session (após identificação). Throttle evita abuso em massa.
Route::get('/evento/{evento}/inscrever', [InscricaoController::class, 'create'])->name('inscricao.create');
Route::post('/inscricoes', [InscricaoController::class, 'store'])->name('inscricao.store')->middleware('throttle:15,1');
// Pagamento da inscrição (PIX/cartão) – protegido por dono/código do grupo dentro do controller, sem exigir login por senha.
Route::get('/inscricao/{inscricao}/pagamento', [PagamentoController::class, 'show'])->name('pagamento.show');
Route::post('/inscricao/{inscricao}/processar-pagamento', [PagamentoController::class, 'process'])->name('pagamento.process')->middleware('throttle:30,1');
Route::post('/inscricao/{inscricao}/comprovante-pagamento', [PagamentoController::class, 'storeComprovante'])->name('pagamento.comprovante.store')->middleware('throttle:10,1');
Route::get('/pagamento/{inscricao}/sucesso', [PagamentoController::class, 'sucesso'])->name('pagamento.sucesso');
Route::get('/pagamento/{inscricao}/falha', [PagamentoController::class, 'falha'])->name('pagamento.falha');
Route::get('/api/atletas/search', [InscricaoController::class, 'pesquisarAtleta'])->name('api.atletas.search');
Route::get('/api/equipes/search', [InscricaoController::class, 'pesquisarEquipe'])->name('api.equipes.search');
Route::get('/api/equipes/{equipe}/atletas', [InscricaoController::class, 'atletasDaEquipe'])->name('api.equipes.atletas');

// --- INSCRIÇÃO EM GRUPO (wizard: atletas → percurso → pagamento) ---
Route::get('/evento/{evento}/inscricao-grupo', [InscricaoGrupoController::class, 'identificacao'])->name('inscricao-grupo.identificacao');
Route::get('/evento/{evento}/inscricao-grupo/atletas', [InscricaoGrupoController::class, 'atletas'])->name('inscricao-grupo.atletas');
Route::post('/evento/{evento}/inscricao-grupo/atletas', [InscricaoGrupoController::class, 'atletasStore'])->name('inscricao-grupo.atletas.store');
Route::get('/evento/{evento}/inscricao-grupo/percurso', [InscricaoGrupoController::class, 'percurso'])->name('inscricao-grupo.percurso');
Route::post('/evento/{evento}/inscricao-grupo/percurso', [InscricaoGrupoController::class, 'percursoStore'])->name('inscricao-grupo.percurso.store');
Route::get('/evento/{evento}/inscricao-grupo/pagamento', [InscricaoGrupoController::class, 'pagamento'])->name('inscricao-grupo.pagamento');
Route::post('/evento/{evento}/inscricao-grupo/aplicar-cupom', [InscricaoGrupoController::class, 'aplicarCupom'])->name('inscricao-grupo.aplicar-cupom');
Route::post('/evento/{evento}/inscricao-grupo/confirmar', [InscricaoGrupoController::class, 'confirmar'])->name('inscricao-grupo.confirmar');

// ROTA: Política de Privacidade
Route::get('/politica-privacidade', function () {
    return view('politica-privacidade');
})->name('politica.privacidade');

/*
|--------------------------------------------------------------------------
| LOJA ONLINE (Checkout Híbrido)
|--------------------------------------------------------------------------
| Estas rotas precisam estar fora do 'auth' para permitir compra sem senha.
*/

// Vitrine da Loja
Route::get('/loja', [LojaController::class, 'index'])->name('loja.index');

// Carrinho (Adicionar/Remover/Atualizar)
Route::get('/carrinho', [CarrinhoController::class, 'index'])->name('carrinho.index'); 
Route::post('/carrinho/adicionar/{id}', [CarrinhoController::class, 'adicionar'])->name('carrinho.adicionar');
Route::patch('/carrinho/atualizar', [CarrinhoController::class, 'atualizar'])->name('carrinho.atualizar');
Route::delete('/carrinho/remover', [CarrinhoController::class, 'remover'])->name('carrinho.remover');

// Identificação (Tela de CPF/Data Nascimento)
Route::get('/loja/identificacao', [LojaCheckoutController::class, 'identificacao'])->name('loja.identificacao');
Route::post('/loja/identificacao', [LojaCheckoutController::class, 'verificarIdentificacao'])->name('loja.identificacao.verificar');

// Checkout (Resumo do Pedido e Processamento)
Route::get('/loja/checkout', [LojaCheckoutController::class, 'index'])->name('loja.checkout');
Route::post('/loja/checkout/cupom', [LojaCheckoutController::class, 'aplicarCupom'])->name('loja.checkout.cupom');
Route::post('/loja/checkout/processar', [LojaCheckoutController::class, 'processar'])->name('loja.checkout.processar');

// Pagamento do Pedido (Pós-Checkout Loja)
Route::get('/loja/pedido/{pedido}/pagamento', [LojaCheckoutController::class, 'pagamento'])->name('loja.pedido.pagamento');
Route::post('/pagamento/processar', [LojaCheckoutController::class, 'processarPagamento'])->name('pagamento.processar')->middleware('throttle:30,1'); // 30 req/min por IP
Route::get('/pedido/{pedido}/sucesso', [LojaCheckoutController::class, 'sucesso'])->name('pedido.sucesso');
Route::get('/pedido/{pedido}/pendente', [LojaCheckoutController::class, 'pendente'])->name('pedido.pendente');


/*
|--------------------------------------------------------------------------
| API & WEBHOOKS
|--------------------------------------------------------------------------
*/
Route::get('/api/estados', [LocationController::class, 'getEstados'])->name('api.estados');
Route::get('/api/estados/{estado}/cidades', [LocationController::class, 'getCidades'])->name('api.cidades');
Route::get('/api/cep', [LocationController::class, 'getCep'])->name('api.cep');
Route::post('/webhook/mercadopago', [PagamentoController::class, 'webhook'])->name('webhook.mercadopago')->middleware('throttle:120,1'); // 120 req/min


/*
|--------------------------------------------------------------------------
| AUTENTICAÇÃO (Login/Registro/Senha)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:10,1');

    // Recuperação de Senha
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                     ->middleware('auth')
                     ->name('logout');


/*
|--------------------------------------------------------------------------
| ÁREA LOGADA (Atleta, Organizador, Admin)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    
    // Verificação de Email
    Route::get('/verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    
    // Dashboard Geral (Redirecionador)
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');
    
    // Perfil do Usuário
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // Equipes
    Route::resource('equipes', EquipeController::class);
    Route::post('/equipes/store-ajax', [EquipeController::class, 'storeAjax'])->name('equipes.store.ajax');

    // --- ÁREA DO ATLETA ---
    Route::prefix('atleta')->name('atleta.')->group(function() {
        Route::get('/dashboard', [AtletaController::class, 'index'])->name('dashboard');
        Route::get('/minhas-inscricoes', [AtletaController::class, 'index'])->name('inscricoes');
    });

    // --- INSCRIÇÃO EM EVENTOS (create/store/pagamento estão fora do auth; show/edit/update exigem dono) ---
    Route::get('/inscricoes/{inscricao}', [InscricaoController::class, 'show'])->name('inscricao.show');
    Route::get('/inscricoes/{inscricao}/recibo', [InscricaoController::class, 'recibo'])->name('inscricao.recibo');
    Route::get('/inscricoes/{inscricao}/editar', [InscricaoController::class, 'edit'])->name('inscricao.edit');
    Route::patch('/inscricoes/{inscricao}', [InscricaoController::class, 'update'])->name('inscricao.update');
    Route::post('/inscricoes/{inscricao}/aplicar-cupom', [InscricaoController::class, 'aplicarCupom'])->name('inscricao.cupom.aplicar');
    // Avatar
    Route::get('/inscricao/{inscricao}/avatar', [InscricaoController::class, 'avatar'])->name('inscricao.avatar');


    // --- PAINEL DO ORGANIZADOR ---
    Route::prefix('organizador')->name('organizador.')->group(function () {
        
        // Cadastro de Organização
        Route::get('/organizacao/criar', [OrganizacaoController::class, 'create'])->name('organizacao.create');
        Route::post('/organizacao', [OrganizacaoController::class, 'store'])->name('organizacao.store');
        Route::get('/organizacao/{organizacao}/editar', [OrganizacaoController::class, 'edit'])->name('organizacao.edit');
        Route::put('/organizacao/{organizacao}', [OrganizacaoController::class, 'update'])->name('organizacao.update');

        Route::get('/organizacao', function() {
            return redirect()->route('organizador.organizacao.create');
        });

        // Rotas Protegidas (Só Organizador)
        Route::middleware('is_organizador')->group(function () {
            
            Route::get('/dashboard', [OrganizadorDashboardController::class, 'index'])->name('dashboard');
            Route::get('/', [OrganizadorDashboardController::class, 'selecaoOrganizacao'])->name('index'); 
            
            Route::get('/financeiro', fn () => redirect()->route('organizador.dashboard', request()->only('org_id')))->name('financeiro.index');
            Route::patch('/financeiro', [OrganizacaoController::class, 'updateFinanceiro'])->name('financeiro.update');
            
            // Campeonatos
            Route::resource('campeonatos', CampeonatoController::class);
            Route::post('campeonatos/{campeonato}/cancelar', [CampeonatoController::class, 'cancelar'])->name('campeonatos.cancelar');
            Route::get('campeonatos/{campeonato}/regras-pontuacao', [RegraPontuacaoController::class, 'index'])->name('campeonatos.regras.index');
            Route::post('campeonatos/{campeonato}/regras-pontuacao', [RegraPontuacaoController::class, 'store'])->name('campeonatos.regras.store');
            Route::get('campeonatos/{campeonato}/ranking', [RankingController::class, 'index'])->name('campeonatos.ranking.index');
            
            // Modelos
            Route::delete('modelos-percurso/{id}', [PercursoModeloController::class, 'destroy'])->name('modelos-percurso.destroy');
            Route::resource('modelos-percurso', PercursoModeloController::class)->except(['show', 'destroy'])->names('modelos-percurso');
            Route::resource('modelos-categoria', CategoriaModeloController::class)->except(['show'])->names('modelos-categoria');

            // Eventos - CRUD e Configurações
            Route::resource('eventos', EventoOrganizadorController::class);
            
            Route::post('eventos/{evento}/lancamentos', [LancamentoFinanceiroController::class, 'store'])->name('lancamentos.store');
            
            Route::resource('eventos/{evento}/percursos', PercursoController::class)->except(['index', 'show', 'create', 'edit'])->scoped()->names('percursos');
            Route::post('eventos/{evento}/percursos-modelo-corrida', [PercursoController::class, 'storePercursosModeloCorrida'])->name('percursos.modelo-corrida');
            Route::patch('eventos/{evento}/percursos/{percurso}/link', [PercursoController::class, 'link'])->name('percursos.link');

            Route::resource('eventos/{evento}/lotes-gerais', LoteInscricaoGeralController::class)->only(['store', 'update', 'destroy'])->names('lotes-gerais');
            Route::resource('eventos/{evento}/produtos', ProdutoOpcionalController::class)->except(['index', 'show'])->names('produtos');
            Route::resource('eventos/{evento}/cupons', CupomController::class)->only(['store', 'update', 'destroy'])->scoped()->names('cupons');

            Route::patch('eventos/{evento}/financeiro', [EventoOrganizadorController::class, 'updateFinanceiro'])->name('eventos.financeiro.update');

            // Rotas Específicas do Evento
            Route::prefix('eventos/{evento}')->name('eventos.')->group(function() {
                Route::post('toggle-public-list', [EventoOrganizadorController::class, 'togglePublicList'])->name('togglePublicList');
                Route::get('exportar-inscritos', [EventoOrganizadorController::class, 'exportarInscritos'])->name('exportarInscritos');

                Route::get('checkin', [EventoOrganizadorController::class, 'checkinIndex'])->name('checkin.index');
                Route::post('checkin/{inscricao}', [EventoOrganizadorController::class, 'checkinStore'])->name('checkin.store');
                Route::post('checkin/{inscricao}/undo', [EventoOrganizadorController::class, 'checkinUndo'])->name('checkin.undo');

                Route::get('numeracao', [EventoOrganizadorController::class, 'numeracao'])->name('numeracao');
                Route::post('numeracao', [EventoOrganizadorController::class, 'salvarNumeracao'])->name('numeracao.store');
                
                Route::get('resultados', [EventoOrganizadorController::class, 'showResultados'])->name('resultados.show');
                Route::post('resultados', [EventoOrganizadorController::class, 'storeResultados'])->name('resultados.store');
                
                Route::get('relatorio-financeiro-pdf', [EventoOrganizadorController::class, 'gerarRelatorioFinanceiroPDF'])->name('relatorio-financeiro.pdf');
                Route::get('relatorio-inscritos-pdf', [EventoOrganizadorController::class, 'gerarRelatorioInscritosPDF'])->name('relatorio-inscritos.pdf');
                Route::post('contatos', [EventoOrganizadorController::class, 'storeContato'])->name('contatos.store');
                Route::patch('contatos/{evento_contato}', [EventoOrganizadorController::class, 'updateContato'])->name('contatos.update');
                Route::delete('contatos/{evento_contato}', [EventoOrganizadorController::class, 'destroyContato'])->name('contatos.destroy');
                Route::patch('regulamento', [EventoOrganizadorController::class, 'updateRegulamento'])->name('regulamento.update');
                Route::patch('formas-pagamento', [EventoOrganizadorController::class, 'updateFormasPagamento'])->name('formas-pagamento.update');
            });
            
            Route::patch('resultados/{inscricao}', [EventoOrganizadorController::class, 'updateSingleResultado'])->name('eventos.resultados.updateSingle');
            Route::post('inscricoes/{inscricao}/confirmar-cortesia', [EventoOrganizadorController::class, 'confirmarCortesia'])->name('inscricoes.confirmarCortesia');
            Route::get('inscricoes/{inscricao}/comprovante', [EventoOrganizadorController::class, 'verComprovante'])->name('inscricoes.comprovante');
            Route::post('inscricoes/{inscricao}/confirmar-pagamento', [EventoOrganizadorController::class, 'toggleConfirmarPagamento'])->name('inscricoes.toggleConfirmarPagamento');


            Route::resource('percursos/{percurso}/categorias', CategoriaController::class)->except(['show'])->scoped();
            
            Route::resource('categorias/{categoria}/lotes', LoteController::class)->except(['show', 'edit', 'update']);
        });
    });

    // --- PAINEL DE ADMINISTRAÇÃO ---
    Route::prefix('admin')->name('admin.')->middleware('is_admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('banners', BannerController::class);
        Route::resource('modalidades', ModalidadeController::class);
        Route::resource('parceiros', AdminParceiroController::class);
        Route::resource('contatos', AdminContatoController::class);

        Route::resource('eventos', AdminEventoController::class)->only(['index', 'edit', 'update']);
        Route::resource('usuarios', AdminUsuarioController::class)->except(['create', 'store', 'show', 'destroy']);
        
        Route::get('acl/dashboard', [AdminUsuarioController::class, 'aclDashboard'])->name('acl.dashboard'); 
        Route::get('usuarios/{usuario}/permissoes', [AdminUsuarioController::class, 'editPermissions'])->name('usuarios.permissions.edit');
        Route::put('usuarios/{usuario}/permissoes', [AdminUsuarioController::class, 'updatePermissions'])->name('usuarios.permissions.update');
        
        Route::get('relatorios-financeiros', [RelatorioFinanceiroController::class, 'index'])->name('relatorios.financeiros.index');
        Route::get('repasses/criar', [RelatorioFinanceiroController::class, 'createRepasse'])->name('repasses.create');
        Route::post('repasses', [RelatorioFinanceiroController::class, 'storeRepasseLote'])->name('repasses.store');
        Route::get('repasses/{repasse}', [RelatorioFinanceiroController::class, 'showRepasseLote'])->name('repasses.show');
        Route::patch('repasses/{repasse}', [RelatorioFinanceiroController::class, 'updateRepasseLote'])->name('repasses.update');
        Route::post('repasses/{repasse}/estornar', [RelatorioFinanceiroController::class, 'estornarRepasse'])->name('repasses.estornar');
        Route::delete('repasses/{repasse}', [RelatorioFinanceiroController::class, 'destroyRepasseLote'])->name('repasses.destroy');

        Route::get('configuracoes', [ConfiguracaoController::class, 'index'])->name('configuracoes.index');
        Route::post('configuracoes', [ConfiguracaoController::class, 'store'])->name('configuracoes.store');
    });

    // --- ROTAS DO STRAVA ---
    Route::get('/strava/connect', [StravaController::class, 'connect'])->name('strava.connect');
    Route::get('/strava/callback', [StravaController::class, 'callback'])->name('strava.callback');
    Route::get('/strava/disconnect', [StravaController::class, 'disconnect'])->name('strava.disconnect');
});

// URL de callback do Strava (pública, para conferir no servidor e corrigir "redirect_uri invalid")
Route::get('/strava/redirect-uri', function () {
    $uri = \App\Http\Controllers\StravaController::redirectUriComputed();
    $domain = parse_url($uri, PHP_URL_HOST) ?: '';
    return response()->json([
        'redirect_uri' => $uri,
        'authorization_callback_domain' => $domain,
        'instrucao' => 'No painel do Strava (Settings > My API Application) use em "Authorization Callback Domain" exatamente: ' . $domain,
    ], 200, [], JSON_UNESCAPED_SLASHES);
})->name('strava.redirect_uri');

Route::get('/debug-php', function () {
    return [
        'Arquivo de Configuração (php.ini)' => php_ini_loaded_file(),
        'Caminho do SSL (curl.cainfo)' => ini_get('curl.cainfo'),
        'Caminho do SSL (openssl.cafile)' => ini_get('openssl.cafile'),
        'O arquivo existe?' => file_exists(ini_get('curl.cainfo')) ? 'SIM' : 'NÃO',
    ];
});