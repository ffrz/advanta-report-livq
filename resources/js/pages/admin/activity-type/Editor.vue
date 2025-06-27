<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Jenis Kegiatan";

const form = useForm({
  id: page.props.data.id,
  name: page.props.data.name,
  default_quarter_target: Number(page.props.data.default_quarter_target),
  default_month1_target: Number(page.props.data.default_month1_target),
  default_month2_target: Number(page.props.data.default_month2_target),
  default_month3_target: Number(page.props.data.default_month3_target),
  weight: Number(page.props.data.weight),
  description: page.props.data.description,
  active: !!page.props.data.active,
});

const submit = () =>
  handleSubmit({ form, url: route("admin.activity-type.save") });

const updateBreakdown = () => {
  const total = form.default_quarter_target || 0;
  form.default_month1_target = 0;
  form.default_month2_target = 0;
  form.default_month3_target = 0;

  if (total <= 0) return;

  const base = Math.floor(total / 3);
  const remainder = total % 3;

  // Mulai dengan base di semua bulan
  const breakdown = [base, base, base];

  // Distribusikan sisa ke bulan 3 → 2 → 1
  for (let i = 0; i < remainder; i++) {
    breakdown[2 - i] += 1;
  }

  [
    form.default_month1_target,
    form.default_month2_target,
    form.default_month3_target,
  ] = breakdown;
};
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <q-form
          class="row"
          @submit.prevent="submit"
          @validation-error="scrollToFirstErrorField"
        >
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-md">
              <input type="hidden" name="id" v-model="form.id" />
              <q-input
                autofocus
                v-model.trim="form.name"
                label="Nama Kegiatan"
                lazy-rules
                :error="!!form.errors.name"
                :disable="form.processing"
                :error-message="form.errors.name"
                :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]"
              />
              <LocaleNumberInput
                v-model:modelValue="form.default_quarter_target"
                label="Default Target Kuartal"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.default_quarter_target"
                :errorMessage="form.errors.default_quarter_target"
                @change="updateBreakdown()"
              />
              <LocaleNumberInput
                v-model:modelValue="form.default_month1_target"
                label="Default Target Bulan 1 (Break down)"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.default_month1_target"
                :errorMessage="form.errors.default_month1_target"
              />
              <LocaleNumberInput
                v-model:modelValue="form.default_month2_target"
                label="Default Target Bulan 2 (Break down)"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.default_month2_target"
                :errorMessage="form.errors.default_month2_target"
              />
              <LocaleNumberInput
                v-model:modelValue="form.default_month3_target"
                label="Default Target Bulan 3 (Break down)"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.default_month3_target"
                :errorMessage="form.errors.default_month3_target"
              />
              <LocaleNumberInput
                v-model:modelValue="form.weight"
                label="Bobot"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.weight"
                :errorMessage="form.errors.weight"
              />
              <div style="margin-left: -10px">
                <q-checkbox
                  class="full-width q-pl-none"
                  v-model="form.active"
                  :disable="form.processing"
                  label="Aktif"
                />
              </div>
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
                @click="router.get(route('admin.activity-type.index'))"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>
