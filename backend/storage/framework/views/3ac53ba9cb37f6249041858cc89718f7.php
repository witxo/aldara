<?php $__env->startSection('title', 'Usuarios'); ?>
<?php $__env->startSection('content'); ?>
<div class="flex justify-between items-center mb-6">
    <h3 class="text-lg font-semibold">Usuarios del equipo</h3>
    <a href="<?php echo e(route('tenant-users.create')); ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">Invitar usuario</a>
</div>
<div class="bg-white rounded-lg shadow">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b bg-gray-50">
                <th class="p-3">Nombre</th>
                <th class="p-3">Email</th>
                <th class="p-3">Rol</th>
                <th class="p-3">Estado</th>
                <th class="p-3">Aceptado</th>
                <th class="p-3"></th>
            </tr></thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3"><?php echo e($user->name); ?></td>
                    <td class="p-3"><?php echo e($user->email); ?></td>
                    <td class="p-3">
                        <span class="px-2 py-1 text-xs rounded-full
                            <?php if($user->pivot->role === 'admin'): ?> bg-purple-100 text-purple-700
                            <?php else: ?> bg-gray-100 text-gray-700 <?php endif; ?>"><?php echo e($user->pivot->role === 'admin' ? 'Admin' : 'Operador'); ?></span>
                    </td>
                    <td class="p-3"><?php echo e($user->pivot->is_active ? 'Activo' : 'Inactivo'); ?></td>
                    <td class="p-3"><?php echo e($user->pivot->accepted_at ? 'Sí' : 'Pendiente'); ?></td>
                    <td class="p-3">
                        <form method="POST" action="<?php echo e(route('tenant-users.destroy', $user)); ?>" onsubmit="return confirm('¿Eliminar usuario?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-red-600 text-xs hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="p-8 text-center text-gray-500">No hay usuarios</td></tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/panels/users/index.blade.php ENDPATH**/ ?>