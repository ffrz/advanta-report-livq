<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import DatePicker from "@/components/DatePicker.vue";
import dayjs from "dayjs";
import { ref, onMounted, computed } from "vue";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
//import CostDetailEditor from "./partial/CostDetailEditor.vue";
const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Rencana Kegiatan";
//const showBreakdown = ref(false);
const users = page.props.users.map((u) => ({
  value: u.id,
  label: `${u.name} (${u.username})`,
}));

const types = page.props.types.map((t) => ({
  value: t.id,
  label: `${t.name}`,
  require_product: t.require_product,
}));

const products = page.props.products.map((p) => ({
  value: p.id,
  label: `${p.name}`,
}));

const form = useForm({
  id: page.props.data.id,
  user_id: page.props.data.user_id ? Number(page.props.data.user_id) : null,
  type_id: page.props.data.type_id ? Number(page.props.data.type_id) : null,
  product_id: page.props.data.product_id
    ? Number(page.props.data.product_id)
    : null,
  date: dayjs(page.props.data.date).format("YYYY-MM-DD"),
  notes: page.props.data.notes,
  cost: page.props.data.cost ? Number(page.props.data.cost) : 0,
  location: page.props.data.location,
  latlong: page.props.data.latlong,
  image_path: page.props.data.image_path,
  image: null,
  cost_details: [],
});

const submit = () =>
  handleSubmit({
    form,
    forceFormData: true,
    url: route("admin.activity-plan.save"),
  });

const fileInput = ref(null);
const imagePreview = ref("");

function triggerInput() {
  fileInput.value.click();
}

function onFileChange(event) {
  const file = event.target.files[0];
  if (file) {
    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
  }
}

function updateLocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        form.latlong = `${position.coords.latitude},${position.coords.longitude}`;
      },
      (error) => {
        alert("Gagal mendapatkan lokasi: " + error.message);
      }
    );
  } else {
    alert("Geolocation tidak didukung browser ini.");
  }
}

onMounted(() => {
  // ga usah update lokasi, ini bakalan diganti dengan pin manual karena perencanaan
  // if (!form.id) {
  //   updateLocation();
  // }

  if (page.props.auth.user.role == window.CONSTANTS.USER_ROLE_BS) {
    form.user_id = page.props.auth.user.id;
  }

  if (form.image_path) {
    imagePreview.value = `/${form.image_path}`;
  }
});

function clearImage() {
  form.image = null;
  form.image_path = null;
  imagePreview.value = null;
  fileInput.value.value = null;
}

function removeLocation() {
  form.latlong = null;
}

const showProductField = computed(() => {
  console.log(form.type_id, types);
  const selectedType = types.find((t) => t.value === form.type_id);
  return !!selectedType?.require_product;
});
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
              <input
                type="hidden"
                name="image_path"
                v-model="form.image_path"
              />
              <q-select
                v-model="form.user_id"
                label="BS"
                :options="users"
                map-options
                emit-value
                v-show="
                  $page.props.auth.user.role == $CONSTANTS.USER_ROLE_ADMIN
                "
                :error="!!form.errors.user_id"
                :disable="form.processing"
                :error-message="form.errors.user_id"
              />
              <q-select
                v-model="form.type_id"
                label="Kegiatan"
                :options="types"
                map-options
                emit-value
                :error="!!form.errors.type_id"
                :disable="form.processing"
                :error-message="form.errors.type_id"
              />
              <q-select
                v-if="showProductField"
                v-model="form.product_id"
                label="Varietas"
                :options="products"
                map-options
                emit-value
                :error="!!form.errors.product_id"
                :disable="form.processing"
                :error-message="form.errors.product_id"
              />
              <date-picker
                v-model="form.date"
                label="Tanggal"
                :error="!!form.errors.date"
                :disable="form.processing"
                :error-message="form.errors.date"
              />
              <!-- <div class="row items-center q-gutter-sm">
                <div class="col"> -->
              <LocaleNumberInput
                v-model:modelValue="form.cost"
                label="Anggaran Biaya (Rp)"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.cost"
                :errorMessage="form.errors.cost"
                :rules="[]"
              />
              <!-- </div>
                <q-btn
                  flat
                  dense
                  round
                  icon="tune"
                  color="primary"
                  @click="showBreakdown = true"
                  aria-label="Breakdown"
                />
              </div> -->
              <q-input
                v-model.trim="form.location"
                type="textarea"
                autogrow
                counter
                maxlength="255"
                label="Lokasi"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.location"
                :error-message="form.errors.location"
                :rules="[]"
              />
              <q-input
                v-model.trim="form.notes"
                type="textarea"
                autogrow
                counter
                maxlength="255"
                label="Catatan"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.notes"
                :error-message="form.errors.notes"
                :rules="[]"
              />
              <div class="q-pt-md">
                <div class="text-subtitle2 text-bold text-grey-9">Foto:</div>
                <q-btn
                  label="Ambil Foto"
                  size="sm"
                  @click="triggerInput"
                  color="secondary"
                  icon="add_a_photo"
                  :disable="form.processing"
                />

                <q-btn
                  class="q-ml-sm"
                  size="sm"
                  icon="close"
                  label="Buang"
                  :disable="form.processing || !imagePreview"
                  color="red"
                  @click="clearImage"
                />
                <input
                  type="file"
                  ref="fileInput"
                  accept="image/*"
                  style="display: none"
                  @change="onFileChange"
                />
                <div>
                  <q-img
                    v-if="imagePreview"
                    :src="imagePreview"
                    class="q-mt-md"
                    style="max-width: 500px"
                    :style="{ border: '1px solid #ddd' }"
                  >
                    <template v-slot:error>
                      <div class="text-negative text-center q-pa-md">
                        Gambar tidak tersedia
                      </div>
                    </template>
                  </q-img>
                </div>
              </div>
              <!-- Ini akan diganti dengan pin lokasi manual -->
              <div class="q-my-md" v-if="false">
                <div>
                  <span class="text-subtitle2 text-bold text-grey-9"
                    >Koordinat:</span
                  >
                  <span class="q-mr-sm">
                    <template v-if="form.latlong" class="q-mt-sm">
                      ({{ form.latlong.split(",")[0] }},
                      {{ form.latlong.split(",")[1] }})
                    </template>
                    <template v-else> Belum tersedia </template>
                  </span>
                </div>
                <div>
                  <q-btn
                    size="sm"
                    label="Perbarui Lokasi"
                    color="secondary"
                    :disable="form.processing"
                    @click="updateLocation()"
                  />
                  <q-btn
                    size="sm"
                    icon="delete"
                    label="Hapus Lokasi"
                    color="red-9"
                    :disable="!form.latlong || form.processing"
                    class="q-ml-sm"
                    @click="removeLocation()"
                  />
                </div>
              </div>
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

    <!-- <CostDetailEditor v-model="showBreakdown" /> -->
  </authenticated-layout>
</template>
