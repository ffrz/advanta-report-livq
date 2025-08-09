<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { useProductFilter } from "@/composables/useProductFilter";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import DatePicker from "@/components/DatePicker.vue";
import { useCustomerFilter } from "@/helpers/useCustomerFilter";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Log Inventori";
console.log(
  page.props.data.quantity ? parseFloat(page.props.data.quantity) : 0.0
);
const form = useForm({
  id: page.props.data.id,
  product_id: page.props.data.product_id,
  customer_id: page.props.data.customer_id,
  user_id: page.props.data.user_id,
  area: page.props.data.area,
  lot_package: page.props.data.lot_package,
  quantity: parseFloat(page.props.data.quantity),
  check_date: page.props.data.check_date,
  notes: page.props.data.notes,
});

const areas = [{ value: "West Java", label: "West Java" }];

const submit = () =>
  handleSubmit({ form, url: route("admin.inventory-log.save") });

const { filteredProducts, filterProducts } = useProductFilter(
  page.props.products
);

const { filteredCustomers, filterCustomers } = useCustomerFilter(
  page.props.customers
);
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <q-form class="row" @submit.prevent="submit">
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-md">
              <input type="hidden" name="id" v-model="form.id" />
              <date-picker
                v-model="form.check_date"
                label="Tanggal Cek"
                :error="!!form.errors.check_date"
                :disable="form.processing"
                :error-message="form.errors.check_date"
              />
              <q-select
                v-model="form.area"
                label="Area"
                :options="areas"
                emit-value
                map-options
                option-label="label"
                option-value="value"
                :error="!!form.errors.area"
                :disable="form.processing"
                :error-message="form.errors.area"
              >
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section>Area tidak ditemukan</q-item-section>
                  </q-item>
                </template>
              </q-select>
              <q-select
                v-model="form.product_id"
                label="Varietas"
                use-input
                input-debounce="300"
                clearable
                :options="filteredProducts"
                map-options
                emit-value
                @filter="filterProducts"
                option-label="label"
                option-value="value"
                :error="!!form.errors.product_id"
                :disable="form.processing"
                :error-message="form.errors.product_id"
              >
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section>Varietas tidak ditemukan</q-item-section>
                  </q-item>
                </template>
              </q-select>
              <q-select
                v-model="form.customer_id"
                label="Client"
                use-input
                input-debounce="300"
                clearable
                :options="filteredCustomers"
                map-options
                emit-value
                @filter="filterCustomers"
                option-label="label"
                option-value="value"
                :error="!!form.errors.customer_id"
                :disable="form.processing"
                :error-message="form.errors.customer_id"
              >
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section>Client tidak ditemukan</q-item-section>
                  </q-item>
                </template>
              </q-select>
              <q-input
                v-model.trim="form.lot_package"
                label="Lot Package"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.lot_package"
                :error-message="form.errors.lot_package"
              />
              <LocaleNumberInput
                v-model:modelValue="form.quantity"
                label="Quantity (kg)"
                lazyRules
                :maxDecimals="3"
                :disable="form.processing"
                :error="!!form.errors.quantity"
                :errorMessage="form.errors.quantity"
              />
              <q-input
                v-model.trim="form.notes"
                type="textarea"
                autogrow
                counter
                maxlength="1000"
                label="Catatan"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.notes"
                :error-message="form.errors.notes"
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
                @click="router.get(route('admin.inventory-log.index'))"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>
