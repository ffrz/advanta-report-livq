<script setup>
import { usePage } from "@inertiajs/vue3";
import DatePicker from "@/components/DatePicker.vue";
import dayjs from "dayjs";
import { useApiForm } from "@/helpers/useApiForm";
import { ref, watch, reactive, onMounted } from "vue";

const page = usePage();
const title = "Laporan";
const form = useApiForm({
  preview: true,
  report_type: page.props.report_type ?? null,
  user_id: "all",
  product_id: "all",
  period: "this_month",
  plant_statuses: [],
  start_date: dayjs().format("YYYY-MM-DD"),
  end_date: dayjs().format("YYYY-MM-DD"),
});

const filter_options = reactive({
  show_user: false,
  show_product: false,
  show_period: false,
  show_download_excel: false,
  showPlantStatus: false,
});

const report_types = [
  { value: "demo-plot-detail", label: "Laporan Demo Plot" },
  { value: "new-demo-plot-detail", label: "Laporan Demo Plot Baru" },
  { value: "demo-plot-with-photo", label: "Laporan Demo Plot dengan Foto" },
  { value: "client-actual-inventory", label: "Laporan Inventori Aktual" },
  { value: "activity-plan-detail", label: "Laporan Rencana Kegiatan" },
  { value: "activity-realization-detail", label: "Laporan Realisasi Kegiatan" },
  { value: "activity-target-detail", label: "Laporan KPI Kegiatan" },
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

const products = [
  { value: "all", label: "Semua" },
  ...page.props.products.map((product) => ({
    value: product.id,
    label: `${product.name}`,
  })),
];

const users = [
  { value: "all", label: "Semua" },
  ...page.props.users.map((user) => ({
    value: user.id,
    label: `${user.name} (${user.username})`,
  })),
];

const plantStatusOptions = [
  ...Object.entries(window.CONSTANTS.DEMO_PLOT_PLANT_STATUSES).map(
    ([key, value]) => ({
      value: key,
      label: value,
    })
  ),
];

const submit = (format) => {
  if (!validate()) return;

  const query = new URLSearchParams();
  if (filter_options.show_user) {
    query.append("user_id", form.user_id);
  }

  if (filter_options.show_period) {
    query.append("period", form.period);
  }

  if (filter_options.show_product) {
    query.append("product_id", form.product_id);
  }

  if (filter_options.showPlantStatus) {
    query.append("plant_statuses", form.plant_statuses);
  }

  if (form.period == "custom") {
    query.append("start_date", form.start_date);
    query.append("end_date", form.end_date);
  }

  query.append("format", format);

  const base_url = route("admin.report." + form.report_type);

  const downloadLink = `${base_url}?${query.toString()}`;
  window.open(downloadLink, "_blank");
};

const validate = () => {
  let is_valid = true;

  form.errors.report_type = null;

  if (!form.report_type) {
    is_valid = false;
    form.errors.report_type = "Pilih jenis laporan!";
  }

  if (form.period === "custom") {
    form.errors.start_date = null;
    form.errors.end_date = null;

    const start = new Date(form.start_date);
    const end = new Date(form.end_date);

    // Validasi start_date
    if (!form.start_date || isNaN(start.getTime())) {
      form.errors.start_date = "Tanggal awal tidak valid atau kosong!";
      is_valid = false;
    }

    // Validasi end_date
    if (!form.end_date || isNaN(end.getTime())) {
      form.errors.end_date = "Tanggal akhir tidak valid atau kosong!";
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
        form.errors.end_date = "Tanggal akhir harus setelah tanggal awal!";
        is_valid = false;
      }
    }
  }

  form.errors.user_id = null;
  // if (form.report_type == 'sales-activity-detail') {
  //   if (form.user_id == 'all') {
  //     is_valid = false;
  //     form.errors.user_id = 'Pilih Sales terlebih dahulu!';
  //   }
  // }

  return is_valid;
};

const reset = () => {
  form.reset();
  form.report_type = null;
};

