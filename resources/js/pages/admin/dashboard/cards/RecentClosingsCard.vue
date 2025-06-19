<script setup>
import { usePage, router } from '@inertiajs/vue3';
import { useQuasar } from "quasar";
import { computed } from 'vue';
import { formatNumber } from "@/helpers/utils";

const $q = useQuasar();
const page = usePage();
const rows = page.props.data.recent_closings;
const columns = [
  { name: "id", label: "#", field: "id", align: "left" },
  { name: "date", label: "Tanggal", field: "date", align: "left"},
  { name: "sales", label: "Sales", field: "sales", align: "left" },
  { name: "customer", label: "Client", field: "customer", align: "left" },
  { name: "service", label: "Layanan", field: "service", align: "left" },
  { name: "description", label: "Deskripsi", field: "description", align: "left" },
  { name: "amount", label: "Jumlah (Rp)", field: "amount", align: "right" },
];

const onRowClicked = (row) => router.get(route("admin.interaction.detail", { id: row.id }));

const computedColumns = computed(() =>
  $q.screen.gt.sm ? columns : columns.filter((col) => ["id", "action"].includes(col.name))
);

</script>
<template>
  <div class="col-lg-6 col-12 card-container">
    <div class="text-subtitle2 text-bold text-grey-8">
      Closing Terbaru
    </div>
    <q-card square bordered class="no-shadow bg-white" style="width:100%;">
      <q-card-section class="q-pa-none">
        <q-table flat bordered square color="primary" row-key="id" dense virtual-scroll :columns="computedColumns"
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
            <q-tr :props="props" :class="props.row.active == 'inactive' ? 'bg-red-1' : ''" class="cursor-pointer"
              @click="onRowClicked(props.row)">
              <q-td key="id" :props="props" class="wrap-column">
                <div>
                  {{ props.row.id }}
                  <template v-if="$q.screen.lt.md">
                    - <span><q-icon name="history" /> {{ $dayjs(props.row.date).format('D MMMM YYYY') }}</span>
                  </template>
                </div>
                <template v-if="$q.screen.lt.md">
                  <div>
                    <q-icon name="person" /> {{ props.row.user.name }} ({{ props.row.user.username }})
                  </div>
                  <div>
                    <q-icon name="account_circle" /> {{ props.row.customer.name }} {{ props.row.customer.company ? ` -
                    ${props.row.customer.company}` : '' }} (#{{ props.row.customer.id }})
                  </div>
                  <div v-if="props.row.customer.address">
                    <q-icon name="location_on" />{{ props.row.customer.address }}
                  </div>
                  <div><q-icon name="apps" /> {{ props.row.service.name }}</div>
                  <div><q-icon name="input" /> {{ props.row.description }}</div>
                  <div><q-icon name="money" /> Rp. {{ formatNumber(props.row.amount) }}</div>
                  <div v-if="props.row.notes"><q-icon name="notes" /> {{ props.row.notes }}</div>
                </template>
              </q-td>
              <q-td key="date" :props="props" class="wrap-column">
                {{ $dayjs(props.row.date).format('D MMMM YYYY') }}
              </q-td>
              <q-td key="sales" :props="props">
                {{ props.row.user.username }}
              </q-td>
              <q-td key="customer" :props="props">
                {{ props.row.customer.name }} - {{ props.row.customer.company }} (#{{ props.row.customer.id }})
                <template v-if="props.row.customer.business_type">
                  <br />{{ props.row.customer.business_type }}
                </template>
                <template v-if="props.row.customer.address">
                  <br />{{ props.row.customer.address }}
                </template>
              </q-td>
              <q-td key="service" :props="props">
                {{ props.row.service.name }}
              </q-td>
              <q-td key="description" :props="props">
                {{ props.row.description }}
              </q-td>
              <q-td key="amount" :props="props">
                {{ formatNumber(props.row.amount) }}
              </q-td>
            </q-tr>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </div>

</template>
