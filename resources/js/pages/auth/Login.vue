<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import AppLogo from '@/components/AppLogo.vue';
import InputError from '@/components/InputError.vue';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head title="Log in" />
    <div class="flex flex-col min-h-screen bg-gray-100 dark:bg-gray-900">
        <header class="w-full px-4 sm:px-6 py-4">
            <div class="flex items-center justify-between max-w-7xl mx-auto">
                <Link :href="route('home')">
                <div class="flex items-center gap-2">
                    <svg class="h-6 w-auto text-gray-800 dark:text-white" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span class="text-lg font-semibold text-gray-800 dark:text-white">AssetMS</span>
                </div>
                </Link>
            </div>
        </header>

        <main class="flex-1 flex items-center justify-center">
            <Card class="w-full max-w-sm">
                <CardHeader class="text-center">
                    <CardTitle class="text-2xl">
                        Log in to your account
                    </CardTitle>
                    <CardDescription>
                        Enter your email and password below to log in.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
                        {{ status }}
                    </div>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <Label for="email">Email address</Label>
                            <Input id="email" v-model="form.email" type="email" placeholder="email@example.com" required
                                autofocus autocomplete="email" class="mt-2" />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <Label for="password">Password</Label>
                                <Link v-if="canResetPassword" :href="route('password.request')"
                                    class="text-sm text-primary hover:underline">
                                Forgot password?
                                </Link>
                            </div>
                            <Input id="password" v-model="form.password" type="password" required
                                autocomplete="current-password" class="mt-2" />
                            <InputError :message="form.errors.password" class="mt-2" />
                        </div>

                        <div class="flex items-center space-x-2">
                            <Checkbox id="remember" v-model:checked="form.remember" />
                            <Label for="remember" class="font-normal">Remember me</Label>
                        </div>

                        <Button type="submit" class="w-full" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 mr-2 animate-spin" />
                            Log in
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </main>

        <footer class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">
            © {{ new Date().getFullYear() }} Asset Management System. All rights reserved.
        </footer>
    </div>
</template>
