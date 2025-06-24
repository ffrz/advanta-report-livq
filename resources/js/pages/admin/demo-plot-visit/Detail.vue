<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import ImageViewer from '@/components/ImageViewer.vue'

const page = usePage();
const title = "Rincian Kunjungan Demplot";
const tab = ref("main");
const showViewer = ref(false)

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn icon="arrow_back" dense color="grey-7" flat rounded
          @click="router.get(route('admin.demo-plot.detail', { id: page.props.data.demo_plot_id }))" />
      </div>
    </template>
    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn icon="edit" dense color="primary"
          @click="router.get(route('admin.demo-plot-visit.edit', { id: page.props.data.id }))" />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <div class="row">
          <q-card square flat bordered class="col q-pa-none">
            <q-card-section class="q-pa-md">
              <div class="row items-center justify-between q-mb-md">
                <q-btn icon="arrow_left" color="grey-8" flat :disabled="!page.props.prev_visit_id" dense rounded
                  @click="router.get(route('admin.demo-plot-visit.detail', { id: page.props.prev_visit_id }))" />
                <div class="text-bold text-grey-7">Info Kunjungan</div>
                <q-btn icon-right="arrow_right" color="grey-8" flat :disabled="!page.props.next_visit_id" dense rounded
                  @click="router.get(route('admin.demo-plot-visit.detail', { id: page.props.next_visit_id }))" />
              </div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width:120px">BS</td>
                    <td>:</td>
                    <td>
                      <template v-if="page.props.data.user">
                        <my-link :href="route('admin.user.detail', { id: page.props.data.user.id })">
                          {{ page.props.data.user.name + ' - ' +
                            (page.props.data.user.username) }}
                        </my-link>
                      </template>
                      <template v-else>
                        -
                      </template>
                    </td>
                  </tr>
                  <tr>
                    <td>Varietas</td>
                    <td>:</td>
                    <td>{{ page.props.data.demo_plot.product.name }}</td>
                  </tr>
                  <tr>
                    <td>Info Lahan</td>
                    <td>:</td>
                    <td>
                      {{ page.props.data.demo_plot.owner_name }}
                      <br>{{ page.props.data.demo_plot.owner_phone ? page.props.data.demo_plot.owner_phone : '-' }}
                      <br>{{ page.props.data.demo_plot.field_location }}
                    </td>
                  </tr>
                  <tr>
                    <td>Status Tanaman</td>
                    <td>:</td>
                    <td>{{ $CONSTANTS.DEMO_PLOT_PLANT_STATUSES[page.props.data.plant_status] }}</td>
                  </tr>
                  <tr>
                    <td>Tanggal Kunjungan</td>
                    <td>:</td>
                    <td>
                      {{ $dayjs(page.props.data.visit_date).fromNow() }} -
                      {{ $dayjs(page.props.data.visit_date).format("DD MMMM YYYY") }}
                    </td>
                  </tr>
                  <tr>
                    <td>Catatan</td>
                    <td>:</td>
                    <td>{{ page.props.data.notes ? page.props.data.notes : '-' }}</td>
                  </tr>
                  <template v-if="page.props.data.image_path">
                    <tr>
                      <td colspan="3" class=" bg-white">
                        <div class="q-mt-md">
                          Dokumentasi:<br>
                          <q-img :src="`/${page.props.data.image_path}`" class="q-mt-none" style="max-width: 500px;"
                            :style="{ border: '1px solid #ddd' }" @click="showViewer = true" />
                        </div>
                      </td>
                    </tr>
                  </template>
                  <template v-if="page.props.data.latlong">
                    <tr>
                      <td colspan="3" class=" bg-white">
                        <div class="q-mt-md">
                          Lokasi:<br>
                          {{ page.props.data.latlong }}<br>
                          <div style="max-width:500px">
                            <iframe
                              :src="`https://www.google.com/maps?q=${encodeURIComponent(page.props.data.latlong)}&output=embed`"
                              width="100%" height="300" style="border:1px solid #ddd; margin-top: 10px" allowfullscreen
                              loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </template>
                  <tr>
                    <td colspan="3" class=" bg-white">
                      <div class="text-bold text-grey-8 q-mt-md">Informasi Rekaman</div>
                    </td>
                  </tr>
                  <tr>
                    <td style="width:125px">Demo Plot Visit ID</td>
                    <td style="width:1px">:</td>
                    <td>#{{ page.props.data.id }}</td>
                  </tr>
                  <tr v-if="page.props.data.created_datetime">
                    <td>Dibuat</td>
                    <td>:</td>
                    <td>
                      {{ $dayjs(page.props.data.created_datetime).fromNow() }} -
                      {{ $dayjs(page.props.data.created_datetime).format("DD MMMM YY HH:mm:ss") }}
                      <template v-if="page.props.data.created_by_user">
                        oleh
                        <my-link :href="route('admin.user.detail', { id: page.props.data.created_by_user.id })">
                          {{ page.props.data.created_by_user.username }}
                        </my-link>
                      </template>
                    </td>
                  </tr>
                  <tr v-if="page.props.data.updated_datetime">
                    <td>Diperbarui</td>
                    <td>:</td>
                    <td>
                      {{ $dayjs(page.props.data.updated_datetime).fromNow() }} -
                      {{ $dayjs(page.props.data.updated_datetime).format("DD MMMM YY HH:mm:ss") }}
                      <template v-if="page.props.data.updated_by_user">
                        oleh
                        <my-link :href="route('admin.user.detail', { id: page.props.data.updated_by_user.id })">
                          {{ page.props.data.updated_by_user.username }}
                        </my-link>
                      </template>
                    </td>
                  </tr>
                </tbody>
              </table>
              <ImageViewer v-model="showViewer" :imageUrl="`/${page.props.data.image_path}`" />
            </q-card-section>
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>
