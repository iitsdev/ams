<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'

const form = useForm({
    name: '',
    color: '#3b82f6',
    description: '',
})

const submit = () => {
    form.post(route('asset-statuses.store'))
}
</script>

<template>

    <Head title="Create Asset Status" />

    <AppLayout>
        <template #header>
            <h2 class="text-3xl font-bold tracking-tight">Create Asset Status</h2>
        </template>

        <form @submit.prevent="submit">
            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Status Details</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Name <span class="text-red-500">*</span></Label>
                        <Input id="name" v-model="form.name" placeholder="e.g., In Use, Available" />
                        <div v-if="form.errors.name" class="text-sm text-red-600">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="color">Color <span class="text-red-500">*</span></Label>
                        <div class="flex gap-2">
                            <Input id="color" v-model="form.color" type="color" class="w-20 h-10" />
                            <Input v-model="form.color" placeholder="#3b82f6" class="flex-1" />
                        </div>
                        <div v-if="form.errors.color" class="text-sm text-red-600">
                            {{ form.errors.color }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Description</Label>
                        <Textarea id="description" v-model="form.description" placeholder="Optional description"
                            rows="3" />
                        <div v-if="form.errors.description" class="text-sm text-red-600">
                            {{ form.errors.description }}
                        </div>
                    </div>

                    <div class="flex gap-2 pt-4">
                        <Button type="submit" :disabled="form.processing">
                            Create Status
                        </Button>
                        <Button type="button" variant="outline" @click="$inertia.visit(route('asset-statuses.index'))">
                            Cancel
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </form>
    </AppLayout>
</template>