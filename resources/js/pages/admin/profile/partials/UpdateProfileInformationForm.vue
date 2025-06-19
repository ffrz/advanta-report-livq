<script setup>
import { handleSubmit } from '@/helpers/client-req-handler';
import { scrollToFirstErrorField } from '@/helpers/utils';
import { useForm, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;
const form = useForm({
  name: user.name,
  username: user.username,
  role: window.CONSTANTS.USER_ROLES[user.role],
});

const submit = () =>
  handleSubmit({ form, url: route('admin.profile.update') });
</script>

<template>
  <q-form class="row" @submit.prevent="submit" @validation-error="scrollToFirstErrorField">
    <q-card square flat bordered class="col">
      <q-inner-loading :showing="form.processing">
        <q-spinner size="50px" color="primary" />
      </q-inner-loading>
      <q-card-section>
        <div class="text-h6 q-my-xs text-subtitle1">Profil Saya</div>
        <p class="text-caption text-grey-9">Perbarui profil anda.</p>
        <q-input readonly v-model="form.username" label="ID Pengguna" :disable="form.processing" />
        <q-input v-model.trim="form.name" label="Nama" :disable="form.processing" lazy-rules :error="!!form.errors.name"
          :error-message="form.errors.name" :rules="[(val) => (val && val.length > 0) || 'Name harus diisi.']" />
        <q-input readonly v-model="form.role" label="Hak Akses" :disable="form.processing" />
      </q-card-section>
      <q-card-section>
        <q-btn type="submit" color="primary" label="Perbarui Profil Saya" :disable="form.processing" icon="save" />
      </q-card-section>
    </q-card>
  </q-form>
</template>
