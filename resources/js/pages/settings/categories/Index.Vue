<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Trash2, Edit, Save, XCircle } from 'lucide-vue-next';
import { BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle
} from '@/components/ui/alert-dialog';

const props = defineProps({
    categories: Object,
});

const addForm = useForm({
    name: '',
});

const submit = () => {
    addForm.post(route('categories.store'), {
        onSuccess: () => addForm.reset('name'),
    });
};

const editingCategoryId = ref<number | null>(null);
const editForm = useForm({
    name: '',
});

const editCategory = (category: any) => {
    editingCategoryId.value = category.id;
    editForm.name = category.name;
};

const cancelEdit = () => {
    editingCategoryId.value = null;
    editForm.reset('name');
};

const updateCategory = (categoryId: number) => {
    editForm.put(route('categories.update', categoryId), {
        preserveScroll: true,
        onSuccess: () => cancelEdit(),
    });
};


const isDiaglogOpen = ref(false);
const categoryToDelete = ref(null);

const confirmDeletion = (category: any) => {
    isDiaglogOpen.value = true;
    categoryToDelete.value = category
};

const deleteCategory = () => {
    if (categoryToDelete.value) {
        router.delete(route('categories.destroy', categoryToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isDiaglogOpen.value = false;
            }
        })
    }
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '#' },
    { title: 'Category', href: route('categories.index') }
];
</script>
<template>

    <Head title="Manage Categories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <h2 class="text-xl font-bold tracking-tight m-2">
                Manage Categories
            </h2>
        </template>
        <div class="p-1">
            <div class="grid gap-6 md:grid-cols-3">
                <div class="md:col-span-1">
                    <form @submit.prevent="submit">
                        <Card>
                            <CardHeader>
                                <CardTitle>Add New Categories</CardTitle>
                                <CardDescription>Create a new category for the assets.</CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="name">Category Name</Label>
                                    <Input id="name" v-model="addForm.name" type="text" placeholder="e.g., Laptop" />
                                    <div v-if="addForm.errors.name" class="text-sm text-red-600">
                                        {{ addForm.errors.name }}
                                    </div>
                                </div>
                                <Button type="submit" :disabled="addForm.processing">
                                    Save Category
                                </Button>
                            </CardContent>
                        </Card>
                    </form>
                </div>
                <div class="md:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Existing Categories</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ScrollArea class="h-72" type="auto">
                                <ul class="space-y-2 pr-4">
                                    <li v-for="category in props.categories?.data" :key="category.id"
                                        class="flex items-center justify-between border-b pb-2">
                                        <div v-if="editingCategoryId === category.id" class="flex-1 mr-2">
                                            <Input v-model="editForm.name" text="text" />
                                            <div class="text-sm text-red-600 mt-1">
                                            </div>
                                        </div>
                                        <span v-else>{{ category.name }}</span>
                                        <div class="flex items-center">
                                            <template v-if="editingCategoryId === category.id">
                                                <Button variant="ghost" size="icon"
                                                    @click="updateCategory(category.id)">
                                                    <Save class="h-4 text-dark-600" />
                                                </Button>
                                                <Button variant="ghost" size="icon" @click="cancelEdit">
                                                    <XCircle class="h-4 w-4 text-muted-foreground" />
                                                </Button>
                                            </template>
                                            <template v-else>
                                                <Button variant="ghost" size="icon" @click="editCategory(category)">
                                                    <Edit class="h-4 w-4 text-muted-foreground" />
                                                </Button>
                                                <Button variant="ghost" size="icon" @click="confirmDeletion(category)">
                                                    <Trash2 class="h-4 w-4 text-muted-foreground" />
                                                </Button>
                                            </template>
                                        </div>
                                    </li>
                                    <li v-if="props.categories?.data.length === 0" class="text-muted-foreground">
                                        No locations found.
                                    </li>
                                </ul>
                            </ScrollArea>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>

    <AlertDialog :open="isDiaglogOpen">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                <AlertDialogDescription>
                    This action cannot be undone. This will delete this category permanently.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction as-child>
                    <Button variant="destructive" @click="deleteCategory">Continue</Button>
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>