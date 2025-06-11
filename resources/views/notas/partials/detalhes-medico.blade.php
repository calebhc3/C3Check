<div class="space-y-8 p-4 text-sm text-gray-800 dark:text-gray-100">

    {{-- Seção: Informações da Nota --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
        <h4 class="text-xl font-semibold mb-4 border-b border-gray-200 dark:border-gray-600 pb-2">📄 Informações da Nota Médica</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><span class="font-bold">Número da NF:</span> {{ $nota->numero_nf }}</div>
            <div><span class="font-bold">Nome do Médico:</span> {{ $nota->med_nome }}</div>
            <div><span class="font-bold">Telefone:</span> {{ $nota->med_telefone ?? '—' }}</div>
            <div><span class="font-bold">E-mail:</span> {{ $nota->med_email ?? '—' }}</div>
            <div><span class="font-bold">Cliente Atendido:</span> {{ $nota->med_cliente_atendido }}</div>
            <div><span class="font-bold">Local:</span> {{ $nota->med_local ?? '—' }}</div>
            <div><span class="font-bold">Vencimento Original:</span> {{ \Carbon\Carbon::parse($nota->vencimento_original)->format('d/m/Y') }}</div>
            <div><span class="font-bold">Vencimento Prorrogado:</span> {{ $nota->vencimento_prorrogado ? \Carbon\Carbon::parse($nota->vencimento_prorrogado)->format('d/m/Y') : '—' }}</div>
            <div><span class="font-bold">Mês de Referência:</span> {{ $nota->mes ?? '—' }}</div>
            <p><strong>Valor Total Original:</strong> R$ {{ number_format($nota->med_valor_total, 2, ',', '.') }}</p>
            @if ($nota->glosa_valor)
                <p><strong>Valor Glosado:</strong> R$ {{ number_format($nota->glosa_valor, 2, ',', '.') }}</p>
                <p><strong>Valor Final:</strong> R$ {{ number_format($nota->med_valor_total - $nota->glosa_valor, 2, ',', '.') }}</p>
            @endif
            <div><span class="font-bold">Tipo de Pagamento:</span> {{ ucfirst($nota->tipo_pagamento) ?? '—' }}</div>
        </div>

        @if ($nota->dados_bancarios)
            <div class="mt-4">
                <span class="font-bold">🏦 Dados Bancários:</span>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $nota->dados_bancarios }}</p>
            </div>
        @endif

        @if ($nota->observacao)
            <div class="mt-4">
                <span class="font-bold">📝 Observação:</span>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $nota->observacao }}</p>
            </div>
        @endif

        @if ($nota->arquivo_nf)
            <div class="mt-4">
                <span class="font-bold">📎 Arquivo da NF:</span>
                <a href="{{ Storage::url($nota->arquivo_nf) }}" target="_blank" class="text-indigo-600 hover:underline block mt-1">
                    Visualizar PDF
                </a>
            </div>
        @endif
    </div>

    {{-- Seção: Horários do Médico --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
        <h4 class="text-xl font-semibold mb-4 border-b border-gray-200 dark:border-gray-600 pb-2">⏱️ Horários Registrados</h4>
        @php $horarios = is_array($nota->med_horarios) ? $nota->med_horarios : json_decode($nota->med_horarios, true); @endphp

        @if (!empty($horarios))
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border border-gray-300">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100">
                    <tr>
                        <th class="p-2 border">Data</th>
                        <th class="p-2 border">Entrada</th>
                        <th class="p-2 border">Saída Almoço</th>
                        <th class="p-2 border">Retorno Almoço</th>
                        <th class="p-2 border">Saída</th>
                        <th class="p-2 border">Valor Hora</th>
                        <th class="p-2 border">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($horarios as $horario)
                        @if ($horario['data'] && $horario['entrada'] && $horario['saida'])
                        <tr class="text-gray-700 dark:text-gray-200">
                            <td class="p-2 border">{{ \Carbon\Carbon::parse($horario['data'])->format('d/m/Y') }}</td>
                            <td class="p-2 border">{{ $horario['entrada'] }}</td>
                            <td class="p-2 border">{{ $horario['saida_almoco'] ?? '—' }}</td>
                            <td class="p-2 border">{{ $horario['retorno_almoco'] ?? '—' }}</td>
                            <td class="p-2 border">{{ $horario['saida'] }}</td>
                            <td class="p-2 border">R$ {{ number_format($horario['valor_hora'], 2, ',', '.') }}</td>
                            <td class="p-2 border font-semibold">R$ {{ number_format($horario['total'], 2, ',', '.') }}</td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <p class="text-gray-500 dark:text-gray-400">Nenhum horário registrado nesta nota.</p>
        @endif
    </div>

    {{-- Seção: Extras --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
        <h4 class="text-xl font-semibold mb-4 border-b border-gray-200 dark:border-gray-600 pb-2">💸 Extras</h4>
        <ul class="text-sm space-y-2">
            <li><span class="font-bold">Deslocamento:</span> {{ $nota->med_deslocamento ? 'Sim (R$ ' . number_format($nota->med_valor_deslocamento, 2, ',', '.') . ')' : 'Não' }}</li>
            <li><span class="font-bold">Almoço Cobrado:</span> {{ $nota->med_cobrou_almoco ? 'Sim (R$ ' . number_format($nota->med_valor_almoco, 2, ',', '.') . ')' : 'Não' }}</li>
            <li><span class="font-bold">Reembolso Correios:</span> {{ $nota->med_reembolso_correios ? 'Sim (R$ ' . number_format($nota->med_valor_correios, 2, ',', '.') . ')' : 'Não' }}</li>
        </ul>
    </div>

</div>
