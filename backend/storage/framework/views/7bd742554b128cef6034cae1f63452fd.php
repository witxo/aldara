<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="text-3xl font-bold text-white"><?php echo e($totalTenants); ?></div>
        <div class="text-sm text-gray-400 mt-1">Total tenants</div>
    </div>
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="text-3xl font-bold text-green-400"><?php echo e($activeTenants); ?></div>
        <div class="text-sm text-gray-400 mt-1">Activos</div>
    </div>
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="text-3xl font-bold text-red-400"><?php echo e($suspendedTenants); ?></div>
        <div class="text-sm text-gray-400 mt-1">Suspendidos</div>
    </div>
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <div class="text-3xl font-bold text-yellow-400"><?php echo e($pastDueTenants); ?></div>
        <div class="text-sm text-gray-400 mt-1">Morosos</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold mb-4">Total usuarios</h3>
        <div class="text-4xl font-bold text-blue-400"><?php echo e($totalUsers); ?></div>
    </div>
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold mb-4">Últimos tenants</h3>
        <div class="space-y-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentTenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a href="<?php echo e(route('admin.tenants.show', $t)); ?>" class="flex justify-between items-center p-2 rounded hover:bg-gray-700">
                <span><?php echo e($t->company_name); ?></span>
                <span class="text-xs px-2 py-1 rounded <?php echo e($t->status === 'active' ? 'bg-green-900 text-green-300' : ($t->status === 'suspended' ? 'bg-red-900 text-red-300' : 'bg-yellow-900 text-yellow-300')); ?>">
                    <?php echo e($t->status); ?>

                </span>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-gray-500">Sin tenants</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>