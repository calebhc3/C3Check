<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 dark:text-white">
            Nova Nota de Pagamento
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto bg-white dark:bg-gray-800 shadow-xl rounded-xl p-8">
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-md">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <h3 class="text-lg font-medium text-red-800">Corrija os seguintes erros para continuar</h3>
                </div>
                <div class="mt-2 pl-8">
                    <ul class="list-disc text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
<form action="{{ route('notas.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
    @csrf

    {{-- Seção: Tipo de Nota --}}
    <div class="flex items-center justify-center mb-8">
        <div class="inline-flex rounded-md shadow-sm" role="group">
            <input type="radio" name="tipo_nota" id="tipo_clinica" value="clinica" class="hidden peer" checked>
            <label for="tipo_clinica" id="label_clinica" class="px-6 py-3 text-sm font-medium rounded-l-lg border
                bg-indigo-600 text-white border-indigo-600
                hover:bg-indigo-700
                cursor-pointer transition-all duration-200 ease-in-out">
                Clínica
            </label>
            
            <input type="radio" name="tipo_nota" id="tipo_medico" value="medico" class="hidden peer">
            <label for="tipo_medico" id="label_medico" class="px-6 py-3 text-sm font-medium border border-gray-200
                bg-white text-gray-900 border-gray-200
                hover:bg-gray-50
                cursor-pointer transition-all duration-200 ease-in-out">
                Médico
            </label>
            <input type="radio" name="tipo_nota" id="tipo_prestador" value="prestador" class="hidden peer">
            <label for="tipo_prestador" id="label_prestador" class="px-6 py-3 text-sm font-medium rounded-r-lg border border-gray-200
                bg-white text-gray-900 border-gray-200
                hover:bg-gray-50
                cursor-pointer transition-all duration-200 ease-in-out">
                Prestador
            </label>
        </div>
    </div>

    {{-- Formulário para Clínica --}}
    <div id="clinica-form">
        {{-- Seção: Informações da Nota --}}
        <div class="border-b border-gray-300 pb-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Informações da Nota</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Número da NF --}}
                <div>
                    <x-input-label for="numero_nf" value="Número da NF *" />
                    <x-text-input name="numero_nf" id="numero_nf" class="w-full" value="{{ old('numero_nf') }}" />
                    @error('numero_nf')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Prestador --}}
                <div>
                    <x-input-label for="prestador" value="Prestador *" />
                    <x-text-input name="prestador" id="prestador" class="w-full" value="{{ old('prestador') }}" />
                    @error('prestador')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- CNPJ --}}
                <div>
                    <x-input-label for="cnpj" value="CNPJ *" />
                    <x-text-input name="cnpj" id="cnpj" class="w-full" value="{{ old('cnpj') }}" 
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
                    <x-text-input name="estado" id="estado" type="text" class="w-full" 
                        value="{{ old('estado', $nota->estado ?? '') }}" maxlength="2" 
                        placeholder="UF" x-mask="AA" />
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
                    <x-text-input name="vencimento_original" id="vencimento_original" type="date" 
                        class="w-full" value="{{ old('vencimento_original') }}" />
                    @error('vencimento_original')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Prorrogação --}}
                <div>
                    <x-input-label for="vencimento_prorrogado" value="Prorrogação (se houver)" />
                    <x-text-input name="vencimento_prorrogado" id="vencimento_prorrogado" type="date" 
                        class="w-full" value="{{ old('vencimento_prorrogado') }}" />
                    @error('vencimento_prorrogado')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Mês de Referência --}}
                <div>
                    <x-input-label for="mes" value="Mês de Referência (MM/AAAA) *" />
                    <x-text-input name="mes" id="mes" placeholder="MM/AAAA" class="w-full" 
                        value="{{ old('mes') }}" x-mask="99/9999" />
                    @error('mes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Taxa de Correio --}}
                <div class="md:col-span-2">
                    <x-input-label for="taxa_correio" value="Taxa de Correio?" />
                    <div class="flex items-center mt-2">
                        <input type="checkbox" name="taxa_correio" id="taxa_correio" 
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                            value="1" @checked(old('taxa_correio')) 
                            x-on:change="document.getElementById('valor_taxa_correio').disabled = !this.checked">
                        <x-input-label for="valor_taxa_correio" value="Valor da Taxa (R$)" class="ml-4 mr-2" />
                        <x-text-input name="valor_taxa_correio" id="valor_taxa_correio" type="number" step="0.01" 
                            class="w-32" value="{{ old('valor_taxa_correio') }}" 
                            :disabled="!old('taxa_correio')" />
                    </div>
                    @error('valor_taxa_correio')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Arquivos da NF --}}
                <div class="md:col-span-2">
                    <x-input-label for="arquivo_nf" value="Arquivos da NF (PDFs) *" />
                    <input type="file" name="arquivo_nf[]" id="arquivo_nf" multiple 
                        accept="application/pdf" class="w-full dark:bg-gray-700 dark:text-white border-gray-300 rounded-md" />
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
                    $oldClientes = old('clientes', [['cliente_atendido' => '', 'valor' => '', 'observacao' => '']]);
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
                        <textarea name="clientes[{{$index}}][observacao]" 
                            class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white">{{ $cliente['observacao'] ?? '' }}</textarea>
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
                    value="{{ old('valor_total') }}" />
                @error('valor_total')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tipo de Pagamento --}}
            <div>
                <x-input-label for="tipo_pagamento" value="Tipo de Pagamento" />
                <select name="tipo_pagamento" id="tipo_pagamento" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                    <option value="">Selecione</option>
                    <option value="boleto" @selected(old('tipo_pagamento') == 'boleto')>Boleto</option>
                    <option value="deposito" @selected(old('tipo_pagamento') == 'deposito')>Depósito</option>
                    <option value="pix" @selected(old('tipo_pagamento') == 'pix')>Pix</option>
                </select>
                @error('tipo_pagamento')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Dados Bancários --}}
            <div class="md:col-span-2">
                <x-input-label for="dados_bancarios" value="Dados Bancários (se aplicável)" />
                <textarea name="dados_bancarios" id="dados_bancarios" rows="3" 
                    class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">{{ old('dados_bancarios') }}</textarea>
                @error('dados_bancarios')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Observação --}}
            <div class="md:col-span-2">
                <x-input-label for="observacao" value="Observação" />
                <textarea name="observacao" id="observacao" rows="3" 
                    class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">{{ old('observacao') }}</textarea>
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
                            @if(old('glosar', isset($nota) ? $nota->glosar : false)) required @endif>
                        {{ old('glosa_motivo', $nota->glosa_motivo ?? '') }}
                    </textarea>
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

        {{-- Botão de envio --}}
        <div class="text-right pt-4">
            <x-primary-button>Salvar Nota</x-primary-button>
        </div>
    </div>

    {{-- Formulário para Médico --}}
    <div id="medico-form" class="hidden">
        <div class="border-b border-gray-300 pb-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Informações do Médico</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Nome do Médico --}}
                <div>
                    <x-input-label for="med_nome" value="Nome do Médico *" />
                    <x-text-input name="med_nome" id="med_nome" class="w-full" value="{{ old('med_nome') }}" />
                    @error('med_nome')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Número da NF --}}
                <div>
                    <x-input-label for="med_numero_nf" value="Número da NF" />
                    <x-text-input name="med_numero_nf" id="med_numero_nf" class="w-full" value="{{ old('med_numero_nf') }}" />
                    @error('med_numero_nf')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Vencimento Original --}}
                <div>
                    <x-input-label for="med_vencimento_original" value="Vencimento Original *" />
                    <x-text-input name="med_vencimento_original" id="med_vencimento_original" type="date" 
                        class="w-full" value="{{ old('med_vencimento_original') }}" />
                    @error('med_vencimento_original')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Mês de Referência --}}
                <div>
                    <x-input-label for="med_mes" value="Mês de Referência (MM/AAAA) *" />
                    <x-text-input name="med_mes" id="med_mes" placeholder="MM/AAAA" class="w-full" 
                        value="{{ old('med_mes') }}" x-mask="99/9999" />
                    @error('med_mes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Prorrogação --}}
                <div>
                    <x-input-label for="med_vencimento_prorrogado" value="Prorrogação (se houver)" />
                    <x-text-input name="med_vencimento_prorrogado" id="med_vencimento_prorrogado" type="date" 
                        class="w-full" value="{{ old('med_vencimento_prorrogado') }}" />
                    @error('med_vencimento_prorrogado')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Telefone --}}
                <div>
                    <x-input-label for="med_telefone" value="Telefone Financeiro" />
                    <x-text-input name="med_telefone" id="med_telefone" class="w-full" 
                        value="{{ old('med_telefone') }}" x-mask="(99) 99999-9999" />
                    @error('med_telefone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <x-input-label for="med_email" value="Email Financeiro" />
                    <x-text-input name="med_email" id="med_email" type="email" class="w-full" 
                        value="{{ old('med_email') }}" />
                    @error('med_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Cliente Atendido --}}
                <div>
                    <x-input-label for="med_cliente_atendido" value="Cliente Atendido *" />
                    <x-text-input name="med_cliente_atendido" id="med_cliente_atendido" class="w-full" 
                        value="{{ old('med_cliente_atendido') }}" />
                    @error('med_cliente_atendido')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Local de Atendimento --}}
                <div>
                    <x-input-label for="med_local" value="Local de Atendimento" />
                    <x-text-input name="med_local" id="med_local" class="w-full" 
                        value="{{ old('med_local') }}" />
                    @error('med_local')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Horários e Valores --}}
                <div class="md:col-span-2">
                    <x-input-label value="Horários e Valores *" />
                    <div id="med-horarios-wrapper" class="space-y-4">
                        @php
                            $oldHorarios = old('med_horarios', [['data' => '', 'entrada' => '', 'saida_almoco' => '', 
                                'retorno_almoco' => '', 'saida' => '', 'valor_hora' => '', 'total' => '', 'horas_trabalhadas' => '']]);
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
                            @checked(old('med_deslocamento'))
                            x-on:change="
                                document.getElementById('med_valor_deslocamento').disabled = !this.checked;
                                document.getElementById('med_valor_deslocamento').required = this.checked;
                            ">
                        <x-input-label for="med_valor_deslocamento" value="Valor do Deslocamento (R$)" class="ml-4 mr-2" />
                        <x-text-input name="med_valor_deslocamento" id="med_valor_deslocamento" type="number" step="0.01" 
                            class="w-32" value="{{ old('med_valor_deslocamento', 0) }}" 
                            :disabled="!old('med_deslocamento')" 
                            :required="old('med_deslocamento')" />
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
                            @checked(old('med_cobrou_almoco'))
                            x-on:change="
                                document.getElementById('med_valor_almoco').disabled = !this.checked;
                                document.getElementById('med_valor_almoco').required = this.checked;
                            ">
                        <x-input-label for="med_valor_almoco" value="Valor do Almoço (R$)" class="ml-4 mr-2" />
                        <x-text-input name="med_valor_almoco" id="med_valor_almoco" type="number" step="0.01" 
                            class="w-32" value="{{ old('med_valor_almoco', 0) }}" 
                            :disabled="!old('med_cobrou_almoco')" 
                            :required="old('med_cobrou_almoco')" />
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
                            @checked(old('med_reembolso_correios'))
                            x-on:change="
                                document.getElementById('med_valor_correios').disabled = !this.checked;
                                document.getElementById('med_valor_correios').required = this.checked;
                            ">
                        <x-input-label for="med_valor_correios" value="Valor dos Correios (R$)" class="ml-4 mr-2" />
                        <x-text-input name="med_valor_correios" id="med_valor_correios" type="number" step="0.01" 
                            class="w-32" value="{{ old('med_valor_correios', 0) }}" 
                            :disabled="!old('med_reembolso_correios')" 
                            :required="old('med_reembolso_correios')" />
                        <input type="hidden" name="med_valor_correios_fallback" value="0">
                    </div>
                    @error('med_valor_correios')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                
                <div class="mt-4">
                    <x-input-label for="med_valor_total_final" value="Valor Total Final (R$) *" />
                    <x-text-input name="med_valor_total_final" id="med_valor_total_final" type="number" step="0.01" 
                        class="w-full" value="{{ old('med_valor_total_final') }}" readonly />
                    @error('med_valor_total_final')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                {{-- Tipo de Pagamento --}}
                <div>
                    <x-input-label for="med_tipo_pagamento" value="Tipo de Pagamento" />
                    <select name="med_tipo_pagamento" id="med_tipo_pagamento" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                        <option value="">Selecione</option>
                        <option value="boleto" @selected(old('med_tipo_pagamento') == 'boleto')>Boleto</option>
                        <option value="deposito" @selected(old('med_tipo_pagamento') == 'deposito')>Depósito</option>
                        <option value="pix" @selected(old('med_tipo_pagamento') == 'pix')>Pix</option>
                    </select>
                    @error('med_tipo_pagamento')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Dados Bancários --}}
                <div class="md:col-span-2">
                    <x-input-label for="med_dados_bancarios" value="Dados Bancários (se aplicável)" />
                    <textarea name="med_dados_bancarios" id="med_dados_bancarios" rows="3" 
                        class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">{{ old('med_dados_bancarios') }}</textarea>
                    @error('med_dados_bancarios')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Observação --}}
                <div class="md:col-span-2">
                    <x-input-label for="med_observacao" value="Observação" />
                    <textarea name="med_observacao" id="med_observacao" rows="3" 
                        class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">{{ old('med_observacao') }}</textarea>
                    @error('med_observacao')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Botão de envio --}}
        <div class="text-right pt-4">
            <x-primary-button>Salvar Nota</x-primary-button>
        </div>
    </div>

    {{-- Formulário para Prestador --}}
    <div id="prestador-form" class="hidden">
        {{-- Seção: Informações da Nota --}}
        <div class="border-b border-gray-300 pb-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Informações da Nota</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Número da NF --}}
                <div>
                    <x-input-label for="prest_numero_nf" value="Número da NF" />
                    <x-text-input name="prest_numero_nf" id="prest_numero_nf" class="w-full" value="{{ old('prest_numero_nf') }}" />
                    @error('prest_numero_nf')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Prestador --}}
                <div>
                    <x-input-label for="prest_prestador" value="Prestador *" />
                    <x-text-input name="prest_prestador" id="prest_prestador" class="w-full" value="{{ old('prest_prestador') }}" />
                    @error('prest_prestador')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- CNPJ --}}
                <div>
                    <x-input-label for="prest_cnpj" value="CNPJ" />
                    <x-text-input name="prest_cnpj" id="prest_cnpj" class="w-full" value="{{ old('prest_cnpj') }}" 
                        x-mask="99.999.999/9999-99" placeholder="00.000.000/0000-00" />
                    @error('prest_cnpj')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Cidade --}}
                <div>
                    <x-input-label for="prest_cidade" value="Cidade *" />
                    <x-text-input name="prest_cidade" id="prest_cidade" type="text" class="w-full" value="{{ old('prest_cidade') }}" />
                    @error('prest_cidade')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Estado --}}
                <div>
                    <x-input-label for="prest_estado" value="Estado *" />
                    <x-text-input name="prest_estado" id="prest_estado" type="text" class="w-full" 
                        value="{{ old('prest_estado') }}" maxlength="2" placeholder="UF" x-mask="AA" />
                    @error('prest_estado')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Região --}}
                <div>
                    <x-input-label for="prest_regiao" value="Região do Brasil *" />
                    <select name="prest_regiao" id="prest_regiao" class="w-full rounded border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Selecione</option>
                        @foreach(['Norte', 'Nordeste', 'Centro-Oeste', 'Sudeste', 'Sul'] as $regiao)
                            <option value="{{ $regiao }}" @selected(old('prest_regiao') === $regiao)>
                                {{ $regiao }}
                            </option>
                        @endforeach
                    </select>
                    @error('prest_regiao')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Vencimento Original --}}
                <div>
                    <x-input-label for="prest_vencimento_original" value="Vencimento Original *" />
                    <x-text-input name="prest_vencimento_original" id="prest_vencimento_original" type="date" 
                        class="w-full" value="{{ old('prest_vencimento_original') }}" />
                    @error('prest_vencimento_original')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Prorrogação --}}
                <div>
                    <x-input-label for="prest_vencimento_prorrogado" value="Prorrogação (se houver)" />
                    <x-text-input name="prest_vencimento_prorrogado" id="prest_vencimento_prorrogado" type="date" 
                        class="w-full" value="{{ old('prest_vencimento_prorrogado') }}" />
                    @error('prest_vencimento_prorrogado')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Mês de Referência --}}
                <div>
                    <x-input-label for="prest_mes" value="Mês de Referência (MM/AAAA) *" />
                    <x-text-input name="prest_mes" id="prest_mes" placeholder="MM/AAAA" class="w-full" 
                        value="{{ old('prest_mes') }}" x-mask="99/9999" />
                    @error('prest_mes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Taxa de Correio --}}
                <div class="md:col-span-2">
                    <x-input-label for="prest_taxa_correio" value="Taxa de Correio?" />
                    <div class="flex items-center mt-2">
                        <input type="checkbox" name="prest_taxa_correio" id="prest_taxa_correio" 
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            value="1" @checked(old('prest_taxa_correio'))
                            x-on:change="document.getElementById('prest_valor_taxa_correio').disabled = !this.checked">
                        <x-input-label for="prest_valor_taxa_correio" value="Valor da Taxa (R$)" class="ml-4 mr-2" />
                        <x-text-input name="prest_valor_taxa_correio" id="prest_valor_taxa_correio" type="number" step="0.01" 
                            class="w-32" value="{{ old('prest_valor_taxa_correio') }}" 
                            :disabled="!old('prest_taxa_correio')" />
                    </div>
                    @error('prest_valor_taxa_correio')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Arquivos da NF --}}
                <div class="md:col-span-2">
                    <x-input-label for="prest_arquivo_nf" value="Arquivos da NF (PDFs) *" />
                    <input type="file" name="prest_arquivo_nf[]" id="prest_arquivo_nf" multiple 
                        accept="application/pdf" class="w-full dark:bg-gray-700 dark:text-white border-gray-300 rounded-md" />
                    @error('prest_arquivo_nf')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('prest_arquivo_nf.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Seção: Clientes Atendidos --}}
        <div class="border-b border-gray-300 pb-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Clientes Atendidos *</h3>
            <div id="prest-clientes-wrapper" class="space-y-6">
                @php
                    $oldClientes = old('prest_clientes', [['cliente_atendido' => '', 'valor' => '', 'observacao' => '']]);
                @endphp
                
                @foreach($oldClientes as $index => $cliente)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 cliente-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner">
                    <div>
                        <x-input-label value="Cliente Atendido *" />
                        <x-text-input name="prest_clientes[{{$index}}][cliente_atendido]" 
                            value="{{ $cliente['cliente_atendido'] ?? '' }}" class="w-full" />
                        @error("prest_clientes.$index.cliente_atendido")
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <x-input-label value="Valor (R$) *" />
                        <x-text-input name="prest_clientes[{{$index}}][valor]" type="number" step="0.01" 
                            value="{{ $cliente['valor'] ?? '' }}" class="w-full" />
                        @error("prest_clientes.$index.valor")
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label value="Observação" />
                        <textarea name="prest_clientes[{{$index}}][observacao]" 
                            class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white">{{ $cliente['observacao'] ?? '' }}</textarea>
                        @error("prest_clientes.$index.observacao")
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
                <button type="button" id="add-prest-cliente" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md">
                    + Adicionar Cliente
                </button>
            </div>
            @error('prest_clientes')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            {{-- Valor Total --}}
            <div>
                <x-input-label for="prest_valor_total" value="Valor Total (R$) *" />
                <x-text-input name="prest_valor_total" id="prest_valor_total" class="w-full" type="number" step="0.01" 
                    value="{{ old('prest_valor_total') }}" />
                @error('prest_valor_total')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tipo de Pagamento --}}
            <div>
                <x-input-label for="prest_tipo_pagamento" value="Tipo de Pagamento" />
                <select name="prest_tipo_pagamento" id="prest_tipo_pagamento" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                    <option value="">Selecione</option>
                    <option value="boleto" @selected(old('prest_tipo_pagamento') == 'boleto')>Boleto</option>
                    <option value="deposito" @selected(old('prest_tipo_pagamento') == 'deposito')>Depósito</option>
                    <option value="pix" @selected(old('prest_tipo_pagamento') == 'pix')>Pix</option>
                </select>
                @error('prest_tipo_pagamento')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Dados Bancários --}}
            <div class="md:col-span-2">
                <x-input-label for="prest_dados_bancarios" value="Dados Bancários (se aplicável)" />
                <textarea name="prest_dados_bancarios" id="prest_dados_bancarios" rows="3" 
                    class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">{{ old('prest_dados_bancarios') }}</textarea>
                @error('prest_dados_bancarios')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Observação --}}
            <div class="md:col-span-2">
                <x-input-label for="prest_observacao" value="Observação" />
                <textarea name="prest_observacao" id="prest_observacao" rows="3" 
                    class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">{{ old('prest_observacao') }}</textarea>
                @error('prest_observacao')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Glosar Nota --}}
            <div class="md:col-span-2">
                <x-input-label value="Glosar nota?" />
                <div class="flex items-center mt-2">
                    <div class="flex items-center mr-4">
                        <input type="radio" name="prest_glosar" id="prest_glosar_sim" value="1" 
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            @checked(old('prest_glosar'))
                            x-on:click="document.getElementById('glosaPrestadorCampos').style.display = 'block'">
                        <x-input-label for="prest_glosar_sim" value="Sim" class="ml-2" />
                    </div>
                    <div class="flex items-center">
                        <input type="radio" name="prest_glosar" id="prest_glosar_nao" value="0"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            @checked(!old('prest_glosar'))
                            x-on:click="document.getElementById('glosaPrestadorCampos').style.display = 'none'">
                        <x-input-label for="prest_glosar_nao" value="Não" class="ml-2" />
                    </div>
                </div>
                @error('prest_glosar')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Campos de Glosa --}}
            <div id="glosaPrestadorCampos" class="md:col-span-2" style="{{ old('prest_glosar') ? '' : 'display: none;' }}">
                <div class="mt-4">
                    <x-input-label for="prest_glosa_motivo" value="Motivo da Glosa" />
                    <textarea name="prest_glosa_motivo" id="prest_glosa_motivo" rows="3" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600"
                            @if(old('prest_glosar')) required @endif>{{ old('prest_glosa_motivo') }}</textarea>
                    @error('prest_glosa_motivo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <x-input-label for="prest_glosa_valor" value="Valor da Glosa (R$)" />
                    <x-text-input type="number" step="0.01" name="prest_glosa_valor" id="prest_glosa_valor" 
                                class="w-full" value="{{ old('prest_glosa_valor') }}"
                                :required="old('prest_glosar')" />
                    @error('prest_glosa_valor')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Botão de envio --}}
        <div class="text-right pt-4">
            <x-primary-button>Salvar Nota</x-primary-button>
        </div>
    </div>
