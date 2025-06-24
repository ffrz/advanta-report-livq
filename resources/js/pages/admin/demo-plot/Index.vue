<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { check_role, getQueryParams, plantAge } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { usePageStorage } from '@/helpers/usePageStorage'
import dayjs from 'dayjs';

const page = usePage();
const storage = usePageStorage('demo-plots')
const title = "Demo Plot";
const $q = useQuasar();
const showFilter = ref(storage.get('show-filter', false));
const rows = ref([]);
const loading = ref(true);

const filter = reactive(storage.get('filter', {
  search: "",
  user_id: "all",
  plant_status: "all",
  status: "all",
  ...getQueryParams(),
}));

const pagination = ref(storage.get('pagination', {
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "id",
  descending: true,
}));

const statuses = [
  { value: "all", label: "Semua" },
  { value: "active", label: "Aktif" },
  { value: "inactive", label: "Tidak Aktif" },
];

const plant_statuses = [
  { value: "all", label: "Semua" },
  ...Object.entries(window.CONSTANTS.DEMO_PLOT_PLANT_STATUSES).map(([key, value]) => ({
    value: key,
    label: value,
  })),
];

const users = [
  { value: "all", label: "Semua" },
  ...page.props.users.map(user => ({
    value: user.id,
    label: `${user.name} (${user.username})`,
  })),
];

const products = [
  { value: "all", label: "Semua" },
  ...page.props.products.map(product => ({
    value: product.id,
    label: `${product.name}`,
  })),
];

const plant_status_colors = {
  not_yet_planted: 'grey',
  not_yet_evaluated: "grey",
  satisfactory: "green",
  unsatisfactory: "red",
  failed: "black",
  completed: "blue",
};

const columns = [
  { name: "field", label: "Lahan", field: "field", align: "left" },
  { name: "product", label: "Varietas", field: "product", align: "left" },
  { name: "plant_date", label: "Tanggal Tanam", field: "date", align: "left", sortable: true },
  { name: "bs", label: "BS", field: "bs", align: "left" },
  { name: "last_visit", label: "Last Visit", field: "last_visit", align: "left" },
  { name: "plant_status", label: "Status Tanaman", field: "plant_status", align: "left" },
  { name: "action", align: "right" },
];

onMounted(() => fetchItems());

const deleteItem = (row) => handleDelete({
  message: `Hapus Demo Plot ${row.name}?`,
  url: route("admin.demo-plot.delete", row.id),
  fetchItemsCallback: fetchItems,
  loading,
});

const fetchItems = (props = null) => handleFetchItems({
  pagination,
  filter,
  props,
  rows,
  url: route("admin.demo-plot.data"),
  loading,
});

const onFilterChange = () => fetchItems();

const onRowClicked = (row) => router.get(route("admin.demo-plot.detail", { id: row.id }));

const computedColumns = computed(() =>
  $q.screen.gt.sm ? columns : columns.filter((col) => ["field", "action"].includes(col.name))
);

