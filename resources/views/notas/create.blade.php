<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 dark:text-white">
            Nova Nota de Pagamento
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto bg-white dark:bg-gray-800 shadow-xl rounded-xl p-8">
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                <ul class="list-disc list-inside text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
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
                <x-text-input name="numero_nf" id="numero_nf" class="w-full" />

                <x-input-label for="prestador" value="Prestador" />
                <x-text-input name="prestador" id="prestador" class="w-full" />

                <x-input-label for="cnpj" value="CNPJ" />
                <x-text-input name="cnpj" id="cnpj" class="w-full" />

                <x-input-label for="vencimento_original" value="Vencimento Original" />
                <x-text-input name="vencimento_original" id="vencimento_original" type="date" class="w-full" />

                <x-input-label for="vencimento_prorrogado" value="Prorrogação (se houver)" />
                <x-text-input name="vencimento_prorrogado" id="vencimento_prorrogado" type="date" class="w-full" />

                <x-input-label for="mes" value="Mês de Referência (MM/AAAA)" />
                <x-text-input name="mes" id="mes" placeholder="MM/AAAA" class="w-full" />

                <div class="md:col-span-2">
                    <x-input-label for="taxa_correio" value="Taxa de Correio?" />
                    <div class="flex items-center mt-2">
                        <input type="checkbox" name="taxa_correio" id="taxa_correio" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <x-input-label for="valor_taxa_correio" value="Valor da Taxa (R$)" class="ml-4 mr-2" />
                        <x-text-input name="valor_taxa_correio" id="valor_taxa_correio" type="number" step="0.01" class="w-32" disabled />
                    </div>
                </div>

            <x-input-label for="arquivo_nf" value="Arquivos da NF (PDFs)" />
            <input 
                type="file" 
                name="arquivo_nf[]" 
                id="arquivo_nf" 
                multiple 
                accept="application/pdf" 
                class="w-full dark:bg-gray-700 dark:text-white border-gray-300 rounded-md"
            />
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-input-label for="valor_total" value="Valor Total (R$)" />
            <x-text-input name="valor_total" id="valor_total" class="w-full" type="number" step="0.01" />

            <x-input-label for="tipo_pagamento" value="Tipo de Pagamento" />
            <select name="tipo_pagamento" id="tipo_pagamento" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white">
                <option value="">Selecione</option>
                <option value="boleto">Boleto</option>
                <option value="deposito">Depósito</option>
                <option value="pix">Pix</option>
            </select>

            <x-input-label for="dados_bancarios" value="Dados Bancários (se aplicável)" />
            <textarea name="dados_bancarios" id="dados_bancarios" rows="3" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white"></textarea>
            
            <x-input-label for="observacao" value="Observação" />
            <textarea name="observacao" id="observacao" rows="3" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white"></textarea>

            <div class="md:col-span-2">
                <x-input-label value="Glosar nota?" />
                <div class="flex items-center mt-2">
                    <div class="flex items-center mr-4">
                        <input type="radio" name="glosar" id="glosar_clinica_sim" value="1" onchange="toggleGlosa('clinica', true)" 
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <x-input-label for="glosar_clinica_sim" value="Sim" class="ml-2" />
                    </div>
                    <div class="flex items-center">
                        <input type="radio" name="glosar" id="glosar_clinica_nao" value="0" onchange="toggleGlosa('clinica', false)" checked 
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <x-input-label for="glosar_clinica_nao" value="Não" class="ml-2" />
                    </div>
                </div>
            </div>

            <div id="glosaClinicaCampos" class="md:col-span-2" style="display: none;">
                <div class="mt-4">
                    <x-input-label for="glosa_motivo" value="Motivo da Glosa" />
                    <textarea name="glosa_motivo" id="glosa_motivo" rows="3" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        {{ old('glosa_motivo', $nota->glosa_motivo ?? '') }}
                    </textarea>
                </div>

                <div class="mt-4">
                    <x-input-label for="glosa_valor" value="Valor da Glosa (R$)" />
                    <x-text-input type="number" step="0.01" name="glosa_valor" id="glosa_valor" 
                                class="w-full" 
                                value="{{ old('glosa_valor', $nota->glosa_valor ?? '') }}" />
                </div>
            </div>
        </div>

        {{-- Botão de envio para formulário Clínica --}}
        <div class="text-right pt-4">
            <x-primary-button>Salvar Nota</x-primary-button>
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
                <x-text-input name="med_numero_nf" id="med_numero_nf" class="w-full" />

                <x-input-label for="med_vencimento_original" value="Vencimento Original" />
                <x-text-input name="med_vencimento_original" id="med_vencimento_original" type="date" class="w-full" />

                <x-input-label for="med_mes" value="Mês de Referência (MM/AAAA)" />
                <x-text-input name="med_mes" id="med_mes" placeholder="MM/AAAA" class="w-full" />

                <x-input-label for="med_vencimento_prorrogado" value="Prorrogação (se houver)" />
                <x-text-input name="med_vencimento_prorrogado" id="med_vencimento_prorrogado" type="date" class="w-full" />

                <x-input-label for="med_telefone" value="Telefone Financeiro" />
                <x-text-input name="med_telefone" id="med_telefone" class="w-full" />

                <x-input-label for="med_email" value="Email Financeiro" />
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
                                <x-text-input name="med_horarios[0][data]" type="date" class="w-full" />
                            </div>
                            <div>
                                <x-input-label value="Entrada" />
                                <x-text-input name="med_horarios[0][entrada]" type="time" class="w-full entrada" />
                            </div>
                            <div>
                                <x-input-label value="Saída Almoço" />
                                <x-text-input name="med_horarios[0][saida_almoco]" type="time" class="w-full saida-almoco" />
                            </div>
                            <div>
                                <x-input-label value="Retorno Almoço" />
                                <x-text-input name="med_horarios[0][retorno_almoco]" type="time" class="w-full retorno-almoco" />
                            </div>
                            <div>
                                <x-input-label value="Saída" />
                                <x-text-input name="med_horarios[0][saida]" type="time" class="w-full saida" />
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label value="Valor por Hora (R$)" />
                                <x-text-input name="med_horarios[0][valor_hora]" type="number" step="0.01" class="w-full valor-hora" />
                            </div>
                            <div class="md:col-span-3">
                                <x-input-label value="Total (R$)" />
                                <x-text-input name="med_horarios[0][total]" type="number" step="0.01" class="w-full total" readonly />
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label value="Horas Trabalhadas" />
                                <x-text-input name="med_horarios[0][horas_trabalhadas]" type="text" class="w-full horas-trabalhadas" readonly />
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
                <textarea name="med_observacao" id="med_observacao" rows="3" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:text-white"></textarea>

            </div>
        </div>

        {{-- Botão de envio para formulário Médico --}}
        <div class="text-right pt-4">
            <x-primary-button>Salvar Nota</x-primary-button>
        </div>
    </div>
