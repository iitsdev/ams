<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { Toaster, toast } from 'vue-sonner'
import { watch } from 'vue'
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';



const page = usePage()


watch(() => page.props.flash, (flash: any) => {

    // console.log('Flash message received from Laravel:', flash);

    if (flash.success) {
        toast.success(flash.success)
    }

    if (flash.error) {
        toast.error(flash.error)
    }
}, { deep: true })

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:left-2 focus:top-2 focus:z-50 focus:bg-primary focus:text-primary-foreground focus:px-3 focus:py-2 focus:rounded">
        Skip to main content
    </a>
    <div class="fixed left-2 top-2 z-50 space-x-2">
        <a href="#primary-navigation"
            class="sr-only focus:not-sr-only focus:inline-block focus:bg-primary focus:text-primary-foreground focus:px-3 focus:py-2 focus:rounded">
            Skip to navigation
        </a>
    </div>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot name="header" />
            <div id="main-content" tabindex="-1" role="main">
                <slot />
            </div>
        </AppContent>
    </AppShell>
    <Toaster rich-colors position="top-right" />
</template>
