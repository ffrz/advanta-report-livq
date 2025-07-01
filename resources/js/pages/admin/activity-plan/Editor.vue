<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import DatePicker from "@/components/DatePicker.vue";
import dayjs from "dayjs";
import { onMounted } from "vue";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Rencana Kegiatan";

const users = page.props.users.map((u) => ({
  value: u.id,
  label: `${u.name} (${u.username})`,
}));

const types = page.props.types.map((t) => ({
  value: t.id,
  label: `${t.name}`,
  require_product: Number(t.require_product) === 1,
}));

const products = page.props.products.map((p) => ({
  value: p.id,
  label: `${p.name}`,
}));

const form = useForm({
  id: page.props.data.id,
  user_id: page.props.data.user_id ? Number(page.props.data.user_id) : null,
  date: dayjs(page.props.data.date).format("YYYY-MM-DD"),
  notes: page.props.data.notes,
  total_cost: page.props.data.total_cost
    ? Number(page.props.data.total_cost)
    : 0,
});

const submit = () =>
  handleSubmit({
    form,
    forceFormData: true,
    url: route("admin.activity-plan.save"),
  });

onMounted(() => {
  if (page.props.auth.user.role == window.CONSTANTS.USER_ROLE_BS) {
    form.user_id = page.props.auth.user.id;
  }
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
              <date-picker
                v-model="form.date"
                label="Tanggal"
                :error="!!form.errors.date"
                :disable="form.processing"
                :error-message="form.errors.date"
              />
              <LocaleNumberInput
                v-model:modelValue="form.total_cost"
                readonly
                label="Total Budget (Rp)"
                lazyRules
                :disable="form.processing"
                :error="!!form.errors.total_cost"
                :errorMessage="form.errors.total_cost"
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
