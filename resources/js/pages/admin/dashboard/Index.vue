<script setup>
import CurrentStatCards from "./cards/CurrentStatCards.vue";
import StatCards from "./cards/StatCards.vue";
import ChartCard from "./cards/ChartCard.vue";
import RecentInteractionsCard from "./cards/RecentInteractionsCard.vue";
import RecentClosingsCard from "./cards/RecentClosingsCard.vue";
import RecentCustomersCard from "./cards/RecentCustomersCard.vue";
import { router, usePage } from "@inertiajs/vue3";

import { reactive, ref } from "vue";
import { create_month_options, current_month, current_quarter, current_year, getQueryParams } from "@/helpers/utils";
import BsTargetCard from "./cards/BsTargetCard.vue";
import { usePageStorage } from "@/helpers/usePageStorage";

const query = getQueryParams();
const currentYear = current_year();
const currentMonth = current_month();
const title = "Dashboard";
const showFilter = ref(true);
const filter = reactive({
  year: Number(query.year ?? currentYear),
  month: Number(query.month ?? currentMonth),
});

const years = [
  ...Array.from({ length: 3 }, (_, i) => {
    const year = currentYear - 1 + i;
    return { value: year, label: String(year) + " / " + String(year + 1) };
  }),
];

const months = create_month_options();

const onFilterChange = () => {
  router.visit(route("admin.dashboard", filter));
};

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn
        class="q-ml-sm"
        :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'"
        color="grey"
        dense
        @click="showFilter = !showFilter"
      />
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select
            class="custom-select col-xs-12 col-sm-6"
            style="min-width: 120px"
            v-model="filter.year"
            :options="years"
            label="Tahun"
            dense
            emit-value
            map-options
            outlined
            @update:model-value="onFilterChange"
          />
          <q-select
            class="custom-select col-xs-12 col-sm-6"
            style="min-width: 120px"
            v-model="filter.month"
            :options="months"
            label="Buan"
            dense
            emit-value
            map-options
            outlined
            @update:model-value="onFilterChange"
          />
        </div>
      </q-toolbar>
    </template>
    <div class="q-pa-sm" v-if="$page.props.auth.user.role === 'bs'">
      <BsTargetCard />
    </div>
    <div class="q-pa-sm" v-if="$page.props.auth.user.role === 'admin'">
      <div>
        <div class="text-subtitle1 text-bold text-grey-8">Statistik Aktual</div>
        <p class="text-grey-8">Belum Tersedia</p>
        <!-- <current-stat-cards class="q-py-none" /> -->
        <div class="row q-col-gutter-sm q-pt-sm">
          <!-- <recent-interactions-card class="q-my-xs" />
          <recent-customers-card class="q-my-xs" />
          <recent-closings-card class="q-my-xs" /> -->
        </div>
      </div>
      <div class="q-pt-md">
        <div class="text-subtitle1 text-bold text-grey-8">
          Statistik Keseluruhan
        </div>
        <p class="text-grey-8">Belum Tersedia</p>
        <!-- <stat-cards class="q-py-none" /> -->
      </div>
      <div>
        <!-- <chart-card class="q-py-none q-pt-lg" /> -->
      </div>
    </div>
  </authenticated-layout>
</template>
