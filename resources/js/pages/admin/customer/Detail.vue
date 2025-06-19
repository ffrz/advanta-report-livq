<script setup>
import { router, usePage } from "@inertiajs/vue3";

const page = usePage();
const title = "Rincian Client";

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn icon="arrow_back" dense color="grey-7" @click="$goBack()" />
        <q-btn icon="edit" dense color="primary"
          @click="router.get(route('admin.customer.edit', { id: page.props.data.id }))" />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-8">Info Client</div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width:100px">Nama</td>
                    <td style="width:1px">:</td>
                    <td>{{ page.props.data.name }}</td>
                  </tr>
                  <tr>
                    <td>Perusahaan</td>
                    <td>:</td>
                    <td>{{ page.props.data.company }}</td>
                  </tr>
                  <tr>
                    <td>Jenis Usaha</td>
                    <td>:</td>
                    <td>{{ page.props.data.business_type }}</td>
                  </tr>
                  <tr>
                    <td>No Telepon</td>
                    <td>:</td>
                    <td>{{ page.props.data.phone }}</td>
                  </tr>
                  <tr v-if="page.props.data.email">
                    <td>Email</td>
                    <td>:</td>
                    <td>{{ page.props.data.email }}</td>
                  </tr>
                  <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{{ page.props.data.address }}</td>
                  </tr>
                  <tr>
                    <td>Sumber</td>
                    <td>:</td>
                    <td>{{ page.props.data.source }}</td>
                  </tr>
                  <tr>
                    <td>Assigned to</td>
                    <td>:</td>
                    <td>
                      <template v-if="page.props.data.assigned_user">
                        {{ page.props.data.assigned_user.name + ' - ' +
                          (page.props.data.assigned_user.username) }}
                      </template>
                      <template v-else>
                        -
                      </template>
                    </td>
                  </tr>
                  <tr>
                    <td>Status</td>
                    <td>:</td>
                    <td>{{ page.props.data.active ? 'Aktif' : 'Tidak Aktif' }}</td>
                  </tr>
                  <tr>
                    <td>Layanan</td>
                    <td>:</td>
                    <td>
                      <div v-for="item in page.props.data.services" :key="item.id">
                        {{ item.service.name }} -
                        <q-badge>
                          {{ $CONSTANTS.CUSTOMER_SERVICE_STATUSES[item.status] }}
                        </q-badge>
                        | <span>{{ item.start_date ?? '?' }}</span>
                        - <span>{{ item.end_date ?? '?' }}</span>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Catatan</td>
                    <td>:</td>
                    <td>{{ page.props.data.notes }}</td>
                  </tr>
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
