<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Nota;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        // Inicializa coleções vazias
        $notasClinicas = collect();
        $notasMedicos = collect();
        $notasPrestadores = collect();
        $notasPendentes = collect();
        $historicoNotas = collect();

        // CONTAS
        if ($user->hasRole('contas')) {
            // CLÍNICAS
            $queryClinicas = Nota::clinicas()
                ->with('user')
                ->orderByDesc('data_emissao');

            // FILTROS CLÍNICAS
            if ($request->filled('prestador')) {
                $queryClinicas->where('prestador', 'like', '%' . $request->prestador . '%');
            }
            if ($request->filled('cnpj')) {
                $queryClinicas->where('cnpj', 'like', '%' . $request->cnpj . '%');
            }
            if ($request->filled('numero_nf')) {
                $queryClinicas->where('numero_nf', 'like', '%' . $request->numero_nf . '%');
            }
            if ($request->filled('status')) {
                $queryClinicas->where('status', $request->status);
            }

            $notasClinicas = $queryClinicas->paginate(10, ['*'], 'clinicas_page')->withQueryString();

            // MÉDICOS
            $queryMedicos = Nota::medicos()->with('user')->orderByDesc('data_emissao');
            if ($request->filled('med_nome')) {
                $queryMedicos->where('med_nome', 'like', '%' . $request->med_nome . '%');
            }
            if ($request->filled('numero_nf_medico')) {
                $queryMedicos->where('numero_nf', 'like', '%' . $request->numero_nf_medico . '%');
            }
            if ($request->filled('status_medico')) {
                $queryMedicos->where('status', $request->status_medico);
            }
            $notasMedicos = $queryMedicos->paginate(10, ['*'], 'medicos_page')->withQueryString();

            // PRESTADORES
            $queryPrestadores = Nota::where('tipo_nota', 'prestador')->with('user')->orderByDesc('data_emissao');
            if ($request->filled('nome_prestador')) {
                $queryPrestadores->where('prestador', 'like', '%' . $request->nome_prestador . '%');
            }
            if ($request->filled('cnpj_prestador')) {
                $queryPrestadores->where('cnpj', 'like', '%' . $request->cnpj_prestador . '%');
            }
            if ($request->filled('numero_nf_prestador')) {
                $queryPrestadores->where('numero_nf', 'like', '%' . $request->numero_nf_prestador . '%');
            }
            if ($request->filled('status_prestador')) {
                $queryPrestadores->where('status', $request->status_prestador);
            }
            $notasPrestadores = $queryPrestadores->paginate(10, ['*'], 'prestadores_page')->withQueryString();

            return view('dashboard', compact('notasClinicas', 'notasMedicos', 'notasPrestadores'));
        }

        // FINANCEIRO
        if ($user->hasRole('financeiro')) {
            $notasPendentes = Nota::where('status', 'aprovada_chefia')
                ->whereNull('confirmado_financeiro_em')
                ->orderBy('created_at', 'desc')
                ->paginate(10, ['*'], 'pendentes');

            $historicoNotas = Nota::whereNotNull('confirmado_financeiro_em')
                ->orderBy('confirmado_financeiro_em', 'desc')
                ->paginate(10, ['*'], 'historico');

            return view('dashboard', compact(
                'notasPendentes',
                'historicoNotas',
                'notasClinicas',
                'notasMedicos',
                'notasPrestadores'
            ));
        }

        // CHEFIA
        if ($user->hasRole('chefia')) {
            // Período selecionado (padrão: hoje)
            $periodo = $request->input('periodo', 'hoje');
            
            // Query base para métricas com filtro de período
            $queryMetricas = $this->aplicarFiltroPeriodo(Nota::query(), $periodo, $now);

            // Total de notas para o período selecionado
            $totalNotasPeriodo = $queryMetricas->count();

            // Base da query para notas pendentes
            $queryPendentes = Nota::query()
                ->where('status', 'lancada');

            // Aplicar filtros às queries
            $this->aplicarFiltrosComuns($request, $queryPendentes, $queryMetricas);

            // Resultados com paginação
            $notasPendentes = $queryPendentes
                ->orderBy('created_at', 'desc')
                ->paginate(10, ['*'], 'pendentes')
                ->withQueryString();

        // No método index() do controller, na parte do 'chefia':
        $queryHistorico = Nota::whereNotNull('aprovado_chefia_em');

        // Aplicar filtros ao histórico
        if ($request->filled('historico_cnpj')) {
            $queryHistorico->where('cnpj', 'like', '%' . $request->historico_cnpj . '%');
        }
        if ($request->filled('historico_numero_nf')) {
            $queryHistorico->where('numero_nf', 'like', '%' . $request->historico_numero_nf . '%');
        }
        if ($request->filled('historico_status')) {
            $queryHistorico->where('status', $request->historico_status);
        }
        if ($request->filled('historico_tipo_nota')) {
            $queryHistorico->where('tipo_nota', $request->historico_tipo_nota);
        }

        $historicoNotas = $queryHistorico
            ->orderBy('aprovado_chefia_em', 'desc')
            ->paginate(10, ['*'], 'historico')
            ->withQueryString();

            // Métricas para os gráficos
            $notasPorTipo = $this->getNotasPorTipo($queryMetricas);
            $notasPorRegiao = $this->getNotasPorRegiao($queryMetricas);
            $glosasPorRegiao = $this->getGlosasPorRegiao();
            $glosasPorUF = $this->getGlosasPorUF();
            $taxasPorRegiao = $this->getTaxasPorRegiao();
            $taxasPorUF = $this->getTaxasPorUF();
            $regioesMaisGlosasTaxas = $this->getRegioesMaisGlosasTaxas();

            return view('dashboard', [
                'notasPendentes' => $notasPendentes,
                'historicoNotas' => $historicoNotas,
                'totalNotasPeriodo' => $totalNotasPeriodo,
                'periodoSelecionado' => $periodo,
                
                // Dados para gráficos
                'notasClinicas' => $notasPorTipo['clinica'],
                'notasMedicos' => $notasPorTipo['medico'],
                'notasPrestadores' => $notasPorTipo['prestador'],
                'notasPorRegiao' => $notasPorRegiao,
                'glosasPorRegiao' => $glosasPorRegiao,
                'glosasPorUF' => $glosasPorUF,
                'taxasPorRegiao' => $taxasPorRegiao,
                'taxasPorUF' => $taxasPorUF,
                'regioesMaisGlosasTaxas' => $regioesMaisGlosasTaxas,
                
                // Métricas gerais
                'totalNotas' => Nota::count(),
                'notasHoje' => Nota::whereDate('created_at', $now->toDateString())->count(),
                'notasSemana' => Nota::whereBetween('created_at', [
                    $now->copy()->startOfWeek(),
                    $now->copy()->endOfWeek()
                ])->count(),
                'notasMes' => Nota::whereMonth('created_at', $now->month)
                    ->whereYear('created_at', $now->year)
                    ->count(),
                'notasAno' => Nota::whereYear('created_at', $now->year)->count(),
            ]);
        }

        // DEFAULT
        return view('dashboard', compact('notasClinicas', 'notasMedicos', 'notasPrestadores'));
    }

    /**
     * Aplica filtro de período à query
     */
    private function aplicarFiltroPeriodo($query, $periodo, $now)
    {
        // Garante que estamos usando o mesmo fuso horário do banco de dados
        $now->setTimezone(config('app.timezone'));
        
        switch ($periodo) {
            case 'hoje':
                // Usando whereBetween para garantir que pegue todo o dia
                return $query->whereBetween('created_at', [
                    $now->copy()->startOfDay(),
                    $now->copy()->endOfDay()
                ]);
            case 'semana':
                return $query->whereBetween('created_at', [
                    $now->copy()->startOfWeek(),
                    $now->copy()->endOfWeek()
                ]);
            case 'mes':
                return $query->whereMonth('created_at', $now->month)
                            ->whereYear('created_at', $now->year);
            case 'ano':
                return $query->whereYear('created_at', $now->year);
            default:
                return $query;
        }
    }

    /**
     * Aplica filtros comuns a múltiplas queries
     */
    private function aplicarFiltrosComuns($request, &$queryPendentes, &$queryMetricas)
    {
        if ($request->filled('cnpj')) {
            $queryPendentes->where('cnpj', 'like', '%' . $request->cnpj . '%');
            $queryMetricas->where('cnpj', 'like', '%' . $request->cnpj . '%');
        }
        if ($request->filled('numero_nf')) {
            $queryPendentes->where('numero_nf', 'like', '%' . $request->numero_nf . '%');
            $queryMetricas->where('numero_nf', 'like', '%' . $request->numero_nf . '%');
        }
        if ($request->filled('status')) {
            $queryPendentes->where('status', $request->status);
        }
        if ($request->filled('tipo_nota')) {
            $queryPendentes->where('tipo_nota', $request->tipo_nota);
            $queryMetricas->where('tipo_nota', $request->tipo_nota);
        }
    }

    /**
     * Obtém contagem de notas por tipo
     */
    private function getNotasPorTipo($queryMetricas)
    {
        return [
            'clinica' => (clone $queryMetricas)->where('tipo_nota', 'clinica')->count(),
            'medico' => (clone $queryMetricas)->where('tipo_nota', 'medico')->count(),
            'prestador' => (clone $queryMetricas)->where('tipo_nota', 'prestador')->count()
        ];
    }

    /**
     * Obtém dados de notas por região e UF
     */
    private function getNotasPorRegiao($queryMetricas)
    {
        return (clone $queryMetricas)
            ->select(
                'regiao',
                'estado',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('regiao', 'estado')
            ->orderBy('total', 'desc')
            ->get()
            ->groupBy('regiao')
            ->map(function ($items, $regiao) {
                return [
                    'regiao' => $regiao,
                    'total' => $items->sum('total'),
                    'ufs' => $items->map(function ($item) {
                        return [
                            'estado' => $item->estado,
                            'total' => $item->total
                        ];
                    })->toArray()
                ];
            })
            ->values()
            ->take(10);
    }

    /**
     * Obtém dados de glosas por região
     */
    private function getGlosasPorRegiao()
    {
        return Nota::where('tipo_nota', 'clinica')
            ->whereNotNull('glosa_valor')
            ->select(
                'regiao',
                'estado',
                'cidade',
                DB::raw('SUM(glosa_valor) as total_glosa'),
                DB::raw('COUNT(*) as quantidade')
            )
            ->groupBy('regiao', 'estado', 'cidade')
            ->orderBy('total_glosa', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Obtém dados de glosas por UF
     */
    private function getGlosasPorUF()
    {
        return Nota::where('tipo_nota', 'clinica')
            ->whereNotNull('glosa_valor')
            ->select(
                'estado',
                DB::raw('COUNT(*) as quantidade'),
                DB::raw('SUM(glosa_valor) as total_glosa')
            )
            ->groupBy('estado')
            ->orderBy('quantidade', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Obtém dados de taxas por região
     */
    private function getTaxasPorRegiao()
    {
        return Nota::where('tipo_nota', 'clinica')
            ->where('valor_taxa_correio', '>', 0)
            ->select(
                'regiao',
                DB::raw('SUM(valor_taxa_correio) as total_taxa'),
                DB::raw('COUNT(*) as quantidade')
            )
            ->groupBy('regiao')
            ->orderBy('total_taxa', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Obtém dados de taxas por UF
     */
    private function getTaxasPorUF()
    {
        return Nota::where('tipo_nota', 'clinica')
            ->where('valor_taxa_correio', '>', 0)
            ->select(
                'estado',
                DB::raw('COUNT(*) as quantidade'),
                DB::raw('SUM(valor_taxa_correio) as total_taxa')
            )
            ->groupBy('estado')
            ->orderBy('quantidade', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Obtém regiões com mais glosas e taxas
     */
    private function getRegioesMaisGlosasTaxas()
    {
        return Nota::where('tipo_nota', 'clinica')
            ->select(
                'regiao',
                DB::raw('SUM(CASE WHEN glosa_valor IS NOT NULL THEN glosa_valor ELSE 0 END) as total_glosa'),
                DB::raw('SUM(CASE WHEN valor_taxa_correio > 0 THEN valor_taxa_correio ELSE 0 END) as total_taxa')
            )
            ->groupBy('regiao')
            ->orderBy(DB::raw('total_glosa + total_taxa'), 'desc')
            ->limit(10)
            ->get();
    }
}