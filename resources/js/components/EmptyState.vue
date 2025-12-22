<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue'

const props = withDefaults(defineProps<{
    title: string
    description?: string
    icon?: any
    actionText?: string
}>(), {
    description: undefined,
    icon: undefined,
    actionText: undefined,
})
</script>

<template>
    <div class="flex flex-col items-center justify-center text-center p-8 border rounded-lg bg-card">
        <component v-if="icon" :is="icon" class="h-10 w-10 text-muted-foreground mb-2" />
        <h3 class="text-lg font-medium">{{ title }}</h3>
        <p v-if="description" class="mt-1 text-sm text-muted-foreground max-w-prose">{{ description }}</p>
        <div class="mt-4">
            <slot name="actions">
                <Button v-if="actionText">{{ actionText }}</Button>
            </slot>
        </div>
    </div>
</template>
