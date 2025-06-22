<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { useProductCategoryFilter } from "@/helpers/useProductCategoryFilter";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Varietas";

const form = useForm({
  id: page.props.data.id,
  category_id: page.props.data.category_id,
  name: page.props.data.name,
  uom_1: page.props.data.uom_1,
  uom_2: page.props.data.uom_2,
  price_1: parseFloat(page.props.data.price_1),
  price_2: parseFloat(page.props.data.price_2),
  active: !!page.props.data.active,
  notes: page.props.data.notes,
});

const submit = () => handleSubmit({ form, url: route('admin.product.save') });

const { filteredCategories, filterCategories } = useProductCategoryFilter(page.props.categories);

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
              <q-input v-model.trim="form.name" label="Nama Varietas" lazy-rules :error="!!form.errors.name"
                :disable="form.processing" :error-message="form.errors.name" :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]" />
              <q-select v-model="form.category_id" label="Kategori" use-input input-debounce="300" clearable
                :options="filteredCategories" map-options emit-value @filter="filterCategories" option-label="label"
                option-value="value" :error="!!form.errors.category_id" :disable="form.processing">
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section>Kategori tidak ditemukan</q-item-section>
                  </q-item>
                </template>
              </q-select>
              <q-input v-model.trim="form.uom_1" label="Satuan Distributor" lazy-rules :error="!!form.errors.uom"
                :disable="form.processing" :error-message="form.errors.uom_1" />
              <q-input v-model.trim="form.uom_2" label="Satuan" lazy-rules :error="!!form.errors.uom"
                :disable="form.processing" :error-message="form.errors.uom_2" />
              <LocaleNumberInput v-model:modelValue="form.price_1" :label="`Harga Distributor / ${form.uom_1} (Rp)`"
                lazyRules :disable="form.processing" :error="!!form.errors.price_1"
                :errorMessage="form.errors.price_1" />
              <LocaleNumberInput v-model:modelValue="form.price_2" :label="`Harga / ${form.uom_2} (Rp)`" lazyRules
                :disable="form.processing" :error="!!form.errors.price_2" :errorMessage="form.errors.price_2" />
              <div style="margin-left: -10px;">
                <q-checkbox class="full-width q-pl-none" v-model="form.active" :disable="form.processing"
                  label="Aktif" />
              </div>
              <q-input v-model.trim="form.notes" type="textarea" autogrow counter maxlength="1000" label="Catatan"
                lazy-rules :disable="form.processing" :error="!!form.errors.notes" :error-message="form.errors.notes" />
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn icon="save" type="submit" label="Simpan" color="primary" :disable="form.processing" />
              <q-btn icon="cancel" label="Batal" :disable="form.processing"
                @click="router.get(route('admin.product.index'))" />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>

  </authenticated-layout>
</template>
