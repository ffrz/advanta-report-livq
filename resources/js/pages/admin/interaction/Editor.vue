<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { useCustomerFilter } from "@/helpers/useCustomerFilter";
import DatePicker from "@/components/DatePicker.vue";
import dayjs from "dayjs";
import { ref, onMounted } from 'vue'

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Interaksi";

const { filteredCustomers, filterCustomerFn } = useCustomerFilter(page.props.customers);

const statuses = Object.entries(window.CONSTANTS.INTERACTION_STATUSES).map(([value, label]) => ({
  label,
  value
}));

const engagement_levels = Object.entries(window.CONSTANTS.INTERACTION_ENGAGEMENT_LEVELS).map(([value, label]) => ({
  label,
  value
}));

const types = Object.entries(window.CONSTANTS.INTERACTION_TYPES).map(([value, label]) => ({
  label,
  value
}));

const users = page.props.users.map(user => ({
  value: user.id,
  label: `${user.name} (${user.username})`,
}));

const services = page.props.services.map(service => ({
  value: service.id,
  label: `${service.name} (#${service.id})`,
}));

const form = useForm({
  id: page.props.data.id,
  user_id: page.props.data.user_id ? Number(page.props.data.user_id) : null,
  customer_id: page.props.data.customer_id ? Number(page.props.data.customer_id) : null,
  service_id: page.props.data.service_id ? Number(page.props.data.service_id) : null,
  date: dayjs(page.props.data.visit_date).format('YYYY-MM-DD'),
  type: page.props.data.type ?? 'visit',
  status: page.props.data.status,
  engagement_level: page.props.data.engagement_level ?? 'none',
  subject: page.props.data.subject,
  summary: page.props.data.summary,
  notes: page.props.data.notes,
  location: page.props.data.location,
  image_path: page.props.data.image_path,
  image: null,
});

const submit = () => handleSubmit({
  form,
  forceFormData: true,
  url: route('admin.interaction.save')
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
        form.location = `${position.coords.latitude},${position.coords.longitude}`;
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
  if (!form.location) {
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

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <q-form class="row" @submit.prevent="submit" @validation-error="scrollToFirstErrorField">
          <q-card square flat bordered class="col">
            <q-inner-loading :showing="form.processing">
              <q-spinner size="50px" color="primary" />
            </q-inner-loading>
            <q-card-section class="q-pt-none">
              <input type="hidden" name="id" v-model="form.id" />
              <input type="hidden" name="image_path" v-model="form.image_path" />
              <date-picker v-model="form.date" label="Tanggal" :error="!!form.errors.date" :disable="form.processing"
                :error-message="form.errors.date" />
              <q-select v-model="form.status" label="Status" :options="statuses" map-options emit-value
                :error-message="form.errors.status" :error="!!form.errors.status" :disable="form.processing" />
              <q-select v-model="form.type" label="Jenis" :options="types" map-options emit-value
                :error="!!form.errors.type" :disable="form.processing" />
              <q-select v-model="form.user_id" label="Sales" :options="users" map-options emit-value
                :error="!!form.errors.user_id" :disable="form.processing" />
              <q-select v-model="form.customer_id" label="Client" use-input input-debounce="300" clearable
                :options="filteredCustomers" map-options emit-value @filter="filterCustomerFn" option-label="label"
                :display-value="selectedCustomerLabel" option-value="value" :error="!!form.errors.customer_id"
                :error-message="form.errors.customer_id" :disable="form.processing">
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section>Client tidak ditemukan</q-item-section>
                  </q-item>
                </template>
              </q-select>
              <q-select v-model="form.service_id" label="Layanan" :options="services" map-options emit-value
                :error="!!form.errors.service_id" :disable="form.processing" />
              <q-select v-model="form.engagement_level" label="Engagement Level" :options="engagement_levels"
                map-options emit-value :error-message="form.errors.engagement_level"
                :error="!!form.errors.engagement_level" :disable="form.processing" />
              <q-input v-model.trim="form.subject" type="text" label="Subject" lazy-rules :disable="form.processing"
                :error="!!form.errors.subject" :error-message="form.errors.subject" :rules="[
                  (val) => (val && val.length > 0) || 'Subject harus diisi.',
                ]" />
              <q-input v-model.trim="form.summary" type="textarea" autogrow counter maxlength="255" label="Summary"
                lazy-rules :disable="form.processing" :error="!!form.errors.summary" :error-message="form.errors.notes"
                :rules="[]" />
              <q-input v-model.trim="form.notes" type="textarea" autogrow counter maxlength="255" label="Catatan"
                lazy-rules :disable="form.processing" :error="!!form.errors.notes" :error-message="form.errors.notes"
                :rules="[]" />
              <div>
                <q-btn label="Ambil Foto" size="sm" @click="triggerInput" color="secondary" icon="add_a_photo"
                  :disable="form.processing" />
                <!-- Tombol buang -->
                <q-btn class="q-ml-sm" size="sm" icon="close" label="Buang" :disable="form.processing || !imagePreview" color="red"
                  @click="clearImage" />
                <input type="file" ref="fileInput" accept="image/*" capture="environment" style="display: none"
                  @change="onFileChange" />
                <q-img v-if="imagePreview" :src="imagePreview" class="q-mt-md" style="max-width: 500px;"
                  :style="{ border: '1px solid #ddd' }">
                  <template v-slot:error>
                    <div class="text-negative text-center q-pa-md">Gambar tidak tersedia</div>
                  </template>
                </q-img>
              </div>
              <div class="q-my-md">
                <span class="text-subtitle2 text-bold text-grey-9">Lokasi:</span>
                <span class="q-mr-sm">
                  <template v-if="form.location" class="q-mt-sm">
                    ({{ form.location.split(',')[0] }}, {{ form.location.split(',')[1] }})
                  </template>
                  <template v-else>
                    Belum tersedia
                  </template>
                </span>
                <q-btn size="sm" label="Perbarui Lokasi" color="grey-8" :disable="form.processing"
                  @click="updateLocation()" />
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
