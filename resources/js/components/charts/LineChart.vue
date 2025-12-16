<script setup lang="ts">
// filepath: c:\Users\daveg\Desktop\Projects\ams\resources\js/components/charts/LineChart.vue

import { ref, onMounted, watch } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

interface Props {
    data: {
        labels: string[]
        datasets: {
            label: string
            data: number[]
            borderColor?: string
            backgroundColor?: string
            tension?: number
            fill?: boolean
        }[]
    }
    options?: any
}

const props = defineProps<Props>()
const chartRef = ref<HTMLCanvasElement | null>(null)
let chartInstance: Chart | null = null

const createChart = () => {
    if (!chartRef.value) return

    if (chartInstance) {
        chartInstance.destroy()
    }

    const ctx = chartRef.value.getContext('2d')
    if (!ctx) return

    chartInstance = new Chart(ctx, {
        type: 'line',
        data: props.data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                },
            },
            ...props.options,
        },
    })
}

onMounted(() => {
    createChart()
})

watch(() => props.data, () => {
    createChart()
}, { deep: true })
</script>

<template>
    <canvas ref="chartRef"></canvas>
</template>