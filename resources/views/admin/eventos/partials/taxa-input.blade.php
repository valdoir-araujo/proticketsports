<div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
    <x-input-label for="TaxaServico" value="Taxa de Serviço (%)" class="text-yellow-800" />
    
    <div class="flex items-center gap-2 mt-1">
        <input type="number" 
               id="taxaservico" 
               name="taxaservico" 
               step="0.01" 
               min="0" 
               max="100"
               class="block w-full border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 rounded-md shadow-sm"
               value="{{ old('taxaservico', $evento->taxaservico) }}" 
               placeholder="10.00">
        <span class="font-bold text-yellow-700">%</span>
    </div>

    <p class="text-xs text-gray-600 mt-2">
        <i class="fa-solid fa-circle-info mr-1"></i>
        <strong>Padrão:</strong> Se deixar vazio, o sistema cobra 10%.<br>
        <strong>Isento:</strong> Digite 0 para não cobrar taxa.
    </p>
    <x-input-error :messages="$errors->get('taxaservico')" class="mt-2" />
</div>