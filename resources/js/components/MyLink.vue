<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
  href: {
    type: String,
    default: '',
  },
  method: {
    type: String,
    default: 'get',
  },
  label: {
    type: String,
    default: '',
  }
});

</script>

<template>
  <a @click.prevent="router[props.method](props.href)" :href="props.href">
    <template v-if="props.label">
      {{ props.label }}
    </template>
    <template v-else>
      <slot></slot>
    </template>
  </a>
</template>

<style scoped>
a {
  cursor: pointer;
  text-decoration: none;
}
</style>
