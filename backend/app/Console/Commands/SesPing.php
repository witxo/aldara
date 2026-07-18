<?php

namespace App\Console\Commands;

use App\Domains\Compliance\Services\SesService;
use Illuminate\Console\Command;

class SesPing extends Command
{
    protected $signature = 'checkin:ses-ping';
    protected $description = 'Verifica conectividad SOAP con el servicio MIR de Hospedajes';

    public function handle(SesService $sesService): int
    {
        $this->info('Verificando conexión SOAP con MIR Hospedajes...');

        $sesConfig = tenant_ses_config();
        $endpoint = $sesConfig['endpoint'] ?: 'https://hospedajes.ses.mir.es/hospedajes-web/ws/v1/comunicacion';
        $this->line('Endpoint: ' . $endpoint);

        if (!config('ses.enabled')) {
            $this->warn('SES está deshabilitado (SES_ENABLED=false).');
            return self::FAILURE;
        }

        $checks = [
            'Usuario' => $sesConfig['username'],
            'Contraseña' => $sesConfig['password'] ? '***configurado***' : null,
            'Código arrendador' => $sesConfig['codigo_arrendador'],
        ];

        $allOk = true;
        foreach ($checks as $key => $value) {
            if (empty($value)) {
                $this->error("  {$key} no configurado");
                $allOk = false;
            } else {
                $this->line("  {$key}: {$value}");
            }
        }

        if (!$allOk) {
            return self::FAILURE;
        }

        try {
            $result = $sesService->ping();

            if (!$result['success']) {
                $this->error('Error de conexión SOAP');
                $this->line('Código: ' . ($result['codigo'] ?? 'N/A'));
                $this->line('Descripción: ' . ($result['descripcion'] ?? 'N/A'));
                return self::FAILURE;
            }

            $this->newLine();
            $this->info(' Conexión SOAP exitosa');
            $this->line('  Código:     ' . $result['codigo']);
            $this->line('  Mensaje:    ' . $result['descripcion']);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
