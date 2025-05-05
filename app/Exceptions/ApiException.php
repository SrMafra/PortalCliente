<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    /**
     * Reportar a exceção
     *
     * @return void
     */
    public function report()
    {
        // Reportar a exceção para o sistema de log
    }

    /**
     * Renderizar a exceção como uma resposta HTTP
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => $this->getMessage(),
                'code' => $this->getCode()
            ], 500);
        }

        return response()->view('errors.api', [
            'exception' => $this
        ], 500);
    }
}