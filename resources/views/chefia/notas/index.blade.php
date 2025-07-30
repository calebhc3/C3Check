<x-slot name="header">
    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 leading-tight">
        Aprovação de Notas
    </h2>
</x-slot>

<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <!-- Filtros -->
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
            <div class="flex flex-wrap items-center gap-4">
                <div>
                    <x-input-label for="filter-type" value="Tipo de Nota" />
                    <select id="filter-type" class="border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                        <option value="">Todos</option>
                        <option value="clinica">Clínica</option>
                        <option value="medico">Médico</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="filter-status" value="Status" />
                    <select id="filter-status" class="border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                        <option value="pendente">Pendentes</option>
                        <option value="aprovada">Aprovadas</option>
                        <option value="rejeitada">Rejeitadas</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <x-primary-button class="ml-0">Filtrar</x-primary-button>
                </div>
                <x-secondary-button class="ml-2" onclick="window.location.reload()">
                    <i class="fas fa-sync-alt mr-1"></i> Atualizar
                </x-secondary-button>

            </div>
        </div>

        <!-- Notas Pendentes -->
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                    Notas Pendentes de Aprovação
                    <span class="ml-2 px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                        {{ $notasPendentes->count() }}
                    </span>
                </h3>
            </div>

            @if ($notasPendentes->isEmpty())
                <div class="p-6 text-gray-600 dark:text-gray-300">
                    Nenhuma nota pendente de aprovação.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Prestador</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Valor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Vencimento</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Detalhes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Responsável</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($notasPendentes as $nota)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $tipoNota = [
                                        'clinica' => ['bg' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200', 'label' => 'Clínica'],
                                        'medico' => ['bg' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200', 'label' => 'Médico'],
                                        'prestador' => ['bg' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200', 'label' => 'Prestador'],
                                    ][$nota->tipo_nota] ?? ['bg' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200', 'label' => ucfirst($nota->tipo_nota)];
                                @endphp

                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $tipoNota['bg'] }}">
                                    {{ $tipoNota['label'] }}
                                </span>
                            </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $nota->tipo_nota === 'clinica' ? $nota->prestador : $nota->med_nome }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $nota->cnpj ?? ($nota->tipo_nota === 'medico' ? $nota->med_telefone : '-') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        R$ {{ number_format($nota->tipo_nota === 'clinica' ? $nota->valor_total : $nota->med_valor_total_final, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($nota->vencimento_original)->format('d/m/Y') }}
                                        @if($nota->vencimento_prorrogado)
                                            <span class="text-xs text-yellow-600 dark:text-yellow-400">(prorrogado)</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        @if($nota->tipo_nota === 'clinica')
                                            <div>Taxa Correio: R$ {{ number_format($nota->valor_taxa_correio, 2, ',', '.') }}</div>
                                            <div>Entregue em: {{ $nota->data_entregue_financeiro ? \Carbon\Carbon::parse($nota->data_entregue_financeiro)->format('d/m/Y') : '-' }}</div>
                                        @else
                                            <div>Deslocamento: {{ $nota->med_deslocamento ? 'Sim' : 'Não' }}</div>
                                            <div>Almoço: {{ $nota->med_cobrou_almoco ? 'Sim' : 'Não' }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $nota->user->name ?? '-' }}
                                        <div class="text-xs">{{ $nota->created_at->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <button onclick="openNoteModal('{{ $nota->id }}')" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                        <form action="{{ route('chefia.notas.aprovar', $nota) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                                <i class="fas fa-check"></i> Aprovar
                                            </button>
                                        </form>
                                        <button onclick="openRejectModal('{{ route('chefia.notas.rejeitar', $nota) }}')" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            <i class="fas fa-times"></i> Rejeitar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                        {{ $notasPendentes->links() }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Histórico de Notas -->
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                    Histórico de Aprovações
                    <span class="ml-2 px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                        {{ $historicoNotas->count() }}
                    </span>
                </h3>
            </div>

            @if ($historicoNotas->isEmpty())
                <div class="p-6 text-gray-600 dark:text-gray-300">
                    Nenhuma nota aprovada/rejeitada ainda.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Prestador</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Valor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Observações</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Visualizar</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($historicoNotas as $nota)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $tipoNota = [
                                                'clinica' => ['bg' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200', 'label' => 'Clínica'],
                                                'medico' => ['bg' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200', 'label' => 'Médico'],
                                                'prestador' => ['bg' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200', 'label' => 'Prestador'],
                                            ][$nota->tipo_nota] ?? ['bg' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200', 'label' => ucfirst($nota->tipo_nota)];
                                        @endphp

                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $tipoNota['bg'] }}">
                                            {{ $tipoNota['label'] }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $nota->tipo_nota === 'clinica' ? $nota->prestador : $nota->med_nome }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        R$ {{ number_format($nota->tipo_nota === 'clinica' ? $nota->valor_total : $nota->med_valor_total_final, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $nota->status === 'aprovada' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                            {{ ucfirst($nota->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($nota->aprovado_chefia_em)->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        @if($nota->status === 'rejeitada')
                                            <div class="text-red-600 dark:text-red-400">
                                                <strong>Motivo:</strong> {{ $nota->motivo_rejeicao_chefia }}
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <button onclick="openNoteModal('{{ $nota->id }}')" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                        {{ $historicoNotas->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de Rejeição -->
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-300"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mt-3">Confirmar Rejeição</h3>
            <div class="mt-2 px-4 py-3">
                <p class="text-sm text-gray-500 dark:text-gray-300">
                    Por favor, informe o motivo da rejeição desta nota fiscal.
                </p>
            </div>
            <form id="rejectForm" method="POST" class="mt-4">
                @csrf
                <div class="mb-4">
                    <textarea name="motivo_rejeicao" rows="4" class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white dark:border-gray-600" 
                              required minlength="10" placeholder="Digite o motivo da rejeição (mínimo 10 caracteres)..."></textarea>
                </div>
                <div class="mt-4 flex justify-between">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500 transition">
                        <i class="fas fa-times mr-2"></i> Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                        <i class="fas fa-check mr-2"></i> Confirmar Rejeição
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Visualização -->
<div id="noteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detalhes da Nota</h3>
            <button onclick="closeNoteModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="noteModalContent" class="mt-4">
            <!-- Conteúdo será carregado via AJAX -->
        </div>
    </div>
</div>

<script>
    // Alternar entre modais
    function openRejectModal(formAction) {
        document.getElementById('rejectForm').action = formAction;
        document.getElementById('rejectModal').classList.remove('hidden');
    }
    
    function closeModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }

    // Visualizar nota
    function openNoteModal(noteId) {
        fetch(`/chefia/notas/${noteId}/detalhes`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('noteModalContent').innerHTML = html;
                document.getElementById('noteModal').classList.remove('hidden');
            });
    }
    
    function closeNoteModal() {
        document.getElementById('noteModal').classList.add('hidden');
    }

    // Fechar modais ao clicar fora
    window.onclick = function(event) {
        const rejectModal = document.getElementById('rejectModal');
        const noteModal = document.getElementById('noteModal');
        
        if (event.target === rejectModal) {
            closeModal();
        }
        if (event.target === noteModal) {
            closeNoteModal();
        }
    }
</script>