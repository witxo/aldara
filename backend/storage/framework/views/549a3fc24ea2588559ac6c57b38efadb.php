<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tenant = current_tenant()): ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tenant->isTrialing()): ?>
    <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 text-blue-700 rounded">
        <p class="font-medium">🧪 Periodo de prueba — <?php echo e(now()->diffInDays($tenant->trial_ends_at)); ?> días restantes</p>
        <p class="text-sm mt-1">Tu prueba gratuita finaliza el <?php echo e($tenant->trial_ends_at->format('d/m/Y')); ?>.
        <a href="<?php echo e(route('billing.index')); ?>" class="underline font-medium">Configurar método de pago</a> para no perder el acceso.</p>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-500">Reservas del día</p>
        <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo e($todayReservations ?? 0); ?></p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-500">Check-ins pendientes</p>
        <p class="text-3xl font-bold text-yellow-600 mt-1"><?php echo e($pendingCheckins ?? 0); ?></p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-500">Huéspedes activos</p>
        <p class="text-3xl font-bold text-green-600 mt-1"><?php echo e($activeGuests ?? 0); ?></p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-500">Próximas llegadas</p>
        <p class="text-3xl font-bold text-blue-600 mt-1"><?php echo e($upcomingArrivals ?? 0); ?></p>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold">Reservas de hoy</h3>
    </div>
    <div class="p-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($todayReservationsList) && $todayReservationsList->count() > 0): ?>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-3">Código</th>
                        <th class="pb-3">Huésped</th>
                        <th class="pb-3">Alojamiento</th>
                        <th class="pb-3">Entrada</th>
                        <th class="pb-3">Salida</th>
                        <th class="pb-3">Estado</th>
                        <th class="pb-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $todayReservationsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $res): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="border-b last:border-0">
                        <td class="py-3"><?php echo e($res->code); ?></td>
                        <td class="py-3"><?php echo e($res->guest_name); ?></td>
                        <td class="py-3"><?php echo e($res->property->name ?? '—'); ?></td>
                        <td class="py-3"><?php echo e($res->checkin_date->format('d/m/Y')); ?></td>
                        <td class="py-3"><?php echo e($res->checkout_date->format('d/m/Y')); ?></td>
                        <td class="py-3">
                            <span class="px-2 py-1 text-xs rounded-full
                                <?php if($res->status === 'confirmed'): ?> bg-blue-100 text-blue-700
                                <?php elseif($res->status === 'checkin_sent'): ?> bg-yellow-100 text-yellow-700
                                <?php elseif($res->status === 'completed'): ?> bg-green-100 text-green-700
                                <?php elseif($res->status === 'cancelled'): ?> bg-red-100 text-red-700
                                <?php else: ?> bg-gray-100 text-gray-700
                                <?php endif; ?>"><?php echo e($res->status); ?></span>
                        </td>
                        <td class="py-3">
                            <a href="<?php echo e(route('reservations.show', $res)); ?>" class="text-blue-600 hover:underline">Ver</a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-gray-500 text-center py-8">No hay reservas para hoy</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/panels/dashboard.blade.php ENDPATH**/ ?>