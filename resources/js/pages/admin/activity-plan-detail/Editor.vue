<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Rincian Plan";

const products = page.props.products.map((product) => ({
  value: product.id,
  label: product.name,
}));

const types = page.props.types.map((type) => ({
  value: type.id,
  label: type.name,
}));

const form = useForm({
  id: page.props.data.id,
  parent_id: page.props.data.parent_id,
  product_id: page.props.data.product_id
    ? Number(page.props.data.product_id)
    : null,
  type_id: page.props.data.type_id ? Number(page.props.data.type_id) : null,
  cost: Number(page.props.data.cost),
  location: page.props.data.location,
  notes: page.props.data.notes,
});

const submit = () =>
  handleSubmit({
    form,
    url: route("admin.activity-plan-detail.save"),
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
              <input type="hidden" name="parent_id" v-model="form.parent_id" />
              <q-select
                v-model="form.type_id"
                label="Kegiatan"
                :options="types"
                map-options
                emit-value
                :error="!!form.errors.type_id"
                :disable="form.processing"
                :error-message="form.errors.type_id"
                :rules="[(val) => (val && val > 0) || 'Pilih kegiatan.']"
                hide-bottom-space
              />
              <q-select
                v-model="form.product_id"
                label="Varietas (Opsional)"
                :options="products"
                map-options
                emit-value
                :error="!!form.errors.product_id"
                :disable="form.processing"
                :error-message="form.errors.product_id"
                hide-bottom-space
              />
              <q-input
                v-model.trim="form.location"
                type="text"
                maxlength="100"
                label="Lokasi"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.location"
                :error-message="form.errors.location"
                hide-bottom-space
              />
              <LocaleNumberInput
                v-model:modelValue="form.cost"
                label="Biaya (Rp)"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.cost"
                :errorMessage="form.errors.cost"
                hide-bottom-space
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
                hide-bottom-space
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
