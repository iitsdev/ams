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
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot name="header" />
            <slot />
        </AppContent>
    </AppShell>
    <Toaster rich-colors position="top-right" />
</template>
