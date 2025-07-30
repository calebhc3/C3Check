<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Nota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class NotaTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }
public function test_criar_nota_clinica_com_sucesso()
{
    $data = [
        'tipo_nota' => 'clinica',
        'numero_nf' => '123456',
        'prestador' => 'Clínica Saúde',
        'cnpj' => '12.345.678/0001-99',
        'valor_total' => 150.00,
        'data_emissao' => now()->format('Y-m-d'),
        'data_entregue_financeiro' => now()->format('Y-m-d'),
        'status' => 'lancada',
        'clientes' => [
            ['cliente_atendido' => 'Empresa A', 'valor' => 150.00],
        ],
        'arquivo_nf' => [UploadedFile::fake()->create('nota.pdf', 100)],
        'taxa_correio' => false,
        'glosar' => false
    ];

    $response = $this->post(route('notas.store'), $data);

    $response->assertValid();
    $response->assertRedirect();
    
    $this->assertDatabaseHas('notas', [
        'tipo_nota' => 'clinica',
        'numero_nf' => '123456',
        'prestador' => 'Clínica Saúde',
        'valor_total' => 150.00,
        'user_id' => $this->user->id,
        'status' => 'lancada'
    ]);

    $nota = Nota::first();
    $this->assertDatabaseHas('nota_clientes', [
        'nota_id' => $nota->id,
        'cliente_atendido' => 'Empresa A',
        'valor' => 150.00
    ]);
}
public function test_validacao_tipo_nota_obrigatorio_e_valido()
{
    // Teste sem tipo_nota (espera erro 422 por validação)
    $response = $this->post(route('notas.store'), [
        'valor_total' => 100,
        'clientes' => [['cliente_atendido' => 'Empresa A', 'valor' => 100]],
        'arquivo_nf' => [UploadedFile::fake()->create('nota.pdf', 100)],
    ]);
    $response->assertInvalid(['tipo_nota']);

    // Teste com tipo_nota inválido (espera redirecionamento + erro flash)
$response = $this->post(route('notas.store'), [
    'tipo_nota' => 'invalido',
    'valor_total' => 100,
    'clientes' => [['cliente_atendido' => 'Empresa A', 'valor' => 100]],
    'arquivo_nf' => [UploadedFile::fake()->create('nota.pdf', 100)],
]);

$response->assertStatus(302);
$response->assertInvalid(['tipo_nota']);
$this->assertDatabaseMissing('notas', ['tipo_nota' => 'invalido']);

}


    public function test_validacao_valor_total_obrigatorio_e_minimo()
    {
        // Sem valor_total
        $response = $this->post(route('notas.store'), [
            'tipo_nota' => 'clinica',
            'clientes' => [['cliente_atendido' => 'Empresa A', 'valor' => 100]],
            'arquivo_nf' => [UploadedFile::fake()->create('nota.pdf', 100)],
        ]);
        $response->assertInvalid(['valor_total']);

        // Valor_total zero
        $response = $this->post(route('notas.store'), [
            'tipo_nota' => 'clinica',
            'valor_total' => 0,
            'clientes' => [['cliente_atendido' => 'Empresa A', 'valor' => 100]],
            'arquivo_nf' => [UploadedFile::fake()->create('nota.pdf', 100)],
        ]);
        $response->assertInvalid(['valor_total']);
    }

    public function test_validacao_clientes_obrigatorios_e_validos()
    {
        // Sem clientes
        $response = $this->post(route('notas.store'), [
            'tipo_nota' => 'clinica',
            'valor_total' => 100,
            'arquivo_nf' => [UploadedFile::fake()->create('nota.pdf', 100)],
        ]);
        $response->assertInvalid(['clientes']);

        // Cliente sem nome
        $response = $this->post(route('notas.store'), [
            'tipo_nota' => 'clinica',
            'valor_total' => 100,
            'clientes' => [['valor' => 100]],
            'arquivo_nf' => [UploadedFile::fake()->create('nota.pdf', 100)],
        ]);
        $response->assertInvalid(['clientes.0.cliente_atendido']);

        // Valor zero
        $response = $this->post(route('notas.store'), [
            'tipo_nota' => 'clinica',
            'valor_total' => 100,
            'clientes' => [['cliente_atendido' => 'Empresa A', 'valor' => 0]],
            'arquivo_nf' => [UploadedFile::fake()->create('nota.pdf', 100)],
        ]);
        $response->assertInvalid(['clientes.0.valor']);
    }

    public function test_validacao_arquivo_nf_obrigatorio_e_tipo_pdf()
    {
        // Sem arquivo
        $response = $this->post(route('notas.store'), [
            'tipo_nota' => 'clinica',
            'valor_total' => 100,
            'clientes' => [['cliente_atendido' => 'Empresa A', 'valor' => 100]],
        ]);
        $response->assertInvalid(['arquivo_nf']);

        // Arquivo inválido
        $response = $this->post(route('notas.store'), [
            'tipo_nota' => 'clinica',
            'valor_total' => 100,
            'clientes' => [['cliente_atendido' => 'Empresa A', 'valor' => 100]],
            'arquivo_nf' => [UploadedFile::fake()->image('foto.jpg')],
        ]);
        $response->assertInvalid(['arquivo_nf.0']);
    }

