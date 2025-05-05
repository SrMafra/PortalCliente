@extends('layouts.app')

@section('title', 'Boletos Pagos')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Boletos Pagos</h1>
    </div>
    
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-light-blue">
            <h5 class="card-title mb-0">Filtrar por Período</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('titulos.pagos') }}" method="GET" class="row g-3">
                <input type="hidden" name="cliente_id" value="{{ $clienteId }}">
                
                <div class="col-md-4">
                    <label for="data_inicio" class="form-label">Data Inicial</label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="{{ $dataInicio ?? '' }}">
                </div>
                
                <div class="col-md-4">
                    <label for="data_fim" class="form-label">Data Final</label>
                    <input type="date" class="form-control" id="data_fim" name="data_fim" value="{{ $dataFim ?? '' }}">
                </div>
                
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('titulos.pagos', ['cliente_id' => $clienteId]) }}" class="btn btn-secondary">
                        <i class="fas fa-broom me-1"></i> Limpar Filtros
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Atalhos para períodos comuns -->
    <div class="mb-4">
        <div class="btn-group">
            <a href="{{ route('titulos.pagos', ['cliente_id' => $clienteId, 'data_inicio' => now()->startOfMonth()->format('Y-m-d'), 'data_fim' => now()->endOfMonth()->format('Y-m-d')]) }}" class="btn btn-outline-primary">
                Mês Atual
            </a>
            <a href="{{ route('titulos.pagos', ['cliente_id' => $clienteId, 'data_inicio' => now()->subMonth()->startOfMonth()->format('Y-m-d'), 'data_fim' => now()->subMonth()->endOfMonth()->format('Y-m-d')]) }}" class="btn btn-outline-primary">
                Mês Anterior
            </a>
            <a href="{{ route('titulos.pagos', ['cliente_id' => $clienteId, 'data_inicio' => now()->startOfYear()->format('Y-m-d'), 'data_fim' => now()->endOfYear()->format('Y-m-d')]) }}" class="btn btn-outline-primary">
                Ano Atual
            </a>
            <a href="{{ route('titulos.pagos', ['cliente_id' => $clienteId, 'data_inicio' => now()->subMonths(3)->format('Y-m-d'), 'data_fim' => now()->format('Y-m-d')]) }}" class="btn btn-outline-primary">
                Últimos 3 Meses
            </a>
        </div>
    </div>
    
    @if(isset($error))
        <div class="alert alert-danger">
            {{ $error }}
        </div>
    @else
        <!-- Card com resumo -->
        <div class="card mb-4 border-success">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">Resumo do Período</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-6">
                        <h3 class="h5">Total de Boletos Pagos</h3>
                        <p class="display-6 text-success">{{ count($boletos ?? []) }}</p>
                    </div>
                    <div class="col-md-6">
                        <h3 class="h5">Valor Total Pago</h3>
                        <p class="display-6 text-success">
                            @if(isset($boletos))
                                R$ {{ number_format($boletos->sum('vlrPago'), 2, ',', '.') }}
                            @else
                                R$ 0,00
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-light-blue d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Lista de Boletos Pagos</h5>
                
                @if(isset($boletos) && count($boletos) > 0)
                    <span class="badge bg-success">
                        Total: {{ count($boletos) }} boleto(s)
                    </span>
                @endif
            </div>
            <div class="card-body">
                @if(isset($boletos) && count($boletos) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nota Fiscal</th>
                                    <th>Vencimento</th>
                                    <th>Data Pagamento</th>
                                    <th>Valor do Título</th>
                                    <th>Valor Pago</th>
                                    <th>Portador</th>
                                    <th>Tipo de Cobrança</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($boletos as $boleto)
                                    <tr>
                                        <td>{{ $boleto->codTitulo }}</td>
                                        <td>{{ $boleto->notaFiscal }}</td>
                                        <td>{{ $boleto->dataVencimentoFormatada }}</td>
                                        <td>{{ $boleto->dataPagamentoFormatada }}</td>
                                        <td>{{ $boleto->valorTituloFormatado }}</td>
                                        <td>{{ $boleto->valorPagoFormatado }}</td>
                                        <td>{{ $boleto->portador }}</td>
                                        <td>{{ $boleto->tipoCobranca }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-success">
                                    <td colspan="4" class="text-end fw-bold">Total:</td>
                                    <td class="fw-bold">
                                        R$ {{ number_format($boletos->sum('valorTitulo'), 2, ',', '.') }}
                                    </td>
                                    <td class="fw-bold">
                                        R$ {{ number_format($boletos->sum('vlrPago'), 2, ',', '.') }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Agrupamento por mês -->
                    <div class="mt-5">
                        <h4>Pagamentos por Mês</h4>
                        
                        @php
                            $boletosPorMes = $boletos->groupBy(function($boleto) {
                                return $boleto->dataPagamento->format('m/Y');
                            });
                            
                            // Ordenar os meses
                            $boletosPorMes = $boletosPorMes->sortKeys();
                        @endphp
                        
                        <div class="row">
                            @foreach($boletosPorMes as $mes => $boletosMes)
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h5 class="card-title mb-0">{{ $mes }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <h6>Total: {{ count($boletosMes) }} boleto(s)</h6>
                                            <h4 class="text-success">
                                                R$ {{ number_format($boletosMes->sum('vlrPago'), 2, ',', '.') }}
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i> Não há boletos pagos para o período selecionado.
                    </div>
                @endif
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Página de boletos pagos carregada');
    });
</script>
@endpush