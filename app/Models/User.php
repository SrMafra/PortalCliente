<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'documento',  // CPF/CNPJ do cliente
        'telefone',
        'empresa_id',
        'cliente_id',
        'tipo',      // 'cliente', 'admin', 'vendedor'
        'ativo'
    ];

    /**
     * Os atributos que devem ser escondidos em arrays/JSON.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'ativo' => 'boolean',
    ];
    
    /**
     * Define o relacionamento com o cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'CpfCnpj', 'documento');
    }
    
    /**
     * Verifica se o usuário é um administrador.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->tipo === 'admin';
    }
    
    /**
     * Verifica se o usuário é um cliente.
     *
     * @return bool
     */
    public function isCliente()
    {
        return $this->tipo === 'cliente';
    }
    
    /**
     * Verifica se o usuário é um vendedor.
     *
     * @return bool
     */
    public function isVendedor()
    {
        return $this->tipo === 'vendedor';
    }
    
    /**
     * Gerar um token de API para o usuário
     *
     * @param string $name Nome do token
     * @param array $abilities Habilidades (capacidades) do token
     * @return string
     */
    public function createApiToken($name = 'api_token', $abilities = ['*'])
    {
        // Revogar tokens anteriores com o mesmo nome
        $this->tokens()->where('name', $name)->delete();
        
        // Criar novo token
        $token = $this->createToken($name, $abilities);
        
        return $token->plainTextToken;
    }
    
    /**
     * Formata o documento (CPF/CNPJ) para exibição
     *
     * @return string
     */
    public function getDocumentoFormatadoAttribute()
    {
        $doc = preg_replace('/[^0-9]/', '', $this->documento);
        
        if (strlen($doc) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $doc);
        }
        
        if (strlen($doc) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $doc);
        }
        
        return $this->documento;
    }
    
    /**
     * Scope para filtrar apenas usuários ativos
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
    
    /**
     * Scope para filtrar apenas usuários do tipo cliente
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClientes($query)
    {
        return $query->where('tipo', 'cliente');
    }
    
    /**
     * Scope para filtrar apenas usuários do tipo admin
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAdmins($query)
    {
        return $query->where('tipo', 'admin');
    }
    
    /**
     * Scope para filtrar apenas usuários do tipo vendedor
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVendedores($query)
    {
        return $query->where('tipo', 'vendedor');
    }
    
    /**
     * Busca usuário por documento (CPF/CNPJ)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $documento
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorDocumento($query, $documento)
    {
        // Remove caracteres não numéricos
        $documento = preg_replace('/[^0-9]/', '', $documento);
        
        return $query->where('documento', $documento);
    }
}