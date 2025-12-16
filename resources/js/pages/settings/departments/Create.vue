<script setup lang="ts">
// filepath: c:\Users\daveg\Desktop\Projects\ams\resources\js\pages\settings\departments\Create.vue

import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import InputError from '@/components/InputError.vue'

const form = useForm({
    name: '',
    description: '',
})

const submit = () => {
    form.post(route('departments.store'))
}
</script>

<template>
    <AppLayout>
        <div class="max-w-2xl space-y-6">
            <Heading>Create Department</Heading>

            <Card>
                <CardHeader>
                    <CardTitle>Department Information</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid gap-3">
                            <Label for="name">Department Name *</Label>
                            <Input id="name" v-model="form.name" type="text" required />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-3">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" rows="3" />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="flex gap-3">
                            <Button type="submit" :disabled="form.processing">
                                Create Department
                            </Button>
                            <Button type="button" variant="outline" @click="$inertia.visit(route('departments.index'))">
                                Cancel
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>