<script setup lang="ts">
// filepath: c:\Users\daveg\Desktop\Projects\ams\resources\js/components/charts/DoughnutChart.vue

import { ref, onMounted, watch } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

interface Props {
    data: {
        labels: string[]
        datasets: {
            data: number[]
            backgroundColor?: string[]
            borderColor?: string[]
            borderWidth?: number
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
        type: 'doughnut',
        data: props.data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
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