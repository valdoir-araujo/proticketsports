<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    /**
     * Exibe a lista de todos os banners.
     */
    public function index(): View
    {
        $banners = Banner::latest()->get();
        return view('admin.banners.index', compact('banners'));
    }

    /**
     * Mostra o formulário para criar um novo banner.
     */
    public function create(): View
    {
        return view('admin.banners.create');
    }

    /**
     * Guarda um novo banner na base de dados.
     */
    public function store(Request $request): RedirectResponse
    {
        $dadosValidados = $request->validate([
            'titulo' => 'required|string|max:255',
            'subtitulo' => 'nullable|string|max:255',
            'link_url' => 'nullable|url',
            'imagem' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'ativo' => 'nullable',
        ]);

        $dadosValidados['ativo'] = $request->boolean('ativo');

        if ($request->hasFile('imagem')) {
            $path = $request->file('imagem')->store('banners', 'public');
            $dadosValidados['imagem_url'] = $path;
        }
        unset($dadosValidados['imagem']);

        Banner::create($dadosValidados);

        return redirect()->route('admin.banners.index')->with('sucesso', 'Banner criado com sucesso!');
    }

    /**
     * Mostra o formulário para editar um banner existente.
     */
    public function edit(Banner $banner): View
    {
        return view('admin.banners.edit', compact('banner'));
    }

    /**
     * Atualiza um banner na base de dados.
     */
    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $dadosValidados = $request->validate([
            'titulo' => 'required|string|max:255',
            'subtitulo' => 'nullable|string|max:255',
            'link_url' => 'nullable|url',
            'imagem' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'ativo' => 'nullable',
        ]);

        $dadosValidados['ativo'] = $request->boolean('ativo');

        if ($request->hasFile('imagem')) {
            // Apaga a imagem antiga
            if ($banner->imagem_url) {
                Storage::disk('public')->delete($banner->imagem_url);
            }
            // Guarda a nova imagem
            $path = $request->file('imagem')->store('banners', 'public');
            $dadosValidados['imagem_url'] = $path;
        }
        unset($dadosValidados['imagem']);

        $banner->update($dadosValidados);

        return redirect()->route('admin.banners.index')->with('sucesso', 'Banner atualizado com sucesso!');
    }

    /**
     * Remove um banner da base de dados. (Método Padrão)
     */
    public function destroy(Banner $banner): RedirectResponse
    {
        // 1. Apaga a imagem do armazenamento para não deixar lixo
        if ($banner->imagem_url) {
            Storage::disk('public')->delete($banner->imagem_url);
        }

        // 2. Apaga o registo do banner da base de dados
        $banner->delete();

        // 3. Redireciona de volta com uma mensagem de sucesso
        return redirect()->route('admin.banners.index')->with('sucesso', 'Banner excluído com sucesso!');
    }
    
    // ======================================================================
    // MÉTODO ADICIONADO PARA CORRIGIR O PROBLEMA
    // ======================================================================
    /**
     * Lida com a requisição de deleção vinda da rota alternativa sem ID.
     */
    public function handleDeleteRequest(Request $request): RedirectResponse
    {
        // Pega o ID do banner que deve ser enviado por um campo oculto no formulário
        $bannerId = $request->input('banner_id');

        // Procura o banner pelo ID
        $banner = Banner::find($bannerId);

        // Se o banner for encontrado, chama o método destroy() padrão para fazer a exclusão
        if ($banner) {
            return $this->destroy($banner);
        }

        // Se não enviou um ID ou o banner não foi encontrado, retorna com erro.
        return redirect()->route('admin.banners.index')->with('erro', 'Não foi possível encontrar o banner para excluir.');
    }
}