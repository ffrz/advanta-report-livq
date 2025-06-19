<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { useCustomerFilter } from "@/helpers/useCustomerFilter";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import DatePicker from "@/components/DatePicker.vue";
import dayjs from "dayjs";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Closing";

const { filteredCustomers, filterCustomerFn } = useCustomerFilter(page.props.customers);

const users = page.props.users.map(user => ({
  value: user.id,
  label: `${user.name} (${user.username})`,
}));

const services = page.props.services.map(service => ({
  value: service.id,
  label: `${service.name} (#${service.id})`,
}));


const form = useForm({
  id: page.props.data.id,
  user_id: page.props.data.user_id ? Number(page.props.data.user_id) : null,
  customer_id: page.props.data.customer_id ? Number(page.props.data.customer_id) : null,
  service_id: page.props.data.service_id ? Number(page.props.data.service_id) : null,
  date: dayjs(page.props.data.visit_date).format('YYYY-MM-DD'),
  amount: page.props.data.amount ? Number(page.props.data.amount) : 0,
  description: page.props.data.description,
  notes: page.props.data.notes,
});

const submit = () =>
  handleSubmit({ form, url: route('admin.closing.save') });

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
              <date-picker v-model="form.date" label="Tanggal" :error="!!form.errors.date" :disable="form.processing"
                :error-message="form.errors.date" />
              <q-select v-model="form.user_id" label="Sales" :options="users" map-options emit-value
                :error="!!form.errors.user_id" :disable="form.processing" />
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
                :error="!!form.errors.service_id" :disable="form.processing" />
              <q-input v-model.trim="form.description" type="text" label="Deskripsi" lazy-rules
                :disable="form.processing" :error="!!form.errors.description" :error-message="form.errors.description"
                :rules="[
                  (val) => (val && val.length > 0) || 'Deskripsi harus diisi.',
                ]" />
              <LocaleNumberInput v-model:modelValue="form.amount" label="Jumlah (Rp)" lazyRules
                :disable="form.processing" :error="!!form.errors.amount" :errorMessage="form.errors.amount"
                :rules="[]" />
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
