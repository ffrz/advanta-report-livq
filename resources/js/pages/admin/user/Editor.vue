<script setup>
import { handleSubmit } from "@/helpers/client-req-handler";
import { create_options } from "@/helpers/utils";
import { validateUsername } from "@/helpers/validations";
import { useForm, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const roles = create_options(window.CONSTANTS.USER_ROLES);
const page = usePage();
const title = !!page.props.data.id ? "Edit Pengguna" : "Tambah Pengguna";
const form = useForm({
  id: page.props.data.id,
  name: page.props.data.name,
  username: page.props.data.username,
  password: "",
  role: !!page.props.data.role ? page.props.data.role : roles[0].value,
  parent_id: page.props.data.parent_id
    ? Number(page.props.data.parent_id)
    : null,
  work_area: page.props.data.work_area ? page.props.data.work_area : null,
  active: !!page.props.data.active,
});

const users = ref([
  {
    value: null,
    label: `Tidak diset`,
    role: null,
  },
  ...page.props.users.map((user) => ({
    value: user.id,
    label: `${user.name} (${user.username})`,
    role: user.role,
  })),
]);

const computedUsers = computed(() => {
  if (form.role === window.CONSTANTS.USER_ROLE_BS) {
    return users.value.filter(
      (item) => item.role === window.CONSTANTS.USER_ROLE_AGRONOMIST
    );
  }
  if (form.role === window.CONSTANTS.USER_ROLE_AGRONOMIST) {
    return users.value.filter(
      (item) => item.role === window.CONSTANTS.USER_ROLE_ASM
    );
  }
  return users.value.filter((item) => item.role === null);
});

const submit = () => handleSubmit({ form, url: route("admin.user.save") });
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <div class="row justify-center">
      <div class="col col-md-6 q-pa-sm">
        <q-form class="row" @submit.prevent="submit">
          <q-card square flat bordered class="col">
            <q-inner-loading :showing="form.processing">
              <q-spinner size="50px" color="primary" />
            </q-inner-loading>
            <q-card-section class="q-pt-none">
              <input type="hidden" name="id" v-model="form.id" />
              <q-input
                autofocus
                v-model.trim="form.name"
                label="Nama"
                lazy-rules
                :error="!!form.errors.name"
                :disable="form.processing"
                :error-message="form.errors.name"
                :rules="[
                  (val) => (val && val.length > 0) || 'Nama harus diisi.',
                ]"
              />
              <q-input
                v-model.trim="form.username"
                type="text"
                label="ID Pengguna"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.username"
                :error-message="form.errors.username"
                :rules="[
                  (val) =>
                    (val && val.length > 0) || 'ID Pengguna harus diisi.',
                  (val) => validateUsername(val) || 'ID Pengguna tidak valid.',
                ]"
              />
              <q-input
                v-model="form.password"
                type="password"
                label="Kata Sandi"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.password"
                :error-message="form.errors.password"
              />
              <p v-if="form.id" class="text-subtitle text-grey-8 q-pt-none">
                Isi jika ingin mengatur ulang sandi.
              </p>
              <q-select
                v-model="form.role"
                label="Role"
                :options="roles"
                map-options
                emit-value
                lazy-rules
                :disable="form.processing"
                transition-show="jump-up"
                transition-hide="jump-up"
                :error="!!form.errors.role"
                :error-message="form.errors.role"
              >
              </q-select>
              <q-select
                v-model="form.parent_id"
                label="Supervisor"
                :options="computedUsers"
                map-options
                emit-value
                :error="!!form.errors.parent_id"
                :disable="form.processing"
              />
              <q-input
                v-model="form.work_area"
                type="text"
                label="Area Kerja"
                lazy-rules
                :disable="form.processing"
                :error="!!form.errors.work_area"
                :error-message="form.errors.work_area"
              />
              <div style="margin-left: -10px">
                <q-checkbox
                  class="full-width"
                  v-model="form.active"
                  :disable="form.processing"
                  label="Aktif"
                />
              </div>
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn
                icon="save"
                type="submit"
                label="Simpan"
                color="primary"
                :disable="form.processing"
                @click="submit"
              />
              <q-btn
                icon="cancel"
                label="Batal"
                class="text-black"
                :disable="form.processing"
                @click="$goBack()"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </div>
  </authenticated-layout>
</template>
