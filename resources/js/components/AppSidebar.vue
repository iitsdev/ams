<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    LayoutGrid,
    Building2,
    Settings,
    MapPin,
    Tag,
    ListCheck,
    User2,
    Package,
    Truck,
    Building,
    ClipboardList,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
        icon: LayoutGrid,
    },
    {
        title: 'Assets',
        href: route('assets.index'),
        icon: Building2,
    },
    {
        title: 'Audits',
        href: route('audits.index'),
        icon: ClipboardList,
    },
    {
        title: 'Settings',
        icon: Settings,
        children: [
            {
                title: 'Locations',
                href: route('locations.index'),
                icon: MapPin,
            },
            {
                title: 'Categories',
                href: route('categories.index'),
                icon: Tag,
            },
            {
                title: 'Statuses',
                href: route('asset-statuses.index'),
                icon: ListCheck,
            },
            {
                title: 'Brands',
                href: route('brands.index'),
                icon: Package,
            },
            {
                title: 'Suppliers',
                href: route('suppliers.index'),
                icon: Truck,
            },
            {
                title: 'Departments',
                href: route('departments.index'),
                icon: Building,
            },
            {
                title: 'Users',
                href: route('users.index'),
                icon: User2,
            }
        ],
    },
];

const footerNavItems: NavItem[] = [
    // {
    //     title: 'Github Repo',
    //     href: 'https://github.com/laravel/vue-starter-kit',
    //     icon: Folder,
    // },
    // {
    //     title: 'Documentation',
    //     href: 'https://laravel.com/docs/starter-kits#vue',
    //     icon: BookOpen,
    // },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="floating" id="primary-navigation" aria-label="Primary navigation">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
