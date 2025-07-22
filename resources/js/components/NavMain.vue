<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { ChevronDownIcon } from 'lucide-vue-next';

defineProps<{
    items: NavItem[];
}>();

function isCurrent(item: NavItem): boolean {
    if (item.children) {
        return item.children.some(child => route().current(child.href as any));
    }

    if (item.href) {
        return route().current(item.href as any);
    }

    return false;
}

// const page = usePage();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <!-- <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
                    <Link :href="item.href">
                    <component :is="item.icon" />
                    <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem> -->
            <template v-for="item in items" :key="item.title">
                <Collapsible v-if="item.children && item.children.length > 0">
                    <CollapsibleTrigger as-child>
                        <SidebarMenuButton :is-active="isCurrent(item)"
                            class="flex w-full items-center justify-between">
                            <div class="flex items-center gap-2">
                                <component :is="item.icon" class="h-4 w-4" />
                                <span>{{ item.title }}</span>
                            </div>
                            <ChevronDownIcon class="h-4 w-4 shrink-0 transition-transform duration-200" />
                        </SidebarMenuButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent class="ml-1">
                        <SidebarMenuItem v-for="child in item.children" :key="child.title" class="!px-2 !py-1">
                            <SidebarMenuButton as-child :is-active="isCurrent(child)">
                                <Link :href="child.href!">
                                <component :is="child.icon" />
                                <span>{{ child.title }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </CollapsibleContent>
                </Collapsible>

                <SidebarMenuItem v-else>
                    <SidebarMenuButton as-child :is-active="isCurrent(item)">
                        <Link :href="item.href!">
                        <component :is="item.icon" class="h-4 w-4" />
                        <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
