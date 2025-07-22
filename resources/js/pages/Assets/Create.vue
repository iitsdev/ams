<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'

import { Textarea } from '@/components/ui/textarea'
import { type BreadcrumbItem } from '@/types'

const props = defineProps({
    dropdowns: Object,
})

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Assets',
        href: route('assets.index'),
    },
    {
        title: 'Create',
        href: route('assets.create'),
    }
]

const form = useForm({
    name: '',
    category_id: null,
    status_id: null,
    location_id: null,
    serial_number: '',
    purchase_date: '',
    purchase_cost: 0,
    warranty_expiry: '',
    description: '',
})


const submit = () => {
    form.post(route('assets.store'))
}
</script>

<template>

    <Head title="Create Asset" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex h-16 items-center justify-between border-b px-4 sm:px-6 lg:px-8 m-2">
                <h2 class="text-xl font-semibold tracking-tight">
                    Create a New Asset
                </h2>
            </div>
        </template>
        <form @submit.prevent="submit" class="mt-2">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div class="grid auto-rows-max items-start gap-4 lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Asset Details</CardTitle>
                            <CardDescription>
                                Please provide the main details of the asset.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-3">
                                <Label for="name">Name</Label>
                                <Input id="name" v-model="form.name" type="text"
                                    placeholder="e.g., MacBook Pro 14 M3" />
                                <div v-if="form.errors.name" class="text-sm text-red-600">
                                    {{ form.errors.name }}
                                </div>
                            </div>
                            <div class="grid gap-3">
                                <Label for="serial_number">Serial Number (Optional)</Label>
                                <Input id="serial_number" v-model="form.serial_number" type="text" />
                                <div v-if="form.errors.serial_number" class="text-sm text-red-600">
                                    {{ form.errors.serial_number }}
                                </div>
                            </div>
                            <div class="grid gap-3">
                                <Label for="description">Description (Optional)</Label>
                                <Textarea id="description" v-model="form.description" />
                                <div v-if="form.errors.description" class="text-sm text-red-600">
                                    {{ form.errors.description }}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>
                                Purchase Information
                            </CardTitle>
                            <CardContent class="grid gap-4 sm:grid-cols-2">
                                <div class="grid gap-3">
                                    <Label for="purchase_date">Purchase Date</Label>
                                    <Input id="purchase_date" v-model="form.purchase_date" type="date" />
                                    <div v-if="form.errors.purchase_date" class="text-sm text-red-600">
                                        {{ form.errors.purchase_date }}
                                    </div>
                                </div>
                                <div class="grid gap-3">
                                    <Label for="purchase_cost">Purchase Cost</Label>
                                    <Input id="purchase_cost" v-model="form.purchase_cost" type="number" step="0.01"
                                        placeholder="e.g., 1299.99" />
                                    <div v-if="form.errors.purchase_cost" class="text-sm text-red-600">
                                        {{ form.errors.purchase_cost }}
                                    </div>
                                </div>
                                <div class="grid gap-3">
                                    <Label for="warranty_expiry">Warranty Expiry</Label>
                                    <Input id="warranty_expiry" v-model="form.warranty_expiry" type="date" />
                                    <div v-if="form.errors.warranty_expiry" class="text-sm text-red-600">
                                        {{ form.errors.warranty_expiry }}
                                    </div>
                                </div>
                            </CardContent>
                        </CardHeader>
                    </Card>
                </div>
                <div class="grid auto-rows-max items-start gap-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>
                                Organization
                            </CardTitle>
                            <CardContent class="space-y-4">
                                <div class="grid gap-3">
                                    <Label for="category">Category</Label>
                                    <Select v-model="form.category_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="item in props.dropdowns?.categories" :key="item.id"
                                                :value="item.id">
                                                {{ item.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <div v-if="form.errors.category_id" class="text-sm text-red-600">
                                        {{ form.errors.category_id }}
                                    </div>
                                </div>
                                <div class="grid gap-3">
                                    <Label for="status">Status</Label>
                                    <Select v-model="form.status_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="item in props.dropdowns?.statuses" :key="item.id"
                                                :value="item.id">
                                                {{ item.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <div v-if="form.errors.status_id" class="text-sm text-red-600">
                                        {{ form.errors.status_id }}
                                    </div>
                                </div>
                                <div class="grid gap-3">
                                    <Label for="location">Location</Label>
                                    <Select v-model="form.location_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a location" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="item in props.dropdowns?.locations" :key="item.id"
                                                :value="item.id">
                                                {{ item.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <div v-if="form.errors.location_id" class="text-sm text-red-600">
                                        {{ form.errors.location_id }}
                                    </div>
                                </div>
                            </CardContent>
                        </CardHeader>
                    </Card>
                    <div class="flex items-center justify-end gap-2">
                        <Link :href="route('assets.index')">
                        <Button variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disable="form.processing">
                            Save Asset
                        </Button>
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>

</template>