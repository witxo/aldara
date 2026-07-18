@extends('layouts.admin')
@section('title', 'Crear tenant')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold mb-6">Crear nuevo tenant</h3>
        <form method="POST" action="{{ route('admin.tenants.store') }}">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Empresa</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" required class="w-full bg-gray-700 border-gray-600 rounded-lg text-white focus:border-blue-500 focus:ring-blue-500">
                    @error('company_name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full bg-gray-700 border-gray-600 rounded-lg text-white focus:border-blue-500 focus:ring-blue-500">
                    @error('email') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Nombre del admin</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-gray-700 border-gray-600 rounded-lg text-white focus:border-blue-500 focus:ring-blue-500">
                    @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Contraseña</label>
                    <input type="password" name="password" required class="w-full bg-gray-700 border-gray-600 rounded-lg text-white focus:border-blue-500 focus:ring-blue-500">
                    @error('password') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">CIF/NIF</label>
                    <input type="text" name="tax_id" value="{{ old('tax_id') }}" class="w-full bg-gray-700 border-gray-600 rounded-lg text-white focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Plan</label>
                    <select name="plan" required class="w-full bg-gray-700 border-gray-600 rounded-lg text-white focus:border-blue-500 focus:ring-blue-500">
                        @foreach($plans as $plan)
                        <option value="{{ $plan->code }}" {{ old('plan') === $plan->code ? 'selected' : '' }}>
                            {{ $plan->name }} — {{ number_format($plan->price_monthly, 0) }}€/mes
                        </option>
                        @endforeach
                    </select>
                    @error('plan') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('admin.tenants') }}" class="px-4 py-2 text-sm text-gray-400 hover:text-gray-200">Cancelar</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-blue-700">Crear tenant</button>
            </div>
        </form>
    </div>
</div>
@endsection
