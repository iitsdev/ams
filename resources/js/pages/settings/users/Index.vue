<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { BreadcrumbItem } from '@/types';


const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '#' },
    { title: 'User Management', href: route('users.index') }
];
</script>

<template>

    <Head tile="User Management" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #head>
            <h2 class="text-xl font-bold tracking-tight m-2">
                Manage Users
            </h2>
        </template>
    </AppLayout>
</template>