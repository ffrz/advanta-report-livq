<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { useCustomerFilter } from "@/helpers/useCustomerFilter";
import DatePicker from "@/components/DatePicker.vue";
import dayjs from "dayjs";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Layanan Client";

const { filteredCustomers, filterCustomerFn } = useCustomerFilter(page.props.customers);

const statuses = Object.entries(window.CONSTANTS.CUSTOMER_SERVICE_STATUSES).map(([value, label]) => ({
  label,
  value
}));
const services = page.props.services.map(service => ({
  value: service.id,
  label: `${service.name} (#${service.id})`,
}));

const form = useForm({
  id: page.props.data.id,
  customer_id: page.props.data.customer_id ? Number(page.props.data.customer_id) : null,
  service_id: page.props.data.service_id ? Number(page.props.data.service_id) : null,
  start_date: page.props.data.start_date ? dayjs(page.props.data.start_date).format('YYYY-MM-DD') : null,
  end_date: page.props.data.end_date ? dayjs(page.props.data.end_date).format('YYYY-MM-DD') : null,
  description: page.props.data.description,
  status: page.props.data.status,
  notes: page.props.data.notes,
});

const submit = () =>
  handleSubmit({ form, url: route('admin.customer-service.save') });

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
              <q-select v-model="form.customer_id" label="Client" use-input input-debounce="300" clearable
                class="editable-select" :options="filteredCustomers" map-options emit-value @filter="filterCustomerFn"
                option-label="label" option-value="value" :error="!!form.errors.customer_id"
                :error-message="form.errors.customer_id" :disable="form.processing">
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section>Client tidak ditemukan</q-item-section>
                  </q-item>
                </template>
              </q-select>
              <q-select v-model="form.service_id" label="Layanan" :options="services" map-options emit-value
                :error="!!form.errors.service_id" :disable="form.processing" :error-message="form.errors.service_id" />
              <q-input v-model.trim="form.description" maxlength="255" label="Deskripsi" lazy-rules
                :disable="form.processing" :error="!!form.errors.description" :error-message="form.errors.description"
                :rules="[
                  (val) => (val && val.length > 0) || 'Deskripsi harus diisi.',
                ]" />
              <date-picker v-model="form.start_date" label="Tgl Mulai" :error="!!form.errors.start_date"
                :disable="form.processing" :error-message="form.errors.start_date" />
              <date-picker v-model="form.end_date" label="Tgl Berhenti" :error="!!form.errors.end_date"
                :disable="form.processing" :error-message="form.errors.end_date" />
              <q-select v-model="form.status" label="Status" :options="statuses" map-options emit-value
                :error-message="form.errors.status" :error="!!form.errors.status" :disable="form.processing" />
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