</form>
<script>
/**
 * FormManager - Gerenciador de formulários modularizado
 */
class FormManager {
  constructor() {
    this.state = {
      currentForm: 'clinica',
      clienteCount: 1,
      prestClienteCount: 1,
      horarioCount: 1
    };

    this.cacheElements();
    this.initEventListeners();
    this.initializeForms();
  }

  // Cache de elementos DOM
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
        addCliente: document.getElementById('add-cliente'),
        addPrestCliente: document.getElementById('add-prest-cliente'),
        addHorario: document.getElementById('add-horario')
      },
      wrappers: {
        clientes: document.getElementById('clientes-wrapper'),
        prestClientes: document.getElementById('prest-clientes-wrapper'),
        horarios: document.getElementById('med-horarios-wrapper')
      },
      calculos: {
        valorTotal: document.getElementById('valor_total'),
        prestValorTotal: document.getElementById('prest_valor_total'),
        taxaCorreio: {
          checkbox: document.getElementById('taxa_correio'),
          valor: document.getElementById('valor_taxa_correio')
        },
        prestTaxaCorreio: {
          checkbox: document.getElementById('prest_taxa_correio'),
          valor: document.getElementById('prest_valor_taxa_correio')
        },
        medico: {
          deslocamento: {
            checkbox: document.getElementById('med_deslocamento'),
            valor: document.getElementById('med_valor_deslocamento')
          },
          almoco: {
            checkbox: document.getElementById('med_cobrou_almoco'),
            valor: document.getElementById('med_valor_almoco')
          },
          correios: {
            checkbox: document.getElementById('med_reembolso_correios'),
            valor: document.getElementById('med_valor_correios')
          },
          totalFinal: document.getElementById('med_valor_total_final')
        }
      }
    };
  }

  // Inicialização dos listeners
  initEventListeners() {
    // Listeners para troca de formulários
    this.elements.radioButtons.clinica.addEventListener('change', () => this.switchForm('clinica'));
    this.elements.radioButtons.medico.addEventListener('change', () => this.switchForm('medico'));
    this.elements.radioButtons.prestador.addEventListener('change', () => this.switchForm('prestador'));

    // Listeners para formulário clínica
    this.setupClinicaListeners();
    
    // Listeners para formulário médico
    this.setupMedicoListeners();
    
    // Listeners para formulário prestador
    this.setupPrestadorListeners();
    
    // Listeners para campos condicionais
    this.setupConditionalFieldsListeners();
  }

  // Inicialização dos formulários
  initializeForms() {
    this.updateFormStyles();
    this.calculateCurrentFormTotal();
    
    // Configura listeners para o primeiro item de horário
    const firstItem = this.elements.wrappers.horarios?.querySelector('.horario-item');
    if (firstItem) {
      this.setupDayListeners(firstItem);
    }
  }

  // Métodos para troca de formulários
  switchForm(formType) {
    this.state.currentForm = formType;
    this.toggleFormVisibility();
    this.updateFormStyles();
    this.calculateCurrentFormTotal();
  }

  toggleFormVisibility() {
    this.elements.forms.clinica.classList.toggle('hidden', this.state.currentForm !== 'clinica');
    this.elements.forms.medico.classList.toggle('hidden', this.state.currentForm !== 'medico');
    this.elements.forms.prestador.classList.toggle('hidden', this.state.currentForm !== 'prestador');
  }

  updateFormStyles() {
    const activeClasses = ['bg-indigo-600', 'text-white', 'border-indigo-600', 'hover:bg-indigo-700'];
    const inactiveClasses = ['bg-white', 'text-gray-900', 'border-gray-200', 'hover:bg-gray-50'];

    const toggleClasses = (element, isActive) => {
      element.classList.remove(...(isActive ? inactiveClasses : activeClasses));
      element.classList.add(...(isActive ? activeClasses : inactiveClasses));
    };

    toggleClasses(this.elements.labels.clinica, this.state.currentForm === 'clinica');
    toggleClasses(this.elements.labels.medico, this.state.currentForm === 'medico');
    toggleClasses(this.elements.labels.prestador, this.state.currentForm === 'prestador');
  }

  // Métodos para formulário clínica
  setupClinicaListeners() {
    if (!this.elements.buttons.addCliente) return;

    // Listener para adicionar cliente
    this.elements.buttons.addCliente.addEventListener('click', () => this.addCliente());
    
    // Listener delegado para cálculo automático
    this.elements.wrappers.clientes.addEventListener('input', (e) => {
      if (e.target.matches('input[name^="clientes"][name$="[valor]"]')) {
        this.calculateClinicaTotal();
      }
    });
    
    // Listener para taxa de correio
    if (this.elements.calculos.taxaCorreio?.valor) {
      this.elements.calculos.taxaCorreio.valor.addEventListener('input', () => this.calculateClinicaTotal());
    }
    
    // Listener para glosa
    document.querySelectorAll('input[name="glosar"]').forEach(radio => {
      radio.addEventListener('change', () => this.calculateClinicaTotal());
    });
    
    document.getElementById('glosa_valor')?.addEventListener('input', () => this.calculateClinicaTotal());
  }

  addCliente() {
    const clienteHTML = `
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 cliente-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner mt-4">
        <div>
          <x-input-label value="Cliente Atendido" />
          <x-text-input name="clientes[${this.state.clienteCount}][cliente_atendido]" class="w-full" />
        </div>
        <div>
          <x-input-label value="Valor (R$)" />
          <x-text-input name="clientes[${this.state.clienteCount}][valor]" type="number" step="0.01" class="w-full cliente-valor" />
        </div>
        <div class="md:col-span-2">
          <x-input-label value="Observação" />
          <textarea name="clientes[${this.state.clienteCount}][observacao]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white"></textarea>
        </div>
      </div>
    `;
    
    this.elements.wrappers.clientes.insertAdjacentHTML('beforeend', clienteHTML);
    
    // Configura listener para o novo campo de valor
    const newInput = this.elements.wrappers.clientes.lastElementChild.querySelector('.cliente-valor');
    newInput.addEventListener('input', () => this.calculateClinicaTotal());
    
    this.state.clienteCount++;
  }

  calculateClinicaTotal() {
    let total = 0;

    // 1. Somar valores dos clientes
    const valoresClientes = this.elements.wrappers.clientes.querySelectorAll('input[name^="clientes"][name$="[valor]"]');
    valoresClientes.forEach(input => {
      total += parseFloat(input.value) || 0;
    });

    // 2. Adicionar taxa de correio se marcada
    if (this.elements.calculos.taxaCorreio?.checkbox?.checked) {
      total += parseFloat(this.elements.calculos.taxaCorreio.valor.value) || 0;
    }

    // 3. Subtrair valor da glosa se aplicável
    const glosaAtiva = document.querySelector('input[name="glosar"][value="1"]')?.checked;
    const valorGlosa = parseFloat(document.getElementById('glosa_valor')?.value) || 0;
    
    if (glosaAtiva && valorGlosa > 0) {
      total -= valorGlosa;
      total = Math.max(total, 0); // Garante que não fique negativo
    }

    // 4. Atualizar o campo de total
    if (this.elements.calculos.valorTotal) {
      this.elements.calculos.valorTotal.value = total.toFixed(2);
    }
  }

  // Métodos para formulário prestador
  setupPrestadorListeners() {
    if (!this.elements.buttons.addPrestCliente) return;

    // Listener para adicionar cliente
    this.elements.buttons.addPrestCliente.addEventListener('click', () => this.addPrestCliente());
    
    // Listener delegado para cálculo automático
    this.elements.wrappers.prestClientes.addEventListener('input', (e) => {
      if (e.target.matches('input[name^="prest_clientes"][name$="[valor]"]')) {
        this.calculatePrestadorTotal();
      }
    });
    
    // Listener para taxa de correio
    if (this.elements.calculos.prestTaxaCorreio?.valor) {
      this.elements.calculos.prestTaxaCorreio.valor.addEventListener('input', () => this.calculatePrestadorTotal());
    }
    
    // Listener para glosa
    document.querySelectorAll('input[name="prest_glosar"]').forEach(radio => {
      radio.addEventListener('change', () => this.calculatePrestadorTotal());
    });
    
    document.getElementById('prest_glosa_valor')?.addEventListener('input', () => this.calculatePrestadorTotal());
  }

  addPrestCliente() {
    const clienteHTML = `
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 cliente-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner mt-4">
        <div>
          <x-input-label value="Cliente Atendido" />
          <x-text-input name="prest_clientes[${this.state.prestClienteCount}][cliente_atendido]" class="w-full" />
        </div>
        <div>
          <x-input-label value="Valor (R$)" />
          <x-text-input name="prest_clientes[${this.state.prestClienteCount}][valor]" type="number" step="0.01" class="w-full prest-cliente-valor" />
        </div>
        <div class="md:col-span-2">
          <x-input-label value="Observação" />
          <textarea name="prest_clientes[${this.state.prestClienteCount}][observacao]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white"></textarea>
        </div>
      </div>
    `;
    
    this.elements.wrappers.prestClientes.insertAdjacentHTML('beforeend', clienteHTML);
    
    // Configura listener para o novo campo de valor
    const newInput = this.elements.wrappers.prestClientes.lastElementChild.querySelector('.prest-cliente-valor');
    newInput.addEventListener('input', () => this.calculatePrestadorTotal());
    
    this.state.prestClienteCount++;
  }

  calculatePrestadorTotal() {
    let total = 0;

    // 1. Somar valores dos clientes
    const valoresClientes = this.elements.wrappers.prestClientes.querySelectorAll('input[name^="prest_clientes"][name$="[valor]"]');
    valoresClientes.forEach(input => {
      total += parseFloat(input.value) || 0;
    });

    // 2. Adicionar taxa de correio se marcada
    if (this.elements.calculos.prestTaxaCorreio?.checkbox?.checked) {
      total += parseFloat(this.elements.calculos.prestTaxaCorreio.valor.value) || 0;
    }

    // 3. Subtrair valor da glosa se aplicável
    const glosaAtiva = document.querySelector('input[name="prest_glosar"][value="1"]')?.checked;
    const valorGlosa = parseFloat(document.getElementById('prest_glosa_valor')?.value) || 0;
    
    if (glosaAtiva && valorGlosa > 0) {
      total -= valorGlosa;
      total = Math.max(total, 0); // Garante que não fique negativo
    }

    // 4. Atualizar o campo de total
    if (this.elements.calculos.prestValorTotal) {
      this.elements.calculos.prestValorTotal.value = total.toFixed(2);
    }
  }

  // Métodos para formulário médico (mantido igual)
  setupMedicoListeners() {
    if (!this.elements.buttons.addHorario) return;

    this.elements.buttons.addHorario.addEventListener('click', () => this.addHorario());
    
    // Listeners para campos condicionais
    const medicoFields = this.elements.calculos.medico;
    if (medicoFields.deslocamento.valor) {
      medicoFields.deslocamento.valor.addEventListener('input', () => this.calculateMedicoTotal());
    }
    if (medicoFields.almoco.valor) {
      medicoFields.almoco.valor.addEventListener('input', () => this.calculateMedicoTotal());
    }
    if (medicoFields.correios.valor) {
      medicoFields.correios.valor.addEventListener('input', () => this.calculateMedicoTotal());
    }
  }

  addHorario() {
    const horarioHTML = `
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 horario-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner mt-4">
        <div>
          <x-input-label value="Data" />
          <x-text-input name="med_horarios[${this.state.horarioCount}][data]" type="date" class="w-full" />
        </div>
        <div>
          <x-input-label value="Entrada" />
          <x-text-input name="med_horarios[${this.state.horarioCount}][entrada]" type="time" class="w-full entrada" />
        </div>
        <div>
          <x-input-label value="Saída Almoço" />
          <x-text-input name="med_horarios[${this.state.horarioCount}][saida_almoco]" type="time" class="w-full saida-almoco" />
        </div>
        <div>
          <x-input-label value="Retorno Almoço" />
          <x-text-input name="med_horarios[${this.state.horarioCount}][retorno_almoco]" type="time" class="w-full retorno-almoco" />
        </div>
        <div>
          <x-input-label value="Saída" />
          <x-text-input name="med_horarios[${this.state.horarioCount}][saida]" type="time" class="w-full saida" />
        </div>
        <div class="md:col-span-2">
          <x-input-label value="Valor por Hora (R$)" />
          <x-text-input name="med_horarios[${this.state.horarioCount}][valor_hora]" type="number" step="0.01" class="w-full valor-hora" />
        </div>
        <div class="md:col-span-2">
          <x-input-label value="Total (R$)" />
          <x-text-input name="med_horarios[${this.state.horarioCount}][total]" type="number" step="0.01" class="w-full total" readonly />
        </div>
        <div class="md:col-span-1">
          <x-input-label value="Horas" />
          <x-text-input name="med_horarios[${this.state.horarioCount}][horas_trabalhadas]" type="text" class="w-full horas-trabalhadas" readonly />
        </div>
      </div>
    `;
    
    this.elements.wrappers.horarios.insertAdjacentHTML('beforeend', horarioHTML);
    
    // Configura os listeners para o novo item
    const newItem = this.elements.wrappers.horarios.lastElementChild;
    this.setupDayListeners(newItem);
    
    this.state.horarioCount++;
  }

  setupDayListeners(item) {
    const fields = {
      entrada: item.querySelector('.entrada'),
      saidaAlmoco: item.querySelector('.saida-almoco'),
      retornoAlmoco: item.querySelector('.retorno-almoco'),
      saida: item.querySelector('.saida'),
      valorHora: item.querySelector('.valor-hora'),
      total: item.querySelector('.total'),
      horasTrabalhadas: item.querySelector('.horas-trabalhadas')
    };

    const calculateDay = () => {
      if (fields.entrada.value && fields.saida.value && fields.valorHora.value) {
        const result = this.calculateDayTotal(
          fields.entrada.value,
          fields.saidaAlmoco.value,
          fields.retornoAlmoco.value,
          fields.saida.value,
          parseFloat(fields.valorHora.value)
        );
        
        fields.total.value = result.valor;
        fields.horasTrabalhadas.value = result.horas;
        this.calculateMedicoTotal();
      }
    };

    Object.values(fields).forEach(field => {
      if (field && (field.classList.contains('entrada') || 
          field.classList.contains('saida-almoco') || 
          field.classList.contains('retorno-almoco') || 
          field.classList.contains('saida') || 
          field.classList.contains('valor-hora'))) {
        field.addEventListener('change', calculateDay);
      }
    });
  }

  calculateDayTotal(entrada, saidaAlmoco, retornoAlmoco, saida, valorHora) {
    const toMinutes = time => {
      if (!time) return 0;
      const [hours, minutes] = time.split(':').map(Number);
      return hours * 60 + minutes;
    };

    const entradaMin = toMinutes(entrada);
    const saidaAlmocoMin = toMinutes(saidaAlmoco);
    const retornoAlmocoMin = toMinutes(retornoAlmoco);
    const saidaMin = toMinutes(saida);

    let totalMinutes = 0;

    if (entrada && saida) {
      if (saidaAlmoco && retornoAlmoco) {
        const manha = saidaAlmocoMin - entradaMin;
        const tarde = saidaMin - retornoAlmocoMin;
        totalMinutes = manha + tarde;
      } else {
        totalMinutes = saidaMin - entradaMin;
      }
    }

    const horasTrabalhadas = totalMinutes / 60;
    const horas = Math.floor(totalMinutes / 60);
    const minutos = totalMinutes % 60;
    
    return {
      valor: parseFloat((horasTrabalhadas * valorHora).toFixed(2)),
      horas: `${horas.toString().padStart(2, '0')}:${minutos.toString().padStart(2, '0')}`
    };
  }

  calculateMedicoTotal() {
    let totalValor = 0;
    let totalHoras = 0;
    let totalMinutos = 0;
    
    // Soma todos os dias de trabalho
    this.elements.wrappers.horarios.querySelectorAll('.horario-item').forEach(item => {
      const totalField = item.querySelector('.total');
      const horasField = item.querySelector('.horas-trabalhadas');
      
      if (totalField?.value) {
        totalValor += parseFloat(totalField.value);
      }
      
      if (horasField?.value) {
        const [horas, minutos] = horasField.value.split(':').map(Number);
        totalHoras += horas;
        totalMinutos += minutos;
      }
    });
    
    // Converter minutos excedentes em horas
    totalHoras += Math.floor(totalMinutos / 60);
    totalMinutos = totalMinutos % 60;
    
    // Adiciona extras se marcados
    const medicoFields = this.elements.calculos.medico;
    
    if (medicoFields.deslocamento.checkbox?.checked) {
      totalValor += parseFloat(medicoFields.deslocamento.valor.value) || 0;
    }
    
    if (medicoFields.almoco.checkbox?.checked) {
      totalValor += parseFloat(medicoFields.almoco.valor.value) || 0;
    }
    
    if (medicoFields.correios.checkbox?.checked) {
      totalValor += parseFloat(medicoFields.correios.valor.value) || 0;
    }
    
    // Atualiza o campo total final
    if (medicoFields.totalFinal) {
      medicoFields.totalFinal.value = totalValor.toFixed(2);
    }
    
    // Atualiza o total de horas
    const totalHorasElement = document.getElementById('med_total_horas');
    if (totalHorasElement) {
      totalHorasElement.value = `${totalHoras.toString().padStart(2, '0')}:${totalMinutos.toString().padStart(2, '0')}`;
    }
  }

  // Métodos para campos condicionais
  setupConditionalFieldsListeners() {
    const setupConditionalField = (checkbox, valorField) => {
      if (!checkbox || !valorField) return;
      
      checkbox.addEventListener('change', () => {
        valorField.disabled = !checkbox.checked;
        if (checkbox.checked) {
          valorField.focus();
        }
        this.calculateCurrentFormTotal();
      });
    };

    // Campos condicionais - Clínica
    if (this.elements.calculos.taxaCorreio) {
      setupConditionalField(
        this.elements.calculos.taxaCorreio.checkbox,
        this.elements.calculos.taxaCorreio.valor
      );
    }

    // Campos condicionais - Prestador
    if (this.elements.calculos.prestTaxaCorreio) {
      setupConditionalField(
        this.elements.calculos.prestTaxaCorreio.checkbox,
        this.elements.calculos.prestTaxaCorreio.valor
      );
    }

    // Campos condicionais - Médico
    const medicoFields = this.elements.calculos.medico;
    if (medicoFields) {
      setupConditionalField(medicoFields.deslocamento.checkbox, medicoFields.deslocamento.valor);
      setupConditionalField(medicoFields.almoco.checkbox, medicoFields.almoco.valor);
      setupConditionalField(medicoFields.correios.checkbox, medicoFields.correios.valor);
    }
  }

  // Método auxiliar para calcular o total do formulário atual
  calculateCurrentFormTotal() {
    switch (this.state.currentForm) {
      case 'clinica':
        this.calculateClinicaTotal();
        break;
      case 'medico':
        this.calculateMedicoTotal();
        break;
      case 'prestador':
        this.calculatePrestadorTotal();
        break;
    }
  }
}

