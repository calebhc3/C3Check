<div class="space-y-8 p-4 text-sm text-gray-800 dark:text-gray-100">

    {{-- Seção: Informações da Nota --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
        <h4 class="text-xl font-semibold mb-4 border-b border-gray-200 dark:border-gray-600 pb-2">📄 Informações da Nota</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><span class="font-bold">Número da NF:</span> {{ $nota->numero_nf }}</div>
            <div><span class="font-bold">Prestador:</span> {{ $nota->prestador }}</div>
            <div><span class="font-bold">CNPJ:</span> {{ $nota->cnpj }}</div>
            @if ($nota->estado || $nota->cidade || $nota->regiao)
            <div><span class="font-bold">Estado:</span> {{ $nota->estado }}</div>
            <div><span class="font-bold">Cidade:</span> {{ $nota->cidade }}</div>
            <div><span class="font-bold">Região:</span> {{ $nota->regiao }}</div>
            @endif
            <div><span class="font-bold">Vencimento Original:</span> {{ \Carbon\Carbon::parse($nota->vencimento_original)->format('d/m/Y') }}</div>
            <div><span class="font-bold">Data Entregue ao Financeiro:</span> {{ \Carbon\Carbon::parse($nota->data_entregue_financeiro)->format('d/m/Y') }}</div>
            <div><span class="font-bold">Mês de Referência:</span> {{ $nota->mes }}</div>
            <div><span class="font-bold">Vencimento Prorrogado:</span> {{ $nota->vencimento_prorrogado ? \Carbon\Carbon::parse($nota->vencimento_prorrogado)->format('d/m/Y') : '—' }}</div>
            <div><span class="font-bold">Taxa de Correio:</span> 
                @if ($nota->taxa_correio)
                    Sim (R$ {{ number_format($nota->valor_taxa_correio, 2, ',', '.') }})
                @else
                    Não
                @endif
            </div>
            <p><strong>Valor Total Original:</strong> R$ {{ number_format($nota->valor_total, 2, ',', '.') }}</p>
            @if ($nota->glosa_valor && $nota->glosa_valor > 0)
                <div class="mt-4 bg-red-100 dark:bg-red-900 p-4 rounded-md border border-red-300 dark:border-red-700">
                    <h5 class="text-md font-bold text-red-800 dark:text-red-200 mb-2">⚠️ Glosa Aplicada</h5>
                    <p class="text-sm text-red-800 dark:text-red-100"><strong>Valor Glosado:</strong> R$ {{ number_format($nota->glosa_valor, 2, ',', '.') }}</p>

                    @if ($nota->glosa_motivo)
                        <p class="text-sm mt-1 italic text-red-700 dark:text-red-300">“{{ $nota->glosa_motivo }}”</p>
                    @endif

                    <p class="text-md mt-3 font-semibold text-red-900 dark:text-red-100">
                        💸 Valor Final Após Glosa: 
                        <span class="text-green-700 dark:text-green-300">
                            R$ {{ number_format($nota->valor_total - $nota->glosa_valor, 2, ',', '.') }}
                        </span>
                    </p>
                </div>
            @endif
            

            <div><span class="font-bold">Tipo de Pagamento:</span> {{ ucfirst($nota->tipo_pagamento) }}</div>
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

        @if ($nota->arquivo_nf && count($nota->arquivo_nf) > 0)
            <div class="mt-4">
                <span class="font-bold">📎 Arquivos da NF:</span>
                <div class="mt-2 space-y-1">
                    @foreach ((array)$nota->arquivo_nf as $arquivo)
                        <a href="{{ Storage::url($arquivo) }}" 
                        target="_blank" 
                        class="text-indigo-600 hover:underline block text-sm">
                            {{ basename($arquivo) }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Seção: Clientes Atendidos --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
        <h4 class="text-xl font-semibold mb-4 border-b border-gray-200 dark:border-gray-600 pb-2">👥 Clientes Atendidos</h4>

        @forelse ($nota->notaclientes ?? [] as $cliente)
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-md mb-3 border border-gray-200 dark:border-gray-600">
                <div class="flex justify-between">
                    <span class="font-medium">{{ $cliente->cliente_atendido }}</span>
                    <span class="font-bold text-green-600 dark:text-green-400">R$ {{ number_format($cliente->valor, 2, ',', '.') }}</span>
                </div>
                @if ($cliente->observacao)
                    <p class="mt-2 text-xs text-gray-600 dark:text-gray-300 italic">"{{ $cliente->observacao }}"</p>
                @endif
            </div>
        @empty
            <p class="text-gray-500 dark:text-gray-400">Nenhum cliente registrado nesta nota.</p>
        @endforelse
    </div>
</div>
