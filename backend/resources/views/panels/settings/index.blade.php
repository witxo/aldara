@extends('layouts.panel')
@section('title', 'Ajustes')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-6">Ajustes del tenant</h3>
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hora check-in por defecto</label>
                    <input type="time" name="default_checkin_time" value="{{ old('default_checkin_time', $settings['default_checkin_time'] ?? '15:00') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hora check-out por defecto</label>
                    <input type="time" name="default_checkout_time" value="{{ old('default_checkout_time', $settings['default_checkout_time'] ?? '11:00') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Días de retención</label>
                    <input type="number" name="retention_days" value="{{ old('retention_days', $settings['retention_days'] ?? 1095) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" min="30" max="3650">
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="checkin_require_signature" value="0">
                    <input type="checkbox" name="checkin_require_signature" value="1" {{ old('checkin_require_signature', $settings['require_signature'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300">
                    <label class="text-sm">Requerir firma en check-in</label>
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="checkin_require_document" value="0">
                    <input type="checkbox" name="checkin_require_document" value="1" {{ old('checkin_require_document', $settings['require_document'] ?? false) ? 'checked' : '' }} class="rounded border-gray-300">
                    <label class="text-sm">Requerir documento en check-in</label>
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="use_web_mrz_reader" value="0">
                    <input type="checkbox" name="use_web_mrz_reader" value="1" {{ old('use_web_mrz_reader', $settings['use_web_mrz_reader'] ?? false) ? 'checked' : '' }} class="rounded border-gray-300">
                    <label class="text-sm">Usar nuevo lector MRZ (web-mrz-reader)</label>
                </div>
                
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm">Guardar ajustes</button>
            </div>
        </form>
    </div>
</div>
@endsection
