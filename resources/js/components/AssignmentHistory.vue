<script setup lang="ts">
// filepath: c:\Users\daveg\Desktop\Projects\ams\resources\js\components\AssignmentHistory.vue

import { computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
// Rename Calendar to CalendarIcon to avoid confusion
import { Download, FileText, Calendar as CalendarIcon, User, Clock } from 'lucide-vue-next'
import { formatDistanceToNow, format } from 'date-fns'

interface Assignment {
    id: number
    user: {
        id: number
        name: string
        email: string
        department?: {
            id: number
            name: string
        }
    }
    assigned_by: {
        id: number
        name: string
    }
    assigned_at: string
    returned_at: string | null
    notes: string | null
    document_path: string | null
    duration_days: number
    is_current: boolean
}

interface Props {
    assignments: Assignment[]
}

const props = defineProps<Props>()

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2)
}

const formatDate = (date: string) => {
    return format(new Date(date), 'MMM dd, yyyy')
}

const formatDateTime = (date: string) => {
    return format(new Date(date), 'MMM dd, yyyy hh:mm a')
}

const getRelativeTime = (date: string) => {
    return formatDistanceToNow(new Date(date), { addSuffix: true })
}

const getDurationText = (days: number) => {
    if (days < 1) return 'Less than a day'
    if (days === 1) return '1 day'
    if (days < 30) return `${days} days`
    if (days < 365) {
        const months = Math.floor(days / 30)
        return `${months} ${months === 1 ? 'month' : 'months'}`
    }
    const years = Math.floor(days / 365)
    const remainingMonths = Math.floor((days % 365) / 30)
    return `${years} ${years === 1 ? 'year' : 'years'}${remainingMonths > 0 ? ` ${remainingMonths} ${remainingMonths === 1 ? 'month' : 'months'}` : ''}`
}

const hasAssignments = computed(() => props.assignments && props.assignments.length > 0)
</script>

<template>
    <Card>
        <CardHeader>
            <div class="flex items-center justify-between">
                <CardTitle class="flex items-center gap-2">
                    <Clock class="h-5 w-5" />
                    Assignment History
                </CardTitle>
                <Badge variant="secondary">
                    {{ assignments?.length || 0 }} {{ assignments?.length === 1 ? 'Assignment' : 'Assignments' }}
                </Badge>
            </div>
        </CardHeader>
        <CardContent>
            <div v-if="!hasAssignments" class="text-center py-8 text-muted-foreground">
                <User class="h-12 w-12 mx-auto mb-3 opacity-50" />
                <p>No assignment history available</p>
                <p class="text-sm">This asset has never been assigned to anyone</p>
            </div>

            <!-- Make this scrollable with max height -->
            <div v-else class="space-y-4 max-h-[500px] overflow-y-auto pr-2">
                <div v-for="assignment in assignments" :key="assignment.id"
                    class="relative border rounded-lg p-4 hover:bg-accent/50 transition-colors"
                    :class="{ 'border-primary bg-primary/5': assignment.is_current }">
                    <!-- Current Badge -->
                    <Badge v-if="assignment.is_current" variant="default" class="absolute top-2 right-2">
                        Current
                    </Badge>

                    <div class="flex items-start gap-4">
                        <!-- User Avatar -->
                        <Avatar class="h-12 w-12">
                            <AvatarImage :src="assignment.user.avatar_url" :alt="assignment.user.name" />
                            <AvatarFallback>{{ getInitials(assignment.user.name) }}</AvatarFallback>
                        </Avatar>

                        <!-- Assignment Details -->
                        <div class="flex-1 space-y-3">
                            <!-- User Info -->
                            <div>
                                <h4 class="font-semibold">{{ assignment.user.name }}</h4>
                                <p class="text-sm text-muted-foreground">{{ assignment.user.email }}</p>
                                <Badge v-if="assignment.user.department" variant="outline" class="mt-1">
                                    {{ assignment.user.department.name }}
                                </Badge>
                            </div>

                            <!-- Timeline -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                <!-- Assigned Date -->
                                <div class="flex items-center gap-2">
                                    <CalendarIcon class="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <p class="text-muted-foreground">Assigned</p>
                                        <p class="font-medium">{{ formatDate(assignment.assigned_at) }}</p>
                                        <p class="text-xs text-muted-foreground">{{
                                            getRelativeTime(assignment.assigned_at) }}</p>
                                    </div>
                                </div>

                                <!-- Returned Date or Duration -->
                                <div v-if="assignment.returned_at" class="flex items-center gap-2">
                                    <CalendarIcon class="h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <p class="text-muted-foreground">Returned</p>
                                        <p class="font-medium">{{ formatDate(assignment.returned_at) }}</p>
                                        <p class="text-xs text-muted-foreground">{{
                                            getRelativeTime(assignment.returned_at) }}</p>
                                    </div>
                                </div>
                                <div v-else class="flex items-center gap-2">
                                    <Clock class="h-4 w-4 text-green-600" />
                                    <div>
                                        <p class="text-muted-foreground">In Use For</p>
                                        <p class="font-medium text-green-600">{{
                                            getDurationText(assignment.duration_days) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Duration Badge -->
                            <div class="flex items-center gap-2">
                                <Badge variant="secondary" class="flex items-center gap-1">
                                    <Clock class="h-3 w-3" />
                                    Duration: {{ getDurationText(assignment.duration_days) }}
                                </Badge>
                            </div>

                            <!-- Assigned By -->
                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <User class="h-4 w-4" />
                                <span>Assigned by {{ assignment.assigned_by.name }}</span>
                            </div>

                            <!-- Notes -->
                            <div v-if="assignment.notes" class="bg-muted/50 rounded-md p-3">
                                <p class="text-sm font-medium mb-1">Notes:</p>
                                <p class="text-sm text-muted-foreground">{{ assignment.notes }}</p>
                            </div>

                            <!-- Document -->
                            <div v-if="assignment.document_path">
                                <Button variant="outline" size="sm" as="a"
                                    :href="`/storage/${assignment.document_path}`" target="_blank" class="gap-2">
                                    <FileText class="h-4 w-4" />
                                    View Document
                                    <Download class="h-3 w-3" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>