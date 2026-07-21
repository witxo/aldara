<?php

namespace App\Domains\Compliance\Services;

use App\Domains\Compliance\Models\SesSubmission;
use App\Domains\Property\Models\Property;
use App\Domains\Reservation\Models\Reservation;
use App\Domains\Checkin\Models\Checkin;
use Illuminate\Support\Facades\Http;

class SesService
{
    public function __construct(
        protected SesPayloadBuilder $payloadBuilder,
        protected SesPayloadValidator $validator,
    ) {}

    public function prepareSubmission(Reservation $reservation, ?Checkin $checkin = null, ?array $guestsData = null): SesSubmission
    {
        $payload = $this->payloadBuilder->build($reservation, $guestsData);
        $validation = $this->validator->validate($payload);

        return SesSubmission::create([
            'tenant_id' => $reservation->tenant_id,
            'reservation_id' => $reservation->id,
            'checkin_id' => $checkin?->id,
            'status' => $validation['valid'] ? 'ready' : 'draft',
            'mode' => 'manual',
            'payload' => $payload,
        ]);
    }

    public function validatePayload(SesSubmission $submission): array
    {
        return $this->validator->validate($submission->payload ?? []);
    }

    public function send(SesSubmission $submission): SesSubmission
    {
        if ($submission->status !== 'ready') {
            $submission->update([
                'error_message' => 'Estado inválido para envío: ' . $submission->status,
                'status' => 'failed',
            ]);
            return $submission;
        }

        if (!config('ses.enabled')) {
            $submission->update([
                'status' => 'draft',
                'error_message' => 'Modo SES deshabilitado',
            ]);
            return $submission;
        }

        $property = $submission->reservation?->property;

        try {
            $response = $this->sendSoapRequest($submission->payload, $property);

            if ($response['success']) {
                $submission->update([
                    'status' => 'sent',
                    'response' => $response,
                    'reference' => $response['lote'] ?? null,
                    'sent_at' => now(),
                    'error_message' => null,
                    'retry_count' => $submission->retry_count + 1,
                    'last_attempt_at' => now(),
                ]);
            } else {
                $submission->update([
                    'status' => 'failed',
                    'response' => $response,
                    'error_message' => $response['error'] ?? 'Error desconocido',
                    'retry_count' => $submission->retry_count + 1,
                    'last_attempt_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            $submission->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'retry_count' => $submission->retry_count + 1,
                'last_attempt_at' => now(),
            ]);
        }

        return $submission;
    }

    public function retry(SesSubmission $submission): SesSubmission
    {
        if ($submission->status !== 'failed') {
            return $submission;
        }

        if ($submission->retry_count >= config('ses.retry.max_attempts', 3)) {
            $submission->update([
                'error_message' => 'Máximo de reintentos alcanzado',
            ]);
            return $submission;
        }

        $submission->update(['status' => 'ready']);
        return $this->send($submission);
    }

    public function ping(?Property $property = null): array
    {
        $consultXml = '<?xml version="1.0" encoding="UTF-8"?>
<con:lotes xmlns:con="http://www.neg.hospedajes.mir.es/consultarComunicacion">
    <con:lote>00000000-0000-0000-0000-000000000000</con:lote>
</con:lotes>';

        $base64 = $this->compressXml($consultXml);

        $soapBody = $this->buildSoapEnvelope($base64, 'C', property: $property);

        $response = $this->soapHttpCall($soapBody, $property);

        if (!$response['success']) {
            return $response;
        }

        $parsed = $this->parseSoapResponse($response['body']);

        return [
            'success' => $parsed['codigo'] === 0,
            'codigo' => $parsed['codigo'],
            'descripcion' => $parsed['descripcion'],
            'raw' => $response['body'],
            'soap_request' => $soapBody,
        ];
    }

    public function consultarLote(string $lote): array
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<con:lotes xmlns:con="http://www.neg.hospedajes.mir.es/consultarComunicacion">
    <con:lote>' . $lote . '</con:lote>
</con:lotes>';

        $base64 = $this->compressXml($xml);

        $soapBody = $this->buildSoapEnvelope($base64, 'C');

        $response = $this->soapHttpCall($soapBody);

        if (!$response['success']) {
            return $response;
        }

        return [
            'success' => true,
            'body' => $response['body'],
        ];
    }

    protected function sendSoapRequest(array $payload, ?Property $property = null): array
    {
        $zipBase64 = $payload['zip_base64'] ?? '';

        if (empty($zipBase64)) {
            return ['success' => false, 'error' => 'No hay datos XML para enviar'];
        }

        $soapBody = $this->buildSoapEnvelope($zipBase64, 'A', 'PV', $property);

        $response = $this->soapHttpCall($soapBody, $property);

        if (!$response['success']) {
            return $response;
        }

        $parsed = $this->parseSoapResponse($response['body']);

        if ($parsed['codigo'] === 0) {
            return [
                'success' => true,
                'lote' => $parsed['lote'],
                'codigo' => $parsed['codigo'],
                'descripcion' => $parsed['descripcion'],
            ];
        }

        return [
            'success' => false,
            'codigo' => $parsed['codigo'],
            'error' => $parsed['descripcion'],
            'raw' => $response['body'],
        ];
    }