function updateState() {
  for (const key of Object.keys(filter_options)) {
    filter_options[key] = false;
  }

  if (["demo-plot-detail", "demo-plot-with-photo"].includes(form.report_type)) {
    filter_options.show_user = true;
  }

  if (["new-demo-plot-detail"].includes(form.report_type)) {
    filter_options.showPlantStatus = true;
  }

  if (
    [
      "new-demo-plot-detail",
      "activity-plan-detail",
      "activity-realization-detail",
      "activity-target-detail",
    ].includes(form.report_type)
  ) {
    filter_options.show_period = true;
    filter_options.show_user = true;
    // filter_options.show_product = true;
  }
  if (["client-actual-inventory"].includes(form.report_type)) {
    filter_options.show_user = true;
    filter_options.show_product = true;
    filter_options.show_download_excel = true;
  }

  validate();
}

onMounted(() => updateState());
// Watch perubahan pada form
watch(
  () => [
    form.report_type,
    form.user_id,
    form.service_id,
    form.period,
    form.start_date,
    form.end_date,
    form.client_id,
  ],
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
              <q-select
                v-model="form.report_type"
                label="Jenis Laporan"
                :options="report_types"
                map-options
                emit-value
                :error="!!form.errors.report_type"
                :disable="form.processing"
                behavior="menu"
                :error-message="form.errors.report_type"
              />
              <q-select
                v-if="filter_options.show_user"
                v-model="form.user_id"
                label="User"
                :options="users"
                map-options
                emit-value
                :error="!!form.errors.user_id"
                :disable="form.processing"
                :error-message="form.errors.user_id"
              />
              <q-select
                v-if="filter_options.show_product"
                v-model="form.product_id"
                label="Varietas"
                :options="products"
                map-options
                emit-value
                :error="!!form.errors.product_id"
                :disable="form.processing"
                :error-message="form.errors.product_id"
              />
              <q-select
                v-if="filter_options.show_period"
                class="custom-select col-12"
                style="min-width: 150px"
                v-model="form.period"
                :options="period_options"
                label="Periode"
                map-options
                emit-value
                :error="!!form.errors.period"
                :disable="form.processing"
              />
              <q-select
                v-if="filter_options.showPlantStatus"
                v-model="form.plant_statuses"
                label="Status Tanaman"
                :options="plantStatusOptions"
                map-options
                emit-value
                :error="!!form.errors.plant_statuses"
                :disable="form.processing"
                :error-message="form.errors.plant_statuses"
                multiple
                use-chips
              />
              <div class="row q-gutter-md" v-if="form.period == 'custom'">
                <div class="col">
                  <date-picker
                    v-model="form.start_date"
                    label="Dari Tanggal"
                    :error="!!form.errors.start_date"
                    clearable
                    :disable="form.processing"
                    :error-message="form.errors.start_date"
                    :rules="[
                      (val) =>
                        (val && val.length > 0) || 'Tanggal awal harus diisi.',
                    ]"
                  />
                </div>
                <div class="col">
                  <date-picker
                    v-model="form.end_date"
                    label="s.d. Tanggal"
                    :error="!!form.errors.end_date"
                    clearable
                    :disable="form.processing"
                    :error-message="form.errors.end_date"
                    :rules="[
                      (val) =>
                        (val && val.length > 0) || 'Tanggal akhir harus diisi.',
                    ]"
                  />
                </div>
              </div>
            </q-card-section>
            <q-card-section class="row q-gutter-sm">
              <q-btn
                icon="cancel"
                type="reset"
                label="Reset"
                class="text-black"
                :disable="form.processing"
                @click="reset"
              />
              <q-btn
                icon="download"
                type="submit"
                label="Download PDF"
                color="primary"
                :disable="form.processing"
                @click="submit('pdf')"
              />
              <q-btn
                v-if="filter_options.show_download_excel"
                icon="csv"
                type="submit"
                label="Download Excel"
                color="primary"
                :disable="form.processing"
                @click="submit('excel')"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </div>
  </authenticated-layout>
</template>
