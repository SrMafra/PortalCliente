@extends('layouts.app')

@section('title', 'Meus Dados')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Meus Dados</h1>
    </div>
    
    @if(isset($error))
        <div class="alert alert-danger">
            {{ $error }}
        </div>
    @elseif(isset($cliente))
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center bg-light-blue">
                        <h5 class="card-title mb-0">Informações Gerais</h5>
                        <span class="badge bg-{{ $cliente->isAtivo ? 'success' : 'danger' }}">
                            {{ $cliente->situacao }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h3 class="h5 mb-3">{{ $cliente->nomeFantasia }}</h3>
                                
                                <p class="mb-1">
                                    <strong>Código:</strong> {{ $cliente->codCliente }}
                                </p>
                                <p class="mb-1">
                                    <strong>CPF/CNPJ:</strong> {{ $cliente->CpfCnpjFormatado }}
                                </p>
                                <p class="mb-1">
                                    <strong>Inscrição Estadual:</strong> {{ $cliente->inscEstadual ?: 'Não informado' }}
                                </p>
                                <p class="mb-1">
                                    <strong>Inscrição SUFRAMA:</strong> {{ $cliente->inscSuframa ?: 'Não informado' }}
                                </p>
                                <p class="mb-1">
                                    <strong>Consumidor Final:</strong> {{ $cliente->consumidorFinal ? 'Sim' : 'Não' }}
                                </p>
                                <p class="mb-1">
                                    <strong>Representante:</strong> {{ $cliente->codRepresentante ?: 'Não informado' }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h4 class="h5 mb-3">Contato</h4>
                                
                                <p class="mb-1">
                                    <strong>E-mail:</strong> {{ $cliente->email ?: 'Não informado' }}
                                </p>
                                <p class="mb-1">
                                    <strong>E-mail para NF:</strong> {{ $cliente->emailNf ?: 'Não informado' }}
                                </p>
                                <p class="mb-1">
                                    <strong>Telefone:</strong> {{ $cliente->telefoneFormatado ?: 'Não informado' }}
                                </p>
                                <p class="mb-1">
                                    <strong>Telefone Cobrança:</strong> {{ $cliente->telefoneCobrancaFormatado ?: 'Não informado' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-light-blue">
                        <h5 class="card-title mb-0">Endereço</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <p class="mb-1">
                                    <strong>Endereço:</strong> {{ $cliente->endereco }}
                                    {{ $cliente->numero ? ', ' . $cliente->numero : '' }}
                                    {{ $cliente->complemento ? ' - ' . $cliente->complemento : '' }}
                                </p>
                                <p class="mb-1">
                                    <strong>Bairro:</strong> {{ $cliente->bairro }}
                                </p>
                                <p class="mb-1">
                                    <strong>Cidade/UF:</strong> {{ $cliente->cidade }}/{{ $cliente->estado }}
                                </p>
                                <p class="mb-1">
                                    <strong>CEP:</strong> {{ $cliente->cepFormatado }}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <div class="text-end">
                                    @php
                                        $endereco = urlencode($cliente->enderecoCompleto);
                                        $mapUrl = "https://www.google.com/maps/search/?api=1&query={$endereco}";
                                    @endphp
                                    <a href="{{ $mapUrl }}" target="_blank" class="btn btn-outline-primary">
                                        <i class="fas fa-map-marked-alt me-1"></i> Ver no Mapa
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection