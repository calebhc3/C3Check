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
                <form action="{{ route('notas.update', $nota->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8" id="nota-form">
                    @csrf
                    @method('PUT')

                    {{-- Seção: Tipo de Nota --}}
                    <div class="flex items-center justify-center mb-8">
                        <div class="inline-flex rounded-md shadow-sm" role="group">
                            <label id="label_clinica" class="px-6 py-3 text-sm font-medium rounded-l-lg border cursor-pointer
                                {{ $nota->tipo_nota === 'clinica' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-gray-700 text-gray-900 border-gray-700' }}">
                                <input id="tipo_clinica" type="radio" name="tipo_nota" value="clinica" class="hidden" {{ $nota->tipo_nota === 'clinica' ? 'checked' : '' }}>
                                Clínica
                            </label>

                            <label id="label_medico" class="px-6 py-3 text-sm font-medium border-t border-b cursor-pointer
                                {{ $nota->tipo_nota === 'medico' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-gray-700 text-gray-900 border-gray-700' }}">
                                <input id="tipo_medico" type="radio" name="tipo_nota" value="medico" class="hidden" {{ $nota->tipo_nota === 'medico' ? 'checked' : '' }}>
                                Médico
                            </label>

                            <label id="label_prestador" class="px-6 py-3 text-sm font-medium rounded-r-lg border cursor-pointer
                                {{ $nota->tipo_nota === 'prestador' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-gray-700 text-gray-900 border-gray-700' }}">
                                <input id="tipo_prestador" type="radio" name="tipo_nota" value="prestador" class="hidden" {{ $nota->tipo_nota === 'prestador' ? 'checked' : '' }}>
                                Prestador
                            </label>
                        </div>
                    </div>

                    {{-- Formulário Clínica --}}
                    <div id="clinica-form" class="{{ $nota->tipo_nota === 'clinica' ? '' : 'hidden' }}">
                        {{-- Seção: Informações da Nota --}}
                        <div class="border-b border-gray-300 pb-6">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Informações da Nota</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Número da NF --}}
                                <div>
                                    <x-input-label for="numero_nf" value="Número da NF *" />
                                    <x-text-input name="numero_nf" id="numero_nf" class="w-full" 
                                        value="{{ old('numero_nf', $nota->numero_nf ?? '') }}" />
                                    @error('numero_nf')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Prestador --}}
                                <div>
                                    <x-input-label for="prestador" value="Prestador *" />
                                    <x-text-input name="prestador" id="prestador" class="w-full" 
                                        value="{{ old('prestador', $nota->prestador ?? '') }}" />
                                    @error('prestador')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- CNPJ --}}
                                <div>
                                    <x-input-label for="cnpj" value="CNPJ *" />
                                    <x-text-input name="cnpj" id="cnpj" class="w-full" 
                                        value="{{ old('cnpj', $nota->cnpj ?? '') }}" 
                                        x-mask="99.999.999/9999-99" placeholder="00.000.000/0000-00" />
                                    @error('cnpj')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Cidade --}}
                                <div>
                                    <x-input-label for="cidade" value="Cidade *" />
                                    <x-text-input name="cidade" id="cidade" type="text" class="w-full" 
                                        value="{{ old('cidade', $nota->cidade ?? '') }}" />
                                    @error('cidade')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Estado --}}
                                <div>
                                    <x-input-label for="estado" value="Estado *" />
                                    <x-text-input name="estado" id="estado" type="text" class="w-full" maxlength="2" 
                                        placeholder="UF" x-mask="AA" value="{{ old('estado', $nota->estado ?? '') }}" />
                                    @error('estado')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Região --}}
                                <div>
                                    <x-input-label for="regiao" value="Região do Brasil *" />
                                    <select name="regiao" id="regiao" class="w-full rounded border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">Selecione</option>
                                        @foreach(['Norte', 'Nordeste', 'Centro-Oeste', 'Sudeste', 'Sul'] as $regiao)
                                            <option value="{{ $regiao }}" @selected(old('regiao', $nota->regiao ?? '') === $regiao)>
                                                {{ $regiao }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('regiao')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Vencimento Original --}}
                                <div>
                                    <x-input-label for="vencimento_original" value="Vencimento Original *" />
                                    <x-text-input name="vencimento_original" id="vencimento_original" type="date" class="w-full" 
                                        value="{{ old('vencimento_original', isset($nota->vencimento_original) ? $nota->vencimento_original : '') }}" />
                                    @error('vencimento_original')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Prorrogação --}}
                                <div>
                                    <x-input-label for="vencimento_prorrogado" value="Prorrogação (se houver)" />
                                    <x-text-input name="vencimento_prorrogado" id="vencimento_prorrogado" type="date" class="w-full" 
                                        value="{{ old('vencimento_prorrogado', isset($nota->vencimento_prorrogado) ? $nota->vencimento_prorrogado->format('Y-m-d') : '') }}" />
                                    @error('vencimento_prorrogado')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Mês de Referência --}}
                                <div>
                                    <x-input-label for="mes" value="Mês de Referência (MM/AAAA) *" />
                                    <x-text-input name="mes" id="mes" placeholder="MM/AAAA" class="w-full" 
                                        value="{{ old('mes', $nota->mes ?? '') }}" x-mask="99/9999" />
                                    @error('mes')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Taxa de Correio --}}
                                <div class="md:col-span-2">
                                    <x-input-label for="taxa_correio" value="Taxa de Correio?" />
                                    <div class="flex items-center mt-2">
                                        <input type="checkbox" name="taxa_correio" id="taxa_correio" value="1" 
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                                            @checked(old('taxa_correio', $nota->taxa_correio ?? false)) 
                                            x-on:change="document.getElementById('valor_taxa_correio').disabled = !this.checked">
                                        <x-input-label for="valor_taxa_correio" value="Valor da Taxa (R$)" class="ml-4 mr-2" />
                                        <x-text-input name="valor_taxa_correio" id="valor_taxa_correio" type="number" step="0.01" class="w-32" 
                                            value="{{ old('valor_taxa_correio', $nota->valor_taxa_correio ?? '') }}" 
                                            :disabled="!old('taxa_correio', $nota->taxa_correio ?? false)" />
                                    </div>
                                    @error('valor_taxa_correio')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            <div class="md:col-span-2 mb-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Arquivos da NF Salvos</label>
                                @if(is_array($nota->arquivo_nf) && count($nota->arquivo_nf) > 0)
                                    <ul class="list-disc list-inside">
                                        @foreach($nota->arquivo_nf as $arquivo)
                                            <li>
                                                <a href="{{ asset('storage/' . $arquivo) }}" target="_blank" class="text-indigo-600 hover:underline">
                                                    {{ basename($arquivo) }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-sm text-gray-500">Nenhum arquivo salvo.</p>
                                @endif
                            </div>

                            {{-- Input de upload --}}
                            <div class="md:col-span-2">
                                <x-input-label for="arquivo_nf" value="Arquivos da NF (PDFs) *" />
                                <input type="file" name="arquivo_nf[]" id="arquivo_nf" multiple accept="application/pdf" 
                                    class="w-full dark:bg-gray-700 dark:text-white border-gray-300 rounded-md" />
                                @error('arquivo_nf')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @error('arquivo_nf.*')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            </div>
                        </div>

                        {{-- Seção: Clientes Atendidos --}}
                        <div class="border-b border-gray-300 pb-6">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Clientes Atendidos *</h3>
                            <div id="clientes-wrapper" class="space-y-6">
                                @php
                                    $oldClientes = old('clientes', $nota->notaClientes ?? [['cliente_atendido' => '', 'valor' => '', 'observacao' => '']]);
                                @endphp
                                
                                @foreach($oldClientes as $index => $cliente)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 cliente-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner">
                                        <div>
                                            <x-input-label value="Cliente Atendido *" />
                                            <x-text-input name="clientes[{{$index}}][cliente_atendido]" 
                                                value="{{ $cliente['cliente_atendido'] ?? '' }}" class="w-full" />
                                            @error("clientes.$index.cliente_atendido")
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <x-input-label value="Valor (R$) *" />
                                            <x-text-input name="clientes[{{$index}}][valor]" type="number" step="0.01" 
                                                value="{{ $cliente['valor'] ?? '' }}" class="w-full" />
                                            @error("clientes.$index.valor")
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <x-input-label value="Observação" />
                                            <textarea name="clientes[{{$index}}][observacao]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white">{{ $cliente['observacao'] ?? '' }}</textarea>
                                            @error("clientes.$index.observacao")
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        @if($index > 0)
                                        <div class="md:col-span-2 flex justify-end">
                                            <button type="button" class="text-red-600 hover:text-red-800 text-sm font-medium remover-cliente">
                                                Remover Cliente
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4">
                                <button type="button" id="add-cliente" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md">
                                    + Adicionar Cliente
                                </button>
                            </div>
                            @error('clientes')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            {{-- Valor Total --}}
                            <div>
                                <x-input-label for="valor_total" value="Valor Total (R$) *" />
                                <x-text-input name="valor_total" id="valor_total" class="w-full" type="number" step="0.01" 
                                    value="{{ old('valor_total', $nota->valor_total ?? '') }}" />
                                @error('valor_total')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Tipo de Pagamento --}}
                            <div>
                                <x-input-label for="tipo_pagamento" value="Tipo de Pagamento" />
                                <select name="tipo_pagamento" id="tipo_pagamento" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                                    <option value="">Selecione</option>
                                    <option value="boleto" @selected(old('tipo_pagamento', $nota->tipo_pagamento ?? '') == 'boleto')>Boleto</option>
                                    <option value="deposito" @selected(old('tipo_pagamento', $nota->tipo_pagamento ?? '') == 'deposito')>Depósito</option>
                                    <option value="pix" @selected(old('tipo_pagamento', $nota->tipo_pagamento ?? '') == 'pix')>Pix</option>
                                </select>
                                @error('tipo_pagamento')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Dados Bancários --}}
                            <div class="md:col-span-2">
                                <x-input-label for="dados_bancarios" value="Dados Bancários (se aplicável)" />
                                <textarea name="dados_bancarios" id="dados_bancarios" rows="3" 
                                    class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">{{ old('dados_bancarios', $nota->dados_bancarios ?? '') }}</textarea>
                                @error('dados_bancarios')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Observação --}}
                            <div class="md:col-span-2">
                                <x-input-label for="observacao" value="Observação" />
                                <textarea name="observacao" id="observacao" rows="3" 
                                    class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">{{ old('observacao', $nota->observacao ?? '') }}</textarea>
                                @error('observacao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Glosar Nota --}}
                            <div class="md:col-span-2">
                                <x-input-label value="Glosar nota?" />
                                <div class="flex items-center mt-2">
                                    <div class="flex items-center mr-4">
                                        <input type="radio" name="glosar" id="glosar_clinica_sim" value="1" 
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            @checked(old('glosar', isset($nota) ? $nota->glosar : false))
                                            x-on:click="document.getElementById('glosaClinicaCampos').style.display = 'block'">
                                        <x-input-label for="glosar_clinica_sim" value="Sim" class="ml-2" />
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" name="glosar" id="glosar_clinica_nao" value="0"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            @checked(!old('glosar', isset($nota) ? $nota->glosar : false))
                                            x-on:click="document.getElementById('glosaClinicaCampos').style.display = 'none'">
                                        <x-input-label for="glosar_clinica_nao" value="Não" class="ml-2" />
                                    </div>
                                </div>
                                @error('glosar')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Campos de Glosa --}}
                            <div id="glosaClinicaCampos" class="md:col-span-2" style="{{ old('glosar', isset($nota) ? $nota->glosar : false) ? '' : 'display: none;' }}">
                                <div class="mt-4">
                                    <x-input-label for="glosa_motivo" value="Motivo da Glosa" />
                                    <textarea name="glosa_motivo" id="glosa_motivo" rows="3" 
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600"
                                        @if(old('glosar', isset($nota) ? $nota->glosar : false)) required @endif>{{ old('glosa_motivo', $nota->glosa_motivo ?? '') }}</textarea>
                                    @error('glosa_motivo')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="glosa_valor" value="Valor da Glosa (R$)" />
                                    <x-text-input type="number" step="0.01" name="glosa_valor" id="glosa_valor" 
                                        class="w-full" 
                                        value="{{ old('glosa_valor', $nota->glosa_valor ?? '') }}"
                                        :required="old('glosar', isset($nota) ? $nota->glosar : false)" />
                                    @error('glosa_valor')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Formulário Médico --}}
                    <div id="medico-form" class="{{ $nota->tipo_nota === 'medico' ? '' : 'hidden' }}">
                        <div class="border-b border-gray-300 pb-6">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Informações do Médico</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Nome do Médico --}}
                                <div>
                                    <x-input-label for="med_nome" value="Nome do Médico *" />
                                    <x-text-input name="med_nome" id="med_nome" class="w-full" 
                                        value="{{ old('med_nome', $nota->med_nome ?? '') }}" />
                                    @error('med_nome')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Número da NF --}}
                                <div>
                                    <x-input-label for="med_numero_nf" value="Número da NF *" />
                                    <x-text-input name="med_numero_nf" id="med_numero_nf" class="w-full" 
                                        value="{{ old('med_numero_nf', $nota->med_numero_nf ?? '') }}" />
                                    @error('med_numero_nf')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Vencimento Original --}}
                                <div>
                                    <x-input-label for="med_vencimento_original" value="Vencimento Original *" />
                                    <x-text-input name="med_vencimento_original" id="med_vencimento_original" type="date" 
                                        class="w-full" value="{{ old('med_vencimento_original', $nota->med_vencimento_original ?? '') }}" />
                                    @error('med_vencimento_original')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Mês de Referência --}}
                                <div>
                                    <x-input-label for="med_mes" value="Mês de Referência (MM/AAAA) *" />
                                    <x-text-input name="med_mes" id="med_mes" placeholder="MM/AAAA" class="w-full" 
                                        value="{{ old('med_mes', $nota->med_mes ?? '') }}" x-mask="99/9999" />
                                    @error('med_mes')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Prorrogação --}}
                                <div>
                                    <x-input-label for="med_vencimento_prorrogado" value="Prorrogação (se houver)" />
                                    <x-text-input name="med_vencimento_prorrogado" id="med_vencimento_prorrogado" type="date" 
                                        class="w-full" value="{{ old('med_vencimento_prorrogado', $nota->med_vencimento_prorrogado ?? '') }}" />
                                    @error('med_vencimento_prorrogado')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Telefone --}}
                                <div>
                                    <x-input-label for="med_telefone" value="Telefone Financeiro" />
                                    <x-text-input name="med_telefone" id="med_telefone" class="w-full" 
                                        value="{{ old('med_telefone', $nota->med_telefone ?? '') }}" x-mask="(99) 99999-9999" />
                                    @error('med_telefone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Email --}}
                                <div>
                                    <x-input-label for="med_email" value="Email Financeiro" />
                                    <x-text-input name="med_email" id="med_email" type="email" class="w-full" 
                                        value="{{ old('med_email', $nota->med_email ?? '') }}" />
                                    @error('med_email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Cliente Atendido --}}
                                <div>
                                    <x-input-label for="med_cliente_atendido" value="Cliente Atendido *" />
                                    <x-text-input name="med_cliente_atendido" id="med_cliente_atendido" class="w-full" 
                                        value="{{ old('med_cliente_atendido', $nota->med_cliente_atendido ?? '') }}" />
                                    @error('med_cliente_atendido')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Local de Atendimento --}}
                                <div>
                                    <x-input-label for="med_local" value="Local de Atendimento" />
                                    <x-text-input name="med_local" id="med_local" class="w-full" 
                                        value="{{ old('med_local', $nota->med_local ?? '') }}" />
                                    @error('med_local')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Horários e Valores --}}
                                <div class="md:col-span-2">
                                    <x-input-label value="Horários e Valores *" />
                                    <div id="med-horarios-wrapper" class="space-y-4">
                                        @php
                                            $oldHorarios = old('med_horarios', $nota->med_horarios ?? [['data' => '', 'entrada' => '', 'saida_almoco' => '', 'retorno_almoco' => '', 'saida' => '', 'valor_hora' => '', 'total' => '', 'horas_trabalhadas' => '']]);
                                        @endphp
                                        
                                        @foreach($oldHorarios as $index => $horario)
                                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 horario-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner">
                                            <div>
                                                <x-input-label value="Data *" />
                                                <x-text-input name="med_horarios[{{$index}}][data]" type="date" 
                                                    value="{{ $horario['data'] ?? '' }}" class="w-full" />
                                                @error("med_horarios.$index.data")
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div>
                                                <x-input-label value="Entrada *" />
                                                <x-text-input name="med_horarios[{{$index}}][entrada]" type="time" 
                                                    value="{{ $horario['entrada'] ?? '' }}" class="w-full entrada" />
                                                @error("med_horarios.$index.entrada")
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div>
                                                <x-input-label value="Saída Almoço" />
                                                <x-text-input name="med_horarios[{{$index}}][saida_almoco]" type="time" 
                                                    value="{{ $horario['saida_almoco'] ?? '' }}" class="w-full saida-almoco" />
                                                @error("med_horarios.$index.saida_almoco")
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div>
                                                <x-input-label value="Retorno Almoço" />
                                                <x-text-input name="med_horarios[{{$index}}][retorno_almoco]" type="time" 
                                                    value="{{ $horario['retorno_almoco'] ?? '' }}" class="w-full retorno-almoco" />
                                                @error("med_horarios.$index.retorno_almoco")
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div>
                                                <x-input-label value="Saída *" />
                                                <x-text-input name="med_horarios[{{$index}}][saida]" type="time" 
                                                    value="{{ $horario['saida'] ?? '' }}" class="w-full saida" />
                                                @error("med_horarios.$index.saida")
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div class="md:col-span-2">
                                                <x-input-label value="Valor por Hora (R$) *" />
                                                <x-text-input name="med_horarios[{{$index}}][valor_hora]" type="number" step="0.01" 
                                                    value="{{ $horario['valor_hora'] ?? '' }}" class="w-full valor-hora" />
                                                @error("med_horarios.$index.valor_hora")
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div class="md:col-span-2">
                                                <x-input-label value="Total (R$) *" />
                                                <x-text-input name="med_horarios[{{$index}}][total]" type="number" step="0.01" 
                                                    value="{{ $horario['total'] ?? '' }}" class="w-full total" readonly />
                                                @error("med_horarios.$index.total")
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div class="md:col-span-1">
                                                <x-input-label value="Horas" />
                                                <x-text-input name="med_horarios[{{$index}}][horas_trabalhadas]" type="text" 
                                                    value="{{ $horario['horas_trabalhadas'] ?? '' }}" class="w-full horas-trabalhadas" readonly />
                                                @error("med_horarios.$index.horas_trabalhadas")
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            @if($index > 0)
                                            <div class="md:col-span-5 flex justify-end">
                                                <button type="button" class="text-red-600 hover:text-red-800 text-sm font-medium remover-horario">
                                                    Remover Dia
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    <button type="button" id="add-horario" class="mt-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md">
                                        + Adicionar Dia de Trabalho
                                    </button>
                                    @error('med_horarios')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Deslocamento --}}
                                <div class="md:col-span-2">
                                    <x-input-label for="med_deslocamento" value="Deslocamento?" />
                                    <div class="flex items-center mt-2">
                                        <input type="checkbox" name="med_deslocamento" id="med_deslocamento" 
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            @checked(old('med_deslocamento', $nota->med_deslocamento ?? false))
                                            x-on:change="
                                                document.getElementById('med_valor_deslocamento').disabled = !this.checked;
                                                document.getElementById('med_valor_deslocamento').required = this.checked;
                                            ">
                                        <x-input-label for="med_valor_deslocamento" value="Valor do Deslocamento (R$)" class="ml-4 mr-2" />
                                        <x-text-input name="med_valor_deslocamento" id="med_valor_deslocamento" type="number" step="0.01" 
                                            class="w-32" value="{{ old('med_valor_deslocamento', $nota->med_valor_deslocamento ?? 0) }}" 
                                            :disabled="!old('med_deslocamento', $nota->med_deslocamento ?? false)" 
                                            :required="old('med_deslocamento', $nota->med_deslocamento ?? false)" />
                                        <input type="hidden" name="med_valor_deslocamento_fallback" value="0">
                                    </div>
                                    @error('med_valor_deslocamento')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Cobrou Almoço --}}
                                <div class="md:col-span-2">
                                    <x-input-label for="med_cobrou_almoco" value="Cobrou Almoço?" />
                                    <div class="flex items-center mt-2">
                                        <input type="checkbox" name="med_cobrou_almoco" id="med_cobrou_almoco" 
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            @checked(old('med_cobrou_almoco', $nota->med_cobrou_almoco ?? false))
                                            x-on:change="
                                                document.getElementById('med_valor_almoco').disabled = !this.checked;
                                                document.getElementById('med_valor_almoco').required = this.checked;
                                            ">
                                        <x-input-label for="med_valor_almoco" value="Valor do Almoço (R$)" class="ml-4 mr-2" />
                                        <x-text-input name="med_valor_almoco" id="med_valor_almoco" type="number" step="0.01" 
                                            class="w-32" value="{{ old('med_valor_almoco', $nota->med_valor_almoco ?? 0) }}" 
                                            :disabled="!old('med_cobrou_almoco', $nota->med_cobrou_almoco ?? false)" 
                                            :required="old('med_cobrou_almoco', $nota->med_cobrou_almoco ?? false)" />
                                        <input type="hidden" name="med_valor_almoco_fallback" value="0">
                                    </div>
                                    @error('med_valor_almoco')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Reembolso Correios --}}
                                <div class="md:col-span-2">
                                    <x-input-label for="med_reembolso_correios" value="Reembolso de Correios?" />
                                    <div class="flex items-center mt-2">
                                        <input type="checkbox" name="med_reembolso_correios" id="med_reembolso_correios" 
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            @checked(old('med_reembolso_correios', $nota->med_reembolso_correios ?? false))
                                            x-on:change="
                                                document.getElementById('med_valor_correios').disabled = !this.checked;
                                                document.getElementById('med_valor_correios').required = this.checked;
                                            ">
                                        <x-input-label for="med_valor_correios" value="Valor dos Correios (R$)" class="ml-4 mr-2" />
                                        <x-text-input name="med_valor_correios" id="med_valor_correios" type="number" step="0.01" 
                                            class="w-32" value="{{ old('med_valor_correios', $nota->med_valor_correios ?? 0) }}" 
                                            :disabled="!old('med_reembolso_correios', $nota->med_reembolso_correios ?? false)" 
                                            :required="old('med_reembolso_correios', $nota->med_reembolso_correios ?? false)" />
                                        <input type="hidden" name="med_valor_correios_fallback" value="0">
                                    </div>
                                    @error('med_valor_correios')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="med_valor_total_final" value="Valor Total Final (R$) *" />
                                    <x-text-input name="med_valor_total_final" id="med_valor_total_final" type="number" step="0.01" 
                                        class="w-full" value="{{ old('med_valor_total_final', $nota->med_valor_total_final ?? '') }}" readonly />
                                    @error('med_valor_total_final')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Tipo de Pagamento --}}
                                <div>
                                    <x-input-label for="med_tipo_pagamento" value="Tipo de Pagamento" />
                                    <select name="med_tipo_pagamento" id="med_tipo_pagamento" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                                        <option value="">Selecione</option>
                                        <option value="boleto" @selected(old('med_tipo_pagamento', $nota->med_tipo_pagamento ?? '') == 'boleto')>Boleto</option>
                                        <option value="deposito" @selected(old('med_tipo_pagamento', $nota->med_tipo_pagamento ?? '') == 'deposito')>Depósito</option>
                                        <option value="pix" @selected(old('med_tipo_pagamento', $nota->med_tipo_pagamento ?? '') == 'pix')>Pix</option>
                                    </select>
                                    @error('med_tipo_pagamento')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Dados Bancários --}}
                                <div class="md:col-span-2">
                                    <x-input-label for="med_dados_bancarios" value="Dados Bancários (se aplicável)" />
                                    <textarea name="med_dados_bancarios" id="med_dados_bancarios" rows="3" 
                                        class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">{{ old('med_dados_bancarios', $nota->med_dados_bancarios ?? '') }}</textarea>
                                    @error('med_dados_bancarios')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Observação --}}
                                <div class="md:col-span-2">
                                    <x-input-label for="med_observacao" value="Observação" />
                                    <textarea name="med_observacao" id="med_observacao" rows="3" 
                                        class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">{{ old('med_observacao', $nota->med_observacao ?? '') }}</textarea>
                                    @error('med_observacao')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Formulário Prestador --}}
                    <div id="prestador-form" class="{{ $nota->tipo_nota === 'prestador' ? '' : 'hidden' }}">
                        {{-- Seção: Informações da Nota --}}
                        <div class="border-b border-gray-300 pb-6">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Informações da Nota</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Número da NF --}}
                                <div>
                                    <x-input-label for="numero_nf" value="Número da NF *" />
                                    <x-text-input name="numero_nf" id="numero_nf" class="w-full" 
                                        value="{{ old('numero_nf', $nota->numero_nf ?? '') }}" />
                                    @error('numero_nf')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Prestador --}}
                                <div>
                                    <x-input-label for="prestador" value="Prestador *" />
                                    <x-text-input name="prestador" id="prestador" class="w-full" 
                                        value="{{ old('prestador', $nota->prestador ?? '') }}" />
                                    @error('prestador')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- CNPJ --}}
                                <div>
                                    <x-input-label for="cnpj" value="CNPJ *" />
                                    <x-text-input name="cnpj" id="cnpj" class="w-full" 
                                        value="{{ old('cnpj', $nota->cnpj ?? '') }}" 
                                        x-mask="99.999.999/9999-99" placeholder="00.000.000/0000-00" />
                                    @error('cnpj')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Cidade --}}
                                <div>
                                    <x-input-label for="cidade" value="Cidade *" />
                                    <x-text-input name="cidade" id="cidade" type="text" class="w-full" 
                                        value="{{ old('cidade', $nota->cidade ?? '') }}" />
                                    @error('cidade')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Estado --}}
                                <div>
                                    <x-input-label for="estado" value="Estado *" />
                                    <x-text-input name="estado" id="estado" type="text" class="w-full" maxlength="2" 
                                        placeholder="UF" x-mask="AA" value="{{ old('estado', $nota->estado ?? '') }}" />
                                    @error('estado')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Região --}}
                                <div>
                                    <x-input-label for="regiao" value="Região do Brasil *" />
                                    <select name="regiao" id="regiao" class="w-full rounded border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">Selecione</option>
                                        @foreach(['Norte', 'Nordeste', 'Centro-Oeste', 'Sudeste', 'Sul'] as $regiao)
                                            <option value="{{ $regiao }}" @selected(old('regiao', $nota->regiao ?? '') === $regiao)>
                                                {{ $regiao }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('regiao')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Vencimento Original --}}
                                <div>
                                    <x-input-label for="vencimento_original" value="Vencimento Original *" />
                                    <x-text-input name="vencimento_original" id="vencimento_original" type="date" class="w-full" 
                                        value="{{ old('vencimento_original', isset($nota->vencimento_original) ? $nota->vencimento_original : '') }}" />
                                    @error('vencimento_original')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Prorrogação --}}
                                <div>
                                    <x-input-label for="vencimento_prorrogado" value="Prorrogação (se houver)" />
                                    <x-text-input name="vencimento_prorrogado" id="vencimento_prorrogado" type="date" class="w-full" 
                                        value="{{ old('vencimento_prorrogado', isset($nota->vencimento_prorrogado) ? $nota->vencimento_prorrogado->format('Y-m-d') : '') }}" />
                                    @error('vencimento_prorrogado')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Mês de Referência --}}
                                <div>
                                    <x-input-label for="mes" value="Mês de Referência (MM/AAAA) *" />
                                    <x-text-input name="mes" id="mes" placeholder="MM/AAAA" class="w-full" 
                                        value="{{ old('mes', $nota->mes ?? '') }}" x-mask="99/9999" />
                                    @error('mes')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Taxa de Correio --}}
                                <div class="md:col-span-2">
                                    <x-input-label for="taxa_correio" value="Taxa de Correio?" />
                                    <div class="flex items-center mt-2">
                                        <input type="checkbox" name="taxa_correio" id="taxa_correio" value="1" 
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                                            @checked(old('taxa_correio', $nota->taxa_correio ?? false)) 
                                            x-on:change="document.getElementById('valor_taxa_correio').disabled = !this.checked">
                                        <x-input-label for="valor_taxa_correio" value="Valor da Taxa (R$)" class="ml-4 mr-2" />
                                        <x-text-input name="valor_taxa_correio" id="valor_taxa_correio" type="number" step="0.01" class="w-32" 
                                            value="{{ old('valor_taxa_correio', $nota->valor_taxa_correio ?? '') }}" 
                                            :disabled="!old('taxa_correio', $nota->taxa_correio ?? false)" />
                                    </div>
                                    @error('valor_taxa_correio')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="md:col-span-2 mb-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Arquivos da NF Salvos</label>
                                    @if(is_array($nota->arquivo_nf) && count($nota->arquivo_nf) > 0)
                                        <ul class="list-disc list-inside">
                                            @foreach($nota->arquivo_nf as $arquivo)
                                                <li>
                                                    <a href="{{ asset('storage/' . $arquivo) }}" target="_blank" class="text-indigo-600 hover:underline">
                                                        {{ basename($arquivo) }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-sm text-gray-500">Nenhum arquivo salvo.</p>
                                    @endif
                                </div>

                                {{-- Input de upload --}}
                                <div class="md:col-span-2">
                                    <x-input-label for="arquivo_nf" value="Arquivos da NF (PDFs) *" />
                                    <input type="file" name="arquivo_nf[]" id="arquivo_nf" multiple accept="application/pdf" 
                                        class="w-full dark:bg-gray-700 dark:text-white border-gray-300 rounded-md" />
                                    @error('arquivo_nf')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    @error('arquivo_nf.*')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        {{-- Seção: Clientes Atendidos --}}
                        <div class="border-b border-gray-300 pb-6">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Clientes Atendidos *</h3>
                            <div id="clientes-wrapper" class="space-y-6">
                                @php
                                    $oldClientes = old('clientes', $nota->notaClientes ?? [['cliente_atendido' => '', 'valor' => '', 'observacao' => '']]);
                                @endphp
                                
                                @foreach($oldClientes as $index => $cliente)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 cliente-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner">
                                        <div>
                                            <x-input-label value="Cliente Atendido *" />
                                            <x-text-input name="clientes[{{$index}}][cliente_atendido]" 
                                                value="{{ $cliente['cliente_atendido'] ?? '' }}" class="w-full" />
                                            @error("clientes.$index.cliente_atendido")
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <x-input-label value="Valor (R$) *" />
                                            <x-text-input name="clientes[{{$index}}][valor]" type="number" step="0.01" 
                                                value="{{ $cliente['valor'] ?? '' }}" class="w-full" />
                                            @error("clientes.$index.valor")
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <x-input-label value="Observação" />
                                            <textarea name="clientes[{{$index}}][observacao]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white">{{ $cliente['observacao'] ?? '' }}</textarea>
                                            @error("clientes.$index.observacao")
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        @if($index > 0)
                                        <div class="md:col-span-2 flex justify-end">
                                            <button type="button" class="text-red-600 hover:text-red-800 text-sm font-medium remover-cliente">
                                                Remover Cliente
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4">
                                <button type="button" id="add-cliente" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md">
                                    + Adicionar Cliente
                                </button>
                            </div>
                            @error('clientes')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            {{-- Valor Total --}}
                            <div>
                                <x-input-label for="valor_total" value="Valor Total (R$) *" />
                                <x-text-input name="valor_total" id="valor_total" class="w-full" type="number" step="0.01" 
                                    value="{{ old('valor_total', $nota->valor_total ?? '') }}" />
                                @error('valor_total')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Tipo de Pagamento --}}
                            <div>
                                <x-input-label for="tipo_pagamento" value="Tipo de Pagamento" />
                                <select name="tipo_pagamento" id="tipo_pagamento" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                                    <option value="">Selecione</option>
                                    <option value="boleto" @selected(old('tipo_pagamento', $nota->tipo_pagamento ?? '') == 'boleto')>Boleto</option>
                                    <option value="deposito" @selected(old('tipo_pagamento', $nota->tipo_pagamento ?? '') == 'deposito')>Depósito</option>
                                    <option value="pix" @selected(old('tipo_pagamento', $nota->tipo_pagamento ?? '') == 'pix')>Pix</option>
                                </select>
                                @error('tipo_pagamento')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Dados Bancários --}}
                            <div class="md:col-span-2">
                                <x-input-label for="dados_bancarios" value="Dados Bancários (se aplicável)" />
                                <textarea name="dados_bancarios" id="dados_bancarios" rows="3" 
                                    class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">{{ old('dados_bancarios', $nota->dados_bancarios ?? '') }}</textarea>
                                @error('dados_bancarios')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Observação --}}
                            <div class="md:col-span-2">
                                <x-input-label for="observacao" value="Observação" />
                                <textarea name="observacao" id="observacao" rows="3" 
                                    class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">{{ old('observacao', $nota->observacao ?? '') }}</textarea>
                                @error('observacao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Glosar Nota --}}
                            <div class="md:col-span-2">
                                <x-input-label value="Glosar nota?" />
                                <div class="flex items-center mt-2">
                                    <div class="flex items-center mr-4">
                                        <input type="radio" name="glosar" id="glosar_clinica_sim" value="1" 
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            @checked(old('glosar', isset($nota) ? $nota->glosar : false))
                                            x-on:click="document.getElementById('glosaClinicaCampos').style.display = 'block'">
                                        <x-input-label for="glosar_clinica_sim" value="Sim" class="ml-2" />
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" name="glosar" id="glosar_clinica_nao" value="0"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            @checked(!old('glosar', isset($nota) ? $nota->glosar : false))
                                            x-on:click="document.getElementById('glosaClinicaCampos').style.display = 'none'">
                                        <x-input-label for="glosar_clinica_nao" value="Não" class="ml-2" />
                                    </div>
                                </div>
                                @error('glosar')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Campos de Glosa --}}
                            <div id="glosaClinicaCampos" class="md:col-span-2" style="{{ old('glosar', isset($nota) ? $nota->glosar : false) ? '' : 'display: none;' }}">
                                <div class="mt-4">
                                    <x-input-label for="glosa_motivo" value="Motivo da Glosa" />
                                    <textarea name="glosa_motivo" id="glosa_motivo" rows="3" 
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600"
                                        @if(old('glosar', isset($nota) ? $nota->glosar : false)) required @endif>{{ old('glosa_motivo', $nota->glosa_motivo ?? '') }}</textarea>
                                    @error('glosa_motivo')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="glosa_valor" value="Valor da Glosa (R$)" />
                                    <x-text-input type="number" step="0.01" name="glosa_valor" id="glosa_valor" 
                                        class="w-full" 
                                        value="{{ old('glosa_valor', $nota->glosa_valor ?? '') }}"
                                        :required="old('glosar', isset($nota) ? $nota->glosar : false)" />
                                    @error('glosa_valor')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                  </div>

                    <div class="text-right pt-4">
                        <x-primary-button>Salvar Alterações</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            class FormManager {
                constructor() {
                    this.state = {
                        currentForm: '{{ $nota->tipo_nota }}',
                        clienteCount: document.querySelectorAll('#clientes-wrapper .cliente-item').length || 1,
                        prestClienteCount: 1,
                        horarioCount: 1
                    };
                    this.cacheElements();
                    this.initEventListeners();
                    this.initializeForms();
                }

                cacheElements() {
                    this.elements = {
                        forms: {
                            clinica: document.getElementById('clinica-form'),
                            medico: document.getElementById('medico-form'),
                            prestador: document.getElementById('prestador-form')
                        },
                        radioButtons: {
                            clinica: document.getElementById('tipo_clinica'),
                            medico: document.getElementById('tipo_medico'),
                            prestador: document.getElementById('tipo_prestador')
                        },
                        labels: {
                            clinica: document.getElementById('label_clinica'),
                            medico: document.getElementById('label_medico'),
                            prestador: document.getElementById('label_prestador')
                        },
                        buttons: {
                            addCliente: document.getElementById('add-cliente')
                        },
                        wrappers: {
                            clientes: document.getElementById('clientes-wrapper')
                        },
                        calculos: {
                            valorTotal: document.getElementById('valor_total'),
                            taxaCorreio: {
                                checkbox: document.getElementById('taxa_correio'),
                                valor: document.getElementById('valor_taxa_correio')
                            }
                        }
                    };
                }

                initEventListeners() {
                    this.elements.radioButtons.clinica.addEventListener('change', () => this.switchForm('clinica'));
                    this.elements.radioButtons.medico.addEventListener('change', () => this.switchForm('medico'));
                    this.elements.radioButtons.prestador.addEventListener('change', () => this.switchForm('prestador'));

                    if(this.elements.buttons.addCliente) {
                        this.elements.buttons.addCliente.addEventListener('click', () => this.addCliente());
                    }

                    this.elements.wrappers.clientes.addEventListener('input', (e) => {
                        if(e.target.matches('input[name^="clientes"][name$="[valor]"]')) {
                            this.calculateClinicaTotal();
                        }
                    });

                    if(this.elements.calculos.taxaCorreio.checkbox) {
                        this.elements.calculos.taxaCorreio.checkbox.addEventListener('change', () => this.toggleTaxaCorreio());
                        this.elements.calculos.taxaCorreio.valor.addEventListener('input', () => this.calculateClinicaTotal());
                    }

                    document.querySelectorAll('input[name="glosar"]').forEach(radio => {
                        radio.addEventListener('change', () => this.toggleGlosa());
                    });

                    document.getElementById('glosa_valor')?.addEventListener('input', () => this.calculateClinicaTotal());
                }

                initializeForms() {
                    this.updateFormStyles();
                    this.toggleTaxaCorreio();
                    this.toggleGlosa();
                    this.calculateClinicaTotal();
                }

                switchForm(formType) {
                    this.state.currentForm = formType;
                    this.elements.forms.clinica.classList.toggle('hidden', formType !== 'clinica');
                    this.elements.forms.medico.classList.toggle('hidden', formType !== 'medico');
                    this.elements.forms.prestador.classList.toggle('hidden', formType !== 'prestador');

                    this.updateFormStyles();
                    this.calculateClinicaTotal();
                }

                updateFormStyles() {
                    const activeClasses = ['bg-indigo-600', 'text-white', 'border-indigo-600', 'hover:bg-indigo-700'];
                    const inactiveClasses = ['bg-gray-700', 'text-gray-900', 'border-gray-700', 'hover:bg-gray-50'];

                    const toggleClasses = (element, isActive) => {
                        element.classList.remove(...(isActive ? inactiveClasses : activeClasses));
                        element.classList.add(...(isActive ? activeClasses : inactiveClasses));
                    };

                    toggleClasses(this.elements.labels.clinica, this.state.currentForm === 'clinica');
                    toggleClasses(this.elements.labels.medico, this.state.currentForm === 'medico');
                    toggleClasses(this.elements.labels.prestador, this.state.currentForm === 'prestador');
                }

                addCliente() {
                    const index = this.state.clienteCount++;
                    const clienteHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 cliente-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner mt-4">
                            <div>
                                <x-input-label value="Cliente Atendido" />
                                <input type="text" name="clientes[${index}][cliente_atendido]" class="w-full" required />
                            </div>
                            <div>
                                <x-input-label value="Valor (R$)" />
                                <input type="number" step="0.01" name="clientes[${index}][valor]" class="w-full cliente-valor" required />
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label value="Observação" />
                                <textarea name="clientes[${index}][observacao]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white"></textarea>
                            </div>
                        </div>
                    `;
                    this.elements.wrappers.clientes.insertAdjacentHTML('beforeend', clienteHTML);
                    const newInput = this.elements.wrappers.clientes.lastElementChild.querySelector('.cliente-valor');
                    newInput.addEventListener('input', () => this.calculateClinicaTotal());
                }

                calculateClinicaTotal() {
                    let total = 0;
                    this.elements.wrappers.clientes.querySelectorAll('input[name^="clientes"][name$="[valor]"]').forEach(input => {
                        total += parseFloat(input.value) || 0;
                    });
                    if(this.elements.calculos.taxaCorreio.checkbox.checked) {
                        total += parseFloat(this.elements.calculos.taxaCorreio.valor.value) || 0;
                    }
                    const glosaAtiva = document.querySelector('input[name="glosar"][value="1"]').checked;
                    const valorGlosa = parseFloat(document.getElementById('glosa_valor').value) || 0;
                    if(glosaAtiva && valorGlosa > 0) {
                        total -= valorGlosa;
                        total = Math.max(total, 0);
                    }
                    this.elements.calculos.valorTotal.value = total.toFixed(2);
                }

                toggleTaxaCorreio() {
                    if(this.elements.calculos.taxaCorreio.checkbox.checked) {
                        this.elements.calculos.taxaCorreio.valor.disabled = false;
                        this.elements.calculos.taxaCorreio.valor.focus();
                    } else {
                        this.elements.calculos.taxaCorreio.valor.disabled = true;
                        this.elements.calculos.taxaCorreio.valor.value = '';
                    }
                    this.calculateClinicaTotal();
                }

                toggleGlosa() {
                    const glosaAtiva = document.querySelector('input[name="glosar"][value="1"]').checked;
                    document.getElementById('glosaClinicaCampos').style.display = glosaAtiva ? 'block' : 'none';
                    this.calculateClinicaTotal();
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                window.formManager = new FormManager();
            });
        </script>
    @endpush
</x-app-layout>
