<?php $__env->startSection('title', "Reserva {$reservation->code}"); ?>
<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-semibold"><?php echo e($reservation->guest_name); ?></h3>
                    <p class="text-sm text-gray-500">Código: <?php echo e($reservation->code); ?></p>
                </div>
                <span class="px-3 py-1 text-sm rounded-full
                    <?php if($reservation->status === 'confirmed'): ?> bg-blue-100 text-blue-700
                    <?php elseif($reservation->status === 'checkin_sent'): ?> bg-yellow-100 text-yellow-700
                    <?php elseif($reservation->status === 'completed'): ?> bg-green-100 text-green-700
                    <?php elseif($reservation->status === 'cancelled'): ?> bg-red-100 text-red-700
                    <?php else: ?> bg-gray-100 text-gray-700 <?php endif; ?>"><?php echo e($reservation->status); ?></span>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500">Alojamiento:</span> <?php echo e($reservation->property->name ?? '—'); ?></div>
                <div><span class="text-gray-500">Origen:</span> <?php echo e($reservation->source); ?></div>
                <div><span class="text-gray-500">Entrada:</span> <?php echo e($reservation->checkin_date->format('d/m/Y')); ?></div>
                <div><span class="text-gray-500">Salida:</span> <?php echo e($reservation->checkout_date->format('d/m/Y')); ?></div>
                <div><span class="text-gray-500">Adultos:</span> <?php echo e($reservation->adults); ?></div>
                <div><span class="text-gray-500">Menores:</span> <?php echo e($reservation->children); ?></div>
                <div><span class="text-gray-500">Email:</span> <?php echo e($reservation->guest_email ?? '—'); ?></div>
                <div><span class="text-gray-500">Teléfono:</span> <?php echo e($reservation->guest_phone ?? '—'); ?></div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reservation->notes): ?>
                <div class="mt-4 p-3 bg-gray-50 rounded text-sm"><?php echo e($reservation->notes); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Huéspedes (<?php echo e($reservation->guests->count()); ?>)</h4>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reservation->guests->count() > 0): ?>
                <div class="space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reservation->guests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $guest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                        <div>
                            <p class="font-medium"><?php echo e($guest->first_name); ?> <?php echo e($guest->last_name); ?></p>
                            <p class="text-sm text-gray-500"><?php echo e($guest->document_type); ?>: <?php echo e(substr($guest->document_number, -4)); ?>... (cifrado)</p>
                        </div>
                        <span class="text-xs text-gray-500"><?php echo e($guest->nationality); ?></span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-sm">No hay huéspedes registrados</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Check-ins (<?php echo e($reservation->checkins->count()); ?>)</h4>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reservation->checkins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $checkin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded mb-2">
                <div>
                    <p class="font-medium"><?php echo e($checkin->type); ?> — <?php echo e($checkin->status); ?></p>
                    <p class="text-sm text-gray-500"><?php echo e($checkin->completed_at?->format('d/m/Y H:i') ?? 'Pendiente'); ?></p>
                </div>
                <a href="<?php echo e(route('checkins.show', $checkin)); ?>" class="text-blue-600 text-sm">Ver</a>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-gray-500 text-sm">Sin check-ins</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Acciones</h4>
            <div class="space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reservation->status === 'confirmed' || $reservation->status === 'pending'): ?>
                <form method="POST" action="<?php echo e(route('api.v1.reservations.send-checkin', $reservation)); ?>" onsubmit="event.preventDefault(); fetch(this.action, {method:'POST', headers:{'X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>'}}).then(r=>r.json()).then(d=>alert('Enlace generado: ' + d.data.url));">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">Enviar enlace check-in</button>
                </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <a href="<?php echo e(route('reservations.edit', $reservation)); ?>" class="block text-center bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm">Editar reserva</a>
                <hr>
                <h5 class="font-semibold text-sm">SES Hospedajes</h5>
                <a href="<?php echo e(route('ses.prepare', $reservation)); ?>" class="block text-center bg-green-100 text-green-700 px-4 py-2 rounded-lg text-sm">Preparar envío SES</a>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reservation->sesSubmissions->count() > 0): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Envios SES</h4>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reservation->sesSubmissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="p-3 bg-gray-50 rounded mb-2 text-sm">
                <p>Estado: <strong><?php echo e($sub->status); ?></strong></p>
                <p>Modo: <?php echo e($sub->mode); ?></p>
                <p class="text-xs text-gray-500"><?php echo e($sub->created_at->format('d/m/Y H:i')); ?></p>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/panels/reservations/show.blade.php ENDPATH**/ ?>