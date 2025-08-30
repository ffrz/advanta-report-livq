<template>
  <q-select
    :model-value="modelValue"
    @update:model-value="(value) => $emit('update:modelValue', value)"
    :label="label"
    :options="options"
    map-options
    emit-value
    :error="error"
    :disable="disable"
    behavior="menu"
    :error-message="errorMessage"
  >
    <template #option="scope">
      <q-item
        v-if="scope.opt.separator"
        :key="scope.index"
        class="q-item--separator separator"
        tag="div"
      >
        <q-item-section>
          <q-separator />
        </q-item-section>
      </q-item>
      <q-item
        v-else
        v-bind="scope.itemProps"
        :key="scope.index"
        :active="scope.opt.value === modelValue"
        clickable
        @click="scope.toggleOption(scope.opt)"
      >
        <q-item-section>
          <q-item-label>{{ scope.opt.label }}</q-item-label>
        </q-item-section>
      </q-item>
    </template>
  </q-select>
</template>

<script setup>
import { defineProps, defineEmits } from "vue";

defineProps({
  modelValue: {
    type: [String, Number, null],
    default: null,
  },
  options: {
    type: Array,
    required: true,
  },
  label: {
    // Menambahkan prop baru untuk label
    type: String,
    default: "Select an option",
  },
  error: {
    type: Boolean,
    default: false,
  },
  errorMessage: {
    type: String,
    default: "",
  },
  disable: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["update:modelValue"]);
</script>

<style scoped>
.separator {
  min-height: 0 !important;
  padding: 0 !important;
}
</style>
