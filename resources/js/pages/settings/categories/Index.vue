<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Trash2, Edit, Save, XCircle, Search } from 'lucide-vue-next';
import { BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import PageHeader from '@/components/PageHeader.vue'
import FormField from '@/components/FormField.vue'
import EmptyState from '@/components/EmptyState.vue'
import SkeletonBlock from '@/components/SkeletonBlock.vue'
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
import { debounce } from 'lodash';

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
const isLoading = ref(false);
const search = ref('');

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
    router.get(route('categories.index'), { search: value }, {
        preserveState: true,
        replace: true,
        onStart: () => { isLoading.value = true },
        onFinish: () => { isLoading.value = false },
    })
}, 300))
</script>
<template>

    <Head title="Manage Categories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="m-2">
                <PageHeader title="Manage Categories" />
            </div>
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
                                <FormField label="Category Name" for="name" :error="addForm.errors.name">
                                    <Input id="name" v-model="addForm.name" type="text" placeholder="e.g., Laptop" />
                                </FormField>
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
                            <div class="flex items-center justify-between gap-3">
                                <CardTitle>Existing Categories</CardTitle>
                                <div class="relative w-full max-w-xs">
                                    <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                    <Input v-model="search" placeholder="Search categories..." class="pl-10" />
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <ScrollArea class="h-72" type="auto">
                                <ul class="space-y-2 pr-4">
                                    <template v-if="isLoading">
                                        <li v-for="n in 6" :key="`sk-${n}`"
                                            class="flex items-center justify-between border-b pb-2">
                                            <div class="flex-1 mr-2">
                                                <SkeletonBlock class="h-4 w-48" />
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <SkeletonBlock class="h-8 w-8 rounded" />
                                                <SkeletonBlock class="h-8 w-8 rounded" />
                                            </div>
                                        </li>
                                    </template>
                                    <template v-else>
                                        <li v-for="category in props.categories?.data" :key="category.id"
                                            class="flex items-center justify-between border-b pb-2">
                                            <div v-if="editingCategoryId === category.id" class="flex-1 mr-2">
                                                <FormField label="Category Name" for="edit-name"
                                                    :error="editForm.errors.name">
                                                    <Input id="edit-name" v-model="editForm.name" type="text"
                                                        @keyup.enter="updateCategory(category.id)"
                                                        @keyup.esc="cancelEdit" />
                                                </FormField>
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
                                                    <Button variant="ghost" size="icon"
                                                        @click="confirmDeletion(category)">
                                                        <Trash2 class="h-4 w-4 text-muted-foreground" />
                                                    </Button>
                                                </template>
                                            </div>
                                        </li>
                                        <li v-if="props.categories?.data.length === 0">
                                            <EmptyState title="No categories found"
                                                description="Create your first category to classify assets." />
                                        </li>
                                    </template>
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