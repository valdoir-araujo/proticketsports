<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Modalidade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModalidadeController extends Controller
{
    public function index()
    {
        $modalidades = Modalidade::orderBy('nome')->get(); // Usamos get() em vez de paginate()
        return view('admin.modalidades.index', compact('modalidades'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['nome' => 'required|string|max:255|unique:modalidades,nome']);
        Modalidade::create($validated);
        return redirect()->route('admin.modalidades.index')->with('sucesso', 'Modalidade criada com sucesso.');
    }

    public function edit(Modalidade $modalidade)
    {
        return view('admin.modalidades.edit', compact('modalidade'));
    }

    public function update(Request $request, Modalidade $modalidade)
    {
        $validated = $request->validate(['nome' => ['required', 'string', 'max:255', Rule::unique('modalidades')->ignore($modalidade->id)]]);
        $modalidade->update($validated);
        return redirect()->route('admin.modalidades.index')->with('sucesso', 'Modalidade atualizada com sucesso.');
    }

    public function destroy(Modalidade $modalidade)
    {
        if ($modalidade->eventos()->count() > 0) {
            return back()->with('erro', 'Não é possível excluir esta modalidade, pois ela já está vinculada a eventos.');
        }
        $modalidade->delete();
        return redirect()->route('admin.modalidades.index')->with('sucesso', 'Modalidade excluída com sucesso.');
    }
}