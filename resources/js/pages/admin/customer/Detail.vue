<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import MainInfo from "./partial/MainInfo.vue";
import { check_role } from "@/helpers/utils";
//import OrderHistory from "./partial/OrderHistory.vue";

const page = usePage();
const title = "Rincian Client";
const tab = ref('main')
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn icon="arrow_back" dense color="grey-7" flat rounded @click="router.get(route('admin.customer.index'))" />
      </div>
    </template>
    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn icon="edit" dense color="primary"
          :disabled="!check_role([$CONSTANTS.USER_ROLE_AGRONOMIST, $CONSTANTS.USER_ROLE_ADMIN])"
          @click="router.get(route('admin.customer.edit', { id: page.props.data.id }))" />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-card-section>
              <q-tabs v-model="tab" align="left">
                <q-tab name="main" label="Info Utama" />
                <!-- <q-tab name="history" label="Riwayat PO" /> -->
              </q-tabs>
              <q-tab-panels v-model="tab">
                <q-tab-panel name="main">
                  <main-info />
                </q-tab-panel>
                <!-- <q-tab-panel name="history" class="q-pa-none q-pt-sm">
                  <order-history class="q-pa-none q-ma-none" />
                </q-tab-panel> -->
              </q-tab-panels>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>
