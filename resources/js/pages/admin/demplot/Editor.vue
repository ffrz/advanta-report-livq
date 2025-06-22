<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import DatePicker from "@/components/DatePicker.vue";
import dayjs from "dayjs";
import { ref, onMounted } from 'vue'

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Demplot";

const users = page.props.users.map(user => ({
  value: user.id,
  label: `${user.name} (${user.username})`,
}));

const varieties = page.props.varieties.map(item => ({
  value: item.id,
  label: `${item.name}`,
}));

const form = useForm({
  id: page.props.data.id,
  user_id: page.props.data.user_id ? Number(page.props.data.user_id) : null,
  variety_id: page.props.data.variety_id ? Number(page.props.data.variety_id) : null,
  date: dayjs(page.props.data.date).format('YYYY-MM-DD'),
  notes: page.props.data.notes,
  location: page.props.data.location,
  latlong: page.props.data.latlong,
  image_path: page.props.data.image_path,
  image: null,
});

const submit = () => handleSubmit({
  form,
  forceFormData: true,
  url: route('admin.demplot.save')
});

const fileInput = ref(null)
const imagePreview = ref('')

function triggerInput() {
  fileInput.value.click()
}

function onFileChange(event) {
  const file = event.target.files[0]
  if (file) {
    form.image = file
    imagePreview.value = URL.createObjectURL(file)
  }
}

function updateLocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        form.latlong = `${position.coords.latitude},${position.coords.longitude}`;
      },
      (error) => {
        alert('Gagal mendapatkan lokasi: ' + error.message)
      }
    )
  } else {
    alert('Geolocation tidak didukung browser ini.')
  }
}

onMounted(() => {
  if (!form.id) {
    updateLocation();
  }

  if (form.image_path) {
    imagePreview.value = `/${form.image_path}`;
  }
})

function clearImage() {
  form.image = null
  form.image_path = null
  imagePreview.value = null
  fileInput.value.value = null
}

function removeLocation() {
  form.latlong = null;
}

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
              <input type="hidden" name="image_path" v-model="form.image_path" />
              <date-picker v-model="form.date" label="Tanggal Tanam" :error="!!form.errors.date"
                :disable="form.processing" :error-message="form.errors.date" />
              <q-select v-model="form.user_id" label="BS" :options="users" map-options emit-value
                :error="!!form.errors.user_id" :disable="form.processing" />
              <q-select v-model="form.variety_id" label="Varietas" :options="varieties" map-options emit-value
                :error="!!form.errors.variety_id" :error-message="form.errors.variety_id" :disable="form.processing" />
              <q-input v-model.trim="form.location" type="text" label="Lokasi" lazy-rules :disable="form.processing"
                :error="!!form.errors.location" :error-message="form.errors.location" :rules="[
                  (val) => (val && val.length > 0) || 'Lokasi harus diisi.',
                ]" />
              <q-input v-model.trim="form.notes" type="textarea" autogrow counter maxlength="255" label="Catatan"
                lazy-rules :disable="form.processing" :error="!!form.errors.notes" :error-message="form.errors.notes"
                :rules="[]" />
              <div>
                <q-btn label="Ambil Foto" size="sm" @click="triggerInput" color="secondary" icon="add_a_photo"
                  :disable="form.processing" />
                <!-- Tombol buang -->
                <q-btn class="q-ml-sm" size="sm" icon="close" label="Buang" :disable="form.processing || !imagePreview"
                  color="red" @click="clearImage" />
                <input type="file" ref="fileInput" accept="image/*" capture="environment" style="display: none"
                  @change="onFileChange" />
                <div>
                  <q-img v-if="imagePreview" :src="imagePreview" class="q-mt-md" style="max-width: 500px;" :ratio="1"
                    :style="{ border: '1px solid #ddd' }">
                    <template v-slot:error>
                      <div class="text-negative text-center q-pa-md">Gambar tidak tersedia</div>
                    </template>
                  </q-img>
                </div>
              </div>
              <div class="q-my-md">
                <div>
                  <span class="text-subtitle2 text-bold text-grey-9">Koordinat:</span>
                  <span class="q-mr-sm">
                    <template v-if="form.latlong" class="q-mt-sm">
                      ({{ form.latlong.split(',')[0] }}, {{ form.latlong.split(',')[1] }})
                    </template>
                    <template v-else>
                      Belum tersedia
                    </template>
                  </span>
                </div>
                <div>
                  <q-btn size="sm" label="Perbarui Koordinat" color="secondary" :disable="form.processing"
                    @click="updateLocation()" />
                  <q-btn size="sm" icon="delete" label="Hapus Koordinat" color="red-9"
                    :disable="!form.latlong || form.processing" class="q-ml-sm" @click="removeLocation()" />
                </div>
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
