<script setup>

import { scrollToFirstErrorField } from '@/helpers/utils';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const showPassword = ref(false);
const passwordInput = ref(null);
const currentPasswordInput = ref(null);
const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const updatePassword = () => {
  form.clearErrors();
  form.post(route('admin.profile.update-password'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
    onError: () => {
      if (form.errors.password) {
        form.reset('password', 'password_confirmation');
        passwordInput.value.focus();
      }
      if (form.errors.current_password) {
        form.reset('current_password');
        currentPasswordInput.value.focus();
      }
    },
  });
};
</script>

<template>
  <q-form class="row" @submit.prevent="updatePassword" @validation-error="scrollToFirstErrorField">
    <q-card square flat bordered class="col">
      <q-inner-loading :showing="form.processing">
        <q-spinner size="50px" color="primary" />
      </q-inner-loading>
      <q-card-section>
        <div class="text-h6 q-my-xs text-subtitle1">Perbarui Kata Sandi</div>
        <p class="text-caption text-grey-9">Pastikan akun anda menggunakan kata sandi acak yang panjang agar akun tetap
          aman.</p>
        <q-input v-model="form.current_password" label="Kata Sandi Sekarang" :type="showPassword ? 'text' : 'password'"
          lazy-rules autocomplete="off" :disable="form.processing" :error="!!form.errors.current_password"
          :error-message="form.errors.current_password"
          :rules="[(val) => (val && val.length > 0) || 'Kata sandi harus diisi.']">
          <template v-slot:append>
            <q-btn dense flat round @click="showPassword = !showPassword"><q-icon
                :name="showPassword ? 'key_off' : 'key'" /></q-btn>
          </template>
        </q-input>
        <q-input v-model="form.password" label="Kata Sandi Baru" :type="showPassword ? 'text' : 'password'" lazy-rules
          autocomplete="off" :disable="form.processing" :error="!!form.errors.password"
          :error-message="form.errors.password"
          :rules="[(val) => (val && val.length > 0) || 'Kata sandi harus diisi.']">
          <template v-slot:append>
            <q-btn dense flat round @click="showPassword = !showPassword"><q-icon
                :name="showPassword ? 'key_off' : 'key'" /></q-btn>
          </template>
        </q-input>
        <q-input v-model="form.password_confirmation" label="Konfirmasi Kata Sandi"
          :type="showPassword ? 'text' : 'password'" lazy-rules autocomplete="off" :disable="form.processing"
          :error="!!form.errors.password_confirmation" :error-message="form.errors.password_confirmation"
          :rules="[(val) => (val && val.length > 0) || 'Kata sandi harus diisi.']">
          <template v-slot:append>
            <q-btn dense flat round @click="showPassword = !showPassword"><q-icon
                :name="showPassword ? 'key_off' : 'key'" /></q-btn>
          </template>
        </q-input>
      </q-card-section>
      <q-card-section>
        <q-btn type="submit" color="primary" label="Perbarui Kata Sandi" :disable="form.processing" icon="save" />
      </q-card-section>
    </q-card>
  </q-form>
</template>
