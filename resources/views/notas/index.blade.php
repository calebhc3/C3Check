    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 leading-tight">
            Notas de Pagamento
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <a href="{{ route('notas.create') }}" class="mb-4 inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Nova Nota
                </a>

                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Notas de Clínicas</h3>
                @if ($notasClinicas->isEmpty())
                    <p class="text-gray-600 dark:text-gray-300">Nenhuma nota de clínica cadastrada.</p>
                @else
                    <div class="overflow-x-auto mb-8">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
<thead>
    <tr class="bg-gray-100 dark:bg-gray-700 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
        <th class="px-4 py-3">Prestador</th>
        <th class="px-4 py-3">CNPJ</th>
        <th class="px-4 py-3">NF</th>
        <th class="px-4 py-3">Valor Líquido</th>
        <th class="px-4 py-3">Vencimento</th>
        <th class="px-4 py-3">Data Entregue para Financeiro</th>
        <th class="px-4 py-3">Mês</th>
        <th class="px-4 py-3">Responsável</th>
        <th class="px-4 py-3 text-center">Ações</th>
    </tr>
</thead>
<tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-900 dark:text-gray-100">
    @foreach ($notasClinicas as $nota)
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <td class="px-4 py-2">{{ $nota->prestador }}</td>
            <td class="px-4 py-2">{{ $nota->cnpj ?? '-' }}</td>
            <td class="px-4 py-2">{{ $nota->numero_nf }}</td>
            <td class="px-4 py-2">R$ {{ number_format($nota->valor_total, 2, ',', '.') }}</td>
            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($nota->vencimento_original)->format('d/m/Y') }}</td>
            <td class="px-4 py-2">{{ $nota->data_entregue_financeiro ? \Carbon\Carbon::parse($nota->data_entregue_financeiro)->format('d/m/Y') : '-' }}</td>
            <td class="px-4 py-2">{{ $nota->data_emissao ? \Carbon\Carbon::parse($nota->data_emissao)->format('m/Y') : '-' }}</td>
            <td class="px-4 py-2">{{ $nota->user->name ?? '-' }}</td>
            <td class="px-4 py-2 text-center space-x-2">
                <a href="{{ route('notas.edit', $nota) }}" class="text-blue-600 hover:underline">Editar</a>
                <form action="{{ route('notas.destroy', $nota) }}" method="POST" class="inline-block" onsubmit="return confirm('Tem certeza que deseja excluir?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:underline">Excluir</button>
                </form>
            </td>
        </tr>
    @endforeach
</tbody>
                        </table>
                        <div class="mt-4">{{ $notasClinicas->links() }}</div>
                    </div>
                @endif

                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Notas de Médicos</h3>
                @if ($notasMedicos->isEmpty())
                    <p class="text-gray-600 dark:text-gray-300">Nenhuma nota de médico cadastrada.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
<thead>
    <tr class="bg-gray-100 dark:bg-gray-700 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
        <th class="px-4 py-3">Médico</th>
        <th class="px-4 py-3">NF</th>
        <th class="px-4 py-3">Valor da Nota</th>
        <th class="px-4 py-3">Vencimento</th>
        <th class="px-4 py-3">Responsável</th>
        <th class="px-4 py-3 text-center">Ações</th>
    </tr>
</thead>
<tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-900 dark:text-gray-100">
    @foreach ($notasMedicos as $nota)
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <td class="px-4 py-2">{{ $nota->med_nome }}</td>
            <td class="px-4 py-2">{{ $nota->numero_nf }}</td>
            <td class="px-4 py-2">R$ {{ number_format($nota->valor_total, 2, ',', '.') }}</td>
            <td class="px-4 py-2">{{ $nota->vencimento_original ? \Carbon\Carbon::parse($nota->vencimento_original)->format('d/m/Y') : '-' }}</td>
            <td class="px-4 py-2">{{ $nota->user->name ?? '-' }}</td>
            <td class="px-4 py-2 text-center space-x-2">
                <a href="{{ route('notas.edit', $nota) }}" class="text-blue-600 hover:underline">Editar</a>
                <form action="{{ route('notas.destroy', $nota) }}" method="POST" class="inline-block" onsubmit="return confirm('Tem certeza que deseja excluir?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:underline">Excluir</button>
                </form>
            </td>
        </tr>
    @endforeach
</tbody>
                        </table>
                        <div class="mt-4">{{ $notasMedicos->links() }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
