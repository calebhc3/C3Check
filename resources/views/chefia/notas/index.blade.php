    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 leading-tight">
            Aprovação de Notas
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                @if ($notasPendentes->isEmpty())
                    <p class="text-gray-600 dark:text-gray-300">Nenhuma nota pendente de aprovação.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-gray-700 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    <th class="px-4 py-3">Prestador</th>
                                    <th class="px-4 py-3">CNPJ</th>
                                    <th class="px-4 py-3">Valor Líquido</th>
                                    <th class="px-4 py-3">Vencimento</th>
                                    <th class="px-4 py-3">Valor Taxa de Correio</th>
                                    <th class="px-4 py-3">Data Entregue para Financeiro</th>
                                    <th class="px-4 py-3">Responsável</th>
                                    <th class="px-4 py-3 text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-900 dark:text-gray-100">
                                @foreach ($notasPendentes as $nota)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <td class="px-4 py-2">{{ $nota->prestador }}</td>
                                        <td class="px-4 py-2">{{ $nota->cnpj ?? '-' }}</td>
                                        <td class="px-4 py-2">R$ {{ number_format($nota->valor_total, 2, ',', '.') }}</td>
                                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($nota->vencimento_original)->format('d/m/Y') }}</td>
                                        <td class="px-4 py-2">R$ {{ number_format($nota->valor_taxa_correio, 2, ',', '.') }}</td>
                                        <td class="px-4 py-2">{{ $nota->data_entregue_financeiro ? \Carbon\Carbon::parse($nota->data_entregue_financeiro)->format('d/m/Y') : '-' }}</td>
                                        <td class="px-4 py-2">{{ $nota->user->name ?? '-' }}</td>
                                        <td class="px-4 py-2 text-center space-x-2">
                                            <form action="{{ route('chefia.notas.aprovar', $nota) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:underline">Aprovar</button>
                                            </form>
                                            <form action="{{ route('chefia.notas.rejeitar', $nota) }}" method="POST" class="inline-block" onsubmit="return confirm('Tem certeza que deseja reprovar esta nota?')">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:underline">Reprovar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">{{ $notasPendentes->links() }}</div>

                    </div>
                @endif
            </div>
            @if ($historicoNotas->isEmpty())
    <p class="text-gray-600 dark:text-gray-300 mt-8">Nenhuma nota aprovada/rejeitada ainda.</p>
@else
    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mt-8 mb-2">Histórico de Notas</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-700 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                    <th class="px-4 py-3">Prestador</th>
                    <th class="px-4 py-3">CNPJ</th>
                    <th class="px-4 py-3">Valor Líquido</th>
                    <th class="px-4 py-3">Vencimento</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Aprovado em</th>
                    <th class="px-4 py-3">Responsável</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-900 dark:text-gray-100">
                @foreach ($historicoNotas as $nota)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-4 py-2">{{ $nota->prestador }}</td>
                        <td class="px-4 py-2">{{ $nota->cnpj ?? '-' }}</td>
                        <td class="px-4 py-2">R$ {{ number_format($nota->valor_total, 2, ',', '.') }}</td>
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($nota->vencimento_original)->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">{{ ucfirst(str_replace('_', ' ', $nota->status)) }}</td>
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($nota->aprovado_chefia_em)->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">{{ $nota->user->name ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $historicoNotas->links('pagination::tailwind') }}</div>
    </div>
@endif

        </div>
    </div>