// Inicialização quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
  new FormManager();
  GlosaManager.init();
});

/**
 * Glosa Manager - Gerenciador independente para campos de glosa
 */
class GlosaManager {
  static init() {
    // Configura listeners para os radio buttons de glosa
    document.querySelectorAll('input[name="glosar"], input[name="med_glosar"], input[name="prest_glosar"]').forEach(radio => {
      radio.addEventListener('change', (e) => {
        const formType = this.getFormType(e.target.name);
        this.toggle(formType, e.target.value === '1');
      });
    });

    // Auto mostrar glosa se já vier com valor preenchido (edição)
    this.checkPreFilledGlosa('clinica', 'glosa_valor', 'glosar');
    this.checkPreFilledGlosa('medico', 'med_glosa_valor', 'med_glosar');
    this.checkPreFilledGlosa('prestador', 'prest_glosa_valor', 'prest_glosar');
  }

  static getFormType(fieldName) {
    if (fieldName === 'glosar') return 'clinica';
    if (fieldName === 'med_glosar') return 'medico';
    if (fieldName === 'prest_glosar') return 'prestador';
    return '';
  }

  static toggle(formType, show) {
    const glosaCampos = document.getElementById(`glosa${this.capitalizeFirstLetter(formType)}Campos`);
    if (glosaCampos) {
      glosaCampos.style.display = show ? 'block' : 'none';
    }
  }

  static capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }

  static checkPreFilledGlosa(formType, valorId, radioName) {
    const glosaValor = parseFloat(document.getElementById(valorId)?.value) || 0;
    if (glosaValor > 0) {
      document.querySelector(`input[name="${radioName}"][value="1"]`).checked = true;
      this.toggle(formType, true);
    }
  }
}
</script>
</x-app-layout>