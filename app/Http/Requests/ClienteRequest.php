<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClienteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Verifica se o usuário está autenticado e tem permissão
        return auth()->check() && (auth()->user()->isAdmin() || 
                                  auth()->user()->isCliente());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'nome' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'telefone' => 'sometimes|nullable|string|max:20',
            'endereco' => 'sometimes|nullable|string|max:255',
            'numero' => 'sometimes|nullable|string|max:20',
            'complemento' => 'sometimes|nullable|string|max:100',
            'bairro' => 'sometimes|nullable|string|max:100',
            'cidade' => 'sometimes|nullable|string|max:100',
            'estado' => 'sometimes|nullable|string|max:2',
            'cep' => 'sometimes|nullable|string|max:10',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'nome.required' => 'O nome é obrigatório',
            'email.required' => 'O e-mail é obrigatório',
            'email.email' => 'O e-mail deve ser um endereço válido',
            'telefone.max' => 'O telefone deve ter no máximo 20 caracteres',
            'cep.max' => 'O CEP deve ter no máximo 10 caracteres',
        ];
    }
}