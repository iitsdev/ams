<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {Input} from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { BrowserMultiFormatReader } from '@zxing/browser';
import axios from 'axios';
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    audit: {
        id: number;
        status: string;
        location?: { id: number; name: string } | null;
        created_at: string;
        closed_at?: string | null;
        notes?: string | null;
    };
    entries: Array<any>;
    locations: Array<{ id: number; name: string }>;
}>();

const scanCode = ref('');
const scanInputRef = ref<HTMLInputElement | null>(null);
const foundLocationId = ref<number | null>(props.audit.location?.id ?? null);
const scanning = ref(false);
const list = ref(props.entries);

// Camera scanning state
const cameraEnabled = ref(false);
const videoRef = ref<HTMLVideoElement | null>(null);
let mediaStream: MediaStream | null = null;
let rafId: number | null = null;
let scanningCooldown = false;
let zxingReader: BrowserMultiFormatReader | null = null;
const usingZXing = ref(false);

// Device selection state
const devices = ref<MediaDeviceInfo[]>([]);
const selectedDeviceId = ref<string | null>(null);
const loadingDevices = ref(false);

function hasBarcodeDetector() {
    return typeof (window as any).BarcodeDetector !== 'undefined';
}

async function getVideoInputs(): Promise<MediaDeviceInfo[]> {
    try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        return devices.filter((d) => d.kind === 'videoinput');
    } catch {
        return [];
    }
}

async function getPreferredVideoDeviceId(): Promise<string | null> {
    const inputs = await getVideoInputs();
    if (!inputs.length) return null;
    const byLabel = inputs.find((d) => /back|rear|environment/i.test(d.label));
    if (byLabel) return byLabel.deviceId;
    return inputs[inputs.length - 1]?.deviceId || inputs[0].deviceId;
}

async function refreshDevices() {
    loadingDevices.value = true;
    const inputs = await getVideoInputs();
    devices.value = inputs;
    if (!selectedDeviceId.value) {
        selectedDeviceId.value = await getPreferredVideoDeviceId();
    }
    loadingDevices.value = false;
}

onMounted(async () => {
    await refreshDevices();
    try {
        const saved = localStorage.getItem('audit.cameraDeviceId');
        if (saved) selectedDeviceId.value = saved;
    } catch {}
    await nextTick();
    scanInputRef.value?.focus();
});

async function startCamera() {
    if (hasBarcodeDetector()) {
        try {
            const effectiveId = selectedDeviceId.value || (await getPreferredVideoDeviceId());
            const video: any = effectiveId ? { deviceId: { exact: effectiveId } } : { facingMode: 'environment' };
            mediaStream = await navigator.mediaDevices.getUserMedia({ video, audio: false });
            if (videoRef.value) {
                videoRef.value.srcObject = mediaStream;
                await videoRef.value.play();
            }
            cameraEnabled.value = true;
            startDetectionLoop();
            // Update device labels after permission is granted
            await refreshDevices();
            return;
        } catch (e) {
            // fall through to ZXing
        }
    }
    await startZXingCamera();
}

function stopCamera() {
    cameraEnabled.value = false;
    if (rafId) cancelAnimationFrame(rafId);
    rafId = null;
    if (videoRef.value) {
        try {
            videoRef.value.pause();
        } catch {}
        videoRef.value.srcObject = null;
    }
    if (mediaStream) {
        mediaStream.getTracks().forEach((t) => t.stop());
        mediaStream = null;
    }
    if (zxingReader) {
        try {
            zxingReader.reset();
        } catch {}
        zxingReader = null;
    }
    usingZXing.value = false;
}

function startDetectionLoop() {
    const BD: any = (window as any).BarcodeDetector;
    const detector = new BD({ formats: ['qr_code', 'code_128', 'code_39', 'ean_13', 'ean_8', 'upc_a', 'upc_e'] });

    const detect = async () => {
        if (!cameraEnabled.value || !videoRef.value) return;
        try {
            const barcodes = await detector.detect(videoRef.value);
            if (barcodes && barcodes.length && !scanningCooldown) {
                scanningCooldown = true;
                const raw = barcodes[0].rawValue || '';
                if (raw) {
                    scanCode.value = raw;
                    await scan();
                }
                setTimeout(() => {
                    scanningCooldown = false;
                }, 800);
            }
        } catch {
            // ignore per-frame errors
        } finally {
            rafId = requestAnimationFrame(detect);
        }
    };
    rafId = requestAnimationFrame(detect);
}

