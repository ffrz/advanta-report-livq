<script setup>
import { usePage } from "@inertiajs/vue3";
import ActivityTable from "@/components/ActivityTable.vue";
import {
  calculateMonthlyActivityProgress,
  calculateQuarterActivityProgress,
} from "@/composables/useCalculateActivityProgress";
import {
  current_month,
  formatNumber,
  getQueryParams,
  getMonthIndexInQuarter,
} from "@/helpers/utils";

const selectedMonth = getQueryParams().month || current_month();
const monthIndex = getMonthIndexInQuarter(selectedMonth);
const monthyProgress = calculateMonthlyActivityProgress(
  usePage().props.data.types,
  monthIndex,
  usePage().props.data.targets,
  usePage().props.data.activities
);
const querterProgress = calculateQuarterActivityProgress(
  usePage().props.data.types,
  usePage().props.data.targets,
  usePage().props.data.activities
);
</script>

<template>
  <div class="row">
    <q-card class="bg-transparent no-shadow no-border col" bordered>
      <q-card-section class="q-pa-none">
        <div class="row q-py-md">
          <div class="col-12">
            <div class="q-my-sm text-bold">
              Progress Bulanan:
              {{ formatNumber(monthyProgress, "id-ID", 2) }}
              %
            </div>
            <div class="q-my-sm">
              <q-linear-progress
                :value="monthyProgress / 100"
                size="10px"
                color="primary"
                class="q-mt-xs"
                rounded
                stripe
                animated
              />
            </div>
            <template v-if="$page.props.data.types.length > 0">
              <ActivityTable
                :types="$page.props.data.types"
                :targets="$page.props.data.targets"
                :plans="$page.props.data.plans"
                :activities="$page.props.data.activities"
                :type="'month' + monthIndex"
              />
            </template>
            <template v-else>
              <div class="text-center text-grey-8">
                <q-icon name="info" size="32px" />
                <p class="text-subtitle1">Target belum ditetapkan</p>
              </div>
            </template>
          </div>
        </div>
        <div class="row q-py-md">
          <div class="col-12">
            <div class="q-my-sm text-bold">
              Progress Kwartal:
              {{ formatNumber(querterProgress, "id-ID", 2 ) }}
              %
            </div>
            <div class="q-my-sm">
              <q-linear-progress
                :value="querterProgress / 100"
                size="10px"
                color="primary"
                class="q-mt-xs"
                rounded
                stripe
                animated
              />
            </div>
            <template v-if="$page.props.data.types.length > 0">
              <ActivityTable
                :types="$page.props.data.types"
                :targets="$page.props.data.targets"
                :plans="$page.props.data.plans"
                :activities="$page.props.data.activities"
              />
            </template>
            <template v-else>
              <div class="text-center text-grey-8">
                <q-icon name="info" size="32px" />
                <p class="text-subtitle1">Target belum ditetapkan</p>
              </div>
            </template>
          </div>
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>
<style scoped>
.dense-table {
  font-size: 0.8rem;
  width: 100%;
  margin: 5px 0;
  border-collapse: collapse;
}
.dense-table th,
.dense-table td {
  padding: 2px 8px !important;
  height: auto !important;
  border: 1px solid #e0e0e0 !important;
}
.dense-table td:not(:nth-child(1)) {
  text-align: center !important;
}
</style>
