<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'

defineProps({
    links: Array,
    from: Number,
    to: Number,
    total: Number,
})
</script>

<template>
    <div v-if="links.length > 3" class="flex items-center justify-between w-full pt-4 text-sm">
        <div v-if="from && to && total" class="text-muted-foreground">
            Showing <strong>{{ from }}</strong> to <strong> {{ to }} </strong> of <strong> {{ total }} </strong>
            results.
        </div>
        <div v-else></div>

        <div class="flex items-center space-x-1">
            <template v-for="(link, key) in links" :key="key">
                <Button v-if="link.url" as-child :variant="link.active ? 'outline' : 'ghost'" size="sm">
                    <Link :href="link.url" v-html="link.label" />
                </Button>
                <Button v-else variant="ghost" size="sm" disabled v-html="link.label">
                </Button>
            </template>
        </div>
    </div>
</template>