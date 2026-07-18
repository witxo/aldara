<?php $__env->startSection('title', 'Alojamientos'); ?>
<?php $__env->startSection('content'); ?>
<div class="flex justify-between items-center mb-6">
    <h3 class="text-lg font-semibold">Mis alojamientos</h3>
    <a href="<?php echo e(route('properties.create')); ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">Nuevo alojamiento</a>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-3">
            <h4 class="font-semibold"><?php echo e($property->name); ?></h4>
            <span class="px-2 py-0.5 text-xs rounded <?php echo e($property->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'); ?>"><?php echo e($property->is_active ? 'Activo' : 'Inactivo'); ?></span>
        </div>
        <p class="text-sm text-gray-500"><?php echo e($property->type); ?> — <?php echo e($property->city); ?></p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($property->license_number): ?>
        <p class="text-xs text-gray-400 mt-1">Licencia: <?php echo e($property->license_number); ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="mt-4 flex justify-between items-center">
            <span class="text-sm text-gray-500">Capacidad: <?php echo e($property->capacity); ?></span>
            <a href="<?php echo e(route('properties.show', $property)); ?>" class="text-blue-600 text-sm hover:underline">Ver</a>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="col-span-full text-center py-12 text-gray-500">
        <p class="text-lg mb-2">No hay alojamientos</p>
        <a href="<?php echo e(route('properties.create')); ?>" class="text-blue-600 hover:underline">Crear el primer alojamiento</a>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/panels/properties/index.blade.php ENDPATH**/ ?>