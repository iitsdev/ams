<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const addForm = useForm({
    name: '',
});

const submit = () => {
    addForm.post(route('users.store'), {
        onSuccess: () => addForm.reset('name'),
    });
};

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
        <div class="p-1">
            <div class="grid gap-6 md:grid-cols-3">
                <div class="md:col-span-1">
                    <form @submit.prevent="submit">
                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    Add New User.
                                </CardTitle>
                                <CardDescription>
                                    Create a new user.
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="name"></Label>
                                    <Input id="name" type="text" />
                                </div>
                            </CardContent>
                        </Card>
                    </form>
                </div>
                <div class="md:col-span-2"></div>
            </div>
        </div>
    </AppLayout>
</template>