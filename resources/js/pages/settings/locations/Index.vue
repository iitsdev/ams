<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Trash2, Edit, Save, XCircle } from 'lucide-vue-next';
import { BreadcrumbItem } from '@/types';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogHeader,
    AlertDialogFooter,
    AlertDialogTitle,

} from '@/components/ui/alert-dialog';

const props = defineProps({
    locations: Object,
});




const form = useForm({
    name: '',
});

const submit = () => {
    form.post(route('locations.store'), {
        onSuccess: () => form.reset('name'),
    });
};


// --Edit in-place logic--
const editingLocationId = ref<number | null>(null);
const editForm = useForm({
    name: '',
});

const editLocation = (location: any) => {
    editingLocationId.value = location.id;
    editForm.name = location.name;
};

const cancelEdit = () => {
    editingLocationId.value = null;
    editForm.reset('name');
};

const updateLocation = (locationId: number) => {
    editForm.put(route('locations.update', locationId), {
        preserveScroll: true,
        onSuccess: () => cancelEdit(),
    });
}

// --Delete location logic--

const isDialogOpen = ref(false);
const locationToDelete = ref(null);

const confirmDeletion = (location: any) => {
    isDialogOpen.value = true
    locationToDelete.value = location
};


const deleteLocation = () => {
    if (locationToDelete.value) {
        router.delete(route('locations.destroy', locationToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isDialogOpen.value = false
            }

        })
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '#' },
    { title: 'Location', href: route('locations.index') },
];

</script>

<template>

    <Head title="Manage Locations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <h2 class="text-xl font-bold tracking-tight m-2">
                Manage Locations
            </h2>
        </template>

        <div class="p-1">
            <div class="grid gap-6 md:grid-cols-3">
                <div class="md:col-span-1">
                    <form @submit.prevent="submit">
                        <Card>
                            <CardHeader>
                                <CardTitle>Add New Location</CardTitle>
                                <CardDescription>
                                    Create a new location to assign assets to.
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="name">Location Name</Label>
                                    <Input id="name" v-model="form.name" type="text"
                                        placeholder="e.g., Main Office - 3rd Floor" />
                                    <div v-if="form.errors.name" class="text-sm text-red-600">
                                        {{ form.errors.name }}
                                    </div>
                                </div>
                                <Button type="submit" :disabled="form.processing">
                                    Save Location
                                </Button>
                            </CardContent>
                        </Card>
                    </form>
                </div>

                <div class="md:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Existing Locations</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ul class="space-y-2">
                                <li v-for="location in props.locations?.data" :key="location.id"
                                    class="flex items-center justify-between border-b pb-2">
                                    <div v-if="editingLocationId === location.id" class="flex-1 mr-2">
                                        <Input v-model="editForm.name" type="text"
                                            @keyup.enter="updateLocation(location.id)" @keyup.esc="cancelEdit" />
                                        <div v-if="editForm.errors.name" class="text-sm text-red-600 mt-1">
                                            {{ editForm.errors.name }}
                                        </div>
                                    </div>
                                    <span v-else>{{ location.name }}</span>

                                    <div class="flex items-center">
                                        <template v-if="editingLocationId === location.id">
                                            <Button variant="ghost" size="icon" @click="updateLocation(location.id)">
                                                <Save class="h-4 w-4 text-dark-600" />
                                            </Button>
                                            <Button variant="ghost" size="icon" @click="cancelEdit">
                                                <XCircle class="h-4 w-4 text-muted-foreground" />
                                            </Button>
                                        </template>
                                        <template v-else>
                                            <Button variant="ghost" size="icon" @click="editLocation(location)">
                                                <Edit class="h-4 w-4 text-muted-foreground" />
                                            </Button>
                                            <Button variant="ghost" size="icon" @click="confirmDeletion(location)">
                                                <Trash2 class="h-4 w-4 text-muted-foreground" />
                                            </Button>
                                        </template>
                                    </div>
                                </li>
                                <li v-if="locations?.data.length === 0" class="text-muted-foreground">
                                    No locations found.
                                    _</li>
                            </ul>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
    <AlertDialog :open="isDialogOpen" @update:open="(value) => isDialogOpen = value">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                <AlertDialogDescription>
                    This action cannot be undone. This will delete this location permanently.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction as-child>
                    <Button variant="destructive" @click="deleteLocation">Continue</Button>
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>