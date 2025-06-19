<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import MonthPicker from "@/components/MonthPicker.vue";
import dayjs from "dayjs";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Target";

const users = page.props.users.map(user => ({
  value: user.id,
  label: `${user.name} (${user.username})`,
}));

const form = useForm({
  id: page.props.data.id,
  user_id: page.props.data.user_id ? Number(page.props.data.user_id) : null,
  date: dayjs(page.props.data.date).format('YYYY-MM-DD'),
  fm: page.props.data.fm ? Number(page.props.data.fm) : 0,
  odp: page.props.data.odp ? Number(page.props.data.odp) : 0,
  ft: page.props.data.ft ? Number(page.props.data.ft) : 0,
  fdd: page.props.data.fdd ? Number(page.props.data.fdd) : 0,
  notes: page.props.data.notes,
});

const submit = () =>
  handleSubmit({ form, url: route('admin.target.save') });

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-sm">
        <q-form class="row" @submit.prevent="submit" @validation-error="scrollToFirstErrorField">
          <q-card square flat bordered class="col">
            <q-inner-loading :showing="form.processing">
              <q-spinner size="50px" color="primary" />
            </q-inner-loading>
            <q-card-section class="q-pt-none">
              <input type="hidden" name="id" v-model="form.id" />
              <MonthPicker v-model="form.date" label="Bulan & Tahun" :error="!!form.errors.date" default-view="months"
                minimal :disable="form.processing" :error-message="form.errors.date" />
              <q-select v-model="form.user_id" label="BS" :options="users" map-options emit-value
                :error="!!form.errors.user_id" :disable="form.processing" :rules="[
                  val => (val !== null && val !== undefined && val !== '') || 'Silakan pilih BS.'
                ]" />
              <LocaleNumberInput v-model:modelValue="form.fm" label="Target FM" lazyRules :disable="form.processing"
                :error="!!form.errors.fm" :errorMessage="form.errors.fm" :rules="[
                ]" />
              <LocaleNumberInput v-model:modelValue="form.odp" label="Target ODP" lazyRules :disable="form.processing"
                :error="!!form.errors.odp" :errorMessage="form.errors.odp" :rules="[
                ]" />
              <LocaleNumberInput v-model:modelValue="form.ft" label="Target Studi Banding / FT" lazyRules
                :disable="form.processing" :error="!!form.errors.ft" :errorMessage="form.errors.ft" :rules="[
                ]" />
              <LocaleNumberInput v-model:modelValue="form.fdd" label="Target FDD (Per 3 Bulan)" lazyRules
                :disable="form.processing" :error="!!form.errors.fdd" :errorMessage="form.errors.fdd" :rules="[
                ]" />
              <q-input v-model.trim="form.notes" type="textarea" autogrow counter maxlength="255" label="Catatan"
                lazy-rules :disable="form.processing" :error="!!form.errors.notes" :error-message="form.errors.notes"
                :rules="[]" />
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn icon="save" type="submit" label="Simpan" color="primary" :disable="form.processing" />
              <q-btn icon="cancel" label="Batal" :disable="form.processing" @click="$goBack()" />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>

  </authenticated-layout>
</template>