onBeforeUnmount(() => {
    stopCamera();
});

async function startZXingCamera() {
    try {
        if (!videoRef.value) return;
        zxingReader = new BrowserMultiFormatReader();
        usingZXing.value = true;
        const effectiveId = selectedDeviceId.value || (await getPreferredVideoDeviceId());
        await zxingReader.decodeFromVideoDevice(effectiveId ?? null, videoRef.value, async (result, err, controls) => {
            if (result && !scanningCooldown) {
                scanningCooldown = true;
                const text = result.getText();
                if (text) {
                    scanCode.value = text;
                    await scan();
                }
                setTimeout(() => {
                    scanningCooldown = false;
                }, 800);
            }
        });
        cameraEnabled.value = true;
        // Update device labels after permission is granted
        await refreshDevices();
    } catch (e) {
        usingZXing.value = false;
        alert('Could not start camera scanning. Please use manual input.');
    }
}

watch(selectedDeviceId, async (id, oldId) => {
    if (id === oldId) return;
    try {
        localStorage.setItem('audit.cameraDeviceId', id || '');
    } catch {}
    if (cameraEnabled.value) {
        // Restart camera with the newly selected device
        stopCamera();
        await startCamera();
    }
});

async function scan() {
    if (!scanCode.value.trim()) return;
    scanning.value = true;
    try {
        const { data } = await axios.post(route('audits.scan', props.audit.id), {
            code: scanCode.value.trim(),
            found_location_id: foundLocationId.value,
        });
        // Update or push entry
        const idx = list.value.findIndex((e: any) => e.asset.id === data.entry.asset_id || e.id === data.entry.id);
        const entry = {
            id: data.entry.id,
            asset: {
                id: data.entry.asset.id,
                name: data.entry.asset.name,
                asset_tag: data.entry.asset.asset_tag,
                category: data.entry.asset.category?.name,
                location: data.entry.asset.location?.name,
            },
            found_location: data.entry.found_location?.name || null,
            scanned_by: data.entry.scanned_by?.name || null,
            scanned_at: data.entry.scanned_at,
            notes: data.entry.notes,
        };
        if (idx >= 0) list.value[idx] = entry;
        else list.value.unshift(entry);
        scanCode.value = '';
        lastScannedId.value = entry.id;
        lastScannedTag.value = entry.asset.asset_tag;
        toast.success(`Scanned ${entry.asset.asset_tag}`);
        await nextTick();
        scanInputRef.value?.focus();
    } catch (e: any) {
        toast.error(e?.response?.data?.message || 'Scan failed');
    } finally {
        scanning.value = false;
    }
}

function closeAudit() {
    router.post(route('audits.close', props.audit.id));
}

async function fetchVariance() {
    try {
        const { data } = await axios.get(route('audits.variance', props.audit.id));
        variance.value = data;
        showVariance.value = true;
    } catch (e) {
        alert('Failed to compute variance');
    }
}

const showVariance = ref(false);
const variance = ref<{ missing: any[]; extra: any[]; moved: any[] } | null>(null);
const lastScannedId = ref<number | null>(null);
const lastScannedTag = ref<string | null>(null);

const torchOn = ref(false);
function canToggleTorch(): boolean {
    try {
        const track = mediaStream?.getVideoTracks()?.[0];
        // @ts-ignore
        return !!track && !!track.getCapabilities && !!track.getCapabilities().torch;
    } catch {
        return false;
    }
}
async function toggleTorch() {
    try {
        const track = mediaStream?.getVideoTracks()?.[0];
        if (!track) return;
        torchOn.value = !torchOn.value;
        // @ts-ignore
        await track.applyConstraints({ advanced: [{ torch: torchOn.value }] });
    } catch {
        toast.error('Torch not supported on this device');
        torchOn.value = false;
    }
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Audit', href: route('audits.index') },
    { title: `Audit #${props.audit.id}`, href: route('audits.show', props.audit.id) },
];
</script>

