<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BoletoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Qualquer usuário autenticado pode gerar boletos
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'nota_fiscal' => 'required|string',
            'cliente_id' => 'sometimes|required|string',
            'cnpj' => 'sometimes|required|string',
            'empresa' => 'sometimes|nullable|string',
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
            'nota_fiscal.required' => 'O número da nota fiscal é obrigatório',
            'cliente_id.required' => 'O código do cliente é obrigatório',
            'cnpj.required' => 'O CNPJ/CPF do cliente é obrigatório',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Se nota_fiscal estiver na rota mas não nos dados
        if ($this->route('nota_fiscal') && !$this->has('nota_fiscal')) {
            $this->merge([
                'nota_fiscal' => $this->route('nota_fiscal')
            ]);
        }
        
        // Se cliente_id for informado mas cnpj não, tenta obter do usuário logado
        if ($this->has('cliente_id') && !$this->has('cnpj') && auth()->check() && auth()->user()->isCliente()) {
            $this->merge([
                'cnpj' => auth()->user()->documento
            ]);
        }
    }
}