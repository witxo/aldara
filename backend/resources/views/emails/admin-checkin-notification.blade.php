<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>body{font-family:sans-serif;line-height:1.6;color:#333}table{width:100%;border-collapse:collapse;margin:16px 0}th,td{padding:8px 12px;text-align:left;border-bottom:1px solid #ddd}th{background:#f5f5f5}.section{margin:24px 0}h2{color:#2563eb;font-size:18px}</style></head>
<body>
<h1>Nuevo check-in completado</h1>
<p>Se ha completado un check-in online.</p>

<div class="section">
    <h2>Reserva</h2>
    <table>
        <tr><th>Código</th><td>{{ $checkin->reservation->code }}</td></tr>
        <tr><th>Propiedad</th><td>{{ $checkin->reservation->property->name ?? 'N/A' }}</td></tr>
        <tr><th>Huésped principal</th><td>{{ $checkin->reservation->guest_name }}</td></tr>
        <tr><th>Email</th><td>{{ $checkin->reservation->guest_email }}</td></tr>
        <tr><th>Teléfono</th><td>{{ $checkin->reservation->guest_phone }}</td></tr>
        <tr><th>Entrada</th><td>{{ $checkin->reservation->checkin_date->format('d/m/Y') }}</td></tr>
        <tr><th>Salida</th><td>{{ $checkin->reservation->checkout_date->format('d/m/Y') }}</td></tr>
        <tr><th>Adultos</th><td>{{ $checkin->reservation->adults }}</td></tr>
        <tr><th>Niños</th><td>{{ $checkin->reservation->children }}</td></tr>
        <tr><th>Origen</th><td>{{ $checkin->reservation->source ?? 'N/A' }}</td></tr>
    </table>
</div>

<div class="section">
    <h2>Check-in</h2>
    <table>
        <tr><th>ID</th><td>{{ $checkin->id }}</td></tr>
        <tr><th>Tipo</th><td>{{ $checkin->type }}</td></tr>
        <tr><th>Completado</th><td>{{ $checkin->completed_at?->format('d/m/Y H:i') ?? 'N/A' }}</td></tr>
        <tr><th>IP</th><td>{{ $checkin->ip_address ?? 'N/A' }}</td></tr>
        <tr><th>Consent. legal</th><td>{{ $checkin->consent_legal ? 'Sí' : 'No' }}</td></tr>
        <tr><th>Consent. marketing</th><td>{{ $checkin->consent_marketing ? 'Sí' : 'No' }}</td></tr>
        <tr><th>Consent. retención</th><td>{{ $checkin->consent_data_retention ? 'Sí' : 'No' }}</td></tr>
    </table>
</div>

<div class="section">
    <h2>Huéspedes ({{ $checkin->reservation->guests->count() }})</h2>
    <table>
        <thead>
            <tr><th>#</th><th>Nombre</th><th>Documento</th><th>Nacionalidad</th><th>Email</th></tr>
        </thead>
        <tbody>
            @foreach($checkin->reservation->guests as $i => $guest)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $guest->full_name }}</td>
                <td>{{ strtoupper($guest->document_type) }} {{ $guest->document_number }}</td>
                <td>{{ $guest->nationality }}</td>
                <td>{{ $guest->email ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<p style="color:#888;font-size:12px;margin-top:32px">Este mensaje se ha generado automáticamente al completarse un check-in.</p>
</body>
</html>
