@extends('layouts.app')

@section('title', 'Títulos em Aberto')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Títulos em Aberto</h1>
    </div>
    
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-light-blue">
            <h5 class="card-title mb-0">Filtros</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('titulos.em-aberto') }}" method="GET" class="row g-3">
                <input type="hidden" name="cliente_id" value="{{ $clienteId }}">
                
                <div class="col-md-3">
                    <label for="data_emissao_ini" class="form-label">Data de Emissão (Início)</label>
                    <input type="date" class="form-control" id="data_emissao_ini" name="data_emissao_ini" value="{{ $filtros['data_emissao_ini'] ?? '' }}">
                </div>
                
                <div class="col-md-3">
                    <label for="data_emissao_fim" class="form-label">Data de Emissão (Fim)</label>
                    <input type="date" class="form-control" id="data_emissao_fim" name="data_emissao_fim" value="{{ $filtros['data_emissao_fim'] ?? '' }}">
                </div>
                
                <div class="col-md-3">
                    <label for="data_vencimento_ini" class="form-label">Data de Vencimento (Início)</label>
                    <input type="date" class="form-control" id="data_vencimento_ini" name="data_vencimento_ini" value="{{ $filtros['data_vencimento_ini'] ?? '' }}">
                </div>
                
                <div class="col-md-3">
                    <label for="data_vencimento_fim" class="form-label">Data de Vencimento (Fim)</label>
                    <input type="date" class="form-control" id="data_vencimento_fim" name="data_vencimento_fim" value="{{ $filtros['data_vencimento_fim'] ?? '' }}">
                </div>
                
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('titulos.em-aberto', ['cliente_id' => $clienteId]) }}" class="btn btn-secondary">
                        <i class="fas fa-broom me-1"></i> Limpar Filtros
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    @if(isset($error))
        <div class="alert alert-danger">
            {{ $error }}
        </div>
    @else
        <div class="card">
            <div class="card-header bg-light-blue d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Lista de Títulos em Aberto</h5>
                
                @if(isset($titulos) && count($titulos) > 0)
                    <span class="badge bg-primary">
                        Total: {{ count($titulos) }} título(s)
                    </span>
                @endif
            </div>
            <div class="card-body">
                @if(isset($titulos) && count($titulos) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nota Fiscal</th>
                                    <th>Data Emissão</th>
                                    <th>Vencimento</th>
                                    <th>Valor Título</th>
                                    <th>Valor Juros</th>
                                    <th>Valor Total</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($titulos as $titulo)
                                    <tr class="{{ $titulo->vencido ? 'table-danger' : '' }}">
                                        <td>{{ $titulo->codTitulo }}</td>
                                        <td>{{ $titulo->notaFiscal }}</td>
                                        <td>{{ $titulo->dataEmissaoFormatada }}</td>
                                        <td>
                                            {{ $titulo->dataVencimentoFormatada }}
                                            @if($titulo->vencido)
                                                <span class="badge bg-danger">
                                                    Vencido há {{ abs($titulo->diasVencimento) }} dia(s)
                                                </span>
                                            @elseif($titulo->diasVencimento <= 5)
                                                <span class="badge bg-warning text-dark">
                                                    Vence em {{ $titulo->diasVencimento }} dia(s)
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $titulo->valorTituloFormatado }}</td>
                                        <td>{{ $titulo->valorJurosFormatado }}</td>
                                        <td class="fw-bold">{{ $titulo->valorTotalFormatado }}</td>
                                        <td>
                                            <div class="btn-group">
                                            <a href="{{ route('boleto.gerar', ['nota_fiscal' => $titulo->notaFiscal]) }}" class="btn btn-sm btn-primary" target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <td colspan="4" class="text-end fw-bold">Total:</td>
                                    <td class="fw-bold">
                                        R$ {{ number_format($titulos->sum('valorTitulo'), 2, ',', '.') }}
                                    </td>
                                    <td class="fw-bold">
                                        R$ {{ number_format($titulos->sum('valorJuros'), 2, ',', '.') }}
                                    </td>
                                    <td class="fw-bold">
                                        R$ {{ number_format($titulos->sum(function($titulo) {
                                            return $titulo->valorTitulo + ($titulo->valorJuros ?? 0);
                                        }), 2, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i> Não há títulos em aberto para os filtros selecionados.
                    </div>
                @endif
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Destacar títulos vencidos
        const titulosVencidos = document.querySelectorAll('.table-danger');
        titulosVencidos.forEach(function(row) {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8d7da';
            });
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    });
</script>
@endpush