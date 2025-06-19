<script setup>
import CurrentStatCards from "./cards/CurrentStatCards.vue";
import StatCards from "./cards/StatCards.vue";
import ChartCard from "./cards/ChartCard.vue";
import RecentInteractionsCard from './cards/RecentInteractionsCard.vue';
import RecentClosingsCard from './cards/RecentClosingsCard.vue';
import RecentCustomersCard from './cards/RecentCustomersCard.vue';
import { router, usePage } from "@inertiajs/vue3";

import { ref } from "vue";
import { getQueryParams } from "@/helpers/utils";

const title = "Dashboard";
const showFilter = ref(true);
const selected_period = ref(getQueryParams()["period"] ?? "this_month");

const page = usePage();

const period_options = ref([
  { value: "today", label: "Hari Ini" },
  { value: "yesterday", label: "Kemarin" },
  { value: "this_week", label: "Minggu Ini" },
  { value: "last_week", label: "Minggu Lalu" },
  { value: "this_month", label: "Bulan Ini" },
  { value: "last_month", label: "Bulan Lalu" },
  { value: "this_year", label: "Tahun Ini" },
  { value: "last_year", label: "Tahun Lalu" },
  { value: "last_7_days", label: "7 Hari Terakhir" },
  { value: "last_30_days", label: "30 Hari Terakhir" },
  { value: "all_time", label: "Seluruh Waktu" },
]);
const onFilterChange = () => {
  router.visit(route("admin.dashboard", { period: selected_period.value }));
};
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn class="q-ml-sm" :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'" color="grey" dense
        @click="showFilter = !showFilter" />
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select class="custom-select col-12" style="min-width: 150px" v-model="selected_period"
            :options="period_options" label="Periode" dense map-options emit-value outlined
            @update:model-value="onFilterChange" />
        </div>
      </q-toolbar>
    </template>
    <div class="q-pa-sm">
      <div>
        <div class="text-subtitle1 text-bold text-grey-8">Statistik Aktual</div>
        <current-stat-cards class="q-py-none" />
        <div class="row q-col-gutter-sm q-pt-sm">
          <recent-interactions-card class="q-my-xs" />
          <recent-customers-card class="q-my-xs" />
          <recent-closings-card class="q-my-xs" />
        </div>
      </div>
      <div class="q-pt-md">
        <div class="text-subtitle1 text-bold text-grey-8">
          Statistik {{period_options.find((a) => a.value == selected_period).label}}
        </div>
        <stat-cards class="q-py-none" />
      </div>
      <div>
        <chart-card class="q-py-none q-pt-lg" />
      </div>
    </div>
  </authenticated-layout>
</template>
