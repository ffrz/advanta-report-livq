<script setup>
import { usePage } from "@inertiajs/vue3";
import { ref } from "vue";
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
        <td>Kegiatan</td>
        <td>:</td>
        <td>{{ page.props.data.type.name }}</td>
      </tr>
      <tr>
        <td>Tanggal</td>
        <td>:</td>
        <td>
          {{ $dayjs(page.props.data.date).format("DD MMMM YYYY") }} ({{ $dayjs(page.props.data.date).fromNow() }})

        </td>
      </tr>
      <tr>
        <td>Status Kegiatan</td>
        <td>:</td>
        <td>
          <template v-if="page.props.data.status == 'approved'">
            Disetujui oleh {{ page.props.data.responded_by.name }} pada
            {{ $dayjs(page.props.data.responded_datetime).format('DD MMMM YYYY HH:mm') }}
            ({{ $dayjs(page.props.data.responded_datetime).fromNow() }})
          </template>
          <template v-else-if="page.props.data.status == 'rejected'">
            Ditolak oleh {{ page.props.data.responded_by.name }} pada
            {{ $dayjs(page.props.data.responded_datetime).format('DD MMMM YYYY HH:mm') }}
            ({{ $dayjs(page.props.data.responded_datetime).fromNow() }})
          </template>
          <template v-else>
            Belum Direspon
          </template>
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
        <td style="width:125px">Kegiatan ID</td>
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
