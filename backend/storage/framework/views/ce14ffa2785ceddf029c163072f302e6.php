<header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <div>
        <h2 class="text-lg font-semibold text-gray-800"><?php echo $__env->yieldContent('title', 'Panel'); ?></h2>
    </div>
    <div class="flex items-center space-x-4">
        <form method="POST" action="<?php echo e(route('logout')); ?>" class="inline">
            <?php echo csrf_field(); ?>
            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Cerrar sesión</button>
        </form>
    </div>
</header>
<?php /**PATH /var/www/resources/views/panels/_header.blade.php ENDPATH**/ ?>