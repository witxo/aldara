<?php $__env->startSection('title', isset($property) ? 'Editar alojamiento' : 'Nuevo alojamiento'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-6"><?php echo $__env->yieldContent('title'); ?></h3>
        <form method="POST" action="<?php echo e(isset($property) ? route('properties.update', $property) : route('properties.store')); ?>">
            <?php echo csrf_field(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($property)): ?> <?php echo method_field('PUT'); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="name" value="<?php echo e(old('name', $property->name ?? '')); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="type" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="apartment" <?php echo e(old('type', $property->type ?? '') === 'apartment' ? 'selected' : ''); ?>>Apartamento</option>
                        <option value="house" <?php echo e(old('type', $property->type ?? '') === 'house' ? 'selected' : ''); ?>>Casa</option>
                        <option value="rural" <?php echo e(old('type', $property->type ?? '') === 'rural' ? 'selected' : ''); ?>>Casa rural</option>
                        <option value="hotel" <?php echo e(old('type', $property->type ?? '') === 'hotel' ? 'selected' : ''); ?>>Hotel</option>
                        <option value="hostel" <?php echo e(old('type', $property->type ?? '') === 'hostel' ? 'selected' : ''); ?>>Hostal</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Capacidad</label>
                    <input type="number" name="capacity" value="<?php echo e(old('capacity', $property->capacity ?? '')); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" min="1">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <input type="text" name="address_line1" value="<?php echo e(old('address_line1', $property->address_line1 ?? '')); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                    <input type="text" name="city" value="<?php echo e(old('city', $property->city ?? '')); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Provincia</label>
                    <input type="text" name="state" value="<?php echo e(old('state', $property->state ?? '')); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Código postal</label>
                    <input type="text" name="postal_code" value="<?php echo e(old('postal_code', $property->postal_code ?? '')); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nº licencia</label>
                    <input type="text" name="license_number" value="<?php echo e(old('license_number', $property->license_number ?? '')); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Código establecimiento MIR</label>
                    <input type="text" name="ses_establecimiento_code" value="<?php echo e(old('ses_establecimiento_code', $property->ses_establecimiento_code ?? '')); ?>" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" maxlength="10">
                    <p class="text-xs text-gray-400 mt-0.5">Código de establecimiento asignado por el Ministerio del Interior (10 caracteres)</p>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($property)): ?>
                <div>
                    <label class="flex items-center gap-2 mt-6">
                        <input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', $property->is_active) ? 'checked' : ''); ?> class="rounded border-gray-300">
                        <span class="text-sm">Activo</span>
                    </label>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="<?php echo e(route('properties.index')); ?>" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm"><?php echo e(isset($property) ? 'Actualizar' : 'Crear'); ?></button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/panels/properties/form.blade.php ENDPATH**/ ?>