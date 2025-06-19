<script setup>
import { usePage } from "@inertiajs/vue3";
import DatePicker from "@/components/DatePicker.vue";
import dayjs from "dayjs";
import { useApiForm } from "@/helpers/useApiForm";
import { ref, watch, reactive, onMounted } from "vue";

const page = usePage();
const title = 'Laporan';
const form = useApiForm({
  preview: true,
  report_type: page.props.report_type ?? null,
  user_id: 'all',
  service_id: 'all',
  client_id: 'all',
  period: 'this_month',
  start_date: dayjs().format('YYYY-MM-DD'),
  end_date: dayjs().format('YYYY-MM-DD'),
});

const filter_options = reactive({
  show_user: false,
  show_service: false,
  show_period: false,
  show_client: false,
});

const report_types = [
  { value: 'interaction', label: 'Laporan Interaksi' },
  { value: 'closing-detail', label: 'Laporan Closing Penjualan' },
  { value: 'closing-by-sales', label: 'Laporan Rekapitulasi Closing Sales' },
  { value: 'closing-by-services', label: 'Laporan Rekap Closing Layanan' },
  { value: 'customer-services-active', label: 'Laporan Layanan Pelanggan Aktif' },
  { value: 'customer-services-new', label: 'Laporan Layanan Pelanggan Baru' },
  { value: 'customer-services-ended', label: 'Laporan Layanan Pelanggan Berakhir' },
  { value: 'sales-performance', label: 'Laporan Rekapitulasi Kinerja Sales' },
  { value: 'sales-activity', label: 'Laporan Rekap Aktivitas Sales' },
  { value: 'sales-activity-detail', label: 'Laporan Rincian Aktivitas Sales' },
  { value: 'client-new', label: 'Laporan Klien Baru' },
  { value: 'client-active-inactive', label: 'Laporan Klien Aktif / Nonaktif' },
  { value: 'client-history', label: 'Laporan Riwayat Klien' },
];

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

const services = [
  { value: 'all', label: 'Semua' },
  ...page.props.services.map(service => ({
    value: service.id,
    label: `${service.name}`,
  }))
];

const users = [
  { value: 'all', label: 'Semua' },
  ...page.props.users.map(user => ({
    value: user.id,
    label: `${user.name} (${user.username})`,
  }))
];

const clients = [
  { value: 'all', label: 'Semua' },
  ...page.props.clients.map(client => ({
    value: client.id,
    label: `${client.name} - ${client.company} [${client.id}]`,
  }))
];

const submit = () => {
  if (!validate()) return;

  const query = new URLSearchParams();
  if (filter_options.show_user) {
    query.append('user_id', form.user_id);
  }

  if (filter_options.show_client) {
    query.append('client_id', form.client_id);
  }

  if (filter_options.show_service) {
    query.append('service_id', form.service_id);
  }

  if (filter_options.show_period) {
    query.append('period', form.period);
  }

  if (form.period == 'custom') {
    query.append('start_date', form.start_date);
    query.append('end_date', form.end_date);
  }

  const base_url = route('admin.report.' + form.report_type)

  const downloadLink = `${base_url}?${query.toString()}`;
  window.open(downloadLink, '_blank');
};

const validate = () => {
  let is_valid = true;

  form.errors.report_type = null;

  if (!form.report_type) {
    is_valid = false;
    form.errors.report_type = 'Pilih jenis laporan!';
  }

  if (form.period === 'custom') {
    form.errors.start_date = null;
    form.errors.end_date = null;

    const start = new Date(form.start_date);
    const end = new Date(form.end_date);

    // Validasi start_date
    if (!form.start_date || isNaN(start.getTime())) {
      form.errors.start_date = 'Tanggal awal tidak valid atau kosong!';
      is_valid = false;
    }

    // Validasi end_date
    if (!form.end_date || isNaN(end.getTime())) {
      form.errors.end_date = 'Tanggal akhir tidak valid atau kosong!';
      is_valid = false;
    }

    // Validasi urutan tanggal
    if (
      form.start_date &&
      form.end_date &&
      !isNaN(start.getTime()) &&
      !isNaN(end.getTime())
    ) {
      if (end < start) {
        form.errors.end_date = 'Tanggal akhir harus setelah tanggal awal!';
        is_valid = false;
      }
    }
  }

  form.errors.client_id = null;
  if (form.report_type == 'client-history') {
    if (form.client_id == 'all') {
      is_valid = false;
      form.errors.client_id = 'Pilih client terlebih dahulu!';
    }
  }

  form.errors.user_id = null;
  if (form.report_type == 'sales-activity-detail') {
    if (form.user_id == 'all') {
      is_valid = false;
      form.errors.user_id = 'Pilih Sales terlebih dahulu!';
    }
  }

  return is_valid;
};

