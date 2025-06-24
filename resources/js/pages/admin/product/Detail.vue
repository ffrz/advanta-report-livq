<script setup>
import { formatNumber, check_role } from "@/helpers/utils";
import { router, usePage } from "@inertiajs/vue3";
import { useQuasar } from "quasar";

const page = usePage();
const title = `Rincian Varietas #${page.props.data.id}`;
const $q = useQuasar();

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn icon="arrow_back" dense color="grey-7" flat rounded @click="router.get(route('admin.product.index'))" />
      </div>
    </template>
    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn icon="edit" dense color="primary" :disabled="!check_role($CONSTANTS.USER_ROLE_ADMIN)"
          @click="router.get(route('admin.product.edit', { id: page.props.data.id }))" />
      </div>
    </template>
    <div class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <div class="row">
          <q-card square flat bordered class="q-card col">
            <q-card-section>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width:120px">Brand</td>
                    <td style="width:1px">:</td>
                    <td>
                      {{ page.props.data.name }}
                    </td>
                  </tr>
                  <tr>
                    <td>Kategori</td>
                    <td>:</td>
                    <td>
                      {{ page.props.data.category ? page.props.data.category.name : '--Tidak memiliki kategori--' }}
                    </td>
                  </tr>
                  <template v-if="$page.props.auth.user.role != 'bs'">
                    <tr>
                      <td>Harga</td>
                      <td>:</td>
                      <td>Rp. {{ formatNumber(page.props.data.price_2) }} / {{ page.props.data.uom_2 }}</td>
                    </tr>
                    <tr>
                      <td>Harga Distributor</td>
                      <td>:</td>
                      <td>Rp. {{ formatNumber(page.props.data.price_1) }} / {{ page.props.data.uom_1 }}</td>
                    </tr>
                  </template>
                  <tr>
                    <td>Catatan</td>
                    <td>:</td>
                    <td>{{ page.props.data.notes }}</td>
                  </tr>
                  <tr>
                    <td>Status</td>
                    <td>:</td>
                    <td>
                      {{ page.props.data.active ? "Aktif" : "Tidak Aktif" }}
                    </td>
                  </tr>

                  <tr v-if="!!page.props.data.created_datetime">
                    <td>Dibuat Oleh</td>
                    <td>:</td>
                    <td>
                      <template v-if="page.props.data.created_by">
                        <i-link :href="route('admin.user.detail', { id: page.props.data.created_by_uid })">
                          {{ page.props.data.created_by.username }} - {{ page.props.data.created_by.name }}
                        </i-link>
                        -
                      </template>
                      {{ $dayjs(new Date(page.props.data.created_datetime)).format("dddd, D MMMM YYYY pukul HH:mm:ss")
                      }}
                    </td>
                  </tr>
                  <tr v-if="!!page.props.data.updated_datetime">
                    <td>Diperbarui oleh</td>
                    <td>:</td>
                    <td>
                      <template v-if="page.props.data.updated_by">
                        <i-link :href="route('admin.user.detail', { id: page.props.data.updated_by_uid })">
                          {{ page.props.data.updated_by.username }} - {{ page.props.data.updated_by.name }}
                        </i-link>
                        -
                      </template>
                      {{ $dayjs(new Date(page.props.data.updated_datetime)).format("dddd, D MMMM YYYY pukul HH:mm:ss")
                      }}
                    </td>
                  </tr>

                </tbody>
              </table>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>
  </authenticated-layout>
</template>
