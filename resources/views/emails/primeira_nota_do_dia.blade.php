@component('mail::message')
# Primeira Nota do Dia Cadastrada

Esta é a primeira nota cadastrada hoje no sistema:

- **Tipo:** {{ ucfirst(str_replace('_', ' ', $dados['tipo_nota'])) }}
- **Prestador:** {{ $dados['prestador'] }}
- **Valor:** R$ {{ $dados['valor'] }}
- **Data/Hora:** {{ $dados['data_criacao'] }}
- **Responsável:** {{ $dados['responsavel'] }}
- **Total de notas hoje:** {{ $dados['total_notas_hoje'] }}

@component('mail::button', ['url' => $dados['link']])
Ver Nota
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent