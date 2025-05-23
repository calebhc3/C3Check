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
                            <x-text-input name="numero_nf" id="numero_nf" class="w-full"  />

                            <x-input-label for="prestador" value="Prestador" />
                            <x-text-input name="prestador" id="prestador" class="w-full"  />

                            <x-input-label for="cnpj" value="CNPJ" />
                            <x-text-input name="cnpj" id="cnpj" class="w-full" />

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

                            <x-input-label for="arquivo_nf" value="Arquivo da NF (PDF)" />
                            <input type="file" name="arquivo_nf" id="arquivo_nf" accept="application/pdf" class="w-full dark:bg-gray-700 dark:text-white border-gray-300 rounded-md" />
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
                                                <x-input-label for="valor_total" class='mt-5' value="Valor Total (R$)" />
                            <x-text-input name="valor_total" id="valor_total" class="w-full mt-1"  type="number" step="0.01" />

                            <x-input-label for="tipo_pagamento" class='mt-5' value="Tipo de Pagamento" />
                            <select name="tipo_pagamento" id="tipo_pagamento" class="w-full mt-1 mb-5 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                                <option value="">Selecione</option>
                                <option value="boleto">Boleto</option>
                                <option value="deposito">Depósito</option>
                                <option value="pix">Pix</option>
                            </select>

                            <x-input-label for="dados_bancarios" value="Dados Bancários (se aplicável)" />
                            <textarea name="dados_bancarios" id="dados_bancarios" rows="3" class="w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white"></textarea>
                            
                            <x-input-label for="observacao" value="Observação" />
                            <textarea name="observacao" id="observacao" rows="3" class="w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white"></textarea>

                </div>
                </div>

                {{-- Formulário para Médico --}}
                <div id="medico-form" class="hidden">
                    <div class="border-b border-gray-300 pb-6">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Informações do Médico</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-input-label for="med_nome" value="Nome do Médico" />
                            <x-text-input name="med_nome" id="med_nome" class="w-full" />

                            <x-input-label for="med_numero_nf" value="Número da NF" />
                            <x-text-input name="med_numero_nf" id="med_numero_nf" class="w-full"  />

                            <x-input-label for="med_vencimento_original" value="Vencimento Original" />
                            <x-text-input name="med_vencimento_original" id="med_vencimento_original" type="date" class="w-full" />

                            <x-input-label for="med_mes" value="Mês de Referência (MM/AAAA)" />
                            <x-text-input name="med_mes" id="med_mes" placeholder="MM/AAAA" class="w-full" />

                            <x-input-label for="med_vencimento_prorrogado" value="Prorrogação (se houver)" />
                            <x-text-input name="med_vencimento_prorrogado" id="med_vencimento_prorrogado" type="date" class="w-full" />

                            <x-input-label for="med_telefone" value="Telefone Financeiro" />
                            <x-text-input name="med_telefone" id="med_telefone" class="w-full" />

                            <x-input-label for="med_email" value="Email Financeiro  " />
                            <x-text-input name="med_email" id="med_email" type="email" class="w-full" />

                            <x-input-label for="med_cliente_atendido" value="Cliente Atendido" />
                            <x-text-input name="med_cliente_atendido" id="med_cliente_atendido" class="w-full" />

                            <x-input-label for="med_local" value="Local de Atendimento" />
                            <x-text-input name="med_local" id="med_local" class="w-full" />

                <div class="md:col-span-2">
                    <x-input-label value="Horários e Valores" />
                    <div id="med-horarios-wrapper" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 horario-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner">
                            <div>
                                <x-input-label value="Data" />
                                <x-text-input name="med_horarios[0][data]" type="date" class="w-full"  />
                            </div>
                            <div>
                                <x-input-label value="Entrada" />
                                <x-text-input name="med_horarios[0][entrada]" type="time" class="w-full entrada"  />
                            </div>
                            <div>
                                <x-input-label value="Saída Almoço" />
                                <x-text-input name="med_horarios[0][saida_almoco]" type="time" class="w-full saida-almoco"  />
                            </div>
                            <div>
                                <x-input-label value="Retorno Almoço" />
                                <x-text-input name="med_horarios[0][retorno_almoco]" type="time" class="w-full retorno-almoco"  />
                            </div>
                            <div>
                                <x-input-label value="Saída" />
                                <x-text-input name="med_horarios[0][saida]" type="time" class="w-full saida"  />
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label value="Valor por Hora (R$)" />
                                <x-text-input name="med_horarios[0][valor_hora]" type="number" step="0.01" class="w-full valor-hora"  />
                            </div>
                            <div class="md:col-span-3">
                                <x-input-label value="Total (R$)" />
                                <x-text-input name="med_horarios[0][total]" type="number" step="0.01" class="w-full total" readonly />
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-horario" class="mt-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-md">
                        + Adicionar Dia de Trabalho
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


                            <x-input-label for="med_tipo_pagamento" value="Tipo de Pagamento" />
                            <select name="med_tipo_pagamento" id="med_tipo_pagamento" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                                <option value="">Selecione</option>
                                <option value="boleto">Boleto</option>
                                <option value="deposito">Depósito</option>
                                <option value="pix">Pix</option>
                            </select>

                            <x-input-label for="med_dados_bancarios" value="Dados Bancários (se aplicável)" />
                            <textarea name="med_dados_bancarios" id="med_dados_bancarios" rows="3" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white"></textarea>
                            
                            <x-input-label for="med_observacao" value="Observação" />
                            <textarea name="med_observacao" id="med_observacao" rows="3" class="w-full mt-1 border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white"></textarea>

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
    document.addEventListener('DOMContentLoaded', function() {
        // Função para calcular horas trabalhadas e valor total por dia
    function calculateDayTotal(entrada, saidaAlmoco, retornoAlmoco, saida, valorHora) {
        function timeToMinutes(time) {
            if (!time) return 0;
            const [hours, minutes] = time.split(':').map(Number);
            return hours * 60 + minutes;
        }

        const entradaMin = timeToMinutes(entrada);
        const saidaAlmocoMin = timeToMinutes(saidaAlmoco);
        const retornoAlmocoMin = timeToMinutes(retornoAlmoco);
        const saidaMin = timeToMinutes(saida);

let totalMinutes = 0;

if (entrada && saida) {
    const entradaMin = timeToMinutes(entrada);
    const saidaMin = timeToMinutes(saida);

    if (saidaAlmoco && retornoAlmoco) {
        const saidaAlmocoMin = timeToMinutes(saidaAlmoco);
        const retornoAlmocoMin = timeToMinutes(retornoAlmoco);
        const manha = saidaAlmocoMin - entradaMin;
        const tarde = saidaMin - retornoAlmocoMin;
        totalMinutes = manha + tarde;
    } else {
        // Sem horário de almoço → considera jornada corrida
        totalMinutes = saidaMin - entradaMin;
    }
}

        const horasTrabalhadas = totalMinutes / 60;
        const valorTotal = horasTrabalhadas * valorHora;

        return parseFloat(valorTotal.toFixed(2));
    }


    // Função para calcular o valor total final
    function calculateFinalTotal() {
        let total = 0;
        
        // Soma todos os dias de trabalho
        document.querySelectorAll('.horario-item').forEach(item => {
            const totalField = item.querySelector('.total');
            if (totalField && totalField.value) {
                total += parseFloat(totalField.value);
            }
        });
        
        // Adiciona deslocamento se marcado
        const deslocamentoCheck = document.getElementById('med_deslocamento');
        const deslocamentoValor = document.getElementById('med_valor_deslocamento');
        if (deslocamentoCheck.checked && deslocamentoValor.value) {
            total += parseFloat(deslocamentoValor.value);
        }
        
        // Adiciona almoço se marcado
        const almocoCheck = document.getElementById('med_cobrou_almoco');
        const almocoValor = document.getElementById('med_valor_almoco');
        if (almocoCheck.checked && almocoValor.value) {
            total += parseFloat(almocoValor.value);
        }
        
        // Adiciona correios se marcado
        const correiosCheck = document.getElementById('med_reembolso_correios');
        const correiosValor = document.getElementById('med_valor_correios');
        if (correiosCheck.checked && correiosValor.value) {
            total += parseFloat(correiosValor.value);
        }
        
        // Atualiza o campo total final
        const totalFinal = document.getElementById('med_valor_total_final');
        totalFinal.value = total.toFixed(2);
    }

    // Função para adicionar listeners a um item de horário
    function setupDayListeners(item) {
        const entrada = item.querySelector('.entrada');
        const saidaAlmoco = item.querySelector('.saida-almoco');
        const retornoAlmoco = item.querySelector('.retorno-almoco');
        const saida = item.querySelector('.saida');
        const valorHora = item.querySelector('.valor-hora');
        const total = item.querySelector('.total');
        
        const calculateDay = () => {
            if (entrada.value && saida.value && valorHora.value) {
                const dayTotal = calculateDayTotal(
                    entrada.value,
                    saidaAlmoco.value,
                    retornoAlmoco.value,
                    saida.value,
                    parseFloat(valorHora.value)
                );
                
                total.value = dayTotal;
                calculateFinalTotal();
            }
        };
        
        entrada.addEventListener('change', calculateDay);
        saidaAlmoco.addEventListener('change', calculateDay);
        retornoAlmoco.addEventListener('change', calculateDay);
        saida.addEventListener('change', calculateDay);
        valorHora.addEventListener('input', calculateDay);
    }

    // Adiciona listeners para campos condicionais
    document.getElementById('med_deslocamento').addEventListener('change', function() {
        const deslocamentoValor = document.getElementById('med_valor_deslocamento');
        deslocamentoValor.disabled = !this.checked;
        if (this.checked) {
            deslocamentoValor.focus();
        }
        calculateFinalTotal();
    });
    
    document.getElementById('med_valor_deslocamento').addEventListener('input', calculateFinalTotal);
    
    document.getElementById('med_cobrou_almoco').addEventListener('change', function() {
        const almocoValor = document.getElementById('med_valor_almoco');
        almocoValor.disabled = !this.checked;
        if (this.checked) {
            almocoValor.focus();
        }
        calculateFinalTotal();
    });
    
    document.getElementById('med_valor_almoco').addEventListener('input', calculateFinalTotal);
    
    document.getElementById('med_reembolso_correios').addEventListener('change', function() {
        const correiosValor = document.getElementById('med_valor_correios');
        correiosValor.disabled = !this.checked;
        if (this.checked) {
            correiosValor.focus();
        }
        calculateFinalTotal();
    });
    
    document.getElementById('med_valor_correios').addEventListener('input', calculateFinalTotal);

    // Adicionar novo dia de trabalho
    let horarioIndex = 1;
    document.getElementById('add-horario').addEventListener('click', () => {
        const wrapper = document.getElementById('med-horarios-wrapper');
        const html = `
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 horario-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner mt-4">
                <div>
                    <x-input-label value="Data" />
                    <x-text-input name="med_horarios[${horarioIndex}][data]" type="date" class="w-full"  />
                </div>
                <div>
                    <x-input-label value="Entrada" />
                    <x-text-input name="med_horarios[${horarioIndex}][entrada]" type="time" class="w-full entrada"  />
                </div>
                <div>
                    <x-input-label value="Saída Almoço" />
                    <x-text-input name="med_horarios[${horarioIndex}][saida_almoco]" type="time" class="w-full saida-almoco"  />
                </div>
                <div>
                    <x-input-label value="Retorno Almoço" />
                    <x-text-input name="med_horarios[${horarioIndex}][retorno_almoco]" type="time" class="w-full retorno-almoco"  />
                </div>
                <div>
                    <x-input-label value="Saída" />
                    <x-text-input name="med_horarios[${horarioIndex}][saida]" type="time" class="w-full saida"  />
                </div>
                <div class="md:col-span-2">
                    <x-input-label value="Valor por Hora (R$)" />
                    <x-text-input name="med_horarios[${horarioIndex}][valor_hora]" type="number" step="0.01" class="w-full valor-hora"  />
                </div>
                <div class="md:col-span-3">
                    <x-input-label value="Total (R$)" />
                    <x-text-input name="med_horarios[${horarioIndex}][total]" type="number" step="0.01" class="w-full total" readonly />
                </div>
            </div>
        `;
        wrapper.insertAdjacentHTML('beforeend', html);
        
        // Configura os listeners para o novo item
        const newItem = wrapper.lastElementChild;
        setupDayListeners(newItem);
        
        horarioIndex++;
    });

    // Configura os listeners para o primeiro item de horário
    const firstItem = document.querySelector('.horario-item');
    if (firstItem) {
        setupDayListeners(firstItem);
    }
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
            document.addEventListener('DOMContentLoaded', function () {
        const wrapper = document.getElementById('clientes-wrapper');
        const addClienteBtn = document.getElementById('add-cliente');
        const valorTotalInput = document.getElementById('valor_total');
        const taxaCorreioCheckbox = document.getElementById('taxa_correio');
        const valorTaxaCorreioInput = document.getElementById('valor_taxa_correio');

        // Ativa/desativa campo de valor do correio
        taxaCorreioCheckbox.addEventListener('change', () => {
            valorTaxaCorreioInput.disabled = !taxaCorreioCheckbox.checked;
            calcularValorTotal();
        });

        // Função mágica do cálculo
        function calcularValorTotal() {
            let total = 0;

            // Somar todos os campos de valor dos clientes
            const valoresClientes = wrapper.querySelectorAll('input[name^="clientes"][name$="[valor]"]');
            valoresClientes.forEach(input => {
                const valor = parseFloat(input.value);
                if (!isNaN(valor)) total += valor;
            });

            // Se a taxa de correio estiver marcada, adiciona
            if (taxaCorreioCheckbox.checked) {
                const taxa = parseFloat(valorTaxaCorreioInput.value);
                if (!isNaN(taxa)) total += taxa;
            }

            // Atualiza o campo de valor total
            valorTotalInput.value = total.toFixed(2);
        }

        // Listener pra recalcular sempre que algo mudar
        wrapper.addEventListener('input', function (e) {
            if (e.target.matches('input[name^="clientes"][name$="[valor]"]')) {
                calcularValorTotal();
            }
        });

        valorTaxaCorreioInput.addEventListener('input', calcularValorTotal);

        // Suporte à adição dinâmica de cliente
        addClienteBtn.addEventListener('click', () => {
            const index = wrapper.querySelectorAll('.cliente-item').length;
            const clienteHtml = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 cliente-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner">
                    <div>
                        <x-input-label value="Cliente Atendido" />
                        <x-text-input name="clientes[${index}][cliente_atendido]" class="w-full" />
                    </div>
                    <div>
                        <x-input-label value="Valor (R$)" />
                        <x-text-input name="clientes[${index}][valor]" type="number" step="0.01" class="w-full" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label value="Observação" />
                        <textarea name="clientes[${index}][observacao]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white"></textarea>
                    </div>
                </div>
            `;
            wrapper.insertAdjacentHTML('beforeend', clienteHtml);

            // Espera um pouquinho e força o recálculo
            setTimeout(calcularValorTotal, 100);
        });

        // Calcula inicialmente, caso o formulário venha preenchido
        calcularValorTotal();
    });
    </script>
</x-app-layout>