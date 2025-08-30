<template>
  <div
    v-if="text"
    class="long-text-view inline-icon"
    style="
      white-space: pre-wrap;
      word-break: break-word;
      overflow-wrap: break-word;
    "
  >
    <q-icon v-if="icon != ''" :name="icon" />
    {{ truncatedText }}
    <q-tooltip v-if="text.length > maxLength">{{ text }}</q-tooltip>
  </div>
</template>

<script setup>
import { defineProps, computed } from "vue";

const props = defineProps({
  text: {
    type: String,
    default: "",
  },
  maxLength: {
    type: Number,
    default: 100, // Default panjang teks sebelum dipotong
  },
  icon: {
    type: String,
    default: "",
  },
});

const truncatedText = computed(() => {
  if (props.text.length > props.maxLength) {
    return props.text.slice(0, props.maxLength) + "...";
  }
  return props.text;
});
</script>

<style scoped>
.long-text-view {
  white-space: pre-wrap;
  word-break: break-word;
  overflow-wrap: break-word;
}
</style>
