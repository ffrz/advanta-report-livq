<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import {
  scrollToFirstErrorField,
  create_year_options,
  create_quarter_options,
} from "@/helpers/utils";
import dayjs from "dayjs";

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

const quarters = create_quarter_options(currentYear);

const defaultTargets = {};
page.props.types.forEach((type) => {
  const id = type.id;
  defaultTargets[id] = {
    q: type.default_quarter_target ?? 0,
    m1: type.default_month1_target ?? 0,
    m2: type.default_month2_target ?? 0,
    m3: type.default_month3_target ?? 0,
  };
});

// Gabungkan data dari page.props.data.targets jika ada
if (page.props.data.targets) {
  page.props.data.targets.forEach((target) => {
    if (defaultTargets[target.type_id]) {
      defaultTargets[target.type_id] = {
        q: target.q ?? 0,
        m1: target.m1 ?? 0,
        m2: target.m2 ?? 0,
        m3: target.m3 ?? 0,
      };
    }
  });
}

const form = useForm({
  id: page.props.data.id,
  user_id: page.props.data.user_id ? Number(page.props.data.user_id) : [],
  quarter: `${page.props.data.year}-q${page.props.data.quarter}`,
  targets: defaultTargets,
  notes: page.props.data.notes,
});

const submit = () =>
  handleSubmit({
    form,
    forceFormData: true,
    url: route("admin.activity-target.save"),
  });

function getQuarterError(typeId) {
  const target = form.targets[typeId];
  if (!target) return false;

  const q = Number(target.q) || 0;
  const m1 = Number(target.m1) || 0;
  const m2 = Number(target.m2) || 0;
  const m3 = Number(target.m3) || 0;

  return q !== m1 + m2 + m3;
}
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
                v-model="form.user_id"
                label="BS"
                :options="users"
                map-options
                emit-value
                :error="!!form.errors.user_id"
                :disable="form.processing"
                :error-message="form.errors.user_id"
                hide-bottom-space
              />
              <q-select
                v-model="form.quarter"
                label="Kwartal"
                :options="quarters"
                map-options
                emit-value
                :error="!!form.errors.quarter"
                :disable="form.processing"
                :error-message="form.errors.quarter"
                hide-bottom-space
              />
              <div
                v-for="(type, index) in types"
                :key="type.id || index"
                class="q-mb-sm"
              >
                <div class="text-subtitle2 q-mb-xs q-mt-sm">
                  {{ `Target ${type.label}` }}
                </div>
                <div class="row q-col-gutter-sm">
                  <div class="col-3">
                    <q-input
                      v-model.number="form.targets[type.value].q"
                      type="number"
                      label="Kuartal"
                      dense
                      outlined
                      :disable="form.processing"
                      hide-bottom-space
                    />
                  </div>
                  <div class="col-3">
                    <q-input
                      v-model.number="form.targets[type.value].m1"
                      type="number"
                      label="Bulan 1"
                      dense
                      outlined
                      :disable="form.processing"
                      hide-bottom-space
                    />
                  </div>
                  <div class="col-3">
                    <q-input
                      v-model.number="form.targets[type.value].m2"
                      type="number"
                      label="Bulan 2"
                      dense
                      outlined
                      :disable="form.processing"
                      hide-bottom-space
                    />
                  </div>
                  <div class="col-3">
                    <q-input
                      v-model.number="form.targets[type.value].m3"
                      type="number"
                      label="Bulan 3"
                      dense
                      outlined
                      :disable="form.processing"
                      hide-bottom-space
                    />
                  </div>
                </div>
                <!-- Pesan Error Jika q ≠ m1 + m2 + m3 -->
                <div
                  v-if="getQuarterError(type.value)"
                  class="text-negative text-caption q-mt-xs"
                >
                  Jumlah bulan tidak sama dengan target kuartal
                </div>
              </div>
              <q-input
                v-model.trim="form.notes"
                type="textarea"
                autogrow
                counter
                maxlength="250"
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
