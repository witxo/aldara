<?php $__env->startSection('title', 'Huéspedes'); ?>
<?php $__env->startSection('content'); ?>
<div class="flex justify-between items-center mb-6">
    <h3 class="text-lg font-semibold">Huéspedes</h3>
</div>
<div class="bg-white rounded-lg shadow">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
                <th class="p-3">Nombre</th>
                <th class="p-3">Nacionalidad</th>
                <th class="p-3">Documento</th>
                <th class="p-3">Reserva</th>
                <th class="p-3"></th>
            </tr></thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $guests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $guest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 font-medium"><?php echo e($guest->first_name); ?> <?php echo e($guest->last_name); ?></td>
                    <td class="p-3"><?php echo e($guest->nationality); ?></td>
                    <td class="p-3 text-gray-500"><?php echo e($guest->document_type); ?>: ****<?php echo e(substr($guest->document_number, -4)); ?></td>
                    <td class="p-3"><?php echo e($guest->reservation?->code ?? '—'); ?></td>
                    <td class="p-3"><a href="<?php echo e(route('guests.show', $guest)); ?>" class="text-blue-600 text-xs hover:underline">Ver</a></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="p-8 text-center text-gray-500">No hay huéspedes</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="p-3 border-t"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(method_exists($guests, 'links')): ?><?php echo e($guests->links()); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/panels/guests/index.blade.php ENDPATH**/ ?>