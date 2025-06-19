<script setup>
import CompanyInfoForm from "./partials/CompanyInfoForm.vue";
const title = "Profil Perusahaan";
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <div class="q-pa-sm">
      <div class="row justify-center">
        <div class="col col-md-6">
          <CompanyInfoForm />
        </div>
      </div>
    </div>
  </authenticated-layout>
</template>
