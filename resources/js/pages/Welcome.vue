<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
</script>

<template>

    <Head title="Welcome" />
    <div class="flex flex-col min-h-screen bg-gray-100 dark:bg-gray-900">
        <header class="w-full px-4 sm:px-6 py-4">
            <div class="flex items-center justify-between max-w-7xl mx-auto">
                <div class="flex items-center gap-2">
                    <svg class="h-6 w-auto text-gray-800 dark:text-white" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span class="text-lg font-semibold text-gray-800 dark:text-white">AssetMS</span>
                </div>
                <div class="space-x-2">
                    <Link :href="route('login')">
                    <Button variant="default">Log in</Button>
                    </Link>
                </div>
            </div>
        </header>

        <main class="flex-1 flex items-center justify-center">
            <div class="max-w-4xl mx-auto text-center p-6">
                <h1 class="text-4xl md:text-6xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Intelligent Asset Management, Simplified.
                </h1>
                <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300">
                    Track, manage, and optimize your valuable assets with a powerful, intuitive platform. From hardware
                    and software to licenses and warranties, take full control of your inventory.
                </p>
                <div class="mt-10">
                    <Link :href="route('login')">
                    <Button size="lg">Launch Application</Button>
                    </Link>
                </div>
            </div>
        </main>

        <footer class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">
            © {{ new Date().getFullYear() }} Asset Management System. All rights reserved.
        </footer>
    </div>
</template>