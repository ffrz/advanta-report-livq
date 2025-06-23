<template>
  <q-dialog v-model="visible" persistent transition-show="scale" transition-hide="scale">
    <q-card class="bg-white" style="max-width: 95vw; max-height: 95vh; overflow: hidden;">
      <q-card-section class="q-pa-none">
        <div ref="container" class="relative-position overflow-hidden flex flex-center" style="touch-action: none;"
          @mousedown="startPan" @mousemove="onPan" @mouseup="endPan"
          :class="imageOrientation === 'portrait' ? 'viewer-portrait' : 'viewer-landscape'" @mouseleave="endPan"
          @touchstart="startTouchPan" @touchmove="onTouchPan" @touchend="endPan">
          <img ref="image" :src="imageUrl" :style="imgStyle" class="no-pointer-events" draggable="false"
            @load="resetView" />
        </div>
      </q-card-section>
      <q-card-actions align="between" class="bg-grey-1">
        <div>
          <q-btn flat round icon="zoom_out" @click="zoomOut" />
          <q-btn flat round icon="zoom_in" @click="zoomIn" />
          <q-btn flat round icon="restart_alt" @click="resetView" />
        </div>
        <q-btn label="Tutup" color="primary" flat @click="visible = false" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, watch, computed, defineExpose, nextTick } from 'vue';

const props = defineProps({
  imageUrl: { type: String, required: true },
  modelValue: { type: Boolean, required: true },
});
const emit = defineEmits(['update:modelValue']);

const visible = ref(props.modelValue);
const imageOrientation = ref('landscape');

watch(() => props.modelValue, val => visible.value = val);
watch(visible, val => emit('update:modelValue', val));

const scale = ref(1);
const offset = ref({ x: 0, y: 0 });
const start = ref({ x: 0, y: 0 });
const dragging = ref(false);

const image = ref(null);
const container = ref(null);

const imgStyle = computed(() => ({
  transform: `scale(${scale.value}) translate(${offset.value.x}px, ${offset.value.y}px)`,
  transition: dragging.value ? 'none' : 'transform 0.1s ease-out',
  'transform-origin': 'center center',
  'max-width': '100%',
  'max-height': '100%',
}))

function resetView() {
  nextTick(() => {
    const img = image.value;
    if (!img) return;

    imageOrientation.value = img.naturalWidth > img.naturalHeight ? 'landscape' : 'portrait';

    scale.value = 1;
    offset.value = { x: 0, y: 0 };
  });
}

function clampOffset(newOffset) {
  const img = image.value;
  const cont = container.value;
  if (!img || !cont) return newOffset;

  const scaledWidth = img.naturalWidth * scale.value;
  const scaledHeight = img.naturalHeight * scale.value;
  const containerWidth = cont.clientWidth;
  const containerHeight = cont.clientHeight;

  const maxOffsetX = Math.max(0, (scaledWidth - containerWidth) / 2);
  const maxOffsetY = Math.max(0, (scaledHeight - containerHeight) / 2);

  return {
    x: Math.min(maxOffsetX, Math.max(-maxOffsetX, newOffset.x)),
    y: Math.min(maxOffsetY, Math.max(-maxOffsetY, newOffset.y)),
  };
}

function zoomIn() {
  scale.value = Math.min(scale.value + 0.2, 5);
}
function zoomOut() {
  scale.value = Math.max(scale.value - 0.2, 0.2);
}

function startPan(e) {
  dragging.value = true;
  start.value = { x: e.clientX, y: e.clientY };
}
function onPan(e) {
  if (!dragging.value) return;
  const dx = e.clientX - start.value.x;
  const dy = e.clientY - start.value.y;

  const newOffset = {
    x: offset.value.x + dx,
    y: offset.value.y + dy,
  };

  offset.value = clampOffset(newOffset);
  start.value = { x: e.clientX, y: e.clientY };
}
function endPan() {
  dragging.value = false;
}

function startTouchPan(e) {
  if (e.touches.length !== 1) return;
  dragging.value = true;
  start.value = { x: e.touches[0].clientX, y: e.touches[0].clientY };
}
function onTouchPan(e) {
  if (!dragging.value || e.touches.length !== 1) return;
  const dx = e.touches[0].clientX - start.value.x;
  const dy = e.touches[0].clientY - start.value.y;

  const newOffset = {
    x: offset.value.x + dx,
    y: offset.value.y + dy,
  };

  offset.value = clampOffset(newOffset);
  start.value = { x: e.touches[0].clientX, y: e.touches[0].clientY };
}

defineExpose({ open: () => (visible.value = true) });
</script>

<style scoped>
img {
  user-select: none;
  display: block;
}
</style>
