<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <div class="fixed left-2 top-2 z-50 space-x-2">
        <a href="#main-content"
            class="sr-only focus:not-sr-only focus:inline-block focus:bg-primary focus:text-primary-foreground focus:px-3 focus:py-2 focus:rounded">
            Skip to main content
        </a>
        <a href="#primary-navigation"
            class="sr-only focus:not-sr-only focus:inline-block focus:bg-primary focus:text-primary-foreground focus:px-3 focus:py-2 focus:rounded">
            Skip to navigation
        </a>
    </div>
    <AppShell class="flex-col">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent>
            <main id="main-content" tabindex="-1" role="main">
                <slot />
            </main>
        </AppContent>
    </AppShell>
</template>