public function test_criar_nota_medico_com_sucesso()
{
    $horarios = [
        [
            'data' => now()->format('Y-m-d'),
            'entrada' => '08:00',
            'saida_almoco' => null,
            'retorno_almoco' => null,
            'saida' => '12:00',
            'valor_hora' => 100,
            'total' => 400
        ]
    ];

    $response = $this->post(route('notas.store'), [
        'tipo_nota' => 'medico',
        'med_nome' => 'Dr. João Silva',
        'med_cliente_atendido' => 'Hospital ABC',
        'med_horarios' => $horarios,
        'med_valor_total_final' => 480,
        'med_numero_nf' => 'NF123456',
        'med_vencimento_original' => now()->addDays(30)->format('Y-m-d'),
        'med_deslocamento' => true,
        'med_valor_deslocamento' => 50,
        'med_cobrou_almoco' => true,
        'med_valor_almoco' => 30,
        'med_dados_bancarios' => json_encode(['banco' => 'Itau', 'agencia' => '1234', 'conta' => '56789']),
        'status' => 'lancada',
        'observacao' => 'Nota de teste para médico',
        'med_reembolso_correios' => false,
        'med_valor_correios' => 0,
        'arquivo_nf' => [UploadedFile::fake()->create('nota.pdf', 100)],
    ]);

    // Debug: mostra o conteúdo da resposta se houver erro
    if ($response->status() !== 302) {
        dd($response->getContent());
    }

    $response->assertValid();
    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
    
    // Verifica primeiro se há alguma nota no banco
    $this->assertNotEmpty(Nota::all(), 'Nenhuma nota foi criada no banco de dados');
    
    // Agora verifica os campos específicos
    $this->assertDatabaseHas('notas', [
        'tipo_nota' => 'medico', // Note que mudei de 'tipo_nota' para 'tipo' conforme seu serviço
        'med_nome' => 'Dr. João Silva',
        'med_cliente_atendido' => 'Hospital ABC',
        'med_valor_total_final' => 480,
        'med_deslocamento' => 1,
        'med_valor_deslocamento' => 50,
        'med_cobrou_almoco' => 1,
        'med_valor_almoco' => 30,
        'status' => 'lancada'
    ]);
}

public function test_validacao_nota_medico_campos_obrigatorios()
{
    $response = $this->post(route('notas.store'), [
        'tipo_nota' => 'medico',
        'arquivo_nf' => [UploadedFile::fake()->create('nota.pdf', 100)],
    ]);

    $response->assertInvalid([
        'med_nome',
        'med_cliente_atendido',
        'med_horarios',
        'med_valor_total_final',
        'med_numero_nf',
        'med_vencimento_original',
        'status'
    ]);
}

public function test_validacao_nota_prestador_campos_obrigatorios()
{
    $response = $this->post(route('notas.store'), [
        'tipo_nota' => 'prestador',
        'arquivo_nf' => [UploadedFile::fake()->create('nota.pdf', 100)],
    ]);

    $response->assertInvalid([
        'prest_numero_nf',
        'prest_prestador',
        'prest_cnpj',
        'prest_valor_total',
        'prest_vencimento_original',
        'prest_clientes',
        'status'
    ]);
}