<template>
    <Head :title="`Audit #${props.audit.id}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Audit #{{ props.audit.id }}</h1>
                    <p class="text-sm text-gray-500">Status: {{ props.audit.status }} | Location: {{ props.audit.location?.name || 'All' }}</p>
                    <p v-if="lastScannedTag" class="mt-1 text-sm text-green-700">Last scanned: {{ lastScannedTag }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <select v-model="foundLocationId" class="rounded border px-2 py-1">
                        <option :value="null">Select found location</option>
                        <option v-for="loc in props.locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                    </select>
                    <Button v-if="props.audit.status === 'open'" variant="secondary" @click="fetchVariance">Compute Variance</Button>
                    <Button v-if="props.audit.status === 'open'" @click="closeAudit">Close Audit</Button>
                </div>
            </div>
        </template>
        <div class="space-y-6">
            <div class="rounded-md border p-4">
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-sm font-medium">Scan Code (asset tag or serial)</label>
                        <Input
                            ref="scanInputRef"
                            v-model="scanCode"
                            @keyup.enter="scan"
                            class="mt-1 w-full"
                            placeholder="Focus here and scan barcode"
                        />
                    </div>
                    <Button :disabled="scanning" @click="scan">Scan</Button>
                </div>
                <div class="mt-4 flex items-center gap-3" v-if="props.audit.status === 'open'">
                    <Button v-if="!cameraEnabled" variant="secondary" @click="startCamera">Enable Camera Scan</Button>
                    <Button v-else variant="outline" @click="stopCamera">Disable Camera</Button>
                    <div v-if="cameraEnabled" class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">Camera</label>
                        <select v-model="selectedDeviceId" class="rounded border px-2 py-1">
                            <option v-for="d in devices" :key="d.deviceId" :value="d.deviceId">{{ d.label || 'Camera' }}</option>
                        </select>
                    </div>
                    <Button v-if="cameraEnabled && canToggleTorch()" variant="outline" @click="toggleTorch">{{
                        torchOn ? 'Torch Off' : 'Torch On'
                    }}</Button>
                </div>
                <div v-if="cameraEnabled" class="mt-3">
                    <video ref="videoRef" class="w-full max-w-md rounded border" playsinline muted></video>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border px-3 py-2 text-left">Asset Tag</th>
                            <th class="border px-3 py-2 text-left">Name</th>
                            <th class="hidden border px-3 py-2 text-left md:table-cell">Category</th>
                            <th class="border px-3 py-2 text-left">Recorded Location</th>
                            <th class="border px-3 py-2 text-left">Found Location</th>
                            <th class="hidden border px-3 py-2 text-left md:table-cell">Scanned By</th>
                            <th class="border px-3 py-2 text-left">Scanned At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="e in list" :key="e.id" :class="['hover:bg-gray-50', lastScannedId === e.id ? 'bg-green-50' : '']">
                            <td class="border px-3 py-2">{{ e.asset.asset_tag }}</td>
                            <td class="border px-3 py-2">{{ e.asset.name }}</td>
                            <td class="hidden border px-3 py-2 md:table-cell">{{ e.asset.category || '—' }}</td>
                            <td class="border px-3 py-2">{{ e.asset.location || '—' }}</td>
                            <td class="border px-3 py-2">{{ e.found_location || '—' }}</td>
                            <td class="hidden border px-3 py-2 md:table-cell">{{ e.scanned_by || '—' }}</td>
                            <td class="border px-3 py-2">{{ e.scanned_at }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="showVariance && variance" class="space-y-4 rounded-md border p-4">
                <h2 class="text-lg font-semibold">Variance</h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <h3 class="mb-2 font-medium">Missing</h3>
                        <ul class="ml-5 list-disc space-y-1">
                            <li v-for="a in variance.missing" :key="a.id">{{ a.asset_tag }} — {{ a.name }} ({{ a.location?.name || '—' }})</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="mb-2 font-medium">Extra</h3>
                        <ul class="ml-5 list-disc space-y-1">
                            <li v-for="a in variance.extra" :key="a.id">{{ a.asset_tag }} — {{ a.name }} ({{ a.location?.name || '—' }})</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="mb-2 font-medium">Moved</h3>
                        <ul class="ml-5 list-disc space-y-1">
                            <li v-for="e in variance.moved" :key="e.id">
                                {{ e.asset.asset_tag }} — {{ e.asset.name }}: recorded {{ e.asset.location?.name || '—' }}, found
                                {{ e.found_location?.name || '—' }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
