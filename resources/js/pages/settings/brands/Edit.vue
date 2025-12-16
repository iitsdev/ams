<script setup lang="ts">
// filepath: c:\Users\daveg\Desktop\Projects\ams\resources\js\pages\settings\brands\Edit.vue

import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from '@/components/ui/checkbox'
import { type BreadcrumbItem } from '@/types'

const props = defineProps({
    brand: Object,
})

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '#' },
    { title: 'Brands', href: route('brands.index') },
    { title: 'Edit', href: route('brands.edit', props.brand?.id) }
]

const form = useForm({
    name: props.brand?.name || '',
    website: props.brand?.website || '',
    description: props.brand?.description || '',
    is_active: props.brand?.is_active ?? true,
})

const submit = () => {
    form.put(route('brands.update', props.brand?.id))
}
</script>

<template>

    <Head :title="`Edit Brand - ${form.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex h-16 items-center justify-between border-b px-4 sm:px-6 lg:px-8 m-2">
                <h2 class="text-xl font-semibold tracking-tight">
                    Edit Brand
                </h2>
            </div>
        </template>

        <form @submit.prevent="submit" class="mt-6 px-4 max-w-2xl mx-auto">
            <Card>
                <CardHeader>
                    <CardTitle>Brand Information</CardTitle>
                    <CardDescription>
                        Update brand details.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-3">
                        <Label for="name">Brand Name <span class="text-red-500">*</span></Label>
                        <Input id="name" v-model="form.name" type="text" required />
                        <div v-if="form.errors.name" class="text-sm text-red-600">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div class="grid gap-3">
                        <Label for="website">Website</Label>
                        <Input id="website" v-model="form.website" type="url" placeholder="https://www.example.com" />
                        <div v-if="form.errors.website" class="text-sm text-red-600">
                            {{ form.errors.website }}
                        </div>
                    </div>

                    <div class="grid gap-3">
                        <Label for="description">Description</Label>
                        <Textarea id="description" v-model="form.description"
                            placeholder="Additional information about the brand..." rows="4" />
                        <div v-if="form.errors.description" class="text-sm text-red-600">
                            {{ form.errors.description }}
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <Checkbox id="is_active" :checked="form.is_active" @update:checked="form.is_active = $event" />
                        <Label for="is_active" class="cursor-pointer">
                            Active (available for asset assignment)
                        </Label>
                    </div>
                </CardContent>
            </Card>

            <div class="flex justify-end gap-2 mt-6">
                <Link :href="route('brands.index')">
                    <Button variant="outline" type="button">
                        Cancel
                    </Button>
                </Link>
                <Button type="submit" :disabled="form.processing">
                    Save Changes
                </Button>
            </div>
        </form>
    </AppLayout>
</template>