<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Client";

const users = page.props.users.map(user => ({
  value: user.id,
  label: `${user.name} (${user.username})`,
}));

const form = useForm({
  id: page.props.data.id,
  name: page.props.data.name,
  phone: page.props.data.phone,
  email: page.props.data.email,
  address: page.props.data.address,
  company: page.props.data.company,
  business_type: page.props.data.business_type,
  active: !!page.props.data.active,
  source: page.props.data.source,
  notes: page.props.data.notes,
  assigned_user_id: page.props.data.assigned_user_id ? Number(page.props.data.assigned_user_id) : null,
});

const submit = () =>
  handleSubmit({ form, url: route('admin.customer.save') });

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
              <q-input autofocus v-model.trim="form.name" label="Nama" lazy-rules :error="!!form.errors.name"
                :disable="form.processing" :error-message="form.errors.name" :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]" />
              <q-input v-model.trim="form.company" label="Nama Perusahaan" lazy-rules :error="!!form.errors.company"
                :disable="form.processing" :error-message="form.errors.company" :rules="[]" />
              <q-input v-model.trim="form.business_type" label="Jenis Usaha" lazy-rules
                :error="!!form.errors.business_type" :disable="form.processing"
                :error-message="form.errors.business_type" :rules="[]" />
              <q-input v-model.trim="form.phone" type="text" label="No HP" lazy-rules :disable="form.processing"
                :error="!!form.errors.phone" :error-message="form.errors.phone" :rules="[
                  (val) => (val && val.length > 0) || 'No HP harus diisi.',
                ]" />
              <q-input v-model.trim="form.address" type="textarea" autogrow counter maxlength="1000" label="Alamat"
                lazy-rules :disable="form.processing" :error="!!form.errors.address" :error-message="form.address"
                :rules="[]" />
              <q-input v-model.trim="form.source" type="text" label="Sumber" lazy-rules :disable="form.processing"
                :error="!!form.errors.source" :error-message="form.errors.source" :rules="[]" />
              <q-input v-model.trim="form.email" type="email" label="Alamat Email" lazy-rules :disable="form.processing"
                :error="!!form.errors.email" :error-message="form.errors.email" :rules="[
                  val => !val || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val) || 'Format email tidak valid'
                ]" />
              <q-select v-model="form.assigned_user_id" label="Assigned To" :options="users" map-options emit-value
                :error="!!form.errors.assigned_user_id" :disable="form.processing" />
              <q-input v-model.trim="form.notes" type="textarea" autogrow counter maxlength="1000" label="Catatan"
                lazy-rules :disable="form.processing" :error="!!form.errors.notes" :error-message="form.errors.notes" />
              <div style="margin-left: -10px;">
                <q-checkbox class="full-width q-pl-none" v-model="form.active" :disable="form.processing"
                  label="Aktif" />
              </div>
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