    protected function compressXml(string $xml): string
    {
        $zip = new \ZipArchive();
        $tmpFile = tempnam(sys_get_temp_dir(), 'ses_');
        if ($zip->open($tmpFile, \ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('No se pudo crear archivo ZIP temporal');
        }
        $zip->addFromString('comunicacion.xml', $xml);
        $zip->close();
        $data = file_get_contents($tmpFile);
        unlink($tmpFile);
        return base64_encode($data);
    }

    protected function buildSoapEnvelope(string $solicitudBase64, string $tipoOperacion, string $tipoComunicacion = '', ?Property $property = null): string
    {
        $sesConfig = tenant_ses_config(null, $property);
        $codigoArrendador = $sesConfig['codigo_arrendador'];
        $aplicacion = $sesConfig['aplicacion'];

        $tipoComunicacionXml = $tipoOperacion === 'A'
            ? "<tipoComunicacion>{$tipoComunicacion}</tipoComunicacion>"
            : '';

        return <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:com="http://www.soap.servicios.hospedajes.mir.es/comunicacion">
    <soapenv:Header/>
    <soapenv:Body>
        <com:comunicacionRequest>
            <peticion>
                <cabecera>
                    <codigoArrendador>{$codigoArrendador}</codigoArrendador>
                    <aplicacion>{$aplicacion}</aplicacion>
                    <tipoOperacion>{$tipoOperacion}</tipoOperacion>
                    {$tipoComunicacionXml}
                </cabecera>
                <solicitud>{$solicitudBase64}</solicitud>
            </peticion>
        </com:comunicacionRequest>
    </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    protected function soapHttpCall(string $soapBody, ?Property $property = null): array
    {
        $sesConfig = tenant_ses_config(null, $property);
        $endpoint = $sesConfig['endpoint'] ?: $this->getDefaultEndpoint();
        $username = $sesConfig['username'];
        $password = $sesConfig['password'];

        if (empty($username) || empty($password)) {
            return ['success' => false, 'error' => 'SES_USERNAME o SES_PASSWORD no configurados'];
        }

        $http = Http::timeout(config('ses.timeout', 60))
            ->withBasicAuth($username, $password)
            ->withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => '',
            ]);

        if (!config('ses.verify_ssl')) {
            $http->withoutVerifying();
        }

        $response = $http->send('POST', $endpoint, ['body' => $soapBody]);

        if ($response->failed()) {
            return [
                'success' => false,
                'error' => 'Error HTTP ' . $response->status() . ': ' . substr($response->body(), 0, 500),
                'status_code' => $response->status(),
                'body' => $response->body(),
            ];
        }

        return [
            'success' => true,
            'body' => $response->body(),
        ];
    }

    protected function parseSoapResponse(string $xml): array
    {
        $result = [
            'codigo' => null,
            'descripcion' => '',
            'lote' => null,
        ];

        try {
            $dom = new \DOMDocument();
            $dom->loadXML($xml);

            $xpath = new \DOMXPath($dom);

            $namespaces = $xpath->query('namespace::*');
            $registered = false;
            foreach ($namespaces as $ns) {
                if (str_contains($ns->nodeValue, 'hospedajes.mir.es')) {
                    $xpath->registerNamespace('ns', $ns->nodeValue);
                    $registered = true;
                    break;
                }
            }
            if (!$registered) {
                $xpath->registerNamespace('ns', 'http://www.soap.servicios.hospedajes.mir.es/comunicacion');
            }

            $codigoNode = $xpath->query('//ns:comunicacionResponse/ns:respuesta/ns:codigoRetorno');
            if ($codigoNode->length === 0) {
                $codigoNode = $xpath->query('//ns:comunicacionResponse/ns:respuesta/ns:codigo');
            }
            if ($codigoNode->length === 0) {
                $codigoNode = $xpath->query('//*[local-name()="comunicacionResponse"]//*[local-name()="codigoRetorno" or local-name()="codigo"]');
            }
            if ($codigoNode->length > 0) {
                $result['codigo'] = (int) $codigoNode->item(0)->nodeValue;
            }

            $descNode = $xpath->query('//ns:comunicacionResponse/ns:respuesta/ns:descripcion');
            if ($descNode->length === 0) {
                $descNode = $xpath->query('//*[local-name()="descripcion"]');
            }
            if ($descNode->length > 0) {
                $result['descripcion'] = $descNode->item(0)->nodeValue;
            }

            $loteNode = $xpath->query('//ns:comunicacionResponse/ns:respuesta/ns:lote');
            if ($loteNode->length === 0) {
                $loteNode = $xpath->query('//*[local-name()="lote"]');
            }
            if ($loteNode->length > 0) {
                $result['lote'] = $loteNode->item(0)->nodeValue;
            }
        } catch (\Throwable $e) {
            $result['descripcion'] = 'Error al parsear respuesta SOAP: ' . $e->getMessage();
        }

        return $result;
    }

    protected function getDefaultEndpoint(): string
    {
        return config('ses.endpoint_produccion', 'https://hospedajes.ses.mir.es/hospedajes-web/ws/v1/comunicacion');
    }

    public function export(array $submissionIds, string $format = 'json'): array
    {
        $submissions = SesSubmission::whereIn('id', $submissionIds)->get();

        $data = $submissions->map(function ($s) {
            $payload = $s->payload;
            $reserva = $payload['reservation'] ?? [];
            return [
                'id' => $s->id,
                'reference' => $s->reference,
                'status' => $s->status,
                'lote' => $s->reference,
                'reservation_id' => $s->reservation_id,
                'created_at' => $s->created_at,
            ];
        });

        if ($format === 'json') {
            return ['format' => 'json', 'data' => $data->toArray()];
        }

        if ($format === 'csv') {
            return ['format' => 'csv', 'data' => $this->toCsv($data)];
        }

        return ['format' => $format, 'data' => $data->toArray()];
    }

    protected function toCsv($data): string
    {
        $csv = "id,reference,status,lote,reservation_id,created_at\n";
        foreach ($data as $row) {
            $csv .= implode(',', [
                $row['id'],
                $row['reference'] ?? '',
                $row['status'],
                $row['lote'] ?? '',
                $row['reservation_id'],
                $row['created_at'],
            ]) . "\n";
        }
        return $csv;
    }
}
