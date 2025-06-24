<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import { plantAge } from '@/helpers/utils';
import ImageViewer from '@/components/ImageViewer.vue'
const page = usePage();
const showViewer = ref(false)
</script>

<template>
  <table class="detail">
    <tbody>
      <tr>
        <td style="width:150px">BS</td>
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
        <td>{{ page.props.data.product.name }}</td>
      </tr>
      <tr>
        <td>Tanggal Tanam</td>
        <td>:</td>
        <td>
          {{ $dayjs(page.props.data.plant_date).fromNow() }} -
          {{ $dayjs(page.props.data.plant_date).format("DD MMMM YYYY") }}
          <template v-if="page.props.data.active">
            <br> ({{ plantAge(page.props.data.plant_date) }} hari)
          </template>
        </td>
      </tr>
      <tr>
        <td>Pemilik Lahan</td>
        <td>:</td>
        <td>{{ page.props.data.owner_name }}</td>
      </tr>
      <tr>
        <td>No Telepon</td>
        <td>:</td>
        <td>{{ page.props.data.owner_phone ? page.props.data.owner_phone : '-' }}</td>
      </tr>
      <tr>
        <td>Lokasi Lahan</td>
        <td>:</td>
        <td>{{ page.props.data.field_location }}</td>
      </tr>
      <tr>
        <td>Status Terkini</td>
        <td>:</td>
        <td>{{ $CONSTANTS.DEMO_PLOT_PLANT_STATUSES[page.props.data.plant_status] }}</td>
      </tr>
      <tr>
        <td>Terakhir Dikunjungi</td>
        <td>:</td>
        <td>
          <template v-if="page.props.data.last_visit">
            {{ $dayjs(page.props.data.last_visit).fromNow() }} -
            {{ $dayjs(page.props.data.last_visit).format("DD MMMM YYYY") }}
          </template>
          <template v-else>
            <span>Belum pernah</span>
          </template>
          <!-- <br>
          <q-btn label="Buat Kunjungan" color="secondary" size="sm" icon="add"
            @click="router.get(route('admin.demo-plot-visit.add', { demo_plot_id: page.props.data.id }))" /> -->
        </td>
      </tr>
      <tr>
        <td>Status Demo Plot</td>
        <td>:</td>
        <td>{{ page.props.data.active ? 'Aktif' : 'Tidak Aktif' }}</td>
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
              Foto Lahan:<br>
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
        <td style="width:125px">Demo Plot ID</td>
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

</template>
