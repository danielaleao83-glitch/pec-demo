<?php

declare(strict_types=1);

namespace App\Http\Requests\Integracao;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class SoapTransactionRequest extends FormRequest
{
    /**
     * Limite máximo do payload
     */
    private const MAX_PAYLOAD_ITEMS = 100;

    /**
     * Tamanho máximo permitido do body
     */
    private const MAX_CONTENT_LENGTH = 1024 * 1024; // 1MB

    public function authorize(): bool
    {
        /**
         * Aqui você pode validar:
         * - token interno
         * - mTLS
         * - IP whitelist
         * - OAuth2
         * - assinatura gov
         */

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [

            'cpf' => [
                'required_without:cns',
                'nullable',
                'string',
                'digits:11',
                'regex:/^[0-9]+$/',
            ],

            'cns' => [
                'required_without:cpf',
                'nullable',
                'string',
                'digits:15',
                'regex:/^[0-9]+$/',
            ],

            'payload' => [
                'required',
                'array',
                'max:' . self::MAX_PAYLOAD_ITEMS,
            ],
        ];
    }

    /**
     * Mensagens seguras
     *
     * Não expor detalhes internos.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [

            'cpf.required_without' =>
                'Documento obrigatório.',

            'cpf.digits' =>
                'CPF inválido.',

            'cpf.regex' =>
                'CPF inválido.',

            'cns.required_without' =>
                'Documento obrigatório.',

            'cns.digits' =>
                'CNS inválido.',

            'cns.regex' =>
                'CNS inválido.',

            'payload.required' =>
                'Payload obrigatório.',

            'payload.array' =>
                'Payload inválido.',

            'payload.max' =>
                'Payload excede limite permitido.',
        ];
    }

    /**
     * Sanitização
     */
    protected function prepareForValidation(): void
    {
        $this->merge([

            'cpf' => $this->sanitizeNumeric(
                $this->input('cpf')
            ),

            'cns' => $this->sanitizeNumeric(
                $this->input('cns')
            ),
        ]);
    }

    /**
     * Regras adicionais de segurança
     */
    public function withValidator(
        Validator $validator
    ): void {

        $validator->after(function (
            Validator $validator
        ): void {

            /**
             * Proteção contra payload gigante
             */
            $contentLength = (int) $this->server(
                'CONTENT_LENGTH',
                0
            );

            if (
                $contentLength > self::MAX_CONTENT_LENGTH
            ) {

                $validator->errors()->add(
                    'payload',
                    'Payload excede limite permitido.'
                );
            }

            /**
             * Proteção contra nesting profundo
             */
            if (
                $this->hasDeepNesting(
                    $this->input('payload')
                )
            ) {

                $validator->errors()->add(
                    'payload',
                    'Payload inválido.'
                );
            }
        });
    }

    /**
     * Sanitiza números
     */
    private function sanitizeNumeric(
        mixed $value
    ): ?string {

        if ($value === null) {
            return null;
        }

        return preg_replace(
            '/[^0-9]/',
            '',
            (string) $value
        );
    }

    /**
     * Detecta estruturas abusivas
     */
    private function hasDeepNesting(
        mixed $data,
        int $depth = 0
    ): bool {

        if ($depth > 10) {
            return true;
        }

        if (! is_array($data)) {
            return false;
        }

        foreach ($data as $item) {

            if (
                $this->hasDeepNesting(
                    $item,
                    $depth + 1
                )
            ) {
                return true;
            }
        }

        return false;
    }
}