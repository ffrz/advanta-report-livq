<script setup>
import { usePage, router } from '@inertiajs/vue3';
import { useQuasar } from "quasar";
import { computed } from 'vue';

const $q = useQuasar();
const page = usePage();
const rows = page.props.data.recent_interactions;
const columns = [
  { name: "id", label: "#", field: "id", align: "left" },
  { name: "date", label: "Tanggal", field: "date", align: "left" },
  { name: "type", label: "Jenis", field: "type", align: "left" },
  { name: "sales", label: "Sales", field: "sales", align: "left" },
  { name: "customer", label: "Client", field: "customer", align: "left" },
  { name: "service", label: "Layanan", field: "service", align: "left" },
  { name: "subject", label: "Subjek", field: "subject", align: "left" },
  { name: "engagement_level", label: "Engagement", field: "engagement_level", align: "center" },
];

const type_colors = {
  visit: "red",
  chat: "orange",
  call: "green",
  email: "blue",
  other: "black",
};

const status_colors = {
  planned: "grey",
  done: "blue",
  cancelled: "red",
};

const engagement_level_colors = {
  none: "grey",
  cold: "blue",
  warm: "yellow-8",
  hot: "orange",
  converted: "green",
  churned: "red",
  lost: "black",
};

const onRowClicked = (row) => router.get(route("admin.interaction.detail", { id: row.id }));

const computedColumns = computed(() =>
  $q.screen.gt.sm ? columns : columns.filter((col) => ["id", "action"].includes(col.name))
);

</script>
<template>
  <div class="col-lg-6 col-12 card-container">
    <div class="text-subtitle2 text-bold text-grey-8">
      Interaksi Terbaru
    </div>
    <q-card square bordered class="no-shadow bg-white" style="width:100%;">
      <q-card-section class="q-pa-none">
        <q-table flat bordered square color="primary" row-key="id" virtual-scroll :rows="rows" dense
          :rows-per-page-options="[5]" :columns="computedColumns">
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
                    <q-icon name="people" /> #{{ props.row.customer.id }} - {{ props.row.customer.name }}
                    - {{ props.row.customer.company }}
                  </div>
                  <div v-if="props.row.customer.address">
                    <q-icon name="location_on" />{{ props.row.customer.address }}
                  </div>
                  <div><q-icon name="apps" /> {{ props.row.service.name }}</div>
                  <div><q-icon name="input" /> {{ props.row.subject }}</div>
                  <div class="flex items-center q-gutter-sm">
                    <q-badge :color="type_colors[props.row.type]">
                      {{ $CONSTANTS.INTERACTION_TYPES[props.row.type] }}
                    </q-badge>
                    <q-badge :color="engagement_level_colors[props.row.engagement_level]">
                      <q-icon name="favorite" />&nbsp;{{
                        $CONSTANTS.INTERACTION_ENGAGEMENT_LEVELS[props.row.engagement_level] }}
                    </q-badge>
                    <q-badge :color="status_colors[props.row.status]">
                      {{ $CONSTANTS.INTERACTION_STATUSES[props.row.status] }}
                    </q-badge>
                  </div>
                  <div v-if="props.row.notes"><q-icon name="notes" /> {{ props.row.notes }}</div>
                </template>
              </q-td>
              <q-td key="date" :props="props" class="wrap-column">
                <div>
                  {{ $dayjs(props.row.interaction_date).format('D MMMM YYYY') }}
                  <template v-if="props.row.interaction_time">
                    <span class="text-grey-6">({{ props.row.interaction_time }})</span>
                  </template>
                </div>
                <div><q-icon name="history" v-if="$q.screen.lt.md" /> {{ props.row.name }}</div>
              </q-td>
              <q-td key="type" :props="props">
                {{ $CONSTANTS.INTERACTION_TYPES[props.row.type] }}
              </q-td>
              <q-td key="sales" :props="props">
                {{ props.row.user.username }}
              </q-td>
              <q-td key="customer" :props="props">
                {{ props.row.customer.name }} - {{ props.row.customer.company }} (#{{ props.row.customer.id }})
                <br />{{ props.row.customer.business_type }}
                <br />{{ props.row.customer.address }}
              </q-td>
              <q-td key="service" :props="props">
                {{ props.row.service.name }}
              </q-td>
              <q-td key="subject" :props="props">
                {{ props.row.subject }}
              </q-td>
              <q-td key="engagement_level" :props="props">
                <q-badge :color="engagement_level_colors[props.row.engagement_level]">
                  {{ $CONSTANTS.INTERACTION_ENGAGEMENT_LEVELS[props.row.engagement_level] }}
                </q-badge>
              </q-td>
            </q-tr>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </div>
</template>
