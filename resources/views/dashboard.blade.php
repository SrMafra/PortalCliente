@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dashboard</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="{{ route('cliente.dados') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-user me-1"></i> Meus Dados
                </a>
                <a href="{{ route('titulos.em-aberto') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-file-invoice-dollar me-1"></i> Títulos em Aberto
                </a>
            </div>
        </div>
    </div>
    
    @if(isset($error))
        <div class="alert alert-danger">
            {{ $error }}
        </div>
    @else
        <!-- Cards com resumo financeiro -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 card-dashboard card-primary">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total em Aberto
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    R$ {{ number_format($totalEmAberto ?? 0, 2, ',', '.') }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 card-dashboard card-danger">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Total Vencido
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    R$ {{ number_format($totalVencido ?? 0, 2, ',', '.') }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-times fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 card-dashboard card-warning">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    A Vencer (30 dias)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    R$ {{ number_format($totalAVencer30Dias ?? 0, 2, ',', '.') }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 card-dashboard card-success">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Pago (Mês Atual)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    R$ {{ number_format($totalPago ?? 0, 2, ',', '.') }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Títulos em Aberto Recentes -->
        <div class="card mb-4">
            <div class="card-header bg-light-blue d-flex justify-content-between align-items-center">
                <h5 class="m-0 font-weight-bold">Títulos em Aberto Recentes</h5>
                <a href="{{ route('titulos.em-aberto') }}" class="btn btn-sm btn-primary">
                    Ver Todos
                </a>
            </div>
            <div class="card-body">
                @if(isset($titulosAbertos) && count($titulosAbertos) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nota Fiscal</th>
                                    <th>Data Emissão</th>
                                    <th>Vencimento</th>
                                    <th>Valor</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($titulosAbertos as $titulo)
                                    <tr class="{{ $titulo->vencido ? 'table-danger' : '' }}">
                                        <td>{{ $titulo->codTitulo }}</td>
                                        <td>{{ $titulo->notaFiscal }}</td>
                                        <td>{{ $titulo->dataEmissaoFormatada }}</td>
                                        <td>
                                            {{ $titulo->dataVencimentoFormatada }}
                                            @if($titulo->vencido)
                                                <span class="badge bg-danger">Vencido</span>
                                            @endif
                                        </td>
                                        <td>{{ $titulo->valorTituloFormatado }}</td>
                                        <td>
                                            <a href="{{ route('boleto.gerar', ['nota_fiscal' => $titulo->notaFiscal]) }}" class="btn btn-sm btn-primary" target="_blank">
                                                <i class="fas fa-file-pdf me-1"></i> Boleto
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i> Não há títulos em aberto.
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Boletos Pagos Recentes -->
        <div class="card mb-4">
            <div class="card-header bg-light-blue d-flex justify-content-between align-items-center">
                <h5 class="m-0 font-weight-bold">Boletos Pagos Recentes</h5>
                <a href="{{ route('titulos.pagos') }}" class="btn btn-sm btn-primary">
                    Ver Todos
                </a>
            </div>
            <div class="card-body">
                @if(isset($boletosPagos) && count($boletosPagos) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nota Fiscal</th>
                                    <th>Vencimento</th>
                                    <th>Data Pagamento</th>
                                    <th>Valor Pago</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($boletosPagos as $boleto)
                                    <tr>
                                        <td>{{ $boleto->codTitulo }}</td>
                                        <td>{{ $boleto->notaFiscal }}</td>
                                        <td>{{ $boleto->dataVencimentoFormatada }}</td>
                                        <td>{{ $boleto->dataPagamentoFormatada }}</td>
                                        <td>{{ $boleto->valorPagoFormatado }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i> Não há boletos pagos no período.
                    </div>
                @endif
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    // Scripts específicos da página de dashboard
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Dashboard carregado com sucesso');
    });
</script>
@endpush