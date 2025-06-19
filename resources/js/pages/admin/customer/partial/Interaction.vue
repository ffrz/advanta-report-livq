<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { check_role, getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";

const $q = useQuasar();
const rows = ref([]);
const loading = ref(true);
const page = usePage();
const filter = reactive({
  customer_id: page.props.data.id,
  ...getQueryParams(),
});

const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "id",
  descending: true,
});

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

const columns = [
  { name: "id", label: "#", field: "id", align: "left", sortable: true },
  { name: "date", label: "Tanggal", field: "date", align: "left", sortable: true },
  { name: "type", label: "Jenis", field: "type", align: "left" },
  { name: "sales", label: "Sales", field: "sales", align: "left" },
  { name: "service", label: "Layanan", field: "service", align: "left" },
  { name: "subject", label: "Subjek", field: "subject", align: "left" },
  { name: "engagement_level", label: "Engagement", field: "engagement_level", align: "center" },
  { name: "action", align: "right" },
];

onMounted(() => fetchItems());

const deleteItem = (row) => handleDelete({
  message: `Hapus interaksi ${row.name}?`,
  url: route("admin.interaction.delete", row.id),
  fetchItemsCallback: fetchItems,
  loading,
});

const fetchItems = (props = null) => handleFetchItems({
  pagination,
  filter,
  props,
  rows,
  url: route("admin.interaction.data"),
  loading,
});

const onRowClicked = (row) => router.get(route("admin.interaction.detail", { id: row.id }));

const computedColumns = computed(() =>
  $q.screen.gt.sm ? columns : columns.filter((col) => ["id", "action"].includes(col.name))
);

</script>

<template>

  <div class="q-pa-none">
    <q-table class="full-height-table" flat bordered square color="primary" row-key="id" virtual-scroll
      v-model:pagination="pagination" :filter="filter.search" :loading="loading" :columns="computedColumns" :rows="rows"
      :rows-per-page-options="[10, 25, 50]" @request="fetchItems" binary-state-sort>
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
                - <span><q-icon name="history" /> {{ $dayjs(props.row.date).format('DD MMMM YYYY') }}</span>
              </template>
            </div>
            <template v-if="$q.screen.lt.md">
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
              {{ $dayjs(props.row.interaction_date).format('YYYY-MM-DD') }}
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
          <!-- <q-td key="customer" :props="props">
            {{ props.row.customer.name }} - {{ props.row.customer.company }} (#{{ props.row.customer.id }})
            <br />{{ props.row.customer.business_type }}
            <br />{{ props.row.customer.address }}
          </q-td> -->
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
          <q-td key="action" :props="props">
            <div class="flex justify-end">
              <q-btn :disabled="!check_role($CONSTANTS.USER_ROLE_ADMIN)" icon="more_vert" dense flat
                style="height: 40px; width: 30px" @click.stop>
                <q-menu anchor="bottom right" self="top right" transition-show="scale" transition-hide="scale">
                  <q-list style="width: 200px">
                    <q-item clickable v-ripple v-close-popup
                      @click.stop="router.get(route('admin.interaction.edit', props.row.id))">
                      <q-item-section avatar>
                        <q-icon name="edit" />
                      </q-item-section>
                      <q-item-section icon="edit">Edit</q-item-section>
                    </q-item>
                    <q-item @click.stop="deleteItem(props.row)" clickable v-ripple v-close-popup>
                      <q-item-section avatar>
                        <q-icon name="delete_forever" />
                      </q-item-section>
                      <q-item-section>Hapus</q-item-section>
                    </q-item>
                  </q-list>
                </q-menu>
              </q-btn>
            </div>
          </q-td>
        </q-tr>
      </template>
    </q-table>
  </div>
</template>
