<x-slot name="header">
    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 leading-tight">
        Notas de Pagamento
    </h2>
</x-slot>

<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
            <a href="{{ route('notas.create') }}" class="mb-4 inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-800">
                Nova Nota
            </a>
            
            <form method="GET" action="{{ route('dashboard') }}" class="mb-6 flex flex-wrap gap-4 items-end">
                <div>
                    <label for="cnpj" class="block text-sm font-medium text-gray-700 dark:text-gray-300">CNPJ</label>
                    <input type="text" name="cnpj" id="cnpj" value="{{ request('cnpj') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="numero_nf" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número NF</label>
                    <input type="text" name="numero_nf" id="numero_nf" value="{{ request('numero_nf') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select name="status" id="status" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Todos</option>
                        @foreach (['lancada', 'aprovada_chefia', 'confirmada_financeiro', 'rejeitada', 'finalizada'] as $status)
                            <option value="{{ $status }}" @if(request('status') === $status) selected @endif>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="self-end">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 dark:hover:bg-green-800">
                        Filtrar
                    </button>
                </div>
            </form>

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
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Comprovante</th>
                                <th class="px-4 py-3">Motivo Rejeição</th>
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
                                    <td class="px-4 py-2">
                                        {{ $nota->vencimento_prorrogado 
                                            ? \Carbon\Carbon::parse($nota->vencimento_prorrogado)->format('d/m/Y') 
                                            : ($nota->vencimento_original 
                                                ? \Carbon\Carbon::parse($nota->vencimento_original)->format('d/m/Y') 
                                                : '-') }}
                                    </td>
                                    <td class="px-4 py-2">
                                        @php
                                            $statusColors = [
                                                'lancada' => 'bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-100',
                                                'aprovada_chefia' => 'bg-blue-200 text-blue-800 dark:bg-blue-600 dark:text-blue-100',
                                                'confirmada_financeiro' => 'bg-green-200 text-green-800 dark:bg-green-600 dark:text-green-100',
                                                'rejeitada' => 'bg-red-200 text-red-800 dark:bg-red-600 dark:text-red-100',
                                                'finalizada' => 'bg-purple-200 text-purple-800 dark:bg-purple-600 dark:text-purple-100'
                                            ];
                                            $statusLabel = ucfirst(str_replace('_', ' ', $nota->status));
                                        @endphp
                                        <span class="px-2 py-1 text-sm font-semibold rounded-full {{ $statusColors[$nota->status] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                    @if($nota->comprovante_path)
                                        <a href="{{ route('notas.comprovante', $nota) }}" target="_blank">Clique aqui</a>
                                    @else
                                        -
                                    @endif

                                    </td>
                                    <td class="px-4 py-2 text-sm text-red-600 dark:text-red-400">
                                        @if($nota->status === 'rejeitada' && $nota->motivo_rejeicao_chefia)
                                            {{ $nota->motivo_rejeicao_chefia }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center space-x-2">
                                            <button onclick="openNoteModal('{{ $nota->id }}')" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                        <a href="{{ route('notas.edit', $nota) }}" 
                                           class="text-blue-600 hover:underline dark:text-blue-400">Editar</a>
                                        @if($nota->status !== 'finalizada')
                                            <form action="{{ route('notas.destroy', $nota) }}" method="POST" 
                                                  class="inline-block" 
                                                  onsubmit="return confirm('Tem certeza que deseja excluir?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-red-600 hover:underline dark:text-red-400">
                                                    Excluir
                                                </button>
                                            </form>
                                        @endif
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
                <div class="overflow-x-auto mb-8">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-700 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                                <th class="px-4 py-3">Médico</th>
                                <th class="px-4 py-3">NF</th>
                                <th class="px-4 py-3">Valor</th>
                                <th class="px-4 py-3">Vencimento</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Comprovante</th>
                                <th class="px-4 py-3">Motivo Rejeição</th>
                                <th class="px-4 py-3 text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-900 dark:text-gray-100">
                            @foreach ($notasMedicos as $nota)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <td class="px-4 py-2">{{ $nota->med_nome }}</td>
                                    <td class="px-4 py-2">{{ $nota->numero_nf }}</td>
                                    <td class="px-4 py-2">R$ {{ number_format($nota->valor_total_final, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2">
                                        {{ $nota->vencimento_prorrogado 
                                            ? \Carbon\Carbon::parse($nota->vencimento_prorrogado)->format('d/m/Y') 
                                            : ($nota->vencimento_original 
                                                ? \Carbon\Carbon::parse($nota->vencimento_original)->format('d/m/Y') 
                                                : '-') }}
                                    </td>
                                    <td class="px-4 py-2">
                                        @php
                                            $statusColors = [
                                                'lancada' => 'bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-100',
                                                'aprovada_chefia' => 'bg-blue-200 text-blue-800 dark:bg-blue-600 dark:text-blue-100',
                                                'confirmada_financeiro' => 'bg-green-200 text-green-800 dark:bg-green-600 dark:text-green-100',
                                                'rejeitada' => 'bg-red-200 text-red-800 dark:bg-red-600 dark:text-red-100',
                                                'finalizada' => 'bg-purple-200 text-purple-800 dark:bg-purple-600 dark:text-purple-100'
                                            ];
                                            $statusLabel = ucfirst(str_replace('_', ' ', $nota->status));
                                        @endphp
                                        <span class="px-2 py-1 text-sm font-semibold rounded-full {{ $statusColors[$nota->status] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        @if($nota->status === 'finalizada' && $nota->comprovante_path)
                                            <a href="{{ route('notas.comprovante', $nota) }}" target="_blank" 
                                               class="text-blue-600 hover:underline dark:text-blue-400">
                                                Visualizar
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm text-red-600 dark:text-red-400">
                                        @if($nota->status === 'rejeitada' && $nota->motivo_rejeicao_chefia)
                                            {{ $nota->motivo_rejeicao_chefia }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center space-x-2">
                                        <button onclick="openNoteModal('{{ $nota->id }}')" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                        <a href="{{ route('notas.edit', $nota) }}" 
                                           class="text-blue-600 hover:underline dark:text-blue-400">Editar</a>
                                        @if($nota->status !== 'finalizada')
                                            <form action="{{ route('notas.destroy', $nota) }}" method="POST" 
                                                  class="inline-block" 
                                                  onsubmit="return confirm('Tem certeza que deseja excluir?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-red-600 hover:underline dark:text-red-400">
                                                    Excluir
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $notasMedicos->links() }}</div>
                </div>
            @endif

            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Notas de Prestador</h3>
            @if ($notasPrestadores->isEmpty())
                <p class="text-gray-600 dark:text-gray-300">Nenhuma nota de prestador cadastrada.</p>
            @else
                <div class="overflow-x-auto mb-8">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-700 text-left text-sm font-semibold text-gray-700 dark:text-gray-200">
                                <th class="px-4 py-3">Prestador</th>
                                <th class="px-4 py-3">CNPJ</th>
                                <th class="px-4 py-3">NF</th>
                                <th class="px-4 py-3">Valor Total</th>
                                <th class="px-4 py-3">Vencimento</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Comprovante</th>
                                <th class="px-4 py-3">Motivo Rejeição</th>
                                <th class="px-4 py-3 text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-900 dark:text-gray-100">
                            @foreach ($notasPrestadores as $nota)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <td class="px-4 py-2">{{ $nota->prestador }}</td>
                                    <td class="px-4 py-2">{{ $nota->cnpj ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $nota->numero_nf }}</td>
                                    <td class="px-4 py-2">R$ {{ number_format($nota->valor_total, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2">
                                        {{ $nota->vencimento_prorrogado 
                                            ? \Carbon\Carbon::parse($nota->vencimento_prorrogado)->format('d/m/Y') 
                                            : ($nota->vencimento_original 
                                                ? \Carbon\Carbon::parse($nota->vencimento_original)->format('d/m/Y') 
                                                : '-') }}
                                    </td>
                                    <td class="px-4 py-2">
                                        @php
                                            $statusColors = [
                                                'lancada' => 'bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-100',
                                                'aprovada_chefia' => 'bg-blue-200 text-blue-800 dark:bg-blue-600 dark:text-blue-100',
                                                'confirmada_financeiro' => 'bg-green-200 text-green-800 dark:bg-green-600 dark:text-green-100',
                                                'rejeitada' => 'bg-red-200 text-red-800 dark:bg-red-600 dark:text-red-100',
                                                'finalizada' => 'bg-purple-200 text-purple-800 dark:bg-purple-600 dark:text-purple-100'
                                            ];
                                            $statusLabel = ucfirst(str_replace('_', ' ', $nota->status));
                                        @endphp
                                        <span class="px-2 py-1 text-sm font-semibold rounded-full {{ $statusColors[$nota->status] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        @if($nota->comprovante_path)
                                            <a href="{{ route('notas.comprovante', $nota) }}" target="_blank">Clique aqui</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm text-red-600 dark:text-red-400">
                                        @if($nota->status === 'rejeitada' && $nota->motivo_rejeicao_chefia)
                                            {{ $nota->motivo_rejeicao_chefia }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center space-x-2">
                                                                                <button onclick="openNoteModal('{{ $nota->id }}')" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                        <a href="{{ route('notas.edit', $nota) }}" 
                                        class="text-blue-600 hover:underline dark:text-blue-400">Editar</a>
                                        @if($nota->status !== 'finalizada')
                                            <form action="{{ route('notas.destroy', $nota) }}" method="POST" 
                                                class="inline-block" 
                                                onsubmit="return confirm('Tem certeza que deseja excluir?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-red-600 hover:underline dark:text-red-400">
                                                    Excluir
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $notasPrestadores->links() }}</div>
                </div>
            @endif
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Total de Notas: {{ $notasClinicas->count() + $notasMedicos->count() + $notasPrestadores->count() }}
            </p>    
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