const reset = () => {
  form.reset();
  form.report_type = null;
}

function updateState() {
  for (const key of Object.keys(filter_options)) {
    filter_options[key] = false;
  }

  if ([
    'interaction',
    'sales-activity',
    'closing-detail',
    'closing-by-services',
    'client-new',
    'client-active-inactive',
    'sales-activity-detail',
  ].includes(form.report_type)) {
    filter_options.show_period = true;
    filter_options.show_user = true;
  }
  else if ([
    'customer-services-active',
    'customer-services-new',
    'customer-services-ended',
    'closing-by-sales',
    'sales-performance',
  ].includes(form.report_type)
  ) {
    filter_options.show_period = true;
  }
  else if ([
    'client-history',
  ].includes(form.report_type)) {
    filter_options.show_client = true;
    filter_options.show_period = true;
  }

  validate();
}

onMounted(() => updateState())
// Watch perubahan pada form
watch(
  () => [form.report_type, form.user_id, form.service_id, form.period, form.start_date, form.end_date, form.client_id],
  () => updateState()
);

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <div class="row justify-center">
      <div class="col col-md-6 q-pa-sm">
        <q-form class="row" @submit.prevent="submit">
          <q-card square flat bordered class="col">
            <q-inner-loading :showing="form.processing">
              <q-spinner size="50px" color="primary" />
            </q-inner-loading>
            <q-card-section class="q-pt-none">
              <q-select v-model="form.report_type" label="Jenis Laporan" :options="report_types" map-options emit-value
                :error="!!form.errors.report_type" :disable="form.processing" behavior="menu"
                :error-message="form.errors.report_type" />
              <q-select v-if="filter_options.show_user" v-model="form.user_id" label="Sales" :options="users"
                map-options emit-value :error="!!form.errors.user_id" :disable="form.processing"
                :error-message="form.errors.user_id" />
              <q-select v-if="filter_options.show_service" v-model="form.service_id" label="Layanan" :options="services"
                map-options emit-value :error="!!form.errors.service_id" :disable="form.processing"
                :error-message="form.errors.service_id" />
              <q-select v-if="filter_options.show_client" v-model="form.client_id" label="Client" :options="clients"
                map-options emit-value :error="!!form.errors.client_id" :disable="form.processing"
                :error-message="form.errors.client_id" />
              <q-select v-if="filter_options.show_period" class="custom-select col-12" style="min-width: 150px"
                v-model="form.period" :options="period_options" label="Periode" map-options emit-value
                :error="!!form.errors.period" :disable="form.processing" />
              <div class="row q-gutter-md" v-if="form.period == 'custom'">
                <div class="col">
                  <date-picker v-model="form.start_date" label="Dari Tanggal" :error="!!form.errors.start_date"
                    clearable :disable="form.processing" :error-message="form.errors.start_date" :rules="[
                      (val) => (val && val.length > 0) || 'Tanggal awal harus diisi.',
                    ]" />
                </div>
                <div class="col">
                  <date-picker v-model="form.end_date" label="s.d. Tanggal" :error="!!form.errors.end_date" clearable
                    :disable="form.processing" :error-message="form.errors.end_date" :rules="[
                      (val) => (val && val.length > 0) || 'Tanggal akhir harus diisi.',
                    ]" />
                </div>
              </div>
            </q-card-section>
            <q-card-section class="row q-gutter-sm">
              <q-btn icon="download" type="submit" label="Download PDF" color="primary" :disable="form.processing"
                @click="submit" />
              <q-btn icon="cancel" type="reset" label="Reset" class="text-black" :disable="form.processing"
                @click="reset" />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </div>
  </authenticated-layout>
</template>
