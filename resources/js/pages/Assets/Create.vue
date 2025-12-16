<script setup lang="ts">
// filepath: c:\Users\daveg\Desktop\Projects\ams\resources\js\pages\assets\Create.vue

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
    { title: 'Assets', href: route('assets.index') },
    { title: 'Create', href: route('assets.create') }
]

const form = useForm({
    name: '',
    brand_id: '', // Changed from 'brand'
    model: '',
    serial_number: '',
    category_id: '',
    status_id: '',
    location_id: '',
    supplier_id: '',
    purchase_date: '',
    deployed_at: '',
    purchase_cost: '',
    warranty_expiry: '',
    notes: '',
    specifications: '',
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

        <form @submit.prevent="submit" class="mt-2 px-4">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <!-- Left Column - Main Details -->
                <div class="grid auto-rows-max items-start gap-4 lg:col-span-2">
                    <!-- Basic Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>
                                Enter the main details of the asset.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-3">
                                <Label for="name">Asset Name <span class="text-red-500">*</span></Label>
                                <Input id="name" v-model="form.name" type="text"
                                    placeholder="e.g., MacBook Pro 14 M3" />
                                <div v-if="form.errors.name" class="text-sm text-red-600">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="grid gap-3">
                                    <Label for="brand_id">Brand</Label>
                                    <Select v-model="form.brand_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a brand" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="item in props.dropdowns?.brands" :key="item.id"
                                                :value="item.id">
                                                {{ item.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <div v-if="form.errors.brand_id" class="text-sm text-red-600">
                                        {{ form.errors.brand_id }}
                                    </div>
                                </div>
                                <div class="grid gap-3">
                                    <Label for="model">Model</Label>
                                    <Input id="model" v-model="form.model" type="text"
                                        placeholder="e.g., MacBook Pro 14-inch" />
                                    <div v-if="form.errors.model" class="text-sm text-red-600">
                                        {{ form.errors.model }}
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-3">
                                <Label for="serial_number">Serial Number</Label>
                                <Input id="serial_number" v-model="form.serial_number" type="text"
                                    placeholder="e.g., ABC123456789" />
                                <div v-if="form.errors.serial_number" class="text-sm text-red-600">
                                    {{ form.errors.serial_number }}
                                </div>
                            </div>

                            <div class="grid gap-3">
                                <Label for="specifications">Specifications</Label>
                                <Textarea id="specifications" v-model="form.specifications"
                                    placeholder="e.g., M3 chip, 16GB RAM, 512GB SSD, 14-inch Liquid Retina XDR display"
                                    rows="3" />
                                <div v-if="form.errors.specifications" class="text-sm text-red-600">
                                    {{ form.errors.specifications }}
                                </div>
                            </div>

                            <div class="grid gap-3">
                                <Label for="notes">Notes</Label>
                                <Textarea id="notes" v-model="form.notes"
                                    placeholder="Additional notes or comments about this asset..." rows="3" />
                                <div v-if="form.errors.notes" class="text-sm text-red-600">
                                    {{ form.errors.notes }}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Purchase Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Purchase Information</CardTitle>
                            <CardDescription>
                                Enter purchase and warranty details.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-3">
                                <Label for="supplier_id">Supplier</Label>
                                <Select v-model="form.supplier_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a supplier" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="item in props.dropdowns?.suppliers" :key="item.id"
                                            :value="item.id">
                                            {{ item.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="form.errors.supplier_id" class="text-sm text-red-600">
                                    {{ form.errors.supplier_id }}
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
                                <Label for="purchase_date">Purchase Date</Label>
                                <Input id="purchase_date" v-model="form.purchase_date" type="date" />
                                <div v-if="form.errors.purchase_date" class="text-sm text-red-600">
                                    {{ form.errors.purchase_date }}
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
                    </Card>
                </div>

                <!-- Right Column - Organization & Actions -->
                <div class="grid auto-rows-max items-start gap-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Organization</CardTitle>
                            <CardDescription>
                                Set category, status, and location.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-3">
                                <Label for="category">Category <span class="text-red-500">*</span></Label>
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
                                <Label for="status">Status <span class="text-red-500">*</span></Label>
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
                                <Label for="location">Location <span class="text-red-500">*</span></Label>
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
                    </Card>

                    <!-- Action Buttons -->
                    <Card>
                        <CardContent class="pt-6">
                            <div class="flex flex-col gap-2">
                                <Button type="submit" :disabled="form.processing" class="w-full">
                                    Create Asset
                                </Button>
                                <Link :href="route('assets.index')" class="w-full">
                                    <Button variant="outline" type="button" class="w-full">
                                        Cancel
                                    </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </form>
    </AppLayout>
</template>