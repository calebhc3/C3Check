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

                {{-- Seção: Informações da Nota --}}
                <div class="border-b border-gray-300 pb-6">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Informações da Nota</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <x-input-label for="numero_nf" value="Número da NF" />
                        <x-text-input name="numero_nf" id="numero_nf" class="w-full" required />

                        <x-input-label for="prestador" value="Prestador" />
                        <x-text-input name="prestador" id="prestador" class="w-full" required />

                        <x-input-label for="valor_total" value="Valor Total (R$)" />
                        <x-text-input name="valor_total" id="valor_total" class="w-full" required type="number" step="0.01" />

                        <x-input-label for="data_emissao" value="Data de Emissão" />
                        <x-text-input name="data_emissao" id="data_emissao" type="date" class="w-full" />

                        <x-input-label for="vencimento_original" value="Vencimento Original" />
                        <x-text-input name="vencimento_original" id="vencimento_original" type="date" class="w-full" />

                        <x-input-label for="vencimento_prorrogado" value="Prorrogação (se houver)" />
                        <x-text-input name="vencimento_prorrogado" id="vencimento_prorrogado" type="date" class="w-full" />

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

                <div class="text-right pt-4">
                    <x-primary-button>Salvar Nota</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let clienteIndex = 1;
        document.getElementById('add-cliente').addEventListener('click', () => {
            const wrapper = document.getElementById('clientes-wrapper');
            const html = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 cliente-item bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner mt-4">
                    <div>
                        <label class="block font-medium text-sm text-gray-700 dark:text-white">Cliente Atendido</label>
                        <input type="text" name="clientes[\${clienteIndex}][cliente_atendido]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white" />
                    </div>
                    <div>
                        <label class="block font-medium text-sm text-gray-700 dark:text-white">Valor (R$)</label>
                        <input type="number" step="0.01" name="clientes[\${clienteIndex}][valor]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block font-medium text-sm text-gray-700 dark:text-white">Observação</label>
                        <textarea name="clientes[\${clienteIndex}][observacao]" class="w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:text-white"></textarea>
                    </div>
                </div>
            `;
            wrapper.insertAdjacentHTML('beforeend', html);
            clienteIndex++;
        });
    </script>
</x-app-layout>