public function test_validacao_nota_medico_formato_horarios()
{
    $horarios = [
        [
            'data' => 'data-invalida',
            'entrada' => 'horario-invalido',
            'saida' => 'horario-invalido',
            'valor_hora' => 'nao-numerico',
            'total' => 'nao-numerico'
        ]
    ];

    $response = $this->post(route('notas.store'), [
        'tipo_nota' => 'medico',
        'med_nome' => 'Dr. Teste',
        'med_cliente_atendido' => 'Hospital Teste',
        'med_horarios' => $horarios,
        'med_valor_total_final' => 100,
        'med_numero_nf' => 'NF123',
        'med_vencimento_original' => now()->format('Y-m-d'),
        'status' => 'lancada',
        'arquivo_nf' => [UploadedFile::fake()->create('nota.pdf', 100)],
    ]);

    $response->assertInvalid([
        'med_horarios.0.data',
        'med_horarios.0.entrada',
        'med_horarios.0.saida',
        'med_horarios.0.valor_hora',
        'med_horarios.0.total'
    ]);
}
    public function test_editar_nota_clinica()
    {
        $nota = Nota::factory()->create([
            'tipo_nota' => 'clinica',
            'user_id' => $this->user->id,
            'status' => 'lancada'
        ]);

        $response = $this->get(route('notas.edit', $nota));

        $response->assertOk();
        $response->assertViewIs('notas.edit');
        $response->assertViewHas('nota', $nota);
    }

    public function test_editar_nota_medico()
    {
        $nota = Nota::factory()->create([
            'tipo_nota' => 'medico',
            'user_id' => $this->user->id,
            'status' => 'lancada'
        ]);

        $response = $this->get(route('notas.edit', $nota));

        $response->assertOk();
        $response->assertViewIs('notas.edit');
        $response->assertViewHas('nota', $nota);
    }

    public function test_nao_pode_editar_nota_de_outro_usuario()
    {
        $outroUsuario = User::factory()->create();
        $nota = Nota::factory()->create(['user_id' => $outroUsuario->id]);

        $response = $this->get(route('notas.edit', $nota));

        $response->assertForbidden();
    }

    public function test_atualizar_nota_clinica_com_sucesso()
    {
        $nota = Nota::factory()->create([
            'tipo_nota' => 'clinica',
            'user_id' => $this->user->id,
            'status' => 'lancada'
        ]);

        $data = [
            'tipo_nota' => 'clinica',
            'numero_nf' => '654321',
            'prestador' => 'Clínica Atualizada',
            'cnpj' => '98.765.432/0001-11',
            'valor_total' => 200.00,
            'data_emissao' => now()->format('Y-m-d'),
            'data_entregue_financeiro' => now()->format('Y-m-d'),
            'status' => 'lancada',
            'clientes' => [
                ['cliente_atendido' => 'Empresa B', 'valor' => 200.00],
            ],
            'arquivo_nf' => [UploadedFile::fake()->create('nota_atualizada.pdf', 100)],
            'taxa_correio' => true,
            'valor_taxa_correio' => 10.00, // Adicionado este campo
            'glosar' => false
        ];

        $response = $this->put(route('notas.update', $nota), $data);

        $response->assertValid();
        $response->assertRedirect();
        
        $this->assertDatabaseHas('notas', [
            'id' => $nota->id,
            'numero_nf' => '654321',
            'prestador' => 'Clínica Atualizada',
            'valor_total' => 200.00
        ]);

        $this->assertDatabaseHas('nota_clientes', [
            'nota_id' => $nota->id,
            'cliente_atendido' => 'Empresa B',
            'valor' => 200.00
        ]);
    }

    public function test_atualizar_nota_medico_com_sucesso()
    {
        $nota = Nota::factory()->create([
            'tipo_nota' => 'medico',
            'user_id' => $this->user->id,
            'status' => 'lancada'
        ]);

        $horarios = [
            [
                'data' => now()->format('Y-m-d'),
                'entrada' => '09:00',
                'saida_almoco' => '12:00',
                'retorno_almoco' => '13:00',
                'saida' => '17:00',
                'valor_hora' => 120,
                'total' => 960 // 8 horas * 120
            ]
        ];

        $data = [
            'tipo_nota' => 'medico',
            'med_nome' => 'Dr. João Silva Atualizado',
            'med_cliente_atendido' => 'Hospital XYZ',
            'med_horarios' => $horarios,
            'med_valor_total_final' => 1080,
            'med_numero_nf' => 'NF654321',
            'med_vencimento_original' => now()->addDays(15)->format('Y-m-d'),
            'med_deslocamento' => true,
            'med_valor_deslocamento' => 80,
            'med_cobrou_almoco' => true,
            'med_valor_almoco' => 40,
            'med_dados_bancarios' => json_encode(['banco' => 'Bradesco', 'agencia' => '4321', 'conta' => '98765']),
            'status' => 'lancada',
            'med_reembolso_correios' => false,
            'med_valor_correios' => 0,
            'arquivo_nf' => [UploadedFile::fake()->create('nota_atualizada.pdf', 100)],
        ];

        $response = $this->put(route('notas.update', $nota), $data);

        $response->assertValid();
        $response->assertRedirect();
        
        $this->assertDatabaseHas('notas', [
            'id' => $nota->id,
            'med_nome' => 'Dr. João Silva Atualizado',
            'med_cliente_atendido' => 'Hospital XYZ',
            'med_valor_total_final' => 1080.00,
            'med_valor_deslocamento' => 80,
            'med_valor_almoco' => 40
        ]);
    }

    public function test_excluir_nota_com_sucesso()
    {
        $nota = Nota::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'lancada'
        ]);

        $response = $this->delete(route('notas.destroy', $nota));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success', 'Nota removida.');
        $this->assertSoftDeleted($nota);
    }

    public function test_nao_pode_excluir_nota_aprovada()
    {
        $nota = Nota::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'aprovada_chefia'
        ]);

        $response = $this->delete(route('notas.destroy', $nota));

        $response->assertForbidden();
        $this->assertDatabaseHas('notas', ['id' => $nota->id]);
    }

    public function test_visualizar_detalhes_nota_clinica()
    {
        $nota = Nota::factory()->create([
            'tipo_nota' => 'clinica',
            'user_id' => $this->user->id
        ]);

        $response = $this->get(route('notas.detalhes', $nota));

        $response->assertOk();
        $response->assertViewIs('notas.partials.detalhes-clinica');
    }

    public function test_visualizar_detalhes_nota_medico()
    {
        $nota = Nota::factory()->create([
            'tipo_nota' => 'medico',
            'user_id' => $this->user->id
        ]);

        $response = $this->get(route('notas.detalhes', $nota));

        $response->assertOk();
        $response->assertViewIs('notas.partials.detalhes-medico');
    }

    public function test_aprovar_nota()
    {
        $nota = Nota::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'lancada'
        ]);

        $response = $this->post(route('chefia.notas.aprovar', $nota));

        $response->assertRedirect();
        $this->assertDatabaseHas('notas', [
            'id' => $nota->id,
            'status' => 'aprovada_chefia'
        ]);
    }

    public function test_rejeitar_nota()
    {
    $nota = Nota::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'lancada',
        'aprovado_chefia_em' => null
    ]);

        $response = $this->post(route('chefia.notas.rejeitar', $nota), [
            'motivo_rejeicao' => 'Motivo de rejeição'

        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('notas', [
            'id' => $nota->id,
            'status' => 'rejeitada'
        ]);
    }
    
    private function dadosValidosBasicos(array $overrides = []): array
    {
        return array_merge([
            'tipo_nota' => 'clinica',
            'prestador' => 'Clinica Exemplo',
            'data_emissao' => now()->format('Y-m-d'),
            'data_entregue_financeiro' => now()->format('Y-m-d'),
            'status' => 'lancada',
            'valor_total' => 100,
            'clientes' => [['cliente_atendido' => 'Empresa A', 'valor' => 100]],
        ], $overrides);
    }

    public function test_upload_pdf_valido()
    {
        $dados = $this->dadosValidosBasicos([
            'arquivo_nf' => [UploadedFile::fake()->create('nota.pdf', 500)],
        ]);

        $response = $this->post(route('notas.store'), $dados);

        $response->assertValid();
        $response->assertRedirect();
        $this->assertDatabaseCount('notas', 1);
        $arquivos = Nota::first()->arquivo_nf;
        $this->assertNotEmpty($arquivos);
        Storage::disk('public')->assertExists($arquivos[0]);
    }

    public function test_rejeita_pdf_muito_grande()
    {
        $arquivoGrande = UploadedFile::fake()->create('grande.pdf', 11000); // 11MB

        $dados = $this->dadosValidosBasicos([
            'arquivo_nf' => [$arquivoGrande],
        ]);

        $response = $this->post(route('notas.store'), $dados);

        $response->assertInvalid(['arquivo_nf.0']);
    }

    public function test_upload_multiplos_pdfs()
    {
        $dados = $this->dadosValidosBasicos([
            'arquivo_nf' => [
                UploadedFile::fake()->create('nota1.pdf', 500),
                UploadedFile::fake()->create('nota2.pdf', 300),
            ],
        ]);

        $response = $this->post(route('notas.store'), $dados);

        $response->assertValid();
        $response->assertRedirect();
    }

    public function test_upload_pdf_sem_extensao()
    {
        $file = UploadedFile::fake()->create('arquivo', 300, 'application/pdf'); // Sem extensão no nome

        $dados = $this->dadosValidosBasicos([
            'arquivo_nf' => [$file],
        ]);

        $response = $this->post(route('notas.store'), $dados);

        $response->assertValid();
        $response->assertRedirect();
    }
public function test_exibe_erros_de_validacao_na_view()
{
    $response = $this->post(route('notas.store'), [
        'tipo_nota' => 'clinica', // Forçando o tipo para acionar as validações corretas
        'status' => 'lancada'
    ]);
    
    $response->assertInvalid([
        'prestador',
        'valor_total',
        'clientes',
        'arquivo_nf'
    ]);
}

public function test_exibe_erros_especificos_para_campos_invalidos()
{
    $invalidData = [
        'tipo_nota' => 'clinica',
        'prestador' => 'Teste',
        'valor_total' => 'abc',
        'clientes' => [['valor' => 'texto']],
        'arquivo_nf' => [UploadedFile::fake()->image('foto.jpg')],
        'status' => 'lancada'
    ];
    
    $response = $this->post(route('notas.store'), $invalidData);
    
    $response->assertInvalid([
        'valor_total',
        'clientes.0.cliente_atendido',
        'clientes.0.valor',
        'arquivo_nf.0'
    ]);
}

public function test_exibe_erros_para_nota_medico()
{
    $invalidMedicoData = [
        'tipo_nota' => 'medico',
        'med_horarios' => [['data' => 'invalido']],
        'arquivo_nf' => [UploadedFile::fake()->create('nota.pdf')],
        'status' => 'lancada'
    ];
    
    $response = $this->post(route('notas.store'), $invalidMedicoData);
    
    $response->assertInvalid([
        'med_nome',
        'med_horarios.0.data'
    ]);
}

public function test_exibe_erros_para_nota_prestador()
{
    $invalidPrestadorData = [
        'tipo_nota' => 'prestador',
        'prest_clientes' => [['valor' => 'texto']],
        'arquivo_nf' => [UploadedFile::fake()->create('nota.pdf')],
        'status' => 'lancada'
    ];
    
    $response = $this->post(route('notas.store'), $invalidPrestadorData);
    
    $response->assertInvalid([
        'prest_numero_nf',
        'prest_clientes.0.valor'
    ]);
}

public function test_exibe_erros_para_arquivos_invalidos()
{
    $invalidFileData = [
        'tipo_nota' => 'clinica',
        'prestador' => 'Teste',
        'valor_total' => 100,
        'clientes' => [['cliente_atendido' => 'Empresa A', 'valor' => 100]],
        'arquivo_nf' => [UploadedFile::fake()->create('nota.txt')],
        'status' => 'lancada'
    ];
    
    $response = $this->post(route('notas.store'), $invalidFileData);
    
    $response->assertInvalid([
        'arquivo_nf.0'
    ]);
}

public function test_exibe_erros_para_arquivos_grandes()
{
    $largeFile = UploadedFile::fake()->create('nota.pdf', 11000); // 11MB
    
    $invalidFileData = [
        'tipo_nota' => 'clinica',
        'prestador' => 'Teste',
        'valor_total' => 100,
        'clientes' => [['cliente_atendido' => 'Empresa A', 'valor' => 100]],
        'arquivo_nf' => [$largeFile],
        'status' => 'lancada'
    ];
    
    $response = $this->post(route('notas.store'), $invalidFileData);
    
    $response->assertInvalid([
        'arquivo_nf.0'
    ]);
}
}
