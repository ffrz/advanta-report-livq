<script setup>
import { usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import ImageViewer from "@/components/ImageViewer.vue";
const page = usePage();
const showViewer = ref(false);
</script>

<template>
  <table class="detail">
    <tbody>
      <tr>
        <td colspan="3" class="bg-white">
          <div class="text-bold text-grey-8 q-mt-md">Informasi Utama</div>
        </td>
      </tr>
      <tr>
        <td style="width: 100px">BS</td>
        <td>:</td>
        <td>
          <template v-if="page.props.data.user">
            <my-link
              :href="
                route('admin.user.detail', { id: page.props.data.user.id })
              "
            >
              {{
                page.props.data.user.name +
                " - " +
                page.props.data.user.username
              }}
            </my-link>
          </template>
          <template v-else> - </template>
        </td>
      </tr>
      <tr>
        <td>Periode</td>
        <td>:</td>
        <td>{{ page.props.data.year }}-Q{{ page.props.data.quarter }}</td>
      </tr>
      <tr>
        <td>Target</td>
        <td>:</td>
        <td></td>
      </tr>
      <tr v-for="detail in page.props.data.details" :key="detail.id">
        <td>
          <span class="q-ml-sm">- {{ detail.type.name }}</span>
        </td>
        <td>:</td>
        <td>
          {{ detail.quarter_qty }} ({{ detail.month1_qty }} /
          {{ detail.month2_qty }} / {{ detail.month3_qty }})
        </td>
      </tr>
      <tr>
        <td>Catatan</td>
        <td>:</td>
        <td>{{ page.props.data.notes ? page.props.data.notes : "-" }}</td>
      </tr>
      <template v-if="page.props.data.image_path">
        <tr>
          <td colspan="3" class="bg-white">
            <div class="q-mt-md">
              Foto Dokumentasi:<br />
              <q-img
                :src="`/${page.props.data.image_path}`"
                class="q-mt-none"
                style="max-width: 500px"
                :style="{ border: '1px solid #ddd' }"
                @click="showViewer = true"
              />
            </div>
          </td>
        </tr>
      </template>
      <template v-if="page.props.data.latlong">
        <tr>
          <td colspan="3" class="bg-white">
            <div class="q-mt-md">
              Lokasi:<br />
              {{ page.props.data.latlong }}<br />
              <div style="max-width: 500px">
                <iframe
                  :src="`https://www.google.com/maps?q=${encodeURIComponent(
                    page.props.data.latlong
                  )}&output=embed`"
                  width="100%"
                  height="300"
                  style="border: 1px solid #ddd; margin-top: 10px"
                  allowfullscreen
                  loading="lazy"
                  referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
              </div>
            </div>
          </td>
        </tr>
      </template>
      <tr>
        <td colspan="3" class="bg-white">
          <div class="text-bold text-grey-8 q-mt-md">Informasi Rekaman</div>
        </td>
      </tr>
      <tr>
        <td style="width: 100px">ID</td>
        <td style="width: 1px">:</td>
        <td>#{{ page.props.data.id }}</td>
      </tr>
      <tr v-if="page.props.data.created_datetime">
        <td>Dibuat</td>
        <td>:</td>
        <td>
          {{ $dayjs(page.props.data.created_datetime).fromNow() }} -
          {{
            $dayjs(page.props.data.created_datetime).format(
              "DD MMMM YY HH:mm:ss"
            )
          }}
          <template v-if="page.props.data.created_by_user">
            oleh
            <my-link
              :href="
                route('admin.user.detail', {
                  id: page.props.data.created_by_user.id,
                })
              "
            >
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
          {{
            $dayjs(page.props.data.updated_datetime).format(
              "DD MMMM YY HH:mm:ss"
            )
          }}
          <template v-if="page.props.data.updated_by_user">
            oleh
            <my-link
              :href="
                route('admin.user.detail', {
                  id: page.props.data.updated_by_user.id,
                })
              "
            >
              {{ page.props.data.updated_by_user.username }}
            </my-link>
          </template>
        </td>
      </tr>
    </tbody>
  </table>

  <ImageViewer
    v-model="showViewer"
    :imageUrl="`/${page.props.data.image_path}`"
  />
</template>
