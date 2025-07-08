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

                            <label class="px-6 py-3 text-sm font-medium border-t border-b cursor-pointer
                                {{ $nota->tipo_nota === 'medico' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-gray-700 text-gray-900 border-gray-700' }}">
                                <input type="radio" name="tipo_nota" value="medico" class="hidden" {{ $nota->tipo_nota === 'medico' ? 'checked' : '' }}>
                                Médico
                            </label>

                            <label class="px-6 py-3 text-sm font-medium rounded-r-lg border cursor-pointer
                                {{ $nota->tipo_nota === 'prestador' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-gray-700 text-gray-900 border-gray-700' }}">
                                <input type="radio" name="tipo_nota" value="prestador" class="hidden" {{ $nota->tipo_nota === 'prestador' ? 'checked' : '' }}>
                                Prestador
                            </label>
                        </div>
                    </div>

                    {{-- Formulário Clínica --}}
                    <div id="clinica-form" class="{{ $nota->tipo_nota === 'clinica' ? '' : 'hidden' }}">
                        <!-- ... (mantenha o conteúdo existente do formulário clínica) ... -->
                    </div>

                    {{-- Formulário Médico --}}
                    <div id="medico-form" class="{{ $nota->tipo_nota === 'medico' ? '' : 'hidden' }}">
                        <!-- ... (mantenha o conteúdo existente do formulário médico) ... -->
                    </div>

                    {{-- Formulário Prestador --}}
                    <div id="prestador-form" class="{{ $nota->tipo_nota === 'prestador' ? '' : 'hidden' }}">
                        <div class="border-b border-gray-300 pb-6">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Informações da Nota</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <x-input-label for="prest_numero_nf" value="Número da NF" />
                                <x-text-input name="prest_numero_nf" id="prest_numero_nf" class="w-full" 
                                    value="{{ old('prest_numero_nf', $nota->numero_nf) }}" required />

                                <x-input-label for="prest_prestador" value="Prestador" />
                                <x-text-input name="prest_prestador" id="prest_prestador" class="w-full" 
                                    value="{{ old('prest_prestador', $nota->prestador) }}" required />

                                <x-input-label for="prest_cnpj" value="CNPJ" />
                                <x-text-input name="prest_cnpj" id="prest_cnpj" class="w-full" 
                                    value="{{ old('prest_cnpj', $nota->cnpj) }}" />

                                <x-input-label for="prest_cidade" value="Cidade" />
                                <x-text-input name="prest_cidade" id="prest_cidade" type="text" class="w-full" 
                                    value="{{ old('prest_cidade', $nota->cidade) }}" />

                                <x-input-label for="prest_estado" value="Estado" />
                                <x-text-input name="prest_estado" id="prest_estado" type="text" class="w-full" 
                                    value="{{ old('prest_estado', $nota->estado) }}" />

                                <x-input-label for="prest_regiao" value="Região do Brasil" />
                                <select name="prest_regiao" id="prest_regiao" class="w-full rounded border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Selecione</option>
                                    @foreach(['Norte', 'Nordeste', 'Centro-Oeste', 'Sudeste', 'Sul'] as $regiao)
                                        <option value="{{ $regiao }}" {{ old('prest_regiao', $nota->regiao) == $regiao ? 'selected' : '' }}>
                                            {{ $regiao }}
                                        </option>
                                    @endforeach
                                </select>

                                <x-input-label for="prest_vencimento_original" value="Vencimento Original" />
                                <x-text-input name="prest_vencimento_original" id="prest_vencimento_original" type="date" class="w-full" 
                                    value="{{ old('prest_vencimento_original', \Carbon\Carbon::parse($nota->vencimento_original)->format('Y-m-d')) }}" />

                                <x-input-label for="prest_vencimento_prorrogado" value="Prorrogação (se houver)" />
                                <x-text-input name="prest_vencimento_prorrogado" id="prest_vencimento_prorrogado" type="date" class="w-full" 
                                    value="{{ old('prest_vencimento_prorrogado', $nota->vencimento_prorrogado ? \Carbon\Carbon::parse($nota->vencimento_prorrogado)->format('Y-m-d') : '') }}" />

                                <x-input-label for="prest_mes" value="Mês de Referência (MM/AAAA)" />
                                <x-text-input name="prest_mes" id="prest_mes" placeholder="MM/AAAA" class="w-full" 
                                    value="{{ old('prest_mes', $nota->mes) }}" />

                                <div class="md:col-span-2">
                                    <x-input-label for="prest_taxa_correio" value="Taxa de Correio?" />
                                    <div class="flex items-center mt-2">
                                        <input type="checkbox" name="prest_taxa_correio" id="prest_taxa_correio" 
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                                            value="1" {{ old('prest_taxa_correio', $nota->taxa_correio) ? 'checked' : '' }}>
                                        <x-input-label for="prest_valor_taxa_correio" value="Valor da Taxa (R$)" class="ml-4 mr-2" />
                                        <x-text-input name="prest_valor_taxa_correio" id="prest_valor_taxa_correio" type="number" step="0.01" 
                                            class="w-32" value="{{ old('prest_valor_taxa_correio', $nota->valor_taxa_correio) }}" 
                                            {{ old('prest_taxa_correio', $nota->taxa_correio) ? '' : 'disabled' }} />
                                    </div>
                                </div>

                                <x-input-label for="prest_arquivo_nf" value="Arquivos da NF (PDFs)" />
                                <input type="file" name="prest_arquivo_nf[]" id="prest_arquivo_nf" multiple accept="application/pdf" 
                                    class="w-full dark:bg-gray-700 dark:text-white border-gray-300 rounded-md" />
                                
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

                        {{-- Seção: Clientes Atendidos --}}
                        <div class="border-b border-gray-300 pb-6">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Clientes Atendidos</h3>
                            <div id="prest-clientes-wrapper" class="space-y-6">
                                @foreach ($nota->notaClientes as $index => $cliente)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 cliente-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner">
                                        <div>
                                            <x-input-label value="Cliente Atendido" />
                                            <x-text-input name="prest_clientes[{{ $index }}][cliente_atendido]" class="w-full" 
                                                value="{{ old("prest_clientes.$index.cliente_atendido", $cliente->cliente_atendido) }}" required />
                                        </div>
                                        <div>
                                            <x-input-label value="Valor (R$)" />
                                            <x-text-input name="prest_clientes[{{ $index }}][valor]" type="number" step="0.01" class="w-full" 
                                                value="{{ old("prest_clientes.$index.valor", $cliente->valor) }}" required />
                                        </div>
                                        <div class="md:col-span-2">
                                            <x-input-label value="Observação" />
                                            <textarea name="prest_clientes[{{ $index }}][observacao]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white">
                                                {{ old("prest_clientes.$index.observacao", $cliente->observacao) }}
                                            </textarea>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4">
                                <button type="button" id="add-prest-cliente" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md">
                                    + Adicionar Cliente
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <x-input-label for="prest_valor_total" value="Valor Total (R$)" />
                            <x-text-input name="prest_valor_total" id="prest_valor_total" class="w-full" type="number" step="0.01" 
                                value="{{ old('prest_valor_total', $nota->valor_total) }}" />

                            <x-input-label for="prest_tipo_pagamento" value="Tipo de Pagamento" />
                            <select name="prest_tipo_pagamento" id="prest_tipo_pagamento" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                                <option value="">Selecione</option>
                                <option value="boleto" {{ old('prest_tipo_pagamento', $nota->tipo_pagamento) == 'boleto' ? 'selected' : '' }}>Boleto</option>
                                <option value="deposito" {{ old('prest_tipo_pagamento', $nota->tipo_pagamento) == 'deposito' ? 'selected' : '' }}>Depósito</option>
                                <option value="pix" {{ old('prest_tipo_pagamento', $nota->tipo_pagamento) == 'pix' ? 'selected' : '' }}>Pix</option>
                            </select>

                            <x-input-label for="prest_dados_bancarios" value="Dados Bancários (se aplicável)" />
                            <textarea name="prest_dados_bancarios" id="prest_dados_bancarios" rows="3" 
                                class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                                {{ old('prest_dados_bancarios', $nota->dados_bancarios) }}
                            </textarea>
                            
                            <x-input-label for="prest_observacao" value="Observação" />
                            <textarea name="prest_observacao" id="prest_observacao" rows="3" 
                                class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                                {{ old('prest_observacao', $nota->observacao) }}
                            </textarea>

                            <div class="md:col-span-2">
                                <x-input-label value="Glosar nota?" />
                                <div class="flex items-center mt-2">
                                    <div class="flex items-center mr-4">
                                        <input type="radio" name="prest_glosar" id="prest_glosar_sim" value="1" 
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            {{ old('prest_glosar', $nota->glosar) ? 'checked' : '' }}>
                                        <x-input-label for="prest_glosar_sim" value="Sim" class="ml-2" />
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" name="prest_glosar" id="prest_glosar_nao" value="0"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            {{ !old('prest_glosar', $nota->glosar) ? 'checked' : '' }}>
                                        <x-input-label for="prest_glosar_nao" value="Não" class="ml-2" />
                                    </div>
                                </div>
                            </div>

                            <div id="glosaPrestadorCampos" class="md:col-span-2 {{ old('prest_glosar', $nota->glosar) ? '' : 'hidden' }}">
                                <div class="mt-4">
                                    <x-input-label for="prest_glosa_motivo" value="Motivo da Glosa" />
                                    <textarea name="prest_glosa_motivo" id="prest_glosa_motivo" rows="3"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                                        {{ old('prest_glosa_motivo', $nota->glosa_motivo) }}
                                    </textarea>
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="prest_glosa_valor" value="Valor da Glosa (R$)" />
                                    <x-text-input type="number" step="0.01" name="prest_glosa_valor" id="prest_glosa_valor" 
                                        class="w-full" value="{{ old('prest_glosa_valor', $nota->glosa_valor) }}" />
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
            // Script para alternar entre os formulários
            document.querySelectorAll('input[name="tipo_nota"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.getElementById('clinica-form').classList.add('hidden');
                    document.getElementById('medico-form').classList.add('hidden');
                    document.getElementById('prestador-form').classList.add('hidden');
                    
                    document.getElementById(this.value + '-form').classList.remove('hidden');
                });
            });

            // Script para habilitar/desabilitar valor da taxa de correio
            document.getElementById('prest_taxa_correio').addEventListener('change', function() {
                const valorInput = document.getElementById('prest_valor_taxa_correio');
                valorInput.disabled = !this.checked;
                if (!this.checked) {
                    valorInput.value = '';
                }
            });

            // Script para mostrar/ocultar campos de glosa
            document.querySelectorAll('input[name="prest_glosar"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.getElementById('glosaPrestadorCampos').style.display = 
                        this.value === '1' ? 'block' : 'none';
                });
            });

            // Script para adicionar novo cliente (prestador)
            document.getElementById('add-prest-cliente').addEventListener('click', function() {
                const wrapper = document.getElementById('prest-clientes-wrapper');
                const index = wrapper.children.length;
                
                const newCliente = document.createElement('div');
                newCliente.className = 'grid grid-cols-1 md:grid-cols-2 gap-4 cliente-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner';
                newCliente.innerHTML = `
                    <div>
                        <x-input-label value="Cliente Atendido" />
                        <x-text-input name="prest_clientes[${index}][cliente_atendido]" class="w-full" required />
                    </div>
                    <div>
                        <x-input-label value="Valor (R$)" />
                        <x-text-input name="prest_clientes[${index}][valor]" type="number" step="0.01" class="w-full" required />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label value="Observação" />
                        <textarea name="prest_clientes[${index}][observacao]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white"></textarea>
                    </div>
                `;
                
                wrapper.appendChild(newCliente);
            });
        </script>
    @endpush
</x-app-layout>