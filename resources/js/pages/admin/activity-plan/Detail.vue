<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import MainInfo from "./partial/MainInfo.vue";

const page = usePage();
const title = "Rincian Rencana Kegiatan";
const tab = ref("main");
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="router.get(route('admin.activity-plan.index'))"
        />
      </div>
    </template>
    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn
          v-if="$can('admin-activity-plan.edit')"
          icon="edit"
          dense
          color="primary"
          @click="
            router.get(
              route('admin.activity-plan.edit', { id: page.props.data.id })
            )
          "
        />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <div class="row">
          <q-card square flat bordered class="col q-pa-none">
            <q-card-section class="q-pa-md">
              <q-tabs v-model="tab" align="left">
                <q-tab name="main" label="Info Utama" />
                <q-tab
                  name="detail"
                  label="Rincian"
                  v-if="$can('admin.activity-plan-detail.index')"
                />
              </q-tabs>
              <q-tab-panels v-model="tab">
                <q-tab-panel name="main">
                  <main-info />
                </q-tab-panel>
                <q-tab-panel
                  name="detail"
                  class="q-pa-none q-pt-sm"
                  v-if="$can('admin.activity-plan-detail.index')"
                >
                  <!-- <visit-history class="q-pa-none q-ma-none" /> -->
                </q-tab-panel>
              </q-tab-panels>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>
