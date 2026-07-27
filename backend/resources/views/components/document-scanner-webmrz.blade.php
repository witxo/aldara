@props([
    'prefix' => '',
    'suffix' => '',
    'debug' => false,
    'buttonLabel' => 'Escanear documento',
    'compact' => false,
])

<div
    x-data="documentScannerWebmrz({
        prefix: @js($prefix),
        suffix: @js($suffix),
        debug: @js($debug),
    })"
    class="document-scanner-webmrz"
>
    <button type="button" @click="openScanner()"
        class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium
               hover:bg-blue-700 active:bg-blue-800 transition disabled:opacity-50"
        :disabled="status === 'processing'">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span x-show="status !== 'processing'">{{ $buttonLabel }}</span>
        <span x-show="status === 'processing'">Procesando...</span>
    </button>

    <label class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-blue-600 border border-blue-300
                    rounded-lg text-sm font-medium hover:bg-blue-50 transition cursor-pointer ml-2">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span>Subir foto</span>
        <input type="file" accept="image/*" class="hidden" @change="uploadFile">
    </label>

    <div x-show="hasResult" x-cloak class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-xs font-medium text-green-700 mb-1.5">Datos extraídos del documento:</p>
        <template x-for="entry in resultEntries" :key="entry.label">
            <div class="flex gap-2 text-sm">
                <span class="text-gray-500 shrink-0 w-28" x-text="entry.label + ':'"></span>
                <span class="text-gray-900 font-medium" x-text="entry.value"></span>
            </div>
        </template>
        <p class="text-xs text-gray-400 mt-1.5">Revise los datos. Puede editarlos manualmente si es necesario.</p>
    </div>

    <template x-teleport="body">
        <div x-show="showScanner" x-cloak
            class="fixed inset-0 z-[9999] flex flex-col bg-black"
            @keydown.escape="closeScanner()"
        >
            <div class="flex items-center justify-between bg-gray-900/90 text-white px-4 py-3 shrink-0">
                <span class="font-semibold text-sm">Escanear documento</span>
                <span class="text-xs text-gray-400 px-2" x-text="statusText"></span>
                <button type="button" @click="closeScanner()"
                    class="text-white text-2xl leading-none hover:text-gray-300 w-8 h-8 flex items-center justify-center">
                    &times;
                </button>
            </div>

            <div class="flex-1 relative min-h-0 bg-black" x-ref="cameraContainer">
                <div x-show="status === 'processing'"
                    class="absolute inset-0 bg-black/60 flex items-center justify-center z-10 pointer-events-none">
                    <div class="flex flex-col items-center gap-3">
                        <svg class="animate-spin h-8 w-8 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        <span class="text-white text-sm">Procesando...</span>
                    </div>
                </div>
            </div>

            <div class="bg-gray-900/90 text-white px-4 py-3 flex items-center justify-between shrink-0">
                <div class="text-xs text-gray-400">
                    <span x-show="status === 'camera'">Capture el documento cuando est&eacute; bien encuadrado</span>
                    <span x-show="status === 'error'" class="text-red-400" x-text="errorMessage"></span>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="capture()"
                        x-show="status === 'camera'"
                        class="w-14 h-14 rounded-full bg-white border-4 border-gray-300
                               hover:bg-gray-100 active:scale-95 transition flex items-center justify-center">
                        <span class="w-10 h-10 rounded-full bg-white border-2 border-gray-400"></span>
                    </button>
                    <button type="button" @click="retry()"
                        x-show="status === 'error'"
                        class="bg-yellow-600 hover:bg-yellow-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
                        Reintentar
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
