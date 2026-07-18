<?php

namespace App\Domains\Compliance\Exports;

use App\Domains\Compliance\Models\SesSubmission;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SesExport implements FromCollection, WithHeadings, WithMapping
{
    protected array $submissionIds;

    public function __construct(array $submissionIds)
    {
        $this->submissionIds = $submissionIds;
    }

    public function collection(): Collection
    {
        return SesSubmission::whereIn('id', $this->submissionIds)->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Lote', 'Estado',
            'Fecha Entrada', 'Fecha Salida',
            'Creado', 'Enviado',
        ];
    }

    public function map($submission): array
    {
        $payload = $submission->payload;
        $reserva = $payload['reservation'] ?? [];

        return [
            $submission->id,
            $submission->reference ?? '—',
            $submission->status,
            $reserva['fecha_entrada'] ?? '—',
            $reserva['fecha_salida'] ?? '—',
            $submission->created_at->format('d/m/Y H:i'),
            $submission->sent_at?->format('d/m/Y H:i') ?? '—',
        ];
    }
}
