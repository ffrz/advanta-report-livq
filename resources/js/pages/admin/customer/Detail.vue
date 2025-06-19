<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import MainInfo from "./partial/MainInfo.vue";
import Interaction from "./partial/Interaction.vue";

const page = usePage();
const title = "Rincian Client";
const tab = ref('main')
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn icon="arrow_back" dense color="grey-7" @click="$goBack()" />
        <q-btn icon="edit" dense color="primary"
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
                <q-tab name="interaction" label="Riwayat Interaksi" />
              </q-tabs>
              <q-tab-panels v-model="tab">
                <q-tab-panel name="main">
                  <main-info />
                  <div class="q-mt-lg">
                    <q-btn label="Catat Interaksi" color="secondary" size="sm" icon="add"
                      :href="route('admin.interaction.add', { customer_id: page.props.data.id })" />
                  </div>
                </q-tab-panel>
                <q-tab-panel name="interaction" class="q-pa-none q-pt-sm">
                  <interaction class="q-pa-none q-ma-none"/>
                </q-tab-panel>
              </q-tab-panels>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>
