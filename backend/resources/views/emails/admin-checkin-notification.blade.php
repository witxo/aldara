<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        line-height: 1.6;
        color: #1f2937;
        margin: 0;
        padding: 0;
        background-color: #f3f4f6;
    }
    .wrapper {
        max-width: 600px;
        margin: 0 auto;
        padding: 24px 16px;
    }
    .header {
        background: linear-gradient(135deg, #1e40af, #2563eb);
        color: #fff;
        padding: 32px 28px;
        border-radius: 12px 12px 0 0;
        text-align: center;
    }
    .header h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        letter-spacing: -0.3px;
    }
    .header p {
        margin: 6px 0 0;
        font-size: 14px;
        opacity: .85;
    }
    .header .badge {
        display: inline-block;
        background: rgba(255,255,255,.2);
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin-top: 10px;
        letter-spacing: .3px;
    }
    .body-card {
        background: #fff;
        padding: 28px;
        border-radius: 0 0 12px 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,.08);
    }
    .section {
        margin-bottom: 28px;
    }
    .section:last-child {
        margin-bottom: 0;
    }
    .section-title {
        font-size: 15px;
        font-weight: 700;
        color: #1e40af;
        margin: 0 0 14px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-title .count {
        background: #dbeafe;
        color: #1e40af;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 10px;
        border-radius: 12px;
    }
    .info-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0;
    }
    .info-row {
        display: flex;
        width: 100%;
        padding: 7px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        width: 130px;
        flex-shrink: 0;
        font-size: 13px;
        color: #6b7280;
        font-weight: 500;
    }
    .info-value {
        flex: 1;
        font-size: 13px;
        font-weight: 600;
        color: #1f2937;
    }
    .info-value a {
        color: #2563eb;
        text-decoration: none;
    }
    .consent-yes {
        display: inline-block;
        background: #d1fae5;
        color: #065f46;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 10px;
        border-radius: 10px;
    }
    .consent-no {
        display: inline-block;
        background: #fee2e2;
        color: #991b1b;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 10px;
        border-radius: 10px;
    }
    .guests-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .guests-table thead th {
        background: #f9fafb;
        color: #6b7280;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .5px;
        padding: 10px 8px;
        text-align: left;
        border-bottom: 2px solid #e5e7eb;
    }
    .guests-table tbody td {
        padding: 10px 8px;
        border-bottom: 1px solid #f3f4f6;
        color: #374151;
    }
    .guests-table tbody tr:last-child td {
        border-bottom: none;
    }
    .guests-table tbody tr:hover {
        background: #f9fafb;
    }
    .doc-badge {
        display: inline-block;
        background: #eff6ff;
        color: #1e40af;
        font-size: 10px;
        font-weight: 700;
        padding: 1px 7px;
        border-radius: 4px;
        text-transform: uppercase;
        margin-right: 4px;
    }
    .footer {
        text-align: center;
        padding: 20px 0 8px;
        font-size: 11px;
        color: #9ca3af;
    }
    @media (max-width: 480px) {
        .wrapper { padding: 12px 8px; }
        .body-card { padding: 18px; }
        .info-label { width: 100px; font-size: 12px; }
        .info-value { font-size: 12px; }
    }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>✓ Nuevo check-in completado</h1>
        <p>Un huésped ha realizado el check-in online</p>
        <span class="badge">{{ $checkin->reservation->property->name ?? 'Aldara' }}</span>
    </div>
    <div class="body-card">

        <div class="section">
            <div class="section-title">Reserva</div>
            <div class="info-grid">
                <div class="info-row">
                    <span class="info-label">Código</span>
                    <span class="info-value">{{ $checkin->reservation->code }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Huésped</span>
                    <span class="info-value">{{ $checkin->reservation->guest_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value"><a href="mailto:{{ $checkin->reservation->guest_email }}">{{ $checkin->reservation->guest_email }}</a></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Teléfono</span>
                    <span class="info-value">{{ $checkin->reservation->guest_phone ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Entrada</span>
                    <span class="info-value">{{ $checkin->reservation->checkin_date->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Salida</span>
                    <span class="info-value">{{ $checkin->reservation->checkout_date->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Huéspedes</span>
                    <span class="info-value">{{ $checkin->reservation->adults }} adulto{{ $checkin->reservation->adults != 1 ? 's' : '' }}{{ $checkin->reservation->children ? ', ' . $checkin->reservation->children . ' niño' . ($checkin->reservation->children != 1 ? 's' : '') : '' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Origen</span>
                    <span class="info-value">{{ $checkin->reservation->source ?? 'Directa' }}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Check-in</div>
            <div class="info-grid">
                <div class="info-row">
                    <span class="info-label">Tipo</span>
                    <span class="info-value">{{ ucfirst($checkin->type) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Completado</span>
                    <span class="info-value">{{ $checkin->completed_at?->format('d/m/Y H:i') ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">IP</span>
                    <span class="info-value" style="font-family:monospace;font-size:12px">{{ $checkin->ip_address ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Consent. legal</span>
                    <span class="info-value">{!! $checkin->consent_legal ? '<span class="consent-yes">Aceptado</span>' : '<span class="consent-no">Rechazado</span>' !!}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Consent. marketing</span>
                    <span class="info-value">{!! $checkin->consent_marketing ? '<span class="consent-yes">Aceptado</span>' : '<span class="consent-no">Rechazado</span>' !!}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Consent. retención</span>
                    <span class="info-value">{!! $checkin->consent_data_retention ? '<span class="consent-yes">Aceptado</span>' : '<span class="consent-no">Rechazado</span>' !!}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Huéspedes <span class="count">{{ $checkin->reservation->guests->count() }}</span></div>
            @if($checkin->reservation->guests->count())
            <table class="guests-table">
                <thead>
                    <tr>
                        <th style="width:32px">#</th>
                        <th>Nombre</th>
                        <th>Documento</th>
                        <th>Nacionalidad</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($checkin->reservation->guests as $i => $guest)
                    <tr>
                        <td style="color:#9ca3af;font-weight:600">{{ $i + 1 }}</td>
                        <td><strong>{{ $guest->full_name }}</strong></td>
                        <td><span class="doc-badge">{{ $guest->document_type }}</span> {{ $guest->document_number }}</td>
                        <td>{{ $guest->nationality }}</td>
                        <td>{{ $guest->email ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p style="color:#9ca3af;font-size:13px;font-style:italic">No se registraron huéspedes adicionales.</p>
            @endif
        </div>

    </div>
    <div class="footer">
        Aldara — Gestión de visitantes &bull; Este mensaje se generó automáticamente
    </div>
</div>
</body>
</html>
