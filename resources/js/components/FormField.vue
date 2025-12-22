<script setup lang="ts">
import { computed } from 'vue'
import { Label } from '@/components/ui/label'

const props = withDefaults(defineProps<{
    label?: string
    for?: string
    hint?: string
    error?: string | string[] | null
    required?: boolean
    class?: string
}>(), {
    label: undefined,
    for: undefined,
    hint: undefined,
    error: null,
    required: false,
    class: '',
})

const errorText = computed(() => {
    if (!props.error) return null
    if (Array.isArray(props.error)) return props.error[0] || null
    return props.error
})
</script>

<template>
    <div :class="['space-y-1', props.class]">
        <Label v-if="label" :for="props.for">
            {{ label }}
            <span v-if="required" class="text-destructive">*</span>
        </Label>
        <div>
            <slot />
        </div>
        <p v-if="hint && !errorText" class="text-xs text-muted-foreground">{{ hint }}</p>
        <p v-if="errorText" class="text-xs text-destructive">{{ errorText }}</p>
    </div>
</template>
