<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card'
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
    asset: Object,
    dropdowns: Object,
})

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Assets', href: route('assets.index') },
    { title: 'Edit', href: route('assets.edit', props.asset?.id) },
]

const form = useForm({
    name: props.asset?.name,
    category_id: props.asset?.category_id,
    status_id: props.asset?.status_id,
    location_id: props.asset?.location_id,
    serial_number: props.asset?.serial_number,
    purchase_date: props.asset?.purchase_date,
    purchase_cost: props.asset?.purchase_cost,
    warranty_expiry: props.asset?.warranty_expiry,
    description: props.asset?.description,
})

const submit = () => {
    form.put(route('assets.update', props.asset?.id))
}

</script>
<template>

    <Head :title="`Edit Asset - ${form.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex h-16 items-center justify-between border-b px-4 sm:px-6 lg:px-8 m-2">
                <h2 class="text-xl font-semibold tracking-tight">
                    Edit Asset
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
                                Update the details of the asset.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-3">
                                <Label for="name">Name</Label>
                                <Input id="name" v-model="form.name" type="text" />
                                <div v-if="form.errors.name" class="text-sm text-red-600">
                                    {{ form.errors.name }}
                                </div>
                            </div>
                            <div class="grid gap-3">
                                <Label for="serial_number">Serial Number</Label>
                                <Input id="serial_number" v-model="form.serial_number" type="text" />
                                <div v-if="form.errors.serial_number" class="text-sm text-red-600">
                                    {{ form.errors.serial_number }}
                                </div>
                            </div>
                            <div class="grid gap-3">
                                <Label for="description">Description</Label>
                                <Textarea id="description" v-model="form.description" />
                                <div v-if="form.errors.description" class="text-sm text-red-600">
                                    {{ form.errors.description }}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Purchase Information</CardTitle>
                        </CardHeader>
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
                                <Input id="purchase_cost" v-model="form.purchase_cost" type="number" step="0.01" />
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
                    </Card>
                </div>

                <div class="grid auto-rows-max items-start gap-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Organization</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-3">
                                <Label for="category">Category</Label>
                                <Select v-model="form.category_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a category" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="item in dropdowns?.categories" :key="item.id"
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
                                        <SelectItem v-for="item in dropdowns?.statuses" :key="item.id" :value="item.id">
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
                                        <SelectItem v-for="item in dropdowns?.locations" :key="item.id"
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
                    <div class="flex items-center justify-end gap-2">
                        <Link :href="route('assets.index')">
                        <Button variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            Save Changes
                        </Button>
                    </div>
                </div>
            </div>
        </form>

    </AppLayout>
</template>