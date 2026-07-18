<?php $__env->startSection('title', $property->name); ?>
<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-semibold"><?php echo e($property->name); ?></h3>
                    <p class="text-sm text-gray-500"><?php echo e($property->type); ?> — <?php echo e($property->license_number ?? 'Sin licencia'); ?></p>
                </div>
                <span class="px-3 py-1 text-sm rounded-full <?php echo e($property->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'); ?>"><?php echo e($property->is_active ? 'Activo' : 'Inactivo'); ?></span>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500">Dirección:</span> <?php echo e($property->address_line1); ?></div>
                <div><span class="text-gray-500">Ciudad:</span> <?php echo e($property->city); ?></div>
                <div><span class="text-gray-500">Provincia:</span> <?php echo e($property->state); ?></div>
                <div><span class="text-gray-500">C.P.:</span> <?php echo e($property->postal_code); ?></div>
                <div><span class="text-gray-500">Capacidad:</span> <?php echo e($property->capacity ?? '—'); ?></div>
                <div><span class="text-gray-500">Código MIR:</span> <?php echo e($property->ses_establecimiento_code ?? '—'); ?></div>
                <div><span class="text-gray-500">Check-in:</span> <?php echo e($property->checkin_time ?? '—'); ?></div>
                <div><span class="text-gray-500">Check-out:</span> <?php echo e($property->checkout_time ?? '—'); ?></div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Próximas reservas</h4>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $property->reservations()->where('checkout_date', '>=', now())->orderBy('checkin_date')->limit(5)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $res): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded mb-2">
                <div>
                    <p class="font-medium"><?php echo e($res->guest_name); ?></p>
                    <p class="text-sm text-gray-500"><?php echo e($res->checkin_date->format('d/m/Y')); ?> — <?php echo e($res->checkout_date->format('d/m/Y')); ?></p>
                </div>
                <span class="text-xs px-2 py-0.5 rounded <?php echo e($res->status === 'confirmed' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700'); ?>"><?php echo e($res->status); ?></span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-gray-500 text-sm">No hay reservas próximas</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Acciones</h4>
            <div class="space-y-3">
                <a href="<?php echo e(route('properties.edit', $property)); ?>" class="block text-center bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm">Editar</a>
                <a href="<?php echo e(route('reservations.index', ['property_id' => $property->id])); ?>" class="block text-center bg-blue-100 text-blue-700 px-4 py-2 rounded-lg text-sm">Ver reservas</a>
                <hr>
                <form method="POST" action="<?php echo e(route('properties.destroy', $property)); ?>" onsubmit="return confirm('¿Eliminar este alojamiento?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="w-full bg-red-100 text-red-700 px-4 py-2 rounded-lg text-sm">Eliminar</button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="font-semibold mb-4">Integraciones</h4>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $property->integrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $int): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="p-3 bg-gray-50 rounded mb-2 text-sm">
                <p class="font-medium"><?php echo e($int->provider); ?></p>
                <p class="text-xs text-gray-500 <?php echo e($int->is_connected ? 'text-green-600' : 'text-red-600'); ?>"><?php echo e($int->is_connected ? 'Conectado' : 'Desconectado'); ?></p>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-gray-500 text-sm">Sin integraciones</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/panels/properties/show.blade.php ENDPATH**/ ?>