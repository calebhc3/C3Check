<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 leading-tight">
            Editar Nota de Pagamento
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
@if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form action="{{ route('notas.update', $nota->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
    @csrf
    @method('PUT')

    {{-- Seção: Tipo de Nota --}}
    <div class="flex items-center justify-center mb-8">
        <div class="inline-flex rounded-md shadow-sm" role="group">
        <label class="px-6 py-3 text-sm font-medium rounded-l-lg border cursor-pointer
            {{ $nota->tipo_nota === 'clinica' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-gray-700 text-gray-900 border-gray-700' }}">
            <input type="radio" name="tipo_nota" value="clinica" class="hidden" {{ $nota->tipo_nota === 'clinica' ? 'checked' : '' }}>
            Clínica
        </label>

        <label class="px-6 py-3 text-sm font-medium rounded-r-lg border cursor-pointer
            {{ $nota->tipo_nota === 'medico' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-gray-700 text-gray-900 border-gray-700' }}">
            <input type="radio" name="tipo_nota" value="medico" class="hidden" {{ $nota->tipo_nota === 'medico' ? 'checked' : '' }}>
            Médico
        </label>
        </div>
    </div>

    {{-- Formulário Clínica --}}
    <div id="clinica-form" class="{{ $nota->tipo_nota === 'clinica' ? '' : 'hidden' }}">
        <div class="border-b border-gray-300 pb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Informações da Nota</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-input-label for="numero_nf" value="Número da NF" />
                <x-text-input name="numero_nf" id="numero_nf" class="w-full" value="{{ $nota->numero_nf }}" required />

                <x-input-label for="prestador" value="Prestador" />
                <x-text-input name="prestador" id="prestador" class="w-full" value="{{ $nota->prestador }}" required />

                <x-input-label for="cnpj" value="CNPJ" />
                <x-text-input name="cnpj" id="cnpj" class="w-full" value="{{ $nota->cnpj }}" />

                <x-input-label for="cidade" value="Cidade" />
                <x-text-input 
                    name="cidade" 
                    id="cidade" 
                    type="text" 
                    class="w-full" 
                    value="{{ old('cidade', $nota->cidade ?? '') }}" 
                />

                <x-input-label for="estado" value="Estado" />
                <x-text-input 
                    name="estado" 
                    id="estado" 
                    type="text" 
                    class="w-full" 
                    value="{{ old('estado', $nota->estado ?? '') }}" 
                />

                <x-input-label for="regiao" value="Região do Brasil" />
                <select 
                    name="regiao" 
                    id="regiao" 
                    class="w-full rounded border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="">Selecione</option>
                    @foreach(['Norte', 'Nordeste', 'Centro-Oeste', 'Sudeste', 'Sul'] as $regiao)
                        <option value="{{ $regiao }}" 
                            {{ old('regiao', $nota->regiao ?? '') === $regiao ? 'selected' : '' }}>
                            {{ $regiao }}
                        </option>
                    @endforeach
                </select>

                <x-input-label for="vencimento_original" value="Vencimento Original" />
                <x-text-input name="vencimento_original" id="vencimento_original" type="date" class="w-full" 
                    value="{{ \Carbon\Carbon::parse($nota->vencimento_original)->format('Y-m-d') }}" />

                <x-input-label for="data_entregue_financeiro" value="Data Entregue ao Financeiro" />
                <x-text-input name="data_entregue_financeiro" id="data_entregue_financeiro" type="date" class="w-full" 
                    value="{{ \Carbon\Carbon::parse($nota->created_at)->format('Y-m-d') }}" />

                <x-input-label for="mes" value="Mês de Referência (MM/AAAA)" />
                <x-text-input name="mes" id="mes" placeholder="MM/AAAA" class="w-full" value="{{ $nota->mes }}" />

                <x-input-label for="vencimento_prorrogado" value="Prorrogação (se houver)" />
                <x-text-input name="vencimento_prorrogado" id="vencimento_prorrogado" type="date" class="w-full" value="{{ \Carbon\Carbon::parse($nota->vencimento_prorrogado)->format('Y-m-d') }}" />

                <div class="md:col-span-2">
                    <x-input-label for="taxa_correio" value="Taxa de Correio?" />
                    <div class="flex items-center mt-2">
                    <input type="checkbox" name="taxa_correio" id="taxa_correio" 
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        {{ old('taxa_correio', $nota->taxa_correio) ? 'checked' : '' }}>
                        <x-input-label for="valor_taxa_correio" value="Valor da Taxa (R$)" class="ml-4 mr-2" />
                        <x-text-input name="valor_taxa_correio" id="valor_taxa_correio" type="number" step="0.01"
                                      class="w-32"
                                      value="{{ $nota->valor_taxa_correio }}" />
                    </div>
                </div>

                <x-input-label for="arquivo_nf" value="Arquivos da NF (PDFs)" />
                <input type="file" name="arquivo_nf[]" id="arquivo_nf" accept="application/pdf"
                    multiple
                    class="w-full dark:bg-gray-700 dark:text-white border-gray-300 rounded-md mt-1" />
                @if ($nota->arquivo_nf)
                    @php
                        $arquivos = is_array(json_decode($nota->arquivo_nf, true)) 
                                    ? json_decode($nota->arquivo_nf, true) 
                                    : [$nota->arquivo_nf];
                    @endphp

                    <div class="mt-2">
                        <p class="text-sm text-gray-500">Arquivos atuais:</p>
                        @foreach ($arquivos as $arquivo)
                            <a href="{{ asset('storage/' . $arquivo) }}" 
                            class="text-indigo-600 hover:text-indigo-800 text-sm underline block"
                            target="_blank">
                                {{ basename($arquivo) }}
                            </a>
                        @endforeach
                        <p class="text-xs text-gray-400 mt-1">
                            Deixe em branco para manter os arquivos atuais ou selecione novos para substituir.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Clientes Atendidos --}}
        <div class="border-b border-gray-300 pb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Clientes Atendidos</h3>
            <div id="clientes-wrapper" class="space-y-6">
                @foreach ($nota->notaClientes as $index => $cliente)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 cliente-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner">
                        <div>
                            <x-input-label value="Cliente Atendido" />
                            <x-text-input name="clientes[{{ $index }}][cliente_atendido]" class="w-full" value="{{ $cliente->cliente_atendido }}" />
                        </div>
                        <div>
                            <x-input-label value="Valor (R$)" />
                            <x-text-input name="clientes[{{ $index }}][valor]" type="number" step="0.01" class="w-full" value="{{ $cliente->valor }}" />
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label value="Observação" />
                            <textarea name="clientes[{{ $index }}][observacao]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white">{{ $cliente->observacao }}</textarea>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                <button type="button" id="add-cliente" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md">
                    + Adicionar Cliente
                </button>
            </div>
        </div>

        <x-input-label for="valor_total" class="mt-5" value="Valor Total (R$)" />
        <x-text-input name="valor_total" id="valor_total" class="w-full mt-1" required type="number" step="0.01" value="{{ $nota->valor_total }}" />

        {{-- Campos de Glosa --}}
        <div class="mt-6 border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Glosa (se aplicável)</h3>
            
            <div class="mt-4">
                <x-input-label for="glosa_motivo" value="Motivo da Glosa" />
                <textarea name="glosa_motivo" id="glosa_motivo" rows="3"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">{{ old('glosa_motivo', $nota->glosa_motivo ?? '') }}</textarea>
            </div>

            <div class="mt-4">
                <x-input-label for="glosa_valor" value="Valor da Glosa (R$)" />
                <x-text-input type="number" step="0.01" name="glosa_valor" id="glosa_valor"
                            class="w-full"
                            value="{{ old('glosa_valor', $nota->glosa_valor ?? '') }}" />
            </div>
        </div>

        <x-input-label for="tipo_pagamento" class="mt-5" value="Tipo de Pagamento" />
        <select name="tipo_pagamento" id="tipo_pagamento" class="w-full mt-1 mb-5 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
            <option value="">Selecione</option>
            <option value="boleto" {{ $nota->tipo_pagamento === 'boleto' ? 'selected' : '' }}>Boleto</option>
            <option value="deposito" {{ $nota->tipo_pagamento === 'deposito' ? 'selected' : '' }}>Depósito</option>
            <option value="pix" {{ $nota->tipo_pagamento === 'pix' ? 'selected' : '' }}>Pix</option>
        </select>

        <x-input-label for="dados_bancarios" value="Dados Bancários (se aplicável)" />
        <textarea name="dados_bancarios" id="dados_bancarios" rows="3" class="w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">{{ $nota->dados_bancarios }}</textarea>

        <x-input-label for="observacao" value="Observação" />
        <textarea name="observacao" id="observacao" rows="3" class="w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">{{ $nota->observacao }}</textarea>
    </div>

    {{-- Médico --}}
    <div id="medico-form" class="{{ $nota->tipo_nota === 'medico' ? '' : 'hidden' }}">
        <div class="border-b border-gray-300 pb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Informações do Médico</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-input-label for="med_nome" value="Nome do Médico" />
                <x-text-input name="med_nome" id="med_nome" class="w-full" value="{{ $nota->med_nome }}" />

                <x-input-label for="med_numero_nf" value="Número da NF" />
                <x-text-input name="med_numero_nf" id="med_numero_nf" class="w-full" value="{{ $nota->numero_nf }}" required />

                <x-input-label for="med_vencimento_original" value="Vencimento Original" />
                <x-text-input name="med_vencimento_original" id="med_vencimento_original" type="date" class="w-full" value="{{ \Carbon\Carbon::parse($nota->vencimento_original)->format('Y-m-d') }}" />

                <x-input-label for="med_mes" value="Mês de Referência (MM/AAAA)" />
                <x-text-input name="med_mes" id="med_mes" placeholder="MM/AAAA" class="w-full" value="{{ $nota->mes }}" />

                <x-input-label for="med_vencimento_prorrogado" value="Prorrogação (se houver)" />
                <x-text-input name="med_vencimento_prorrogado" id="med_vencimento_prorrogado" type="date" class="w-full" value="{{ \Carbon\Carbon::parse($nota->vencimento_prorrogado)->format('Y-m-d') }}" />

                <x-input-label for="med_telefone" value="Telefone Financeiro" />
                <x-text-input name="med_telefone" id="med_telefone" class="w-full" value="{{ $nota->med_telefone }}" />

                <x-input-label for="med_email" value="Email Financeiro" />
                <x-text-input name="med_email" id="med_email" type="email" class="w-full" value="{{ $nota->med_email }}" />

                <x-input-label for="med_cliente_atendido" value="Cliente Atendido" />
                <x-text-input name="med_cliente_atendido" id="med_cliente_atendido" class="w-full" value="{{ $nota->med_cliente_atendido }}" />

                <x-input-label for="med_local" value="Local de Atendimento" />
                <x-text-input name="med_local" id="med_local" class="w-full" value="{{ $nota->med_local }}" />

                <div id="med-horarios-wrapper" class="w-full space-y-4 md:col-span-2">
        @php
            $horariosRaw = old('med_horarios', $nota->med_horarios ?? []);
            $horarios = is_string($horariosRaw) ? json_decode($horariosRaw, true) : $horariosRaw;
        @endphp

    @foreach ($horarios as $index => $horario)
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 horario-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner">
                    <div class="md:col-span-2">
                        <x-input-label value="Data" />
                        <x-text-input name="med_horarios[{{ $index }}][data]" type="date" class="w-full" value="{{ $horario['data'] ?? '' }}" required />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label value="Entrada" />
                        <x-text-input name="med_horarios[{{ $index }}][entrada]" type="time" class="w-full entrada" value="{{ $horario['entrada'] ?? '' }}" required />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label value="Saída Almoço" />
                        <x-text-input name="med_horarios[{{ $index }}][saida_almoco]" type="time" class="w-full saida-almoco" value="{{ $horario['saida_almoco'] ?? '' }}" required />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label value="Retorno Almoço" />
                        <x-text-input name="med_horarios[{{ $index }}][retorno_almoco]" type="time" class="w-full retorno-almoco" value="{{ $horario['retorno_almoco'] ?? '' }}" required />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label value="Saída" />
                        <x-text-input name="med_horarios[{{ $index }}][saida]" type="time" class="w-full saida" value="{{ $horario['saida'] ?? '' }}" required />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label value="Valor por Hora (R$)" />
                        <x-text-input name="med_horarios[{{ $index }}][valor_hora]" type="number" step="0.01" class="w-full valor-hora" value="{{ $horario['valor_hora'] ?? '' }}" required />
                    </div>
                    <div class="md:col-span-4">
                        <x-input-label value="Total (R$)" />
                        <x-text-input name="med_horarios[{{ $index }}][total]" type="number" step="0.01" class="w-full total" value="{{ $horario['total'] ?? '' }}" readonly />
                    </div>
                </div>
                @endforeach
                <x-input-label for="med_valor_total" class="mt-5" value="Valor Total (R$)" />
                <x-text-input name="med_valor_total" id="med_valor_total" class="w-full mt-1" required type="number" step="0.01" value="{{ $nota->valor_total }}" />

                <x-input-label for="tipo_pagamento" class="mt-5" value="Tipo de Pagamento" />
                <select name="med_tipo_pagamento" id="med_tipo_pagamento" class="w-full mt-1 mb-5 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                    <option value="">Selecione</option>
                    <option value="boleto" {{ $nota->tipo_pagamento === 'boleto' ? 'selected' : '' }}>Boleto</option>
                    <option value="deposito" {{ $nota->tipo_pagamento === 'deposito' ? 'selected' : '' }}>Depósito</option>
                    <option value="pix" {{ $nota->tipo_pagamento === 'pix' ? 'selected' : '' }}>Pix</option>
                </select>

                <x-input-label for="med_dados_bancarios" value="Dados Bancários (se aplicável)" />
                <textarea name="med_dados_bancarios" id="med_dados_bancarios" rows="3" class="w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">{{ $nota->dados_bancarios }}</textarea>

                <x-input-label for="med_observacao" value="Observação" />
                <textarea name="med_observacao" id="med_observacao" rows="3" class="w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">{{ $nota->observacao }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
                <div class="mt-8">
                    <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md">
                        Salvar Alterações
                    </button>
                </div>
            </form>
            </div>
        </div>
    </div>
</x-app-layout>
