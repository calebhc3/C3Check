<x-slot name="header">
    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 leading-tight">
        Aprovação de Notas - Financeiro
    </h2>
</x-slot>

<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
            @if ($notasPendentes->isEmpty())
                <p class="text-gray-600 dark:text-gray-300">Nenhuma nota pendente de processamento.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-700 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                                <th class="px-4 py-3">Prestador</th>
                                <th class="px-4 py-3">CNPJ</th>
                                <th class="px-4 py-3">Valor Líquido</th>
                                <th class="px-4 py-3">Vencimento</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Aprovado Chefia</th>
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
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 text-sm font-semibold rounded-full bg-blue-200 text-blue-800 dark:bg-blue-600 dark:text-blue-100">
                                            {{ ucfirst(str_replace('_', ' ', $nota->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($nota->aprovado_chefia_em)->format('d/m/Y') }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <button onclick="openPaymentModal('{{ route('financeiro.notas.aceitar', $nota) }}')" 
                                                class="text-green-600 hover:underline">
                                            Registrar Pagamento
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $notasPendentes->links() }}</div>
                </div>
            @endif
        </div>

        <!-- Modal de Pagamento -->
        <div id="paymentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Registrar Pagamento</h3>
                    <form id="paymentForm" method="POST" enctype="multipart/form-data" class="mt-4">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comprovante (PDF/JPG/PNG)</label>
                            <input type="file" name="comprovante" required 
                                   class="w-full px-3 py-2 border rounded-lg text-gray-700 focus:outline-none dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observações</label>
                            <textarea name="observacao" rows="3" 
                                      class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none dark:bg-gray-700 dark:text-white dark:border-gray-600"
                                      placeholder="Informações adicionais sobre o pagamento..."></textarea>
                        </div>
                        <div class="mt-4 flex justify-between">
                            <button type="button" onclick="closeModal()" 
                                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500">
                                Cancelar
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                Confirmar Pagamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($historicoNotas->isEmpty())
            <p class="text-gray-600 dark:text-gray-300 mt-8">Nenhuma nota processada ainda.</p>
        @else
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mt-8 mb-2">Histórico de Pagamentos</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-700 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                            <th class="px-4 py-3">Prestador</th>
                            <th class="px-4 py-3">Valor</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Pagamento em</th>
                            <th class="px-4 py-3">Comprovante</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-900 dark:text-gray-100">
                        @foreach ($historicoNotas as $nota)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <td class="px-4 py-2">{{ $nota->prestador }}</td>
                                <td class="px-4 py-2">R$ {{ number_format($nota->valor_total, 2, ',', '.') }}</td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-1 text-sm font-semibold rounded-full bg-green-200 text-green-800 dark:bg-green-600 dark:text-green-100">
                                        Finalizada
                                    </span>
                                </td>
                                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($nota->confirmado_financeiro_em)->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-2">
                                    @if($nota->comprovante_path)
                                        <a href="{{ Storage::url($nota->comprovante_path) }}" target="_blank" 
                                           class="text-blue-600 hover:underline dark:text-blue-400">
                                            Visualizar
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4">{{ $historicoNotas->links('pagination::tailwind') }}</div>
            </div>
        @endif
    </div>
</div>

<script>
    function openPaymentModal(formAction) {
        document.getElementById('paymentForm').action = formAction;
        document.getElementById('paymentModal').classList.remove('hidden');
    }
    
    function closeModal() {
        document.getElementById('paymentModal').classList.add('hidden');
    }

    window.onclick = function(event) {
        const modal = document.getElementById('paymentModal');
        if (event.target === modal) {
            closeModal();
        }
    }
</script>