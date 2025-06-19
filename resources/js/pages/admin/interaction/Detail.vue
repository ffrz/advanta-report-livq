<script setup>
import { router, usePage } from "@inertiajs/vue3";

const page = usePage();
const title = "Rincian Interaksi";

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn icon="arrow_back" dense color="grey-7" @click="$goBack()" />
        <q-btn icon="edit" dense color="primary"
          @click="router.get(route('admin.interaction.edit', { id: page.props.data.id }))" />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-8">Info Interaksi</div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width:150px">Id</td>
                    <td style="width:1px">:</td>
                    <td>#{{ page.props.data.id }}</td>
                  </tr>
                  <tr>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td>{{ $dayjs(page.props.data.date).format('DD MMMM YYYY') }}</td>
                  </tr>
                  <tr>
                    <td>Jenis</td>
                    <td>:</td>
                    <td>{{ $CONSTANTS.INTERACTION_TYPES[page.props.data.type] }}</td>
                  </tr>
                  <tr>
                    <td>Status</td>
                    <td>:</td>
                    <td>{{ $CONSTANTS.INTERACTION_STATUSES[page.props.data.status] }}</td>
                  </tr>
                  <tr>
                    <td>Sales</td>
                    <td>:</td>
                    <td>
                      <a :href="route('admin.user.detail', { id: page.props.data.user.id })">
                        {{ page.props.data.user.name }} ({{ page.props.data.user.username }})
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <td>Client</td>
                    <td>:</td>
                    <td>
                      <a :href="route('admin.customer.detail', { id: page.props.data.customer.id })">
                        {{ page.props.data.customer.name }}
                        {{ page.props.data.customer.company ? `- ${page.props.data.customer.company}` : '' }}
                        - (#{{ page.props.data.customer.id }})
                      </a>
                      <template v-if="page.props.data.customer.address">
                        <br />{{ page.props.data.customer.address }}
                      </template>
                    </td>
                  </tr>
                  <tr>
                    <td>Service</td>
                    <td>:</td>
                    <td>
                      <a :href="route('admin.service.detail', { id: page.props.data.service.id })">
                        {{ page.props.data.service.name }}
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <td>Engagement Level</td>
                    <td>:</td>
                    <td>{{ $CONSTANTS.INTERACTION_ENGAGEMENT_LEVELS[page.props.data.engagement_level] }}</td>
                  </tr>
                  <tr>
                    <td>Subject</td>
                    <td>:</td>
                    <td>{{ page.props.data.subject }}</td>
                  </tr>
                  <tr>
                    <td>Summary</td>
                    <td>:</td>
                    <td>{{ page.props.data.summary }}</td>
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
