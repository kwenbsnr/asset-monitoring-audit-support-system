<?php if (!defined('APP_START')) exit; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – NIA Asset Monitoring</title>
    <link href="/asset-monitoring-audit-support-system/public/css/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-linear-to-br from-[#182919] via-[#243C25] to-[#345635] min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-10 hover:shadow-[0_20px_60px_rgba(0,0,0,0.5)] transition-shadow duration-300 border border-white/10">

            <!-- Logo & Header -->
            <div class="text-center mb-6">
                <img src="/asset-monitoring-audit-support-system/public/images/nia-logo.png" alt="NIA Logo" class="h-20 mx-auto mb-3 drop-shadow-lg" onerror="this.style.display='none'">
                <h4 class="text-2xl font-extrabold text-[#022E23] tracking-tight">NIA Regional Office IX</h4>
                <p class="text-sm font-medium text-[#044031] opacity-80">Asset Monitoring & Audit Support</p>
            </div>

            <!-- Decorative divider -->
            <div class="flex items-center gap-3 my-4">
                <div class="flex-1 h-px bg-linear-to-r from-transparent to-[#D1EEB8]"></div>
                <div class="w-2 h-2 bg-yellow-400 rounded-full shadow-[0_0_0_3px_rgba(251,191,36,0.25)]"></div>
                <div class="flex-1 h-px bg-linear-to-l from-transparent to-[#D1EEB8]"></div>
            </div>

            <!-- Error messages -->
            <?php if (!empty($errors)): ?>
                <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm flex flex-col gap-1">
                    <?php foreach ($errors as $err): ?>
                        <div><?= htmlspecialchars($err) ?></div>
                    <?php endforeach; ?>
                    <button type="button" class="self-end text-gray-400 hover:text-gray-600 text-lg leading-none" onclick="this.parentElement.remove()">&times;</button>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="index.php">
                <div class="space-y-5">
                    <div>
                        <label for="username" class="block text-sm font-semibold text-[#022E23] mb-1">Username</label>
                        <input type="text" name="username" id="username" 
                               value="<?= htmlspecialchars($username ?? '') ?>" 
                               class="w-full px-4 py-3 bg-gray-50 border-2 border-[#d4dfd4] rounded-xl text-[#022E23] placeholder-gray-400 focus:bg-white focus:border-yellow-400 focus:ring-4 focus:ring-yellow-200/50 transition duration-200 outline-none"
                               placeholder="Enter your username" required autofocus>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-semibold text-[#022E23] mb-1">Password</label>
                        <input type="password" name="password" id="password"
                               class="w-full px-4 py-3 bg-gray-50 border-2 border-[#d4dfd4] rounded-xl text-[#022E23] placeholder-gray-400 focus:bg-white focus:border-yellow-400 focus:ring-4 focus:ring-yellow-200/50 transition duration-200 outline-none"
                               placeholder="Enter your password" required>
                    </div>
                    <button type="submit" name="login"
                            class="w-full py-3.5 bg-linear-to-r from-yellow-400 to-amber-500 text-[#022E23] font-bold text-lg rounded-xl shadow-md hover:scale-[1.02] hover:shadow-lg transition-all duration-200">
                        <i class="bi bi-box-arrow-in-right mr-2"></i>Sign In
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center text-sm text-[#5c7a5c]">
                &copy; <?= date('Y') ?> NIA Regional Office IX
            </div>
        </div>
    </div>
</body>
</html>