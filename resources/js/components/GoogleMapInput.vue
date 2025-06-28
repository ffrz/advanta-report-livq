<template>
  <div ref="mapContainer" class="map-container"></div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";

const props = defineProps({
  modelValue: String, // format: "lat,lng"
});
const emit = defineEmits(["update:modelValue"]);

const map = ref(null);
const marker = ref(null);
const mapContainer = ref(null);

onMounted(() => {
  const [lat, lng] = props.modelValue?.split(",").map(Number) || [-6.9, 108.4];
  const initialLatLng = { lat, lng };

  map.value = new google.maps.Map(mapContainer.value, {
    center: initialLatLng,
    zoom: 15,
  });

  marker.value = new google.maps.Marker({
    position: initialLatLng,
    map: map.value,
    draggable: true,
  });

  marker.value.addListener("dragend", () => {
    const pos = marker.value.getPosition();
    emit("update:modelValue", `${pos.lat()},${pos.lng()}`);
  });

  map.value.addListener("click", (e) => {
    marker.value.setPosition(e.latLng);
    emit("update:modelValue", `${e.latLng.lat()},${e.latLng.lng()}`);
  });
});

// if modelValue is updated externally, update marker
watch(
  () => props.modelValue,
  (newVal) => {
    if (!map.value || !marker.value) return;
    const [lat, lng] = newVal?.split(",").map(Number) || [];
    if (lat && lng) {
      const newLatLng = { lat, lng };
      marker.value.setPosition(newLatLng);
      map.value.setCenter(newLatLng);
    }
  }
);
</script>

<style scoped>
.map-container {
  width: 100%;
  height: 300px;
  border: 1px solid #ccc;
  border-radius: 4px;
}
</style>