</form>
<script>
function toggleGlosa(formType, show) {
    const glosaCampos = document.getElementById(`glosa${formType.charAt(0).toUpperCase() + formType.slice(1)}Campos`);
    if (glosaCampos) {
        glosaCampos.style.display = show ? 'block' : 'none';
    }
}

function checkTipoNota() {
    const tipoNota = document.querySelector('input[name="tipo_nota"]:checked')?.value;
    
    // Mostra/oculta os formulários principais
    document.getElementById('clinica-form').style.display = tipoNota === 'clinica' ? 'block' : 'none';
    document.getElementById('medico-form').style.display = tipoNota === 'medico' ? 'block' : 'none';
    
    // Reseta os campos de glosa quando muda o tipo
    if (tipoNota === 'clinica') {
        document.querySelector('input[name="glosar"][value="0"]').checked = true;
        toggleGlosa('clinica', false);
    } else if (tipoNota === 'medico') {
        document.querySelector('input[name="med_glosar"][value="0"]').checked = true;
        toggleGlosa('medico', false);
    }
}

window.addEventListener('DOMContentLoaded', () => {
    checkTipoNota();

    // Configura listeners para os radio buttons de tipo de nota
    document.querySelectorAll('input[name="tipo_nota"]').forEach(radio => {
        radio.addEventListener('change', checkTipoNota);
    });

    // Auto mostrar glosa se já vier com valor preenchido (edição)
    const glosaValor = parseFloat(document.getElementById('glosa_valor')?.value || 0);
    if (glosaValor > 0) {
        document.querySelector('input[name="glosar"][value="1"]').checked = true;
        toggleGlosa('clinica', true);
    }

    const medGlosaValor = parseFloat(document.getElementById('med_glosa_valor')?.value || 0);
    if (medGlosaValor > 0) {
        document.querySelector('input[name="med_glosar"][value="1"]').checked = true;
        toggleGlosa('medico', true);
    }
});
/**
 * FormManager - Singleton para gerenciamento de formulários
 */
