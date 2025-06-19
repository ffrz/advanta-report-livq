<script setup>
import { usePage, router } from "@inertiajs/vue3";
import { formatNumber } from "@/helpers/utils";

const page = usePage();
const title = "Rincian Target";

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn icon="arrow_back" dense color="grey-7" @click="$goBack()" />
        <q-btn icon="edit" dense color="primary"
          @click="router.get(route('admin.target.edit', { id: page.props.data.id }))" />
      </div>
    </template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-8">Info Target</div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width:150px">Id</td>
                    <td style="width:1px">:</td>
                    <td>#{{ page.props.data.id }}</td>
                  </tr>
                  <tr>
                    <td>Bulan</td>
                    <td>:</td>
                    <td>{{ $dayjs(page.props.data.date).format('MMMM YYYY') }}</td>
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
                    <td>Target FM</td>
                    <td>:</td>
                    <td>{{ formatNumber(page.props.data.fm) }}</td>
                  </tr>
                  <tr>
                    <td>Target ODP</td>
                    <td>:</td>
                    <td>{{ formatNumber(page.props.data.odp) }}</td>
                  </tr>
                  <tr>
                    <td>Target FT</td>
                    <td>:</td>
                    <td>{{ formatNumber(page.props.data.ft) }}</td>
                  </tr>
                  <tr>
                    <td>Target FDD</td>
                    <td>:</td>
                    <td>{{ formatNumber(page.props.data.fdd) }}</td>
                  </tr>
                  <tr>
                    <td>Catatan</td>
                    <td>:</td>
                    <td>{{ page.props.data.notes ?? '-' }}</td>
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
                          {{ page.props.data.created_by_user.name }} ({{ page.props.data.created_by_user.username }})
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
                          {{ page.props.data.created_by_user.name }} ({{ page.props.data.created_by_user.username }})
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
