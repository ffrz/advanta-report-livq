<script setup>

import { handleSubmit } from '@/helpers/client-req-handler';
import { scrollToFirstErrorField } from '@/helpers/utils';
import { validateEmail } from '@/helpers/validations';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const nameInputRef = ref();
const page = usePage();
const form = useForm({
  name: page.props.data.name,
  phone: page.props.data.phone,
  address: page.props.data.address,
  logo_path: page.props.data.logo_path,
  logo_image: null,
});

const fileInput = ref(null)
const imagePreview = ref('')

onMounted(() => {
  if (form.logo_path) {
    imagePreview.value = `/${form.logo_path}`;
  }
})

function triggerInput() {
  fileInput.value.click()
}

function onFileChange(event) {
  const file = event.target.files[0]
  if (file) {
    form.logo_image = file
    imagePreview.value = URL.createObjectURL(file)
  }
}

function clearImage() {
  form.logo_image = null
  form.logo_path = null
  imagePreview.value = null
  fileInput.value.value = null
}

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
        <input type="hidden" name="logo_path" :value="form.logo_path" />
        <div class="text-subtitle1 q-my-xs">Profil Perusahaan</div>
        <p class="text-caption text-grey-9">Perbarui profil perusahaan anda.</p>
        <q-input ref="nameInputRef" v-model.trim="form.name" label="Nama Perusahaan" :disable="form.processing"
          lazy-rules :error="!!form.errors.name" :error-message="form.errors.name"
          :rules="[(val) => (val && val.length > 0) || 'Nama Perusahaan harus diisi.']" />
        <q-input v-model.trim="form.phone" label="No Telepon" :disable="form.processing" lazy-rules
          :error="!!form.errors.phone" :error-message="form.errors.phone" />
        <q-input type="textarea" counter autogrow maxlength="1000" v-model.trim="form.address" label="Alamat Perusahaan"
          :disable="form.processing" lazy-rules :error="!!form.errors.address" :error-message="form.errors.address" />
        <div>
          <div class="text-subtitle1 q-my-sm">Logo:</div>
          <q-btn label="Browse Logo" size="sm" @click="triggerInput" color="secondary" icon="add_a_photo"
            :disable="form.processing" />
          <!-- Tombol buang -->
          <q-btn class="q-ml-sm" size="sm" icon="close" label="Buang" :disable="form.processing || !imagePreview"
            color="red" @click="clearImage" />
          <input type="file" ref="fileInput" accept="image/*" capture="environment" style="display: none"
            @change="onFileChange" />
          <div>
            <q-img :src="imagePreview" class="q-mt-md" :ratio="1" spinner-color="white"
              style="width: 240px; border: 1px solid #ddd;">
              <template v-slot:error>
                <div class="text-negative text-center q-pa-md">Gambar tidak tersedia</div>
              </template>
              <template v-slot:default v-if="!imagePreview">
                <div class="absolute-full text-subtitle2 flex flex-center">Logo belum diset</div>
              </template>
            </q-img>
            <p class="q-my-sm text-grey-9">Logo akan di resize ke ukuran 240px x 240px</p>
          </div>
        </div>
      </q-card-section>
      <q-card-section>
        <q-btn icon="save" type="submit" color="primary" label="Simpan" :disable="form.processing" />
      </q-card-section>
    </q-card>
  </q-form>
</template>
