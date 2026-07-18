<?php $__env->startSection('title', 'SES Hospedajes'); ?>
<?php $__env->startSection('content'); ?>
<div class="flex justify-between items-center mb-6">
    <h3 class="text-lg font-semibold">Envíos SES Hospedajes</h3>
    <form action="<?php echo e(route('ses.export')); ?>" method="GET">
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm">Exportar todo</button>
    </form>
</div>
<div class="bg-white rounded-lg shadow">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
                <th class="p-3">ID</th>
                <th class="p-3">Reserva</th>
                <th class="p-3">Estado</th>
                <th class="p-3">Modo</th>
                <th class="p-3">Referencia</th>
                <th class="p-3">Creado</th>
                <th class="p-3"></th>
            </tr></thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $submissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3">#<?php echo e($s->id); ?></td>
                    <td class="p-3"><?php echo e($s->reservation->code ?? '—'); ?></td>
                    <td class="p-3">
                        <span class="px-2 py-1 text-xs rounded-full
                            <?php if($s->status === 'sent' || $s->status === 'acknowledged'): ?> bg-green-100 text-green-700
                            <?php elseif($s->status === 'partially_sent'): ?> bg-yellow-100 text-yellow-700
                            <?php elseif($s->status === 'failed' || $s->status === 'rejected'): ?> bg-red-100 text-red-700
                            <?php elseif($s->status === 'ready'): ?> bg-blue-100 text-blue-700
                            <?php else: ?> bg-gray-100 text-gray-700 <?php endif; ?>"><?php echo e($s->status); ?></span>
                    </td>
                    <td class="p-3"><?php echo e($s->mode); ?></td>
                    <td class="p-3"><?php echo e($s->reference ?? '—'); ?></td>
                    <td class="p-3"><?php echo e($s->created_at->format('d/m/Y H:i')); ?></td>
                    <td class="p-3">
                        <a href="<?php echo e(route('ses.show', $s)); ?>" class="text-blue-600 text-xs hover:underline">Ver</a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="p-8 text-center text-gray-500">No hay envíos SES</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="p-4"><?php echo e($submissions->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/panels/ses/index.blade.php ENDPATH**/ ?>