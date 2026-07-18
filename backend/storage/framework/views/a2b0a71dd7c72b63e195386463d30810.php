<?php $__env->startSection('title', 'Auditoría'); ?>
<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-lg shadow">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
                <th class="p-3">Fecha</th>
                <th class="p-3">Evento</th>
                <th class="p-3">Tipo</th>
                <th class="p-3">Usuario</th>
                <th class="p-3">IP</th>
            </tr></thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-b hover:bg-gray-50 text-sm">
                    <td class="p-3 text-gray-500"><?php echo e($log->created_at->format('d/m/Y H:i')); ?></td>
                    <td class="p-3"><?php echo e($log->event); ?></td>
                    <td class="p-3 text-gray-500"><?php echo e($log->auditable_type); ?></td>
                    <td class="p-3"><?php echo e($log->user?->name ?? '—'); ?></td>
                    <td class="p-3 text-gray-500"><?php echo e($log->ip_address ?? '—'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="p-8 text-center text-gray-500">Sin registros de auditoría</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="p-3 border-t"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(method_exists($logs, 'links')): ?><?php echo e($logs->links()); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/panels/audit/index.blade.php ENDPATH**/ ?>