<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import {
  scrollToFirstErrorField,
  create_month_options,
  create_year_options,
  create_quarter_options,
} from "@/helpers/utils";
import dayjs from "dayjs";
import { ref, onMounted } from "vue";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Target Kegiatan";

const users = page.props.users.map((user) => ({
  value: user.id,
  label: `${user.name} (${user.username})`,
}));

const types = page.props.types.map((type) => ({
  value: type.id,
  label: `${type.name}`,
}));

// buatkan kode untuk generate daftar tahun, setahun kebelakang, tahun ini dan tahun depan
// lalu masukan sebagai parameter di bawah ini
const currentYear = new Date().getFullYear();
const years = create_year_options(
  String(currentYear - 1),
  String(currentYear + 1)
);
const period_types = [
  { value: "month", label: "Bulanan" },
  { value: "quarter", label: "Kwartal" },
];
const months = create_month_options();
const quarters = create_quarter_options();

const form = useForm({
  id: page.props.data.id,
  user_id: page.props.data.user_id ? Number(page.props.data.user_id) : null,
  type_id: page.props.data.type_id ? Number(page.props.data.type_id) : null,
  year: page.props.data.year,
  month: page.props.data.month,
  quarter: page.props.data.quarter,
  period_type: page.props.data.period_type,
  notes: page.props.data.notes,
});

const submit = () =>
  handleSubmit({
    form,
    forceFormData: true,
    url: route("admin.activity-target.save"),
  });
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center">
      <div class="col col-md-6 q-pa-sm">
        <q-form
          class="row"
          @submit.prevent="submit"
          @validation-error="scrollToFirstErrorField"
        >
          <q-card square flat bordered class="col">
            <q-inner-loading :showing="form.processing">
              <q-spinner size="50px" color="primary" />
            </q-inner-loading>
            <q-card-section class="q-pt-md">
              <input type="hidden" name="id" v-model="form.id" />
              <q-select
                v-model="form.type_id"
                label="Kegiatan"
                :options="types"
                map-options
                emit-value
                :error="!!form.errors.type_id"
                :disable="form.processing"
                :error-message="form.errors.type_id"
              />
              <q-select
                v-model="form.user_id"
                label="BS"
                :options="users"
                map-options
                emit-value
                :error="!!form.errors.user_id"
                :disable="form.processing"
                :error-message="form.errors.user_id"
              />
              <q-select
                v-model="form.period_type"
                label="Periode"
                :options="period_types"
                map-options
                emit-value
                :error="!!form.errors.period_type"
                :disable="form.processing"
                :error-message="form.errors.period_type"
              />
              <q-select
                v-model="form.year"
                label="Tahun"
                :options="years"
                map-options
                emit-value
                :error="!!form.errors.year"
                :disable="form.processing"
                :error-message="form.errors.year"
              />
              <q-select
                v-if="form.period_type == 'quarter'"
                v-model="form.quarter"
                label="Kwartal"
                :options="quarters"
                map-options
                emit-value
                :error="!!form.errors.quarter"
                :disable="form.processing"
                :error-message="form.errors.quarter"
              />
              <q-select
                v-if="form.period_type == 'month'"
                v-model="form.month"
                label="Bulan"
                :options="months"
                map-options
                emit-value
                :error="!!form.errors.month"
                :disable="form.processing"
                :error-message="form.errors.month"
              />
              <q-input
                v-model.trim="form.qty"
                type="number"
                autogrow
                counter
                maxlength="2"
                label="Jumlah"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.qty"
                :error-message="form.errors.qty"
              />
              <q-input
                v-model.trim="form.notes"
                type="textarea"
                autogrow
                counter
                maxlength="255"
                label="Catatan"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.notes"
                :error-message="form.errors.notes"
                :rules="[]"
              />
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn
                icon="save"
                type="submit"
                label="Simpan"
                color="primary"
                :disable="form.processing"
              />
              <q-btn
                icon="cancel"
                label="Batal"
                :disable="form.processing"
                @click="$goBack()"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>
