<form method="POST" action="{{ route('public.checkin.search.submit', $property->checkin_code) }}" class="space-y-4">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="checkin" class="block text-sm font-medium text-gray-700">Fecha de entrada *</label>
            <input type="date" name="checkin" id="checkin" required
                   value="{{ old('checkin') }}"
                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('checkin') border-red-300 @enderror">
            @error('checkin')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="checkout" class="block text-sm font-medium text-gray-700">Fecha de salida *</label>
            <input type="date" name="checkout" id="checkout" required
                   value="{{ old('checkout') }}"
                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('checkout') border-red-300 @enderror">
            @error('checkout')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
    <button type="submit"
            class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
        Buscar reserva
    </button>
</form>
