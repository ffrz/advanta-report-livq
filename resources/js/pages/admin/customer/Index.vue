<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { check_role, getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { usePageStorage } from '@/helpers/usePageStorage'

const storage = usePageStorage('customers')
const title = "Client";
const $q = useQuasar();
const showFilter = ref(true);
const rows = ref([]);
const loading = ref(true);

const filter = reactive(storage.get('filter', {
  status: "all",
  ...getQueryParams(),
}));

const pagination = ref(storage.get('pagination', {
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "name",
  descending: false,
}));

const columns = [
  { name: "type", label: "Jenis", field: "type", align: "left", sortable: true },
  { name: "name", label: "Nama", field: "name", align: "left", sortable: true },
  { name: "phone", label: "No HP", field: "phone", align: "left" },
  { name: "address", label: "Alamat", field: "address", align: "left" },
  { name: "action", align: "right" },
];

const statuses = [
  { value: "all", label: "Semua" },
  { value: "active", label: "Aktif" },
  { value: "inactive", label: "Tidak Aktif" },
];

onMounted(() => {
  fetchItems();
});

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus client ${row.name}?`,
    url: route("admin.customer.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.customer.data"),
    loading,
  });
};

const onFilterChange = () => fetchItems();
const onRowClicked = (row) => router.get(route('admin.customer.detail', { id: row.id }));
const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter((col) => col.name === "name" || col.name === "action");
});

watch(filter, () => storage.set('filter', filter), { deep: true })
watch(pagination, () => storage.set('pagination', pagination.value), { deep: true })

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn icon="add" dense color="primary" @click="router.get(route('admin.customer.add'))"
        :disabled="!check_role([$CONSTANTS.USER_ROLE_AGRONOMIST, $CONSTANTS.USER_ROLE_ADMIN])" />
      <q-btn class=" q-ml-sm" :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'" color="grey" dense
        @click="showFilter = !showFilter" />
      <q-btn icon="file_export" dense class="q-ml-sm" color="grey" style="" @click.stop>
        <q-menu anchor="bottom right" self="top right" transition-show="scale" transition-hide="scale">
          <q-list style="width: 200px">
            <q-item clickable v-ripple v-close-popup :href="route('admin.customer.export', { format: 'pdf' })">
              <q-item-section avatar>
                <q-icon name="picture_as_pdf" color="red-9" />
              </q-item-section>
              <q-item-section>Export PDF</q-item-section>
            </q-item>
            <q-item clickable v-ripple v-close-popup :href="route('admin.customer.export', { format: 'excel' })">
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
          <q-tr :props="props" :class="!props.row.active ? 'bg-red-1' : ''" class="cursor-pointer"
            @click="onRowClicked(props.row)">
            <q-td key="id" :props="props" class="wrap-column">
              <div>{{ props.row.id }}</div>
            </q-td>
            <q-td key="type" :props="props">
              {{ props.row.type }}
            </q-td>
            <q-td key="name" :props="props" class="wrap-column">
              <div><q-icon name="domain" v-if="$q.screen.lt.md" /> {{ props.row.name }}</div>
              <template v-if="$q.screen.lt.md">
                <div><q-icon name="phone" /> {{ props.row.phone }}</div>
                <div><q-icon name="home_pin" /> {{ props.row.address }}</div>
                <div><q-icon name="local_shipping" /> {{ props.row.shipping_address }}</div>
                <div><q-icon name="category" /> {{ props.row.type }}</div>
                <div v-if="props.row.notes"><q-icon name="notes" /> {{ props.row.notes }}</div>
              </template>
            </q-td>
            <q-td key="phone" :props="props">
              {{ props.row.phone }}
            </q-td>
            <q-td key="address" :props="props">
              {{ props.row.address }}
            </q-td>
            <q-td key="action" :props="props">
              <div class="flex justify-end">
                <q-btn :disabled="!check_role([$CONSTANTS.USER_ROLE_AGRONOMIST, $CONSTANTS.USER_ROLE_ADMIN])"
                  icon="more_vert" dense flat style="height: 40px; width: 30px" @click.stop>
                  <q-menu anchor="bottom right" self="top right" transition-show="scale" transition-hide="scale">
                    <q-list style="width: 200px">
                      <q-item clickable v-ripple v-close-popup
                        @click.stop="router.get(route('admin.customer.duplicate', props.row.id))">
                        <q-item-section avatar>
                          <q-icon name="file_copy" />
                        </q-item-section>
                        <q-item-section> Duplikat </q-item-section>
                      </q-item>
                      <q-item clickable v-ripple v-close-popup
                        @click.stop="router.get(route('admin.customer.edit', props.row.id))">
                        <q-item-section avatar>
                          <q-icon name="edit" />
                        </q-item-section>
                        <q-item-section>Edit</q-item-section>
                      </q-item>
                      <q-item v-if="check_role([$CONSTANTS.USER_ROLE_ADMIN])" @click.stop="deleteItem(props.row)"
                        clickable v-ripple v-close-popup>
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
