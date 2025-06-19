<script setup>
import { handleSubmit } from "@/helpers/client-req-handler";
import {  usePage } from "@inertiajs/vue3";
import DatePicker from "@/components/DatePicker.vue";
import dayjs from "dayjs";
import { useApiForm } from "@/helpers/useApiForm";
import { ref, watch } from "vue";

const page = usePage();
const title = 'Laporan Closing';
const form = useApiForm({
  preview: true,
  user_id: 'all',
  period: 'this_month',
  start_date: dayjs().format('YYYY-MM-DD'),
  end_date: dayjs().format('YYYY-MM-DD'),
});

const period_options = ref([
  { value: "custom", label: "Custom" },
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
]);

const users = [
  { value: 'all', label: 'Semua' },
  ...page.props.users.map(user => ({
    value: user.id,
    label: `${user.name} (${user.username})`,
  }))
];

const downloadUrl = ref(null);

const submit = () =>
  handleSubmit({
    form,
    url: route('admin.report.closing-detail'),
    onSuccess: (data) => {
      const query = new URLSearchParams();
      query.append('user_id', data.params.user_id);
      query.append('start_date', data.params.start_date);
      query.append('end_date', data.params.end_date);
      downloadUrl.value = `${data.url}?${query.toString()}`;
    }
  });

const reset = () => {
  form.reset();
  downloadUrl.value = null;
}

// Watch perubahan pada form
watch(
  () => [form.user_id, form.start_date, form.end_date, form.period],
  () => {
    downloadUrl.value = null;
  }
);

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <div class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <q-form class="row" @submit.prevent="submit">
          <q-card square flat bordered class="col">
            <q-inner-loading :showing="form.processing">
              <q-spinner size="50px" color="primary" />
            </q-inner-loading>
            <q-card-section class="q-pt-none">
              <div class="q-mt-lg q-mb-sm text-subtitle1 text-bold text-grey-8">Generate Laporan</div>
              <q-select v-model="form.user_id" label="Sales" :options="users" map-options emit-value
                :error="!!form.errors.user_id" :disable="form.processing" :error-message="form.errors.user_id" />
              <q-select class="custom-select col-12" style="min-width: 150px" v-model="form.period"
                :options="period_options" label="Periode" map-options emit-value :error="!!form.errors.period" />
              <div class="row q-gutter-md" v-if="form.period=='custom'">
                <div class="col">
                  <date-picker v-model="form.start_date" label="Dari Tanggal" :error="!!form.errors.start_date"
                    :disable="form.processing" :error-message="form.errors.start_date" />
                </div>
                <div class="col">
                  <date-picker v-model="form.end_date" label="s.d. Tanggal" :error="!!form.errors.end_date"
                    :disable="form.processing" :error-message="form.errors.end_date" />
                </div>
              </div>
            </q-card-section>
            <q-card-section class="row q-gutter-sm">
              <q-btn icon="check" type="submit" label="Terapkan" color="primary" :disable="form.processing"
                @click="submit" />
              <q-btn icon="cancel" type="reset" label="Reset" class="text-black" :disable="form.processing"
                @click="reset" />
              <template v-if="downloadUrl">
                <q-space /> <!-- Mendorong tombol download ke kanan -->
                <q-btn icon="download" color="accent" label="Download" :disable="form.processing" :href="downloadUrl"
                  target="_blank" />
              </template>
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </div>
  </authenticated-layout>
</template>
