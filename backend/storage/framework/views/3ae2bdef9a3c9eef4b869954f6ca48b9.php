<?php $__env->startSection('title', 'Reservas'); ?>
<?php $__env->startSection('content'); ?>
<div class="flex justify-between items-center mb-6">
    <h3 class="text-lg font-semibold">Todas las reservas</h3>
    <a href="<?php echo e(route('reservations.create')); ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Nueva reserva</a>
</div>
<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" placeholder="Buscar huésped o código..." value="<?php echo e(request('search')); ?>" class="rounded-lg border-gray-300 text-sm flex-1">
            <select name="status" class="rounded-lg border-gray-300 text-sm">
                <option value="">Todos los estados</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['pending','confirmed','checkin_sent','partial','completed','cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($s); ?>" <?php echo e(request('status') === $s ? 'selected' : ''); ?>><?php echo e(ucfirst($s)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
            <button type="submit" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm">Filtrar</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
                <th class="p-3">Código</th>
                <th class="p-3">Huésped</th>
                <th class="p-3">Alojamiento</th>
                <th class="p-3">Entrada</th>
                <th class="p-3">Salida</th>
                <th class="p-3">Origen</th>
                <th class="p-3">Estado</th>
                <th class="p-3"></th>
            </tr></thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reservations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $res): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 font-medium"><?php echo e($res->code); ?></td>
                    <td class="p-3"><?php echo e($res->guest_name); ?></td>
                    <td class="p-3"><?php echo e($res->property->name ?? '—'); ?></td>
                    <td class="p-3"><?php echo e($res->checkin_date->format('d/m/Y')); ?></td>
                    <td class="p-3"><?php echo e($res->checkout_date->format('d/m/Y')); ?></td>
                    <td class="p-3"><?php echo e($res->source); ?></td>
                    <td class="p-3">
                        <span class="px-2 py-1 text-xs rounded-full
                            <?php if($res->status === 'confirmed'): ?> bg-blue-100 text-blue-700
                            <?php elseif($res->status === 'checkin_sent'): ?> bg-yellow-100 text-yellow-700
                            <?php elseif($res->status === 'completed'): ?> bg-green-100 text-green-700
                            <?php elseif($res->status === 'cancelled'): ?> bg-red-100 text-red-700
                            <?php else: ?> bg-gray-100 text-gray-700 <?php endif; ?>"><?php echo e($res->status); ?></span>
                    </td>
                    <td class="p-3">
                        <a href="<?php echo e(route('reservations.show', $res)); ?>" class="text-blue-600 hover:underline text-xs">Ver</a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" class="p-8 text-center text-gray-500">No hay reservas</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="p-4"><?php echo e($reservations->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/panels/reservations/index.blade.php ENDPATH**/ ?>