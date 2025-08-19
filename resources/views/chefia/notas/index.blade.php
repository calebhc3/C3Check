<x-slot name="header">
    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 leading-tight">
        Aprovação de Notas
    </h2>
</x-slot>

<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configurações comuns para os gráficos
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#6b7280',
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 12
                        },
                        padding: 12,
                        usePointStyle: true
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6b7280'
                        }
                    },
                    y: {
                        grid: {
                            color: '#e5e7eb'
                        },
                        ticks: {
                            color: '#6b7280'
                        }
                    }
                }
            };

            // Gráfico de Notas por Tipo
            new Chart(document.getElementById('notasPorTipoChart'), {
                type: 'bar',
                data: {
                    labels: ['Clínicas', 'Médicos', 'Prestadores'],
                    datasets: [{
                        label: 'Quantidade de Notas',
                        data: [
                            {{ $notasClinicas }},
                            {{ $notasMedicos }},
                            {{ $notasPrestadores }}
                        ],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.7)',
                            'rgba(139, 92, 246, 0.7)',
                            'rgba(16, 185, 129, 0.7)'
                        ],
                        borderColor: [
                            'rgba(59, 130, 246, 1)',
                            'rgba(139, 92, 246, 1)',
                            'rgba(16, 185, 129, 1)'
                        ],
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    ...chartOptions,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Gráfico de Notas por Região e UF
            const notasPorRegiaoData = {!! json_encode($notasPorRegiao) !!};
            
            const regioes = notasPorRegiaoData.map(item => item.regiao);
            const totaisPorRegiao = notasPorRegiaoData.map(item => item.total);
            const ufsPorRegiao = notasPorRegiaoData.map(item => 
                item.ufs.map(uf => `${uf.estado} (${uf.total})`).join(', ')
            );

            new Chart(document.getElementById('notasPorRegiaoChart'), {
                type: 'bar',
                data: {
                    labels: regioes,
                    datasets: [{
                        label: 'Total de Notas',
                        data: totaisPorRegiao,
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    ...chartOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Quantidade de Notas',
                                color: '#6b7280'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Regiões',
                                color: '#6b7280'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                afterLabel: function(context) {
                                    const index = context.dataIndex;
                                    return `UF's: ${ufsPorRegiao[index]}`;
                                }
                            }
                        },
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Gráficos secundários (Glosas e Taxas)
            const secondaryCharts = [
                {
                    id: 'glosasPorRegiaoChart',
                    title: 'Valor Total (R$)',
                    data: {!! json_encode($glosasPorRegiao->pluck('total_glosa')) !!},
                    labels: {!! json_encode($glosasPorRegiao->pluck('regiao')) !!},
                    color: 'rgba(239, 68, 68, 0.7)',
                    borderColor: 'rgba(239, 68, 68, 1)'
                },
                {
                    id: 'glosasPorUFChart',
                    title: 'Quantidade',
                    data: {!! json_encode($glosasPorUF->pluck('quantidade')) !!},
                    labels: {!! json_encode($glosasPorUF->pluck('estado')) !!},
                    color: 'rgba(249, 115, 22, 0.7)',
                    borderColor: 'rgba(249, 115, 22, 1)'
                },
                {
                    id: 'taxasPorRegiaoChart',
                    title: 'Valor Total (R$)',
                    data: {!! json_encode($taxasPorRegiao->pluck('total_taxa')) !!},
                    labels: {!! json_encode($taxasPorRegiao->pluck('regiao')) !!},
                    color: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgba(59, 130, 246, 1)'
                },
                {
                    id: 'taxasPorUFChart',
                    title: 'Quantidade',
                    data: {!! json_encode($taxasPorUF->pluck('quantidade')) !!},
                    labels: {!! json_encode($taxasPorUF->pluck('estado')) !!},
                    color: 'rgba(16, 185, 129, 0.7)',
                    borderColor: 'rgba(16, 185, 129, 1)'
                }
            ];

            secondaryCharts.forEach(chartConfig => {
                new Chart(document.getElementById(chartConfig.id), {
                    type: 'bar',
                    data: {
                        labels: chartConfig.labels,
                        datasets: [{
                            label: chartConfig.title,
                            data: chartConfig.data,
                            backgroundColor: chartConfig.color,
                            borderColor: chartConfig.borderColor,
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        ...chartOptions,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });

            // Gráfico Comparativo Regiões
            new Chart(document.getElementById('comparativoRegioesChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($regioesMaisGlosasTaxas->pluck('regiao')) !!},
                    datasets: [
                        {
                            label: 'Valor Glosas (R$)',
                            data: {!! json_encode($regioesMaisGlosasTaxas->pluck('total_glosa')) !!},
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: 'Valor Taxas (R$)',
                            data: {!! json_encode($regioesMaisGlosasTaxas->pluck('total_taxa')) !!},
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    ...chartOptions,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
        </script>
        @endpush

        <!-- Filtros -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <form method="GET" action="{{ route('dashboard') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="cnpj" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CNPJ</label>
                    <input type="text" name="cnpj" id="cnpj" value="{{ request('cnpj') }}" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600">
                </div>

                <div>
                    <label for="numero_nf" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Número NF</label>
                    <input type="text" name="numero_nf" id="numero_nf" value="{{ request('numero_nf') }}" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select name="status" id="status" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600">
                        <option value="">Todos</option>
                        @foreach (['lancada', 'aprovada_chefia', 'confirmada_financeiro', 'rejeitada', 'finalizada'] as $status)
                            <option value="{{ $status }}" @if(request('status') === $status) selected @endif>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="tipo_nota" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo da nota</label>
                    <select name="tipo_nota" id="tipo_nota" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600">
                        <option value="">Todos</option>
                        @foreach (['medico', 'clinica', 'prestador'] as $tipo_nota)
                            <option value="{{ $tipo_nota }}" @if(request('tipo_nota') === $tipo_nota) selected @endif>
                                {{ ucfirst(str_replace('_', ' ', $tipo_nota)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="md:col-span-4 flex justify-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                        Filtrar
                    </button>
                    <x-secondary-button onclick="window.location.href='{{ route('dashboard') }}'">
                        <i class="fas fa-sync-alt mr-1"></i> Limpar
                    </x-secondary-button>
                </div>
            </form>
        </div>

        <!-- Notas Pendentes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mt-6">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
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
                                            <i class="fas fa-eye mr-1">ver</i>
                                        </button>
                                        <form action="{{ route('chefia.notas.aprovar', $nota) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                                <i class="fas fa-check mr-1">aprovar</i>
                                            </button>
                                        </form>
                                        <button onclick="openRejectModal('{{ route('chefia.notas.rejeitar', $nota) }}')" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            <i class="fas fa-times mr-1">reprovar
                                                
                                            </i>
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
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mt-6">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                    Histórico de Aprovações
                    <span class="ml-2 px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                        {{ $historicoNotas->total() }}
                    </span>
                </h3>
                
                <!-- Filtros para o Histórico -->
                <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap gap-4 items-end">
                    <!-- Manter filtros principais como hidden -->
                    @if(request('cnpj'))
                        <input type="hidden" name="cnpj" value="{{ request('cnpj') }}">
                    @endif
                    @if(request('numero_nf'))
                        <input type="hidden" name="numero_nf" value="{{ request('numero_nf') }}">
                    @endif
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @if(request('tipo_nota'))
                        <input type="hidden" name="tipo_nota" value="{{ request('tipo_nota') }}">
                    @endif
                    @if(request('periodo'))
                        <input type="hidden" name="periodo" value="{{ request('periodo') }}">
                    @endif
                    
                    <div>
                        <label for="historico_cnpj" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CNPJ</label>
                        <input type="text" name="historico_cnpj" id="historico_cnpj" value="{{ request('historico_cnpj') }}" 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600">
                    </div>

                    <div>
                        <label for="historico_numero_nf" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Número NF</label>
                        <input type="text" name="historico_numero_nf" id="historico_numero_nf" value="{{ request('historico_numero_nf') }}" 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600">
                    </div>

                    <div>
                        <label for="historico_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select name="historico_status" id="historico_status" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600">
                            <option value="">Todos</option>
                            <option value="aprovada" @if(request('historico_status') === 'aprovada') selected @endif>Aprovada</option>
                            <option value="rejeitada" @if(request('historico_status') === 'rejeitada') selected @endif>Rejeitada</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="historico_tipo_nota" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo da nota</label>
                        <select name="historico_tipo_nota" id="historico_tipo_nota" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600">
                            <option value="">Todos</option>
                            @foreach (['medico', 'clinica', 'prestador'] as $tipo_nota)
                                <option value="{{ $tipo_nota }}" @if(request('historico_tipo_nota') === $tipo_nota) selected @endif>
                                    {{ ucfirst(str_replace('_', ' ', $tipo_nota)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                        <a href="{{ route('dashboard', array_merge(request()->except(['historico_cnpj', 'historico_numero_nf', 'historico_status', 'historico_tipo_nota', 'historico_page']), ['historico_page' => 1])) }}" 
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500 transition">
                            <i class="fas fa-times mr-1"></i> Limpar
                        </a>
                    </div>
                </form>
            </div>

            @if ($historicoNotas->isEmpty())
                <div class="p-6 text-gray-600 dark:text-gray-300">
                    Nenhuma nota encontrada com os filtros aplicados.
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data Aprovação</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Observações</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
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
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $nota->cnpj ?? ($nota->tipo_nota === 'medico' ? $nota->med_telefone : '-') }}
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
                                            <i class="fas fa-eye mr-1">ver</i>
                                        </button>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                        {{ $historicoNotas->appends([
                            'cnpj' => request('cnpj'),
                            'numero_nf' => request('numero_nf'),
                            'status' => request('status'),
                            'tipo_nota' => request('tipo_nota'),
                            'periodo' => request('periodo'),
                            'historico_cnpj' => request('historico_cnpj'),
                            'historico_numero_nf' => request('historico_numero_nf'),
                            'historico_status' => request('historico_status'),
                            'historico_tipo_nota' => request('historico_tipo_nota')
                        ])->links() }}
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Seção de Gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Widget Principal -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 col-span-1 lg:col-span-2">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300">
                            <i class="fas fa-file-invoice fa-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Notas Lançadas</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ $totalNotasPeriodo }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Período: {{ ucfirst($periodoSelecionado) }}
                            </p>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <select name="periodo" id="periodo" 
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-600 text-sm"
                                onchange="this.form.submit()">
                            <option value="hoje" @if($periodoSelecionado === 'hoje') selected @endif>Hoje</option>
                            <option value="semana" @if($periodoSelecionado === 'semana') selected @endif>Esta Semana</option>
                            <option value="mes" @if($periodoSelecionado === 'mes') selected @endif>Este Mês</option>
                            <option value="ano" @if($periodoSelecionado === 'ano') selected @endif>Este Ano</option>
                            <option value="todos" @if($periodoSelecionado === 'todos') selected @endif>Todos</option>
                        </select>
                        
                        @if(request('cnpj'))
                            <input type="hidden" name="cnpj" value="{{ request('cnpj') }}">
                        @endif
                        @if(request('numero_nf'))
                            <input type="hidden" name="numero_nf" value="{{ request('numero_nf') }}">
                        @endif
                    </form>
                </div>

                <div class="h-80">
                    <canvas id="notasPorRegiaoChart"></canvas>
                </div>
            </div>

            <!-- Gráfico de Notas por Tipo -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200">Distribuição por Tipo</h3>
                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                        Total: {{ $notasClinicas + $notasMedicos + $notasPrestadores }}
                    </span>
                </div>
                <div class="h-64">
                    <canvas id="notasPorTipoChart"></canvas>
                </div>
            </div>

            <!-- Gráfico Comparativo -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200">Glosas vs Taxas por Região</h3>
                    <div class="flex gap-2">
                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                            Glosas: R$ {{ number_format($regioesMaisGlosasTaxas->sum('total_glosa'), 2, ',', '.') }}
                        </span>
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            Taxas: R$ {{ number_format($regioesMaisGlosasTaxas->sum('total_taxa'), 2, ',', '.') }}
                        </span>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="comparativoRegioesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Seção de Gráficos Secundários -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Gráfico de Glosas por Região -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200">Glosas por Região</h3>
                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                        Total: R$ {{ number_format($glosasPorRegiao->sum('total_glosa'), 2, ',', '.') }}
                    </span>
                </div>
                <div class="h-64">
                    <canvas id="glosasPorRegiaoChart"></canvas>
                </div>
            </div>

            <!-- Gráfico de Glosas por UF -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200">Glosas por UF</h3>
                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                        Total: {{ $glosasPorUF->sum('quantidade') }} glosas
                    </span>
                </div>
                <div class="h-64">
                    <canvas id="glosasPorUFChart"></canvas>
                </div>
            </div>

            <!-- Gráfico de Taxas por Região -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200">Taxas por Região</h3>
                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        Total: R$ {{ number_format($taxasPorRegiao->sum('total_taxa'), 2, ',', '.') }}
                    </span>
                </div>
                <div class="h-64">
                    <canvas id="taxasPorRegiaoChart"></canvas>
                </div>
            </div>

            <!-- Gráfico de Taxas por UF -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200">Taxas por UF</h3>
                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        Total: {{ $taxasPorUF->sum('quantidade') }} taxas
                    </span>
                </div>
                <div class="h-64">
                    <canvas id="taxasPorUFChart"></canvas>
                </div>
            </div>
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
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500 transition">
                        <i class="fas fa-times mr-2"></i> Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
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