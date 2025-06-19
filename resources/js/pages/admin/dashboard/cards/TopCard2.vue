<script setup>
import { formatNumber } from '@/helpers/utils';

const props = defineProps({
  items: Array,
  title: String,
  route_url: String,
});
</script>

<template>
  <q-card class="no-shadow" square bordered>
    <q-card-section>
      <div class="text-subtitle1 text-bold text-grey-9">{{ props.title }}</div>
      <div>
        <div v-for="(item, row) in props.items" :key="item.id">
          <div class="flex items-center justify-between">
            <span><i-link :href="route(props.route_url, {id: item.id})">{{ row + 1 }} - {{ item.name }}</i-link></span>
            <span>{{ formatNumber(item.total) }} pt</span>
          </div>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>
