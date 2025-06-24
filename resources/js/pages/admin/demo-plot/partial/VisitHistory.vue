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

  ...getQueryParams(),
});

const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "visit_date",
  descending: true,
});

const PLANT_STATUS_COLORS = {
  not_yet_planted: 'grey',
  not_yet_evaluated: "grey",
  satisfactory: "green",
  unsatisfactory: "red",
  failed: "black",
  completed: "blue",
};

const columns = [
  { name: "visit_date", label: "Tanggal", field: "visit_date", align: "left", sortable: true },
  { name: "user", label: "BS", field: "user", align: "left" },
  { name: "status", label: "Status", field: "status", align: "left" },
  { name: "notes", label: "Catatan", field: "notes", align: "left" },
  { name: "action", align: "right" },
];

onMounted(() => fetchItems());

const deleteItem = (row) => handleDelete({
  message: `Hapus kunjungan #${row.id} - ${row.visit_date}?`,
  url: route("admin.demo-plot-visit.delete", row.id),
  fetchItemsCallback: fetchItems,
  loading,
});

const fetchItems = (props = null) => handleFetchItems({
  pagination,
  filter,
  props,
  rows,
  url: route("admin.demo-plot-visit.data", { demo_plot_id: page.props.data.id }),
  loading,
});

const onRowClicked = (row) => router.get(route("admin.demo-plot-visit.detail", { id: row.id }));

const computedColumns = computed(() =>
  $q.screen.gt.sm ? columns : columns.filter((col) => ["visit_date", "action"].includes(col.name))
);

</script>

<template>

  <div class="q-pa-none">
    <div class="q-pa-sm">
      <q-btn label="Tambah&nbsp;&nbsp;" color="primary" size="sm" icon="add" dense
        @click="router.get(route('admin.demo-plot-visit.add', { demo_plot_id: page.props.data.id }))" />
    </div>
    <q-table flat bordered square color="primary" row-key="id" virtual-scroll v-model:pagination="pagination"
      :filter="filter.search" :loading="loading" :columns="computedColumns" :rows="rows"
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
          <q-td key="visit_date" :props="props" class="wrap-column">
            <div v-if="!$q.screen.lt.md" class="row items-start q-gutter-sm">
              <q-img :src="`/${props.row.image_path}`" style="width: 64px; height: 64px; border: 1px solid #ddd"
                spinner-color="grey" fit="cover" class="rounded-borders" />
              <div class="column">
                {{ $dayjs(props.row.visit_date).format('DD MMMM YYYY') }}
                <div class="text-caption text-italic text-grey-8">{{ $dayjs(props.row.visit_date).fromNow() }}</div>
              </div>
            </div>
            <div v-else>
              <template v-if="$q.screen.lt.md">
                <q-icon name="history" />
              </template>
              {{ $dayjs(props.row.visit_date).format('DD MMMM YYYY') }}
              <div class="text-caption text-italic text-grey-8">{{ $dayjs(props.row.visit_date).fromNow() }}</div>
            </div>
            <template v-if="$q.screen.lt.md">
              <q-img :src="`/${props.row.image_path}`" style="border: 1px solid #ddd; max-height: 150px;"
                spinner-color="grey" fit="scale-down" class="rounded-borders bg-light-green-2" />
              <div class="text-subtitle2">
                <q-icon name="person" /> {{ props.row.user.name }}
                - {{ props.row.user.username }}
              </div>
              <div>
                <q-badge :color="PLANT_STATUS_COLORS[props.row.plant_status]">
                  {{ $CONSTANTS.DEMO_PLOT_PLANT_STATUSES[props.row.plant_status] }}
                </q-badge>
              </div>
              <div v-if="props.row.notes"><q-icon name="notes" /> {{ props.row.notes }}</div>
            </template>
          </q-td>
          <q-td key="user" :props="props">
            {{ props.row.user.name }}
          </q-td>
          <q-td key="status" :props="props">
            <q-badge :color="PLANT_STATUS_COLORS[props.row.plant_status]">
              {{ $CONSTANTS.DEMO_PLOT_PLANT_STATUSES[props.row.plant_status] }}
            </q-badge>
          </q-td>
          <q-td key="notes" :props="props">
            {{ props.row.notes }}
          </q-td>
          <q-td key="action" :props="props">
            <div class="flex justify-end">
              <q-btn :disabled="!check_role($CONSTANTS.USER_ROLE_ADMIN)" icon="more_vert" dense flat
                style="height: 40px; width: 30px" @click.stop>
                <q-menu anchor="bottom right" self="top right" transition-show="scale" transition-hide="scale">
                  <q-list style="width: 200px">
                    <q-item clickable v-ripple v-close-popup
                      @click.stop="router.get(route('admin.demo-plot-visit.edit', props.row.id))">
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
