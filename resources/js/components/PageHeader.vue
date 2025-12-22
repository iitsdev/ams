<script setup lang="ts">
import { computed } from 'vue'
import Button from '@/components/ui/button/Button.vue'

const props = withDefaults(defineProps<{
    title: string
    subtitle?: string | null
    dense?: boolean
}>(), {
    subtitle: null,
    dense: false,
})

const wrapperClasses = computed(() => props.dense ? 'py-2' : 'py-4')
</script>

<template>
    <div :class="['flex items-center justify-between', wrapperClasses]">
        <div>
            <h2 class="text-2xl font-bold tracking-tight">{{ title }}</h2>
            <p v-if="subtitle" class="mt-1 text-sm text-muted-foreground">{{ subtitle }}</p>
        </div>
        <div class="flex items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