watch(filter, () => storage.set('filter', filter), { deep: true })
watch(pagination, () => storage.set('pagination', pagination.value), { deep: true })
watch(showFilter, () => storage.set('show-filter', showFilter.value), { deep: true })

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn icon="add" dense color="primary" @click="router.get(route('admin.demo-plot.add'))" />
      <q-btn class="q-ml-sm" :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'" color="grey" dense
        @click="showFilter = !showFilter" />
      <q-btn icon="file_export" dense class="q-ml-sm" color="grey" style="" @click.stop>
        <q-menu anchor="bottom right" self="top right" transition-show="scale" transition-hide="scale">
          <q-list style="width: 200px">
            <q-item clickable v-ripple v-close-popup
              :href="route('admin.demo-plot.export', { format: 'pdf', filter: filter })">
              <q-item-section avatar>
                <q-icon name="picture_as_pdf" color="red-9" />
              </q-item-section>
              <q-item-section>Export PDF</q-item-section>
            </q-item>
            <q-item clickable v-ripple v-close-popup
              :href="route('admin.demo-plot.export', { format: 'excel', filter: filter })">
              <q-item-section avatar>
                <q-icon name="csv" color="green-9" />
              </q-item-section>
              <q-item-section>Export Excel</q-item-section>
            </q-item>
          </q-list>
        </q-menu>
      </q-btn>
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select class="custom-select col-xs-12 col-sm-2" style="min-width: 150px" v-model="filter.status"
            :options="statuses" label="Status" dense map-options emit-value outlined
            @update:model-value="onFilterChange" />
          <q-select class="custom-select col-xs-12 col-sm-2" style="min-width: 150px" v-model="filter.plant_status"
            :options="plant_statuses" label="Status Tanaman" dense map-options emit-value outlined
            @update:model-value="onFilterChange" />
          <q-select class="custom-select col-xs-12 col-sm-2" style="min-width: 150px" v-model="filter.user_id"
            :options="users" label="BS" dense map-options emit-value outlined @update:model-value="onFilterChange" />
          <q-select class="custom-select col-xs-12 col-sm-2" style="min-width: 150px" v-model="filter.product_id"
            :options="products" label="Varietas" dense map-options emit-value outlined
            @update:model-value="onFilterChange" />
          <q-input class="col" outlined dense debounce="300" v-model="filter.search" placeholder="Cari" clearable>
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
      </q-toolbar>
    </template>
    <div class="q-pa-sm">
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
            <q-td key="field" :props="props">
              <template v-if="!$q.screen.lt.md">
                <div class="row items-start q-gutter-sm">
                  <q-img :src="`/${props.row.image_path}`" style="width: 64px; height: 64px; border: 1px solid #ddd"
                    spinner-color="grey" fit="cover" class="rounded-borders" />
                  <div class="column">
                    <div class="text-subtitle2">{{ props.row.owner_name }} - {{ props.row.user.owner_phone }}</div>
                    <div class="text-caption">{{ props.row.field_location }}</div>
                  </div>
                </div>
              </template>
              <template v-else>
                <q-img :src="`/${props.row.image_path}`" style="border: 1px solid #ddd; max-height: 150px;"
                  spinner-color="grey" fit="scale-down" class="rounded-borders bg-light-green-2" />
                <div class="text-subtitle2"><q-icon name="person" /> {{ props.row.owner_name }} - {{
                  props.row.user.owner_phone }}</div>
                <div class="text-caption"><q-icon name="distance" /> Lokasi: {{ props.row.field_location }}</div>
              </template>

              <template v-if="$q.screen.lt.md">
                <div>
                  <q-icon name="edit_calendar" /> Tgl Tanam: {{ $dayjs(props.row.plant_date).format('DD MMMM YYYY') }}
                  <template v-if="props.row.active">
                    <br> ({{ plantAge(props.row.plant_date) }} hari)
                  </template>
                </div>
                <template v-if="props.row.active">
                  <div>
                    <q-icon name="calendar_clock" /> Umur: {{ plantAge(props.row.plant_date) }} hari
                  </div>
                </template>
                <template v-if="props.row.last_visit">
                  <div>
                    <q-icon name="calendar_clock" /> Last Visit:
                    {{ $dayjs(props.row.last_visit).format('DD MMMM YYYY') }} /
                    {{ $dayjs(props.row.last_visit).fromNow() }}
                  </div>
                </template>
              </template>
              <template v-if="$q.screen.lt.md">
                <div class="flex items-center q-gutter-sm">
                  <q-badge :color="plant_status_colors[props.row.plant_status]">
                    Status: {{ $CONSTANTS.DEMO_PLOT_PLANT_STATUSES[props.row.plant_status] }}
                  </q-badge>
                </div>
                <div v-if="props.row.notes"><q-icon name="notes" /> {{ props.row.notes }}</div>
              </template>
            </q-td>
            <q-td key="product" :props="props">
              {{ props.row.product.name }}
            </q-td>
            <q-td key="plant_date" :props="props">
              {{ $dayjs(props.row.plant_date).format('YYYY-MM-DD') }}
              <template v-if="props.row.active">
                ({{ plantAge(props.row.plant_date) }} hari)
              </template>
            </q-td>
            <q-td key="bs" :props="props">
              {{ props.row.user.name }} ({{ props.row.user.username }})
            </q-td>
            <q-td key="last_visit" :props="props">
              <template v-if="props.row.last_visit">
                {{ $dayjs(props.row.last_visit).format('DD MMMM YYYY') }} /
                {{ $dayjs(props.row.last_visit).fromNow() }}
              </template>
            </q-td>
            <q-td key="plant_status" :props="props">
              {{ $CONSTANTS.DEMO_PLOT_PLANT_STATUSES[props.row.plant_status] }}
            </q-td>
            <q-td key="action" :props="props">
              <div class="flex justify-end">
                <q-btn :disabled="!check_role($CONSTANTS.USER_ROLE_ADMIN)" icon="more_vert" dense flat
                  style="height: 40px; width: 30px" @click.stop>
                  <q-menu anchor="bottom right" self="top right" transition-show="scale" transition-hide="scale">
                    <q-list style="width: 200px">
                      <q-item clickable v-ripple v-close-popup
                        @click.stop="router.get(route('admin.demo-plot.duplicate', props.row.id))">
                        <q-item-section avatar>
                          <q-icon name="content_copy" />
                        </q-item-section>
                        <q-item-section icon="content_copy">Duplikat</q-item-section>
                      </q-item>
                      <q-item clickable v-ripple v-close-popup
                        @click.stop="router.get(route('admin.demo-plot.edit', props.row.id))">
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
  </authenticated-layout>
</template>
