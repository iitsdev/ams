<script setup lang="ts">
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
    contact_person: '',
    email: '',
    phone: '',
    address: '',
    website: '',
})

const submit = () => {
    form.post(route('suppliers.store'))
}
</script>

<template>
    <AppLayout>
        <div class="max-w-2xl space-y-6">
            <Heading>Create Supplier</Heading>

            <Card>
                <CardHeader>
                    <CardTitle>Supplier Information</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid gap-3">
                            <Label for="name">Supplier Name *</Label>
                            <Input id="name" v-model="form.name" type="text" required />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-3">
                            <Label for="contact_person">Contact Person</Label>
                            <Input id="contact_person" v-model="form.contact_person" type="text" />
                            <InputError :message="form.errors.contact_person" />
                        </div>

                        <div class="grid gap-3">
                            <Label for="email">Email</Label>
                            <Input id="email" v-model="form.email" type="email" />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-3">
                            <Label for="phone">Phone</Label>
                            <Input id="phone" v-model="form.phone" type="text" />
                            <InputError :message="form.errors.phone" />
                        </div>

                        <div class="grid gap-3">
                            <Label for="website">Website</Label>
                            <Input id="website" v-model="form.website" type="url" placeholder="https://" />
                            <InputError :message="form.errors.website" />
                        </div>

                        <div class="grid gap-3">
                            <Label for="address">Address</Label>
                            <Textarea id="address" v-model="form.address" rows="3" />
                            <InputError :message="form.errors.address" />
                        </div>

                        <div class="flex gap-3">
                            <Button type="submit" :disabled="form.processing">
                                Create Supplier
                            </Button>
                            <Button type="button" variant="outline" @click="$inertia.visit(route('suppliers.index'))">
                                Cancel
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>