<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
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
        'codEmpresa',
        'codCliente',
        'CpfCnpj',
        'codRepresentante',
        'inscEstadual',
        'inscSuframa',
        'situacao',
        'nomeFantasia',
        'consumidorFinal',
        'endereco',
        'numero',
        'bairro',
        'cep',
        'complemento',
        'cidade',
        'estado',
        'email',
        'emailNf',
        'telefone',
        'telefoneCobranca'
    ];

    /**
     * Define o relacionamento com o usuário do sistema.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'CpfCnpj', 'documento');
    }
    
    /**
     * Formata o CPF/CNPJ para exibição
     *
     * @return string
     */
    public function getCpfCnpjFormatadoAttribute()
    {
        $documento = preg_replace('/[^0-9]/', '', $this->CpfCnpj);
        
        if (strlen($documento) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $documento);
        }
        
        if (strlen($documento) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $documento);
        }
        
        return $documento;
    }
    
    /**
     * Formata o CEP para exibição
     *
     * @return string
     */
    public function getCepFormatadoAttribute()
    {
        $cep = preg_replace('/[^0-9]/', '', $this->cep);
        
        if (strlen($cep) === 8) {
            return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep);
        }
        
        return $this->cep;
    }
    
    /**
     * Formata o telefone para exibição
     *
     * @return string
     */
    public function getTelefoneFormatadoAttribute()
    {
        $telefone = preg_replace('/[^0-9]/', '', $this->telefone);
        
        if (strlen($telefone) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
        }
        
        if (strlen($telefone) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
        }
        
        return $this->telefone;
    }
    
    /**
     * Retorna o endereço completo
     *
     * @return string
     */
    public function getEnderecoCompletoAttribute()
    {
        $endereco = $this->endereco;
        
        if ($this->numero) {
            $endereco .= ', ' . $this->numero;
        }
        
        if ($this->complemento) {
            $endereco .= ' - ' . $this->complemento;
        }
        
        $endereco .= ' - ' . $this->bairro;
        $endereco .= ' - ' . $this->cidade . '/' . $this->estado;
        $endereco .= ' - CEP: ' . $this->getCepFormatadoAttribute();
        
        return $endereco;
    }
    
    /**
     * Retorna se o cliente está ativo
     *
     * @return bool
     */
    public function getIsAtivoAttribute()
    {
        return strtoupper($this->situacao) === 'ATIVO';
    }
}