<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import DatePicker from "@/components/DatePicker.vue";
import dayjs from "dayjs";
import { ref, onMounted } from 'vue'

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Demo Plot";
const plant_statuses = Object.entries(window.CONSTANTS.DEMO_PLOT_PLANT_STATUSES).map(([value, label]) => ({
  label,
  value
}));

const users = page.props.users.map(user => ({
  value: user.id,
  label: `${user.name} (${user.username})`,
}));

const products = page.props.products.map(product => ({
  value: product.id,
  label: `${product.name}`,
}));

const form = useForm({
  id: page.props.data.id,
  user_id: page.props.data.user_id ? Number(page.props.data.user_id) : null,
  product_id: page.props.data.product_id ? Number(page.props.data.product_id) : null,
  owner_name: page.props.data.owner_name,
  owner_phone: page.props.data.owner_phone,
  field_location: page.props.data.field_location,
  plant_date: dayjs(page.props.data.plant_date).format('YYYY-MM-DD'),
  plant_status: page.props.data.plant_status,
  notes: page.props.data.notes,
  active: !!page.props.data.active,
  latlong: page.props.data.latlong,
  image_path: page.props.data.image_path,
  image: null,
});

const submit = () => handleSubmit({
  form,
  forceFormData: true,
  url: route('admin.demo-plot.save')
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
            <q-card-section class="q-pt-md">
              <input type="hidden" name="id" v-model="form.id" />
              <input type="hidden" name="image_path" v-model="form.image_path" />
              <q-select v-model="form.user_id" label="BS" :options="users" map-options emit-value
                :error="!!form.errors.user_id" :disable="form.processing" :error-message="form.errors.user_id" />
              <q-select v-model="form.product_id" label="Varietas" :options="products" map-options emit-value
                :error="!!form.errors.product_id" :disable="form.processing" :error-message="form.errors.product_id" />
              <date-picker v-model="form.plant_date" label="Tanggal Tanam" :error="!!form.errors.plant_date"
                :disable="form.processing" :error-message="form.errors.plant_date" />
              <q-select v-model="form.plant_status" label="Status" :options="plant_statuses" map-options emit-value
                :error-message="form.errors.plant_status" :error="!!form.errors.plant_status"
                :disable="form.processing" />
              <q-input v-model.trim="form.owner_name" type="text" label="Pemilik Lahan" lazy-rules
                :disable="form.processing" :error="!!form.errors.owner_name" :error-message="form.errors.owner_name"
                :rules="[
                  (val) => (val && val.length > 0) || 'Pemilik lahan harus diisi.',
                ]" />
              <q-input v-model.trim="form.owner_phone" type="text" label="No Telepon Pemilik" lazy-rules
                :disable="form.processing" :error="!!form.errors.owner_phone" :error-message="form.errors.owner_phone"
                :rules="[]" />
              <q-input v-model.trim="form.field_location" type="text" label="Lokasi" lazy-rules
                :disable="form.processing" :error="!!form.errors.field_location"
                :error-message="form.errors.field_location" :rules="[
                  (val) => (val && val.length > 0) || 'Lokasi harus diisi.',
                ]" />
              <q-input v-model.trim="form.notes" type="textarea" autogrow counter maxlength="255" label="Catatan"
                lazy-rules :disable="form.processing" :error="!!form.errors.notes" :error-message="form.errors.notes"
                :rules="[]" />
              <div style="margin-left: -10px;">
                <q-checkbox class="full-width q-pl-none" v-model="form.active" :disable="form.processing"
                  label="Demplot Aktif" />
              </div>
              <div class="q-pt-md">
                <div class="text-subtitle2 text-bold text-grey-9">Foto:</div>
                <q-btn label="Ambil Foto" size="sm" @click="triggerInput" color="secondary" icon="add_a_photo"
                  :disable="form.processing" />
                <!-- Tombol buang -->
                <q-btn class="q-ml-sm" size="sm" icon="close" label="Buang" :disable="form.processing || !imagePreview"
                  color="red" @click="clearImage" />
                <input type="file" ref="fileInput" accept="image/*" style="display: none" @change="onFileChange" />
                <div>
                  <q-img v-if="imagePreview" :src="imagePreview" class="q-mt-md" style="max-width: 500px;"
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
                  <q-btn size="sm" label="Perbarui Lokasi" color="secondary" :disable="form.processing"
                    @click="updateLocation()" />
                  <q-btn size="sm" icon="delete" label="Hapus Lokasi" color="red-9"
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
