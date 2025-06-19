<script setup>
import { usePage, router } from '@inertiajs/vue3';
import { useQuasar } from "quasar";
import { computed } from 'vue';

const $q = useQuasar();
const page = usePage();
const rows = page.props.data.recent_customers;
const columns = [
  { name: "created_datetime", label: "Ditambahkan", field: "created_datetime", align: "left" },
  { name: "name", label: "Nama", field: "name", align: "left" },
  { name: "company", label: "Perusahaan", field: "company", align: "left" },
  { name: "phone", label: "No HP", field: "phone", align: "left" },
];

const onRowClicked = (row) => router.get(route("admin.customer.detail", { id: row.id }));

const computedColumns = computed(() =>
  $q.screen.gt.sm ? columns : columns.filter((col) => ["created_datetime"].includes(col.name))
);

</script>
<template>
  <div class="col-lg-6 col-12 card-container">
    <div class="text-subtitle2 text-bold text-grey-8">
      Client Terbaru
    </div>
    <q-card square bordered class="no-shadow bg-white" style="width:100%;">
      <q-card-section class="q-pa-none">
        <q-table flat bordered square color="primary" row-key="id" virtual-scroll :columns="computedColumns"
          :rows="rows" :rows-per-page-options="[5]">
          <template v-slot:loading>
            <q-inner-loading showing color="red" />
          </template>

          <template v-slot:no-data="{ icon, message, filter }">
            <div class="full-width row flex-center text-grey-8 q-gutter-sm">
              <span>
                {{ message }}
                {{ filter ? " with term " + filter : "" }}
              </span>
            </div>
          </template>

          <template v-slot:body="props">
            <q-tr :props="props" :class="!props.row.active ? 'bg-red-1' : ''" class="cursor-pointer"
              @click="onRowClicked(props.row)">
              <q-td key="created_datetime" :props="props" class="wrap-column">
                <div>{{ $dayjs(props.row.created_datetime).format('D MMMM YYYY HH:mm') }}</div>
                <template v-if="$q.screen.lt.md">
                  <div><q-icon name="domain" /> {{ props.row.company }}
                    <template v-if="props.row.business_type"> - {{ props.row.business_type }}</template>
                  </div>
                  <div><q-icon name="phone" /> {{ props.row.phone }}</div>
                  <div><q-icon name="home_pin" /> {{ props.row.address }}</div>
                  <div v-if="props.row.email"><q-icon name="email" /> {{ props.row.email }}</div>
                  <div v-if="props.row.notes"><q-icon name="notes" /> {{ props.row.notes }}</div>
                </template>
              </q-td>
              <q-td key="name" :props="props" class="wrap-column">
                <div><q-icon name="person" v-if="$q.screen.lt.md" /> {{ props.row.name }}</div>
              </q-td>
              <q-td key="company" :props="props">
                {{ props.row.company }} <template v-if="props.row.business_type"> - {{ props.row.business_type
                }}</template>
              </q-td>
              <q-td key="phone" :props="props">
                {{ props.row.phone }}
              </q-td>
            </q-tr>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </div>

</template>
