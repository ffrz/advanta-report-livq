<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import MainInfo from "./partial/MainInfo.vue";

const page = usePage();
const title = "Rincian Kegiatan";
const tab = ref("main");

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn icon="arrow_back" dense color="grey-7" flat rounded @click="router.get(route('admin.activity.index'))" />
      </div>
    </template>
    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn icon="edit" dense color="primary"
          @click="router.get(route('admin.activity.edit', { id: page.props.data.id }))" />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <div class="row">
          <q-card square flat bordered class="col q-pa-none">
            <q-card-section class="q-pa-md">
              <main-info />
            </q-card-section>
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>
