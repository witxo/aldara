<?php

namespace App\Domains\Compliance\Services;

class SesPayloadValidator
{
    protected array $errors = [];

    public function validate(array $payload): array
    {
        $this->errors = [];

        if (empty($payload['establecimiento_code'])) {
            $this->errors[] = 'El código de establecimiento (ses_establecimiento_code) es obligatorio. Configúralo en la ficha del alojamiento.';
        }

        if (empty($payload['zip_base64'])) {
            $this->errors[] = 'No se pudo generar el XML de solicitud';
        }

        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
        ];
    }
}
