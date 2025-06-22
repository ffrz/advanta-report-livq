<script setup>
import { router, usePage } from "@inertiajs/vue3";

const page = usePage();
const title = "Rincian Demplot";

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn icon="arrow_back" dense color="grey-7" @click="$goBack()" />
        <q-btn icon="edit" dense color="primary"
          @click="router.get(route('admin.demplot.edit', { id: page.props.data.id }))" />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-8">Info Demplot</div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width:150px">Id</td>
                    <td style="width:1px">:</td>
                    <td>#{{ page.props.data.id }}</td>
                  </tr>
                  <tr>
                    <td>BS</td>
                    <td>:</td>
                    <td>
                      <a :href="route('admin.user.detail', { id: page.props.data.user.id })">
                        {{ page.props.data.user.name }} ({{ page.props.data.user.username }})
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <td>Varietas</td>
                    <td>:</td>
                    <td>
                      <a :href="route('admin.variety.detail', { id: page.props.data.variety.id })">
                        {{ page.props.data.variety.name }}
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <td>Tanggal Tanam</td>
                    <td>:</td>
                    <td>{{ $dayjs(page.props.data.date).format('DD MMMM YYYY') }}</td>
                  </tr>
                  <tr>
                    <td>Catatan</td>
                    <td>:</td>
                    <td>{{ page.props.data.notes }}</td>
                  </tr>
                  <template v-if="page.props.data.location">
                    <tr>
                      <td>Lokasi</td>
                      <td>:</td>
                      <td>Koordinat: {{ page.props.data.location }}</td>
                    </tr>
                    <tr>
                      <td colspan="3">
                        <div style="max-width:500px">
                          <iframe
                            :src="`https://www.google.com/maps?q=${encodeURIComponent(page.props.data.location)}&output=embed`"
                            width="100%" height="300" style="border:1px solid #ddd; margin-top: 10px" allowfullscreen
                            loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                      </td>
                    </tr>
                  </template>
                  <template v-if="page.props.data.image_path">
                    <tr>
                      <td>Foto</td>
                      <td>:</td>
                      <td></td>
                    </tr>
                    <tr>
                      <td colspan="3">
                        <q-img :src="`/${page.props.data.image_path}`" class="q-mt-md" style="max-width: 500px;"
                          :style="{ border: '1px solid #ddd' }" />
                      </td>
                    </tr>
                  </template>
                  <tr v-if="page.props.data.created_datetime">
                    <td>Dibuat</td>
                    <td>:</td>
                    <td>
                      {{ $dayjs(page.props.data.created_datetime).fromNow() }} -
                      {{ $dayjs(page.props.data.created_datetime).format("DD MMMM YY HH:mm:ss") }}
                      <template v-if="page.props.data.created_by_user">
                        oleh
                        <a :href="route('admin.user.detail', { id: page.props.data.created_by_user.id })">
                          {{ page.props.data.created_by_user.username }}
                        </a>
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
                        <a :href="route('admin.user.detail', { id: page.props.data.updated_by_user.id })">
                          {{ page.props.data.updated_by_user.username }}
                        </a>
                      </template>
                    </td>
                  </tr>
                </tbody>
              </table>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </q-page>
  </authenticated-layout>
</template>
