<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Trash2, Edit, Save, XCircle, Search } from 'lucide-vue-next';
import { BreadcrumbItem } from '@/types';
import { ScrollArea } from '@/components/ui/scroll-area';
import PageHeader from '@/components/PageHeader.vue'
import FormField from '@/components/FormField.vue'
import EmptyState from '@/components/EmptyState.vue'
import SkeletonBlock from '@/components/SkeletonBlock.vue'
import { debounce } from 'lodash'
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
    filters: Object,
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
const isLoading = ref(false)
const search = ref('')
if ((props as any)?.filters?.search) {
    search.value = (props as any).filters.search
}

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

function onStart() { isLoading.value = true }
function onFinish() { isLoading.value = false }

onMounted(() => {
    window.addEventListener('inertia:start', onStart)
    window.addEventListener('inertia:finish', onFinish)
})

onUnmounted(() => {
    window.removeEventListener('inertia:start', onStart)
    window.removeEventListener('inertia:finish', onFinish)
})

watch(search, debounce((value: string) => {
    router.get(route('locations.index'), { search: value }, {
        preserveState: true,
        replace: true,
        onStart: () => { isLoading.value = true },
        onFinish: () => { isLoading.value = false },
    })
}, 300))

</script>

<template>

    <Head title="Manage Locations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="m-2">
                <PageHeader title="Manage Locations" />
            </div>
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
                                <FormField label="Location Name" for="name" :error="form.errors.name">
                                    <Input id="name" v-model="form.name" type="text"
                                        placeholder="e.g., Main Office - 3rd Floor" />
                                </FormField>
                                <Button type="submit" :disabled="form.processing">
                                    Save Location
                                </Button>
                            </CardContent>
                        </Card>
                    </form>
                </div>

                <div class="md:col-span-2">
                    <Card>
                        <CardHeader class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                            <CardTitle>Existing Locations</CardTitle>
                            <div class="flex items-center gap-2 w-full md:w-72">
                                <Search class="h-4 w-4 text-muted-foreground" aria-hidden="true" />
                                <Input v-model="search" type="search" placeholder="Search locations..."
                                    aria-label="Search locations" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <template v-if="isLoading">
                                <ul class="space-y-2 pr-4">
                                    <li v-for="n in 6" :key="n" class="flex items-center justify-between border-b pb-2">
                                        <SkeletonBlock class="h-5 w-1/3" />
                                        <div class="flex items-center gap-2">
                                            <SkeletonBlock class="h-8 w-8 rounded" />
                                            <SkeletonBlock class="h-8 w-8 rounded" />
                                        </div>
                                    </li>
                                </ul>
                            </template>
                            <template v-else>
                                <ScrollArea class="h-72" type="auto">
                                    <ul class="space-y-2 pr-4">
                                        <li v-for="location in props.locations?.data" :key="location.id"
                                            class="flex items-center justify-between border-b pb-2">
                                            <div v-if="editingLocationId === location.id" class="flex-1 mr-2">
                                                <FormField label="Location Name" for="edit-name"
                                                    :error="editForm.errors.name">
                                                    <Input id="edit-name" v-model="editForm.name" type="text"
                                                        @keyup.enter="updateLocation(location.id)"
                                                        @keyup.esc="cancelEdit" />
                                                </FormField>
                                            </div>
                                            <span v-else>{{ location.name }}</span>

                                            <div class="flex items-center">
                                                <template v-if="editingLocationId === location.id">
                                                    <Button variant="ghost" size="icon"
                                                        @click="updateLocation(location.id)">
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
                                                    <Button variant="ghost" size="icon"
                                                        @click="confirmDeletion(location)">
                                                        <Trash2 class="h-4 w-4 text-muted-foreground" />
                                                    </Button>
                                                </template>
                                            </div>
                                        </li>
                                        <li v-if="locations?.data.length === 0">
                                            <EmptyState title="No locations found"
                                                description="Create your first location to organize assets.">
                                                <template #actions>
                                                    <Button @click="() => { form.name = ''; }">Add Location</Button>
                                                </template>
                                            </EmptyState>
                                        </li>
                                    </ul>
                                </ScrollArea>
                            </template>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
    <AlertDialog :open="isDialogOpen">
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