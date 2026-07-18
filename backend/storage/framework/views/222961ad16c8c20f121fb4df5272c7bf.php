<?php $__env->startSection('title', $tenant->company_name); ?>
<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-xl font-semibold"><?php echo e($tenant->company_name); ?></h3>
                    <p class="text-gray-400 mt-1"><?php echo e($tenant->email); ?> <?php echo e($tenant->tax_id ? '· CIF: '.$tenant->tax_id : ''); ?></p>
                </div>
                <form method="POST" action="<?php echo e(route('admin.tenants.toggle', $tenant)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm <?php echo e($tenant->status === 'suspended' ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-red-600 text-white hover:bg-red-700'); ?>">
                        <?php echo e($tenant->status === 'suspended' ? 'Activar' : 'Suspender'); ?>

                    </button>
                </form>
            </div>
            <div class="mt-4 flex gap-4">
                <span class="px-3 py-1 text-sm rounded <?php echo e($tenant->status === 'active' ? 'bg-green-900 text-green-300' : ($tenant->status === 'trialing' ? 'bg-blue-900 text-blue-300' : ($tenant->status === 'suspended' ? 'bg-red-900 text-red-300' : 'bg-yellow-900 text-yellow-300'))); ?>">
                    <?php echo e($tenant->status); ?>

                </span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tenant->trial_ends_at): ?>
                <span class="text-sm text-gray-400">Trial hasta: <?php echo e($tenant->trial_ends_at->format('d/m/Y')); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="font-semibold mb-4">Usuarios (<?php echo e($tenant->users->count()); ?>)</h4>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $tenant->users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="flex justify-between py-2 border-b border-gray-700 last:border-0">
                <div>
                    <span class="text-white"><?php echo e($user->name); ?></span>
                    <span class="text-gray-400 text-sm ml-2"><?php echo e($user->email); ?></span>
                </div>
                <span class="text-xs px-2 py-1 rounded <?php echo e($user->pivot->role === 'admin' ? 'bg-purple-900 text-purple-300' : 'bg-gray-700 text-gray-300'); ?>">
                    <?php echo e($user->pivot->role); ?>

                </span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-gray-500">Sin usuarios</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="font-semibold mb-4">Alojamientos (<?php echo e($tenant->properties->count()); ?>)</h4>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $tenant->properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="py-2 border-b border-gray-700 last:border-0">
                <span class="text-white"><?php echo e($property->name); ?></span>
                <span class="text-gray-400 text-sm ml-2"><?php echo e($property->city); ?></span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-gray-500">Sin alojamientos</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="font-semibold mb-4">Plan actual</h4>
            <?php $sub = $tenant->subscriptions->firstWhere('status', '!=', 'canceled'); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sub && $sub->plan): ?>
            <div class="text-2xl font-bold text-blue-400"><?php echo e($sub->plan->name); ?></div>
            <div class="text-sm text-gray-400 mt-1"><?php echo e(number_format($sub->plan->price_monthly, 0)); ?>€/mes</div>
            <div class="text-sm text-gray-400 mt-1">Estado: <?php echo e($sub->status); ?></div>
            <?php else: ?>
            <p class="text-gray-500">Sin suscripción activa</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="font-semibold mb-4">Cambiar plan</h4>
            <form method="POST" action="<?php echo e(route('admin.tenants.plan', $tenant)); ?>">
                <?php echo csrf_field(); ?>
                <select name="plan_code" class="w-full bg-gray-700 border-gray-600 rounded-lg text-white mb-3 focus:border-blue-500 focus:ring-blue-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($plan->code); ?>" <?php echo e($sub && $sub->plan && $sub->plan->code === $plan->code ? 'selected' : ''); ?>>
                        <?php echo e($plan->name); ?> — <?php echo e(number_format($plan->price_monthly, 0)); ?>€/mes
                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg text-sm hover:bg-blue-700">Cambiar plan</button>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/admin/tenants/show.blade.php ENDPATH**/ ?>