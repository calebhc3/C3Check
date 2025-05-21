<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 leading-tight">
            Editar Nota de Pagamento
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">

                <form action="{{ route('notas.update', $nota) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Dados da Nota -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                        <div>
                            <label for="prestador" class="block font-medium text-gray-700 dark:text-gray-300">Prestador</label>
                            <input type="text" name="prestador" id="prestador" value="{{ old('prestador', $nota->prestador) }}" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100" required>
                        </div>

                        <div>
                            <label for="cnpj" class="block font-medium text-gray-700 dark:text-gray-300">CNPJ</label>
                            <input type="text" name="cnpj" id="cnpj" value="{{ old('cnpj', $nota->cnpj) }}" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100">
                        </div>

                        <div>
                            <label for="numero_nf" class="block font-medium text-gray-700 dark:text-gray-300">Número NF</label>
                            <input type="text" name="numero_nf" id="numero_nf" value="{{ old('numero_nf', $nota->numero_nf) }}" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100" required>
                        </div>

                        <div>
                            <label for="valor_total" class="block font-medium text-gray-700 dark:text-gray-300">Valor Líquido</label>
                            <input type="number" step="0.01" name="valor_total" id="valor_total" value="{{ old('valor_total', $nota->valor_total) }}" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100" required>
                        </div>

                        <div>
                            <label for="vencimento_original" class="block font-medium text-gray-700 dark:text-gray-300">Vencimento</label>
                            <input type="date" name="vencimento_original" id="vencimento_original" value="{{ old('vencimento_original', $nota->vencimento_original ? \Carbon\Carbon::parse($nota->vencimento_original)->format('Y-m-d') : '') }}" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100" required>
                        </div>

                        <div class="flex items-center space-x-2 mt-6">
                            <input type="checkbox" name="taxa_correio" id="taxa_correio" value="1" {{ old('taxa_correio', $nota->taxa_correio) ? 'checked' : '' }} class="h-5 w-5 text-blue-600">
                            <label for="taxa_correio" class="font-medium text-gray-700 dark:text-gray-300">Taxa de Correio</label>
                        </div>

                        <div>
                            <label for="valor_taxa_correio" class="block font-medium text-gray-700 dark:text-gray-300">Valor Taxa de Correio</label>
                            <input type="number" step="0.01" name="valor_taxa_correio" id="valor_taxa_correio" value="{{ old('valor_taxa_correio', $nota->valor_taxa_correio) }}" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100">
                        </div>

                        <div>
                            <label for="data_entregue_financeiro" class="block font-medium text-gray-700 dark:text-gray-300">Data Entregue para Financeiro</label>
                            <input type="date" name="data_entregue_financeiro" id="data_entregue_financeiro" value="{{ old('data_entregue_financeiro', $nota->data_entregue_financeiro ? $nota->data_entregue_financeiro->format('Y-m-d') : '') }}" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100">
                        </div>

                        <div>
                            <label for="data_emissao" class="block font-medium text-gray-700 dark:text-gray-300">Mês (Data Emissão)</label>
                            <input type="month" name="data_emissao" id="data_emissao" value="{{ old('vencimento_original', $nota->data_emissao ? \Carbon\Carbon::parse($nota->data_emissao)->format('Y-m-d') : '') }}" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100">
                        </div>

                    </div>

                    <hr class="my-6 border-gray-300 dark:border-gray-600">

                    <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Responsável</h3>

                    <div class="mb-6">
                        <input type="text" name="user_name" id="user_name" value="{{ old('user_name', $nota->user->name ?? '-') }}" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:text-gray-100" disabled>
                    </div>

                    <hr class="my-6 border-gray-300 dark:border-gray-600">

                    {{-- Se tiver relação clientes, aqui você edita eles --}}

                    @if ($nota->clientes && $nota->clientes->count() > 0)
                        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Clientes da Nota</h3>

                        @foreach($nota->clientes as $index => $cliente)
                            <div class="mb-6 p-4 border rounded bg-gray-50 dark:bg-gray-700">
                                <h4 class="font-semibold mb-2 text-gray-900 dark:text-gray-100">Cliente #{{ $index + 1 }}</h4>

                                <input type="hidden" name="clientes[{{ $index }}][id]" value="{{ $cliente->id }}">

                                <label for="clientes[{{ $index }}][nome]" class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Nome</label>
                                <input type="text" name="clientes[{{ $index }}][nome]" id="clientes[{{ $index }}][nome]" value="{{ old("clientes.$index.nome", $cliente->nome) }}" class="w-full border rounded px-3 py-2 mb-3 dark:bg-gray-700 dark:text-gray-100" required>

                                <label for="clientes[{{ $index }}][cpf]" class="block mb-1 font-medium text-gray-700 dark:text-gray-300">CPF</label>
                                <input type="text" name="clientes[{{ $index }}][cpf]" id="clientes[{{ $index }}][cpf]" value="{{ old("clientes.$index.cpf", $cliente->cpf) }}" class="w-full border rounded px-3 py-2 mb-3 dark:bg-gray-700 dark:text-gray-100" required>

                                {{-- Adicione mais campos aqui se precisar --}}
                            </div>
                        @endforeach
                    @endif

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                            Atualizar Nota
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>
