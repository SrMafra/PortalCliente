<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Titulo extends Model
{
    /**
     * Indica se o model deve ter timestamps (created_at e updated_at)
     *
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'codTitulo',
        'dataEmissao',
        'notaFiscal',
        'valorTitulo',
        'valorJuros',
        'numeroBancario',
        'agenciaBancaria',
        'numeroDuplicatas',
        'chaveNfeNotaFiscal',
        'dataVencimento',
        'dataPagamento',
        'vlrPago',
        'portador',
        'tipoCobranca',
        'cliente'
    ];
    
    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'valorTitulo' => 'float',
        'valorJuros' => 'float',
        'vlrPago' => 'float',
        'dataEmissao' => 'date',
        'dataVencimento' => 'date',
        'dataPagamento' => 'date',
    ];
    
    /**
     * Verifica se o título já venceu
     *
     * @return bool
     */
    public function getVencidoAttribute()
    {
        if (!$this->dataVencimento) {
            return false;
        }
        
        return Carbon::parse($this->dataVencimento)->isPast();
    }
    
    /**
     * Retorna quantos dias faltam para o vencimento ou há quantos dias venceu
     *
     * @return int
     */
    public function getDiasVencimentoAttribute()
    {
        if (!$this->dataVencimento) {
            return 0;
        }
        
        $hoje = Carbon::today();
        $vencimento = Carbon::parse($this->dataVencimento);
        
        return $hoje->diffInDays($vencimento, false);
    }
    
    /**
     * Retorna se o título está pago
     *
     * @return bool
     */
    public function getPagoAttribute()
    {
        return !empty($this->dataPagamento);
    }
    
    /**
     * Calcula o valor total (título + juros)
     *
     * @return float
     */
    public function getValorTotalAttribute()
    {
        return $this->valorTitulo + ($this->valorJuros ?? 0);
    }
    
    /**
     * Formata a data de emissão para exibição
     *
     * @return string
     */
    public function getDataEmissaoFormatadaAttribute()
    {
        if (!$this->dataEmissao) {
            return '-';
        }
        
        return $this->dataEmissao->format('d/m/Y');
    }
    
    /**
     * Formata a data de vencimento para exibição
     *
     * @return string
     */
    public function getDataVencimentoFormatadaAttribute()
    {
        if (!$this->dataVencimento) {
            return '-';
        }
        
        return $this->dataVencimento->format('d/m/Y');
    }
    
    /**
     * Formata a data de pagamento para exibição
     *
     * @return string
     */
    public function getDataPagamentoFormatadaAttribute()
    {
        if (!$this->dataPagamento) {
            return '-';
        }
        
        return $this->dataPagamento->format('d/m/Y');
    }
    
    /**
     * Formata o valor do título para exibição
     *
     * @return string
     */
    public function getValorTituloFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valorTitulo, 2, ',', '.');
    }
    
    /**
     * Formata o valor de juros para exibição
     *
     * @return string
     */
    public function getValorJurosFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valorJuros ?? 0, 2, ',', '.');
    }
    
    /**
     * Formata o valor total para exibição
     *
     * @return string
     */
    public function getValorTotalFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->getValorTotalAttribute(), 2, ',', '.');
    }
    
    /**
     * Formata o valor pago para exibição
     *
     * @return string
     */
    public function getValorPagoFormatadoAttribute()
    {
        if (!$this->vlrPago) {
            return '-';
        }
        
        return 'R$ ' . number_format($this->vlrPago, 2, ',', '.');
    }
}