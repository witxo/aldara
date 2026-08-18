<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo_hospedacheck.png') }}">
    <title>{{ config('app.name') }} — Gestión de visitantes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        html { scroll-behavior: smooth; }
        .testimonial-enter { opacity: 0; transform: translateX(30px); }
        .testimonial-active { opacity: 1; transform: translateX(0); }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased">

<!-- NAV -->
<nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="#hero" class="flex items-center gap-2">
                <img src="{{ asset('images/logo_hospedacheck.png') }}" alt="HospedaCheck" class="h-8 w-8">
                <span class="font-bold text-xl text-blue-600">{{ config('app.name') }}</span>
            </a>
            <div class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600">
                <a href="#features" class="hover:text-blue-600 transition">Funcionalidades</a>
                <a href="#plans" class="hover:text-blue-600 transition">Planes</a>
                <a href="#contact" class="hover:text-blue-600 transition">Contacto</a>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-blue-600 transition">Iniciar sesión</a>
                <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">Registrarse</a>
            </div>
        </div>
    </div>
</nav>

<!-- HERO -->
<section id="hero" class="pt-24 pb-16 md:pt-32 md:pb-24 bg-gradient-to-br from-blue-50 via-white to-blue-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center gap-12">
            <div class="flex-1 text-center lg:text-left">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight">
                    Gestiona el check-in de tus huéspedes<br>
                    <span class="text-blue-600">de forma sencilla y legal</span>
                </h1>
                <p class="mt-6 text-lg text-gray-600 max-w-xl">
                    HospedaCheck te permite cumplir con la normativa SES Hospedajes, escanear documentos con MRZ,
                    gestionar huéspedes y conectar con tus OTAs favoritas. Todo desde un solo panel.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="{{ route('register') }}" class="bg-blue-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-blue-700 transition shadow-lg shadow-blue-200 text-center">
                        Comenzar ahora — 15 días gratis
                    </a>
                    <a href="#features" class="border border-gray-300 text-gray-700 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-50 transition text-center">
                        Ver funcionalidades
                    </a>
                </div>
                <p class="mt-4 text-sm text-gray-400">Sin tarjeta de crédito. Cancela cuando quieras.</p>
            </div>
            <div class="flex-1 hidden lg:block">
                <div class="bg-white rounded-2xl shadow-2xl shadow-blue-200/50 p-6 border border-gray-100">
                    <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600 text-sm font-bold">✓</div>
                            <div><p class="text-sm font-medium">Huésped verificado</p><p class="text-xs text-gray-400">DNI escaneado con MRZ</p></div>
                        </div>
                        <div class="border-t border-gray-100"></div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-sm font-bold">S</div>
                            <div><p class="text-sm font-medium">SES Hospedajes</p><p class="text-xs text-gray-400">Enviado correctamente</p></div>
                        </div>
                        <div class="border-t border-gray-100"></div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 text-sm font-bold">📋</div>
                            <div><p class="text-sm font-medium">Check-in completado</p><p class="text-xs text-gray-400">4 huéspedes registrados</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section id="features" class="py-16 md:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900">Todo lo que necesitas en un solo lugar</h2>
            <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">De la recepción del huésped al envío a SES Hospedajes, sin papeles ni complicaciones.</p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-gray-50 rounded-2xl p-6 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-2xl mb-4">📱</div>
                <h3 class="text-lg font-semibold mb-2">Check-in online</h3>
                <p class="text-gray-500 text-sm">El huésped rellena sus datos desde su móvil antes de llegar. Sin colas ni formularios en papel.</p>
            </div>
            <div class="bg-gray-50 rounded-2xl p-6 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-2xl mb-4">🔐</div>
                <h3 class="text-lg font-semibold mb-2">SES Hospedajes</h3>
                <p class="text-gray-500 text-sm">Cumple con la normativa española. Genera y envía los partes de viajeros a la SES automáticamente.</p>
            </div>
            <div class="bg-gray-50 rounded-2xl p-6 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-2xl mb-4">📷</div>
                <h3 class="text-lg font-semibold mb-2">Escáner MRZ</h3>
                <p class="text-gray-500 text-sm">Escanea el DNI o pasaporte con la cámara. Los datos se rellenan solos. Compatible con todos los formatos.</p>
            </div>
            <div class="bg-gray-50 rounded-2xl p-6 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center text-2xl mb-4">👥</div>
                <h3 class="text-lg font-semibold mb-2">Gestión de huéspedes</h3>
                <p class="text-gray-500 text-sm">Historial completo de huéspedes por reserva. Datos cifrados y cumplimiento RGPD.</p>
            </div>
            <div class="bg-gray-50 rounded-2xl p-6 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center text-2xl mb-4">🔄</div>
                <h3 class="text-lg font-semibold mb-2">Conectores OTA</h3>
                <p class="text-gray-500 text-sm">Importa reservas desde Booking, Airbnb y otras plataformas. Tus huéspedes se sincronizan automáticamente.</p>
            </div>
            <div class="bg-gray-50 rounded-2xl p-6 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-rose-100 rounded-xl flex items-center justify-center text-2xl mb-4">📊</div>
                <h3 class="text-lg font-semibold mb-2">Panel completo</h3>
                <p class="text-gray-500 text-sm">Dashboard con reservas activas, check-ins pendientes, historial SES y mucho más. Una vista general de tu negocio.</p>
            </div>
        </div>
    </div>
</section>

<!-- PLANS -->
<section id="plans" class="py-16 md:py-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900">Planes transparentes</h2>
            <p class="mt-4 text-lg text-gray-600">Elige el plan que mejor se adapte a tu negocio. Todos incluyen 15 días de prueba gratuita.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            @foreach($plans as $plan)
                @if($plan->code === 'enterprise')
                    <div class="bg-white rounded-2xl shadow-sm border border-dashed border-gray-300 p-8 flex flex-col items-center text-center">
                        <h3 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h3>
                        <p class="text-4xl font-bold text-blue-600 mt-4">Bajo demanda</p>
                        <p class="text-gray-500 text-sm mt-2">{{ $plan->description }}</p>
                        <ul class="mt-6 space-y-3 text-sm text-gray-600 w-full">
                            <li class="flex items-center gap-2">✓ Alojamientos ilimitados</li>
                            <li class="flex items-center gap-2">✓ Usuarios ilimitados</li>
                            <li class="flex items-center gap-2">✓ Reservas ilimitadas</li>
                            <li class="flex items-center gap-2">✓ Soporte prioritario</li>
                        </ul>
                        <a href="{{ route('contacto.show') }}" class="mt-8 w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition text-center">
                            Contactar
                        </a>
                    </div>
                @else
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 flex flex-col @if($plan->code === 'advanced') ring-2 ring-blue-500 shadow-lg scale-105 @endif">
                        @if($plan->code === 'advanced')
                            <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-3 py-1 rounded-full self-start mb-3">MÁS POPULAR</span>
                        @endif
                        <h3 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h3>
                        <div class="mt-4">
                            <span class="text-4xl font-bold text-gray-900">{{ number_format($plan->price_yearly, 0) }}€</span>
                            <span class="text-gray-500 text-sm">/año</span>
                        </div>
                        <p class="text-gray-500 text-sm mt-2">{{ $plan->description }}</p>
                        <ul class="mt-6 space-y-3 text-sm text-gray-600 flex-1">
                            <li class="flex items-center gap-2">✓ {{ $plan->max_properties }} {{ $plan->max_properties === 1 ? 'alojamiento' : 'alojamientos' }}</li>
                            <li class="flex items-center gap-2">✓ {{ $plan->max_users }} {{ $plan->max_users === 1 ? 'usuario' : 'usuarios' }}</li>
                            <li class="flex items-center gap-2">✓ {{ $plan->max_reservations == -1 ? 'Reservas ilimitadas' : $plan->max_reservations . ' reservas/mes' }}</li>
                            <li class="flex items-center gap-2">✓ Check-in online + presencial</li>
                            <li class="flex items-center gap-2">✓ SES Hospedajes manual</li>
                            <li class="flex items-center gap-2">✓ Escáner MRZ</li>
                            <li class="flex items-center gap-2">✓ Gestión de huéspedes</li>
                            <li class="flex items-center gap-2">✓ Panel completo</li>
                        </ul>
                        <a href="{{ route('register') }}" class="mt-8 w-full @if($plan->code === 'advanced') bg-blue-600 text-white hover:bg-blue-700 @else bg-gray-100 text-gray-700 hover:bg-gray-200 @endif py-3 rounded-lg font-semibold transition text-center">
                            Elegir {{ $plan->name }}
                        </a>
                        <p class="text-xs text-gray-400 text-center mt-3">15 días de prueba gratuita</p>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</section>

<!-- TESTIMONIALS -->
<section class="py-16 md:py-24 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900">Lo que dicen nuestros clientes</h2>
        </div>
        <div x-data="testimonialCarousel()" class="relative">
            <div class="overflow-hidden">
                <template x-for="(t, i) in testimonials" :key="i">
                    <div x-show="current === i"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0 translate-x-8"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100 translate-x-0"
                         x-transition:leave-end="opacity-0 -translate-x-8"
                         class="bg-gray-50 rounded-2xl p-8 md:p-12">
                        <div class="text-4xl text-blue-200 mb-4">"</div>
                        <p class="text-lg md:text-xl text-gray-700 italic leading-relaxed" x-text="t.text"></p>
                        <div class="mt-6 flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg" x-text="t.initials"></div>
                            <div>
                                <p class="font-semibold text-gray-900" x-text="t.name"></p>
                                <p class="text-sm text-gray-500" x-text="t.role"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <button @click="prev()" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 w-10 h-10 rounded-full bg-white shadow-md flex items-center justify-center text-gray-600 hover:text-blue-600 transition">←</button>
            <button @click="next()" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 w-10 h-10 rounded-full bg-white shadow-md flex items-center justify-center text-gray-600 hover:text-blue-600 transition">→</button>
            <div class="flex justify-center gap-2 mt-6">
                <template x-for="(t, i) in testimonials" :key="i">
                    <button @click="current = i" class="w-2.5 h-2.5 rounded-full transition" :class="current === i ? 'bg-blue-600' : 'bg-gray-300'"></button>
                </template>
            </div>
        </div>
    </div>
</section>

<script>
    function testimonialCarousel() {
        return {
            current: 0,
            testimonials: [
                { text: 'HospedaCheck me ha simplificado muchísimo el check-in. Mis huéspedes lo hacen todo desde el móvil y yo recibo los datos al instante. El envío a SES ya no es un dolor de cabeza.', name: 'María García', role: 'Gestora turística — Málaga', initials: 'MG' },
                { text: 'Probé varias herramientas y HospedaCheck es la más completa para el tema SES. El escáner MRZ funciona muy bien, reconoce todos los documentos.', name: 'Carlos López', role: 'Propietario — Barcelona', initials: 'CL' },
                { text: 'Desde que uso HospedaCheck he reducido el tiempo de check-in un 80%. Mis clientes valoran poder dejar los datos antes de llegar. Muy recomendable.', name: 'Ana Martínez', role: 'Agencia inmobiliaria — Madrid', initials: 'AM' },
            ],
            init() {
                this._interval = setInterval(() => { this.next(); }, 5000);
            },
            next() {
                this.current = (this.current + 1) % this.testimonials.length;
            },
            prev() {
                this.current = (this.current - 1 + this.testimonials.length) % this.testimonials.length;
            },
            destroy() {
                if (this._interval) clearInterval(this._interval);
            }
        };
    }
</script>

<!-- CTA -->
<section class="py-16 md:py-24 bg-gradient-to-br from-blue-600 to-blue-800">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white">¿Listo para simplificar tu gestión?</h2>
        <p class="mt-4 text-lg text-blue-100">Únete a los profesionales que ya confían en HospedaCheck. Prueba gratuita de 15 días, sin compromiso.</p>
        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}" class="bg-white text-blue-700 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-blue-50 transition shadow-lg">
                Crear cuenta gratuita
            </a>
            <a href="{{ route('contacto.show') }}" class="border border-blue-300 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-blue-700 transition">
                Hablar con ventas
            </a>
        </div>
        <p class="mt-4 text-sm text-blue-200">Sin tarjeta de crédito. Cancela cuando quieras.</p>
    </div>
</section>

<!-- FOOTER -->
<footer class="bg-gray-900 text-gray-400 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-8">
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <img src="{{ asset('images/logo_hospedacheck.png') }}" alt="HospedaCheck" class="h-8 w-8">
                    <span class="font-bold text-xl text-white">{{ config('app.name') }}</span>
                </div>
                <p class="text-sm">Gestión de visitantes y cumplimiento SES Hospedajes.</p>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4">Enlaces</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#features" class="hover:text-white transition">Funcionalidades</a></li>
                    <li><a href="#plans" class="hover:text-white transition">Planes</a></li>
                    <li><a href="{{ route('contacto.show') }}" class="hover:text-white transition">Contacto</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4">Clientes</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('login') }}" class="hover:text-white transition">Iniciar sesión</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white transition">Crear cuenta</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-800 mt-8 pt-8 text-sm text-center">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
        </div>
    </div>
</footer>

</body>
</html>
