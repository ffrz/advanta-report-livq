<script setup>
import { handleSubmit } from "@/helpers/client-req-handler";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";


let form = useForm({
  username: window.CONFIG.APP_DEMO ? "admin" : "",
  password: window.CONFIG.APP_DEMO ? "12345" : "",
  remember: true,
});

const submit = () => handleSubmit({ form, url: route('admin.auth.login') });
const showPassword = ref(false);

</script>

<template>
  <i-head title="Login" />
  <guest-layout>
    <q-page class="row justify-center items-center">
      <div class="column">
        <div class="row">
          <q-form class="q-gutter-md" @submit.prevent="submit">
            <q-card square bordered class="q-pa-md shadow-1">
              <q-card-section>
                <h5 class="q-my-sm text-center">Masuk</h5>
              </q-card-section>
              <q-card-section>
                <q-input v-model.trim="form.username" label="ID Pengguna" lazy-rules
                  :error="!!form.errors.username" autocomplete="username" :error-message="form.errors.username"
                  :disable="form.processing" :rules="[(val) => (val && val.length > 0) || 'Masukkan ID Pengguna',]">
                  <template v-slot:append>
                    <q-icon name="person" />
                  </template>
                </q-input>
                <q-input v-model="form.password" :type="showPassword ? 'text' : 'password'" label="Kata Sandi"
                  :error="!!form.errors.password" autocomplete="current-password" :error-message="form.errors.password"
                  lazy-rules :disable="form.processing"
                  :rules="[(val) => (val && val.length > 0) || 'Masukkan kata sandi',]">
                  <template v-slot:append>
                    <q-btn
                      dense
                      flat
                      round
                      @click="showPassword = !showPassword"
                      ><q-icon :name="showPassword ? 'key_off' : 'key'"
                    /></q-btn>
                  </template>
                </q-input>
                <q-checkbox class="q-mt-sm q-pl-none" style="margin-left: -10px;" v-model="form.remember"
                  :disable="form.processing" label="Ingat saya di perangkat ini" />
              </q-card-section>
              <q-card-actions>
                <q-btn icon="login" type="submit" color="primary" class="full-width" label="Login"
                  :disable="form.processing" />
              </q-card-actions>

            </q-card>
          </q-form>
        </div>
      </div>
    </q-page>
  </guest-layout>
</template>

<style>
.q-card {
  width: 360px;
}
</style>