const FormManager = (function() {
    let instance;
    
    function createInstance() {
        // Estado do formulário
        const state = {
            currentForm: 'clinica',
            clienteCount: 1,
            horarioCount: 1
        };
        
        // Elementos do DOM
        const elements = {
            forms: {
                clinica: document.getElementById('clinica-form'),
                medico: document.getElementById('medico-form')
            },
            radioButtons: {
                clinica: document.getElementById('tipo_clinica'),
                medico: document.getElementById('tipo_medico')
            },
            labels: {
                clinica: document.getElementById('label_clinica'),
                medico: document.getElementById('label_medico')
            },
            buttons: {
                addCliente: document.getElementById('add-cliente'),
                addHorario: document.getElementById('add-horario')
            },
            wrappers: {
                clientes: document.getElementById('clientes-wrapper'),
                horarios: document.getElementById('med-horarios-wrapper')
            },
            calculos: {
                valorTotal: document.getElementById('valor_total'),
                taxaCorreio: {
                    checkbox: document.getElementById('taxa_correio'),
                    valor: document.getElementById('valor_taxa_correio')
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
        
        // Classes CSS
        const classes = {
            active: ['bg-indigo-600', 'text-white', 'border-indigo-600', 'hover:bg-indigo-700'],
            inactive: ['bg-white', 'text-gray-900', 'border-gray-200', 'hover:bg-gray-50']
        };
        
        // Métodos privados
        const privateMethods = {
            toggleClasses(element, addClasses, removeClasses) {
                element.classList.remove(...removeClasses);
                element.classList.add(...addClasses);
            },
            
            updateFormStyles() {
                if (state.currentForm === 'clinica') {
                    privateMethods.toggleClasses(
                        elements.labels.clinica, 
                        classes.active, 
                        classes.inactive
                    );
                    privateMethods.toggleClasses(
                        elements.labels.medico, 
                        classes.inactive, 
                        classes.active
                    );
                } else {
                    privateMethods.toggleClasses(
                        elements.labels.medico, 
                        classes.active, 
                        classes.inactive
                    );
                    privateMethods.toggleClasses(
                        elements.labels.clinica, 
                        classes.inactive, 
                        classes.active
                    );
                }
            },
            
            toggleFormVisibility() {
                elements.forms.clinica.classList.toggle('hidden', state.currentForm !== 'clinica');
                elements.forms.medico.classList.toggle('hidden', state.currentForm !== 'medico');
            },
            
            setupConditionalFields() {
                // Campos condicionais - Clínica
                elements.calculos.taxaCorreio.checkbox.addEventListener('change', () => {
                    const { valor } = elements.calculos.taxaCorreio;
                    valor.disabled = !elements.calculos.taxaCorreio.checkbox.checked;
                    if (elements.calculos.taxaCorreio.checkbox.checked) {
                        valor.focus();
                    }
                    privateMethods.calculateClinicaTotal();
                });
                
                // Campos condicionais - Médico
                elements.calculos.medico.deslocamento.checkbox.addEventListener('change', () => {
                    const { valor } = elements.calculos.medico.deslocamento;
                    valor.disabled = !elements.calculos.medico.deslocamento.checkbox.checked;
                    privateMethods.calculateMedicoTotal();
                });
                
                elements.calculos.medico.almoco.checkbox.addEventListener('change', () => {
                    const { valor } = elements.calculos.medico.almoco;
                    valor.disabled = !elements.calculos.medico.almoco.checkbox.checked;
                    privateMethods.calculateMedicoTotal();
                });
                
                elements.calculos.medico.correios.checkbox.addEventListener('change', () => {
                    const { valor } = elements.calculos.medico.correios;
                    valor.disabled = !elements.calculos.medico.correios.checkbox.checked;
                    privateMethods.calculateMedicoTotal();
                });
            },
            
            timeToMinutes(time) {
                if (!time) return 0;
                const [hours, minutes] = time.split(':').map(Number);
                return hours * 60 + minutes;
            },
            
            calculateDayTotal(entrada, saidaAlmoco, retornoAlmoco, saida, valorHora) {
            const entradaMin = privateMethods.timeToMinutes(entrada);
            const saidaAlmocoMin = privateMethods.timeToMinutes(saidaAlmoco);
            const retornoAlmocoMin = privateMethods.timeToMinutes(retornoAlmoco);
            const saidaMin = privateMethods.timeToMinutes(saida);

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
        },

            formatHours(totalMinutes) {
                const hours = Math.floor(totalMinutes / 60);
                const minutes = Math.round(totalMinutes % 60);
                return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
            },
            
            setupDayListeners(item) {
                const entrada = item.querySelector('.entrada');
                const saidaAlmoco = item.querySelector('.saida-almoco');
                const retornoAlmoco = item.querySelector('.retorno-almoco');
                const saida = item.querySelector('.saida');
                const valorHora = item.querySelector('.valor-hora');
                const total = item.querySelector('.total');
                const horasTrabalhadas = item.querySelector('.horas-trabalhadas');
                
                const calculateDay = () => {
                    if (entrada.value && saida.value && valorHora.value) {
                        const result = privateMethods.calculateDayTotal(
                            entrada.value,
                            saidaAlmoco.value,
                            retornoAlmoco.value,
                            saida.value,
                            parseFloat(valorHora.value)
                        );
                        
                        total.value = result.valor;
                        horasTrabalhadas.value = result.horas;
                        privateMethods.calculateMedicoTotal();
                    }
                };
                
                entrada.addEventListener('change', calculateDay);
                saidaAlmoco.addEventListener('change', calculateDay);
                retornoAlmoco.addEventListener('change', calculateDay);
                saida.addEventListener('change', calculateDay);
                valorHora.addEventListener('input', calculateDay);
            },
            
            calculateClinicaTotal() {
                let total = 0;

                // Somar valores dos clientes
                const valoresClientes = elements.wrappers.clientes.querySelectorAll('input[name^="clientes"][name$="[valor]"]');
                valoresClientes.forEach(input => {
                    const valor = parseFloat(input.value) || 0;
                    total += valor;
                });

                // Adicionar taxa de correio se marcada
                if (elements.calculos.taxaCorreio.checkbox.checked) {
                    const taxa = parseFloat(elements.calculos.taxaCorreio.valor.value) || 0;
                    total += taxa;
                }

                elements.calculos.valorTotal.value = total.toFixed(2);
            },
            
            calculateMedicoTotal() {
            let totalValor = 0;
            let totalHoras = 0;
            let totalMinutos = 0;
            
            // Soma todos os dias de trabalho
            elements.wrappers.horarios.querySelectorAll('.horario-item').forEach(item => {
                const totalField = item.querySelector('.total');
                const horasField = item.querySelector('.horas-trabalhadas');
                
                if (totalField && totalField.value) {
                    totalValor += parseFloat(totalField.value);
                }
                
                if (horasField && horasField.value) {
                    const [horas, minutos] = horasField.value.split(':').map(Number);
                    totalHoras += horas;
                    totalMinutos += minutos;
                }
            });
            
            // Converter minutos excedentes em horas
            totalHoras += Math.floor(totalMinutos / 60);
            totalMinutos = totalMinutos % 60;
            
            // Adiciona deslocamento se marcado
            if (elements.calculos.medico.deslocamento.checkbox.checked) {
                totalValor += parseFloat(elements.calculos.medico.deslocamento.valor.value) || 0;
            }
            
            // Adiciona almoço se marcado
            if (elements.calculos.medico.almoco.checkbox.checked) {
                totalValor += parseFloat(elements.calculos.medico.almoco.valor.value) || 0;
            }
            
            // Adiciona correios se marcado
            if (elements.calculos.medico.correios.checkbox.checked) {
                totalValor += parseFloat(elements.calculos.medico.correios.valor.value) || 0;
            }
            
            // Atualiza o campo total final
            elements.calculos.medico.totalFinal.value = totalValor.toFixed(2);
            
            // Atualiza o total de horas (adicionar este campo no seu HTML se ainda não existir)
            const totalHorasElement = document.getElementById('med_total_horas');
            if (totalHorasElement) {
                totalHorasElement.value = `${totalHoras.toString().padStart(2, '0')}:${totalMinutos.toString().padStart(2, '0')}`;
            }
        },
            
            setupClinicaListeners() {
                // Listener para adicionar cliente
                elements.buttons.addCliente.addEventListener('click', () => {
                    const html = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 cliente-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner mt-4">
                            <div>
                                <x-input-label value="Cliente Atendido" />
                                <x-text-input name="clientes[${state.clienteCount}][cliente_atendido]" class="w-full" />
                            </div>
                            <div>
                                <x-input-label value="Valor (R$)" />
                                <x-text-input name="clientes[${state.clienteCount}][valor]" type="number" step="0.01" class="w-full cliente-valor" />
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label value="Observação" />
                                <textarea name="clientes[${state.clienteCount}][observacao]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white"></textarea>
                            </div>
                        </div>
                    `;
                    elements.wrappers.clientes.insertAdjacentHTML('beforeend', html);
                    
                    // Configura listener para o novo campo de valor
                    const newInput = elements.wrappers.clientes.lastElementChild.querySelector('.cliente-valor');
                    newInput.addEventListener('input', () => privateMethods.calculateClinicaTotal());
                    
                    state.clienteCount++;
                });
                
                // Listener para cálculo automático
                elements.wrappers.clientes.addEventListener('input', (e) => {
                    if (e.target.matches('input[name^="clientes"][name$="[valor]"]')) {
                        privateMethods.calculateClinicaTotal();
                    }
                });
                
                // Listener para taxa de correio
                elements.calculos.taxaCorreio.valor.addEventListener('input', () => {
                    privateMethods.calculateClinicaTotal();
                });
            },
            
            setupMedicoListeners() {
            // Listener para adicionar horário
            elements.buttons.addHorario.addEventListener('click', () => {
                const html = `
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 horario-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner mt-4">
                        <div>
                            <x-input-label value="Data" />
                            <x-text-input name="med_horarios[${state.horarioCount}][data]" type="date" class="w-full" />
                        </div>
                        <div>
                            <x-input-label value="Entrada" />
                            <x-text-input name="med_horarios[${state.horarioCount}][entrada]" type="time" class="w-full entrada" />
                        </div>
                        <div>
                            <x-input-label value="Saída Almoço" />
                            <x-text-input name="med_horarios[${state.horarioCount}][saida_almoco]" type="time" class="w-full saida-almoco" />
                        </div>
                        <div>
                            <x-input-label value="Retorno Almoço" />
                            <x-text-input name="med_horarios[${state.horarioCount}][retorno_almoco]" type="time" class="w-full retorno-almoco" />
                        </div>
                        <div>
                            <x-input-label value="Saída" />
                            <x-text-input name="med_horarios[${state.horarioCount}][saida]" type="time" class="w-full saida" />
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label value="Valor por Hora (R$)" />
                            <x-text-input name="med_horarios[${state.horarioCount}][valor_hora]" type="number" step="0.01" class="w-full valor-hora" />
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label value="Total (R$)" />
                            <x-text-input name="med_horarios[${state.horarioCount}][total]" type="number" step="0.01" class="w-full total" readonly />
                        </div>
                        <div class="md:col-span-1">
                            <x-input-label value="Horas" />
                            <x-text-input name="med_horarios[${state.horarioCount}][horas_trabalhadas]" type="text" class="w-full horas-trabalhadas" readonly />
                        </div>
                    </div>
                `;
                elements.wrappers.horarios.insertAdjacentHTML('beforeend', html);
                
                // Configura os listeners para o novo item
                const newItem = elements.wrappers.horarios.lastElementChild;
                privateMethods.setupDayListeners(newItem);
                
                state.horarioCount++;
            });

            // Listeners para campos condicionais
            elements.calculos.medico.deslocamento.valor.addEventListener('input', () => {
                privateMethods.calculateMedicoTotal();
            });
            
            elements.calculos.medico.almoco.valor.addEventListener('input', () => {
                privateMethods.calculateMedicoTotal();
            });
            
            elements.calculos.medico.correios.valor.addEventListener('input', () => {
                privateMethods.calculateMedicoTotal();
            });
        },
        };
        
        // Inicialização
        function init() {
            // Configura listeners para alternar entre formulários
            elements.radioButtons.clinica.addEventListener('change', () => {
                state.currentForm = 'clinica';
                privateMethods.toggleFormVisibility();
                privateMethods.updateFormStyles();
            });
            
            elements.radioButtons.medico.addEventListener('change', () => {
                state.currentForm = 'medico';
                privateMethods.toggleFormVisibility();
                privateMethods.updateFormStyles();
            });
            
            // Configura listeners específicos de cada formulário
            privateMethods.setupClinicaListeners();
            privateMethods.setupMedicoListeners();
            privateMethods.setupConditionalFields();
            
            // Configura listeners para o primeiro item de horário
            const firstItem = elements.wrappers.horarios.querySelector('.horario-item');
            if (firstItem) {
                privateMethods.setupDayListeners(firstItem);
            }
            
            // Inicializa os estilos
            privateMethods.updateFormStyles();
            
            // Calcula totais iniciais
            privateMethods.calculateClinicaTotal();
            privateMethods.calculateMedicoTotal();
        }
        
        // Executa a inicialização
        init();
        
        // API pública
        return {
            getCurrentForm() {
                return state.currentForm;
            },
            
            recalculateTotals() {
                if (state.currentForm === 'clinica') {
                    privateMethods.calculateClinicaTotal();
                } else {
                    privateMethods.calculateMedicoTotal();
                }
            }
        };
    }
    
    return {
        getInstance() {
            if (!instance) {
                instance = createInstance();
            }
            return instance;
        }
    };
})();

// Inicializa o FormManager quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    FormManager.getInstance();
});
</script>
</x-app-layout>