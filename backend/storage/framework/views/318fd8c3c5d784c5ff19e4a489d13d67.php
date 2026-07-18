<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Admin'); ?> — <?php echo e(config('app.name')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <?php echo $__env->yieldPushContent('head'); ?>
</head>
<body class="bg-gray-900 text-gray-100 antialiased">
    <div class="min-h-screen flex">
        <aside class="w-64 bg-gray-800 border-r border-gray-700 hidden lg:flex flex-col">
            <div class="p-4 border-b border-gray-700">
                <h1 class="text-xl font-bold text-blue-400"><?php echo e(config('app.name')); ?></h1>
                <p class="text-xs text-gray-400 mt-1">Panel de administración</p>
            </div>
            <nav class="flex-1 p-4 space-y-1">
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-700 <?php echo e(request()->routeIs('admin.dashboard') ? 'bg-gray-700 text-blue-400' : 'text-gray-300'); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
                <a href="<?php echo e(route('admin.tenants')); ?>" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-700 <?php echo e(request()->routeIs('admin.tenants*') ? 'bg-gray-700 text-blue-400' : 'text-gray-300'); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Tenants
                </a>
                <a href="<?php echo e(route('admin.users')); ?>" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-700 <?php echo e(request()->routeIs('admin.users*') ? 'bg-gray-700 text-blue-400' : 'text-gray-300'); ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                    Usuarios
                </a>
            </nav>
            <div class="p-4 border-t border-gray-700">
                <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center px-3 py-2 text-sm text-gray-400 hover:text-gray-200">
                    ← Volver al panel principal
                </a>
            </div>
        </aside>
        <div class="flex-1 flex flex-col">
            <header class="bg-gray-800 border-b border-gray-700 px-6 py-3 flex justify-between items-center">
                <h2 class="text-lg font-semibold"><?php echo $__env->yieldContent('title', 'Admin'); ?></h2>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-400"><?php echo e(auth()->user()->name); ?></span>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="text-sm text-red-400 hover:text-red-300">Cerrar sesión</button>
                    </form>
                </div>
            </header>
            <main class="flex-1 p-6 bg-gray-900">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                    <div class="mb-4 p-4 bg-green-900 border-l-4 border-green-500 text-green-300 rounded"><?php echo e(session('success')); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                    <div class="mb-4 p-4 bg-red-900 border-l-4 border-red-500 text-red-300 rounded"><?php echo e(session('error')); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>
    </div>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /var/www/resources/views/layouts/admin.blade.php ENDPATH**/ ?>