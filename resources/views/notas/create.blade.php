<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 dark:text-white">
            Nova Nota de Pagamento
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto bg-white dark:bg-gray-800 shadow-xl rounded-xl p-8">
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
                        <label for="tipo_medico" id="label_medico" class="px-6 py-3 text-sm font-medium rounded-r-lg border border-gray-200
                            bg-white text-gray-900 border-gray-200
                            hover:bg-gray-50
                            cursor-pointer transition-all duration-200 ease-in-out">
                            Médico
                        </label>
                    </div>
                </div>

                {{-- Formulário para Clínica --}}
                <div id="clinica-form">
                    {{-- Seção: Informações da Nota --}}
                    <div class="border-b border-gray-300 pb-6">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Informações da Nota</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-input-label for="numero_nf" value="Número da NF" />
                            <x-text-input name="numero_nf" id="numero_nf" class="w-full" required />

                            <x-input-label for="prestador" value="Prestador" />
                            <x-text-input name="prestador" id="prestador" class="w-full" required />

                            <x-input-label for="cnpj" value="CNPJ" />
                            <x-text-input name="cnpj" id="cnpj" class="w-full" />

                            <x-input-label for="valor_total" value="Valor Total (R$)" />
                            <x-text-input name="valor_total" id="valor_total" class="w-full" required type="number" step="0.01" />

                            <x-input-label for="data_emissao" value="Data de Emissão" />
                            <x-text-input name="data_emissao" id="data_emissao" type="date" class="w-full" />

                            <x-input-label for="vencimento_original" value="Vencimento Original" />
                            <x-text-input name="vencimento_original" id="vencimento_original" type="date" class="w-full" />

                            <x-input-label for="data_entregue_financeiro" value="Data Entregue ao Financeiro" />
                            <x-text-input name="data_entregue_financeiro" id="data_entregue_financeiro" type="date" class="w-full" />

                            <x-input-label for="mes" value="Mês de Referência (MM/AAAA)" />
                            <x-text-input name="mes" id="mes" placeholder="MM/AAAA" class="w-full" />

                            <x-input-label for="vencimento_prorrogado" value="Prorrogação (se houver)" />
                            <x-text-input name="vencimento_prorrogado" id="vencimento_prorrogado" type="date" class="w-full" />

                            <div class="md:col-span-2">
                                <x-input-label for="taxa_correio" value="Taxa de Correio?" />
                                <div class="flex items-center mt-2">
                                    <input type="checkbox" name="taxa_correio" id="taxa_correio" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <x-input-label for="valor_taxa_correio" value="Valor da Taxa (R$)" class="ml-4 mr-2" />
                                    <x-text-input name="valor_taxa_correio" id="valor_taxa_correio" type="number" step="0.01" class="w-32" disabled />
                                </div>
                            </div>

                            <x-input-label for="tipo_pagamento" value="Tipo de Pagamento" />
                            <select name="tipo_pagamento" id="tipo_pagamento" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                                <option value="">Selecione</option>
                                <option value="boleto">Boleto</option>
                                <option value="deposito">Depósito</option>
                                <option value="pix">Pix</option>
                            </select>

                            <x-input-label for="dados_bancarios" value="Dados Bancários (se aplicável)" />
                            <textarea name="dados_bancarios" id="dados_bancarios" rows="3" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white"></textarea>

                            <x-input-label for="arquivo_nf" value="Arquivo da NF (PDF)" />
                            <input type="file" name="arquivo_nf" id="arquivo_nf" accept="application/pdf" class="w-full dark:bg-gray-700 dark:text-white border-gray-300 rounded-md" />
                        </div>
                    </div>

                    {{-- Seção: Clientes Atendidos --}}
                    <div class="border-b border-gray-300 pb-6">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Clientes Atendidos</h3>
                        <div id="clientes-wrapper" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 cliente-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner">
                                <div>
                                    <x-input-label value="Cliente Atendido" />
                                    <x-text-input name="clientes[0][cliente_atendido]" class="w-full" />
                                </div>
                                <div>
                                    <x-input-label value="Valor (R$)" />
                                    <x-text-input name="clientes[0][valor]" type="number" step="0.01" class="w-full" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label value="Observação" />
                                    <textarea name="clientes[0][observacao]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="button" id="add-cliente" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md">
                                + Adicionar Cliente
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Formulário para Médico --}}
                <div id="medico-form" class="hidden">
                    <div class="border-b border-gray-300 pb-6">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Informações do Médico</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-input-label for="med_nome" value="Nome do Médico" />
                            <x-text-input name="med_nome" id="med_nome" class="w-full" />

                            <x-input-label for="med_telefone" value="Telefone" />
                            <x-text-input name="med_telefone" id="med_telefone" class="w-full" />

                            <x-input-label for="med_email" value="Email" />
                            <x-text-input name="med_email" id="med_email" type="email" class="w-full" />

                            <x-input-label for="med_cliente_atendido" value="Cliente Atendido" />
                            <x-text-input name="med_cliente_atendido" id="med_cliente_atendido" class="w-full" />

                            <x-input-label for="med_local" value="Local de Atendimento" />
                            <x-text-input name="med_local" id="med_local" class="w-full" />

                            <div class="md:col-span-2">
                                <x-input-label value="Horários e Valores" />
                                <div id="med-horarios-wrapper" class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 horario-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner">
                                        <div>
                                            <x-input-label value="Data" />
                                            <x-text-input name="med_horarios[0][data]" type="date" class="w-full" />
                                        </div>
                                        <div>
                                            <x-input-label value="Horário" />
                                            <x-text-input name="med_horarios[0][horario]" type="time" class="w-full" />
                                        </div>
                                        <div>
                                            <x-input-label value="Valor (R$)" />
                                            <x-text-input name="med_horarios[0][valor]" type="number" step="0.01" class="w-full" />
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="add-horario" class="mt-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md">
                                    + Adicionar Horário
                                </button>
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="med_deslocamento" value="Deslocamento?" />
                                <div class="flex items-center mt-2">
                                    <input type="checkbox" name="med_deslocamento" id="med_deslocamento" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <x-input-label for="med_valor_deslocamento" value="Valor do Deslocamento (R$)" class="ml-4 mr-2" />
                                    <x-text-input name="med_valor_deslocamento" id="med_valor_deslocamento" type="number" step="0.01" class="w-32" disabled />
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="med_cobrou_almoco" value="Cobrou Almoço?" />
                                <div class="flex items-center mt-2">
                                    <input type="checkbox" name="med_cobrou_almoco" id="med_cobrou_almoco" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <x-input-label for="med_valor_almoco" value="Valor do Almoço (R$)" class="ml-4 mr-2" />
                                    <x-text-input name="med_valor_almoco" id="med_valor_almoco" type="number" step="0.01" class="w-32" disabled />
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                    <div>
                                        <x-input-label for="med_almoco_inicio" value="Início do Almoço" />
                                        <x-text-input name="med_almoco_inicio" id="med_almoco_inicio" type="time" class="w-full" disabled />
                                    </div>
                                    <div>
                                        <x-input-label for="med_almoco_fim" value="Fim do Almoço" />
                                        <x-text-input name="med_almoco_fim" id="med_almoco_fim" type="time" class="w-full" disabled />
                                    </div>
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="med_reembolso_correios" value="Reembolso de Correios?" />
                                <div class="flex items-center mt-2">
                                    <input type="checkbox" name="med_reembolso_correios" id="med_reembolso_correios" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <x-input-label for="med_valor_correios" value="Valor dos Correios (R$)" class="ml-4 mr-2" />
                                    <x-text-input name="med_valor_correios" id="med_valor_correios" type="number" step="0.01" class="w-32" disabled />
                                </div>
                            </div>

                            <x-input-label for="med_valor_total_final" value="Valor Total Final (R$)" />
                            <x-text-input name="med_valor_total_final" id="med_valor_total_final" type="number" step="0.01" class="w-full" />

                            <x-input-label for="med_dados_bancarios" value="Dados Bancários" />
                            <textarea name="med_dados_bancarios" id="med_dados_bancarios" rows="3" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white"></textarea>
                        </div>
                    </div>
                </div>

                <div class="text-right pt-4">
                    <x-primary-button>Salvar Nota</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Alternar entre formulários de Clínica e Médico
        document.addEventListener('DOMContentLoaded', function() {
            const tipoClinica = document.getElementById('tipo_clinica');
            const tipoMedico = document.getElementById('tipo_medico');
            const labelClinica = document.getElementById('label_clinica');
            const labelMedico = document.getElementById('label_medico');
            const formClinica = document.getElementById('clinica-form');
            const formMedico = document.getElementById('medico-form');

            // Função para atualizar estilos
            function updateStyles() {
                if (tipoClinica.checked) {
                    // Estilo para Clínica ativo
                    labelClinica.classList.remove('bg-white', 'text-gray-900', 'border-gray-200', 'hover:bg-gray-50');
                    labelClinica.classList.add('bg-indigo-600', 'text-white', 'border-indigo-600', 'hover:bg-indigo-700');
                    
                    // Estilo para Médico inativo
                    labelMedico.classList.remove('bg-indigo-600', 'text-white', 'border-indigo-600', 'hover:bg-indigo-700');
                    labelMedico.classList.add('bg-white', 'text-gray-900', 'border-gray-200', 'hover:bg-gray-50');
                } else {
                    // Estilo para Médico ativo
                    labelMedico.classList.remove('bg-white', 'text-gray-900', 'border-gray-200', 'hover:bg-gray-50');
                    labelMedico.classList.add('bg-indigo-600', 'text-white', 'border-indigo-600', 'hover:bg-indigo-700');
                    
                    // Estilo para Clínica inativo
                    labelClinica.classList.remove('bg-indigo-600', 'text-white', 'border-indigo-600', 'hover:bg-indigo-700');
                    labelClinica.classList.add('bg-white', 'text-gray-900', 'border-gray-200', 'hover:bg-gray-50');
                }
            }

            // Event listeners
            tipoClinica.addEventListener('change', function() {
                formClinica.classList.remove('hidden');
                formMedico.classList.add('hidden');
                updateStyles();
            });

            tipoMedico.addEventListener('change', function() {
                formClinica.classList.add('hidden');
                formMedico.classList.remove('hidden');
                updateStyles();
            });

            // Inicializa os estilos
            updateStyles();
        });

        // Habilitar/desabilitar campos condicionais
        document.getElementById('taxa_correio').addEventListener('change', function() {
            document.getElementById('valor_taxa_correio').disabled = !this.checked;
        });

        document.getElementById('med_deslocamento').addEventListener('change', function() {
            document.getElementById('med_valor_deslocamento').disabled = !this.checked;
        });

        document.getElementById('med_cobrou_almoco').addEventListener('change', function() {
            const disabled = !this.checked;
            document.getElementById('med_valor_almoco').disabled = disabled;
            document.getElementById('med_almoco_inicio').disabled = disabled;
            document.getElementById('med_almoco_fim').disabled = disabled;
        });

        document.getElementById('med_reembolso_correios').addEventListener('change', function() {
            document.getElementById('med_valor_correios').disabled = !this.checked;
        });

        // Adicionar clientes (para formulário de clínica)
        let clienteIndex = 1;
        document.getElementById('add-cliente').addEventListener('click', () => {
            const wrapper = document.getElementById('clientes-wrapper');
            const html = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 cliente-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner mt-4">
                    <div>
                        <label class="block font-medium text-sm text-gray-700 dark:text-white">Cliente Atendido</label>
                        <input type="text" name="clientes[${clienteIndex}][cliente_atendido]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white" />
                    </div>
                    <div>
                        <label class="block font-medium text-sm text-gray-700 dark:text-white">Valor (R$)</label>
                        <input type="number" step="0.01" name="clientes[${clienteIndex}][valor]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block font-medium text-sm text-gray-700 dark:text-white">Observação</label>
                        <textarea name="clientes[${clienteIndex}][observacao]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white"></textarea>
                    </div>
                </div>
            `;
            wrapper.insertAdjacentHTML('beforeend', html);
            clienteIndex++;
        });

        // Adicionar horários (para formulário de médico)
        let horarioIndex = 1;
        document.getElementById('add-horario').addEventListener('click', () => {
            const wrapper = document.getElementById('med-horarios-wrapper');
            const html = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 horario-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner">
                    <div>
                        <label class="block font-medium text-sm text-gray-700 dark:text-white">Data</label>
                        <input type="date" name="med_horarios[${horarioIndex}][data]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white" />
                    </div>
                    <div>
                        <label class="block font-medium text-sm text-gray-700 dark:text-white">Horário</label>
                        <input type="time" name="med_horarios[${horarioIndex}][horario]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white" />
                    </div>
                    <div>
                        <label class="block font-medium text-sm text-gray-700 dark:text-white">Valor (R$)</label>
                        <input type="number" step="0.01" name="med_horarios[${horarioIndex}][valor]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white" />
                    </div>
                </div>
            `;
            wrapper.insertAdjacentHTML('beforeend', html);
            horarioIndex++;
        });
    </script>
</x-app-layout>