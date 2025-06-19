<script setup>

import { handleSubmit } from '@/helpers/client-req-handler';
import { scrollToFirstErrorField } from '@/helpers/utils';
import { validateEmail } from '@/helpers/validations';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const nameInputRef = ref();
const page = usePage();
const form = useForm({
  name: page.props.data.name,
  phone: page.props.data.phone,
  address: page.props.data.address,
});

const submit = () =>
  handleSubmit({ form, url: route('admin.company-profile.update') });

</script>

<template>
  <q-form class="row" @submit.prevent="submit" @validation-error="scrollToFirstErrorField">
    <q-card flat bordered square class="col">
      <q-inner-loading :showing="form.processing">
        <q-spinner size="50px" color="primary" />
      </q-inner-loading>
      <q-card-section>
        <div class="text-subtitle1 q-my-xs">Profil Perusahaan</div>
        <p class="text-caption text-grey-9">Perbarui profil perusahaan anda.</p>
        <q-input ref="nameInputRef" v-model.trim="form.name" label="Nama Perusahaan" :disable="form.processing"
          lazy-rules :error="!!form.errors.name" :error-message="form.errors.name"
          :rules="[(val) => (val && val.length > 0) || 'Nama Perusahaan harus diisi.']" />
        <q-input v-model.trim="form.phone" label="No Telepon" :disable="form.processing" lazy-rules
          :error="!!form.errors.phone" :error-message="form.errors.phone" />
        <q-input type="textarea" counter autogrow maxlength="1000" v-model.trim="form.address" label="Alamat Perusahaan"
          :disable="form.processing" lazy-rules :error="!!form.errors.address" :error-message="form.errors.address" />
      </q-card-section>
      <q-card-section>
        <q-btn icon="save" type="submit" color="primary" label="Simpan" :disable="form.processing" />
      </q-card-section>
    </q-card>
  </q-form>
</template>
