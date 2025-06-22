<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Kategori Varietas";
const target_period_options = Object.entries(window.CONSTANTS.ACTIVITY_TYPE_TARGET_PERIODS).map(([value, label]) => ({
  label,
  value
}));
const form = useForm({
  id: page.props.data.id,
  name: page.props.data.name,
  target_period: page.props.data.target_period,
  default_target: Number(page.props.data.default_target),
  weight: Number(page.props.data.weight),
  description: page.props.data.description,
  active: !!page.props.data.active,
});

const submit = () => handleSubmit({ form, url: route('admin.activity-type.save') });

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <q-form class="row" @submit.prevent="submit" @validation-error="scrollToFirstErrorField">
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-md">
              <input type="hidden" name="id" v-model="form.id" />
              <q-input autofocus v-model.trim="form.name" label="Nama Kegiatan" lazy-rules :error="!!form.errors.name"
                :disable="form.processing" :error-message="form.errors.name" :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]" />
              <q-select v-model="form.target_period" label="Periode Target" :options="target_period_options" map-options
                emit-value option-label="label" option-value="value" :error="!!form.errors.target_period"
                :disable="form.processing" />
              <q-input v-model.trim="form.description" type="textarea" autogrow counter maxlength="500"
                label="Deskripsi" lazy-rules :disable="form.processing" :error="!!form.errors.description"
                :error-message="form.errors.description" />
              <LocaleNumberInput v-model:modelValue="form.default_target" label="Default Target" lazyRules
                :disable="form.processing" :error="!!form.errors.default_target"
                :errorMessage="form.errors.default_target" />
              <LocaleNumberInput v-model:modelValue="form.weight" label="Bobot" lazyRules :disable="form.processing"
                :error="!!form.errors.weight" :errorMessage="form.errors.weight" />
              <div style="margin-left: -10px;">
                <q-checkbox class="full-width q-pl-none" v-model="form.active" :disable="form.processing"
                  label="Aktif" />
              </div>
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn icon="save" type="submit" label="Simpan" color="primary" :disable="form.processing" />
              <q-btn icon="cancel" label="Batal" :disable="form.processing"
                @click="router.get(route('admin.activity-type.index'))" />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>

  </authenticated-layout>
</template>
