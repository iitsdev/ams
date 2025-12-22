<script setup lang="ts">
// filepath: c:\Users\daveg\Desktop\Projects\ams\resources\js\pages\settings\users\Create.vue

import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import InputError from '@/components/InputError.vue'

interface Props {
    departments: Array<{ id: number; name: string }>
    roles: Array<{ id: number; name: string }>
}

const props = defineProps<Props>()

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    department_id: null,
    role: '',
})

const submit = () => {
    form.post(route('users.store'))
}
</script>

<template>
    <AppLayout>
        <div class="max-w-2xl space-y-6">
            <Heading>Create User</Heading>

            <Card>
                <CardHeader>
                    <CardTitle>User Information</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid gap-3">
                            <Label for="name">Name *</Label>
                            <Input id="name" v-model="form.name" type="text" required />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-3">
                            <Label for="email">Email *</Label>
                            <Input id="email" v-model="form.email" type="email" required />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-3">
                            <Label for="department">Department</Label>
                            <Select v-model="form.department_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select a department" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">None</SelectItem>
                                    <SelectItem v-for="dept in departments" :key="dept.id" :value="dept.id">
                                        {{ dept.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.department_id" />
                        </div>

                        <div class="grid gap-3">
                            <Label for="role">Role *</Label>
                            <Select v-model="form.role">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select a role" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="role in roles" :key="role.id" :value="role.name">
                                        {{ role.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.role" />
                        </div>

                        <div class="grid gap-3">
                            <Label for="password">Password *</Label>
                            <Input id="password" v-model="form.password" type="password" required />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="grid gap-3">
                            <Label for="password_confirmation">Confirm Password *</Label>
                            <Input id="password_confirmation" v-model="form.password_confirmation" type="password"
                                required />
                        </div>

                        <div class="flex gap-3">
                            <Button type="submit" :disabled="form.processing">
                                Create User
                            </Button>
                            <Button type="button" variant="outline" @click="$inertia.visit(route('users.index'))">
                                Cancel
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>