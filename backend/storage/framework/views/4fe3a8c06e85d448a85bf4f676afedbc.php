<?php $__env->startSection('title', isset($reservation) ? 'Editar reserva' : 'Nueva reserva'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-6"><?php echo $__env->yieldContent('title'); ?></h3>
        <form method="POST" action="<?php echo e(isset($reservation) ? route('reservations.update', $reservation) : route('reservations.store')); ?>">
            <?php echo csrf_field(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($reservation)): ?> <?php echo method_field('PUT'); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alojamiento</label>
                    <select name="property_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->id); ?>" <?php echo e(old('property_id', $reservation->property_id ?? '') == $p->id ? 'selected' : ''); ?>><?php echo e($p->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del huésped</label>
                    <input type="text" name="guest_name" value="<?php echo e(old('guest_name', $reservation->guest_name ?? '')); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="guest_email" value="<?php echo e(old('guest_email', $reservation->guest_email ?? '')); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="text" name="guest_phone" value="<?php echo e(old('guest_phone', $reservation->guest_phone ?? '')); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adultos</label>
                    <input type="number" name="adults" value="<?php echo e(old('adults', $reservation->adults ?? 1)); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" min="1" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Menores</label>
                    <input type="number" name="children" value="<?php echo e(old('children', $reservation->children ?? 0)); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" min="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha entrada</label>
                    <input type="date" name="checkin_date" value="<?php echo e(old('checkin_date', isset($reservation) ? $reservation->checkin_date->format('Y-m-d') : '')); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha salida</label>
                    <input type="date" name="checkout_date" value="<?php echo e(old('checkout_date', isset($reservation) ? $reservation->checkout_date->format('Y-m-d') : '')); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total (€)</label>
                    <input type="number" step="0.01" name="total_amount" value="<?php echo e(old('total_amount', $reservation->total_amount ?? '')); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($reservation)): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="status" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="pending" <?php echo e($reservation->status === 'pending' ? 'selected' : ''); ?>>Pendiente</option>
                        <option value="confirmed" <?php echo e($reservation->status === 'confirmed' ? 'selected' : ''); ?>>Confirmada</option>
                        <option value="checkin_sent" <?php echo e($reservation->status === 'checkin_sent' ? 'selected' : ''); ?>>Check-in enviado</option>
                        <option value="completed" <?php echo e($reservation->status === 'completed' ? 'selected' : ''); ?>>Completada</option>
                        <option value="cancelled" <?php echo e($reservation->status === 'cancelled' ? 'selected' : ''); ?>>Cancelada</option>
                    </select>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                    <textarea name="notes" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo e(old('notes', $reservation->notes ?? '')); ?></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <a href="<?php echo e(route('reservations.index')); ?>" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm"><?php echo e(isset($reservation) ? 'Actualizar' : 'Crear'); ?></button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/panels/reservations/form.blade.php ENDPATH**/ ?>