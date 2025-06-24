<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { check_role, getQueryParams, formatNumber } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { useProductCategoryFilter } from "@/helpers/useProductCategoryFilter";
import { usePageStorage } from '@/helpers/usePageStorage'

const page = usePage();
const storage = usePageStorage('products')
const statuses = [
  { value: "all", label: "Semua" },
  { value: "active", label: "Aktif" },
  { value: "inactive", label: "Tidak Aktif" },
];

const title = "Varietas";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
const filter = reactive(storage.get('filter', {
  status: "all",
  category_id: "all",
  search: "",
  ...getQueryParams(),
}));
const pagination = ref(storage.get('pagination', {
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "name",
  descending: false,
}));
let columns;
if (page.props.auth.user.role != 'bs') {
  columns = [
    { name: "category", label: "Kategori", field: "category", align: "left" },
    { name: "name", label: "Brand", field: "name", align: "left", sortable: true },
    { name: "price_1", label: "Harga Distributor (Rp)", field: "price_1", align: "right", sortable: true },
    { name: "price_2", label: "Harga (Rp)", field: "price_2", align: "right", sortable: true },
    { name: "action", align: "right" },
  ];
}
else {
  columns = [
    { name: "category", label: "Kategori", field: "category", align: "left" },
    { name: "name", label: "Brand", field: "name", align: "left", sortable: true },
    { name: "action", align: "right" },
  ];
}

onMounted(() => {
  fetchItems();
});

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus Varietas ${row.name}?`,
    url: route("admin.product.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.product.data"),
    loading,
  });
};

const onFilterChange = () => {
  fetchItems();
};

const { filteredCategories, filterCategories } = useProductCategoryFilter(page.props.categories, true);

const computedColumns = computed(() =>
  $q.screen.gt.sm ? columns : columns.filter((col) => ["name", "action"].includes(col.name)));

watch(filter, () => storage.set('filter', filter), { deep: true })
watch(showFilter, () => storage.set('show-filter', showFilter.value), { deep: true })
watch(pagination, () => storage.set('pagination', pagination.value), { deep: true })

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn icon="add" dense color="primary" @click="router.get(route('admin.product.add'))"
        :disabled="!check_role($CONSTANTS.USER_ROLE_ADMIN)" />
      <q-btn class="q-ml-sm" :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'" color="grey" dense
        @click="showFilter = !showFilter" />
      <q-btn icon="file_export" dense class="q-ml-sm" color="grey" style="" @click.stop>
        <q-menu anchor="bottom right" self="top right" transition-show="scale" transition-hide="scale">
          <q-list style="width: 200px">
            <q-item clickable v-ripple v-close-popup :href="route('admin.product.export', { format: 'pdf' })">
              <q-item-section avatar>
                <q-icon name="picture_as_pdf" color="red-9" />
              </q-item-section>
              <q-item-section>Export PDF</q-item-section>
            </q-item>
            <q-item clickable v-ripple v-close-popup :href="route('admin.product.export', { format: 'excel' })">
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
          <q-select v-model="filter.status" class="custom-select col-xs-12 col-sm-2" :options="statuses" label="Status"
            dense map-options emit-value outlined style="min-width: 150px" @update:model-value="onFilterChange" />
          <q-select v-model="filter.category_id" label="Kategori" class="custom-select col-xs-12 col-sm-2" outlined
            use-input input-debounce="300" clearable :options="filteredCategories" map-options dense emit-value
            @filter="filterCategories" style="min-width:150px" @update:model-value="onFilterChange" />
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
            <span>{{ message }} {{ filter ? " with term " + filter : "" }}</span>
          </div>
        </template>
        <template v-slot:body="props">
          <q-tr :props="props" :class="{ 'inactive': !props.row.active }" class="cursor-pointer"
            @click="router.get(route('admin.product.detail', props.row.id))">
            <q-td key="category" :props="props" class="wrap-column">
              {{ props.row.category ? props.row.category.name : '' }}
            </q-td>
            <q-td key="name" :props="props" class="wrap-column">
              {{ props.row.name }}
              <template v-if="!$q.screen.gt.sm">
                <div v-if="props.row.category_id" class="text-grey-8">
                  <q-icon name="category" /> {{ props.row.category.name }}
                </div>
                <template v-if="$page.props.auth.user.role != 'bs'">
                  <div>
                    <q-icon name="sell" /> Harga: Rp. {{ formatNumber(props.row.price_2) }} / {{ props.row.uom_2 }}
                  </div>
                  <div>
                    <q-icon name="sell" /> Harga Distributor: Rp. {{ formatNumber(props.row.price_1) }} / {{
                      props.row.uom_1 }}
                  </div>
                </template>
              </template>
            </q-td>
            <q-td key="price_1" :props="props" class="wrap-column">
              {{ formatNumber(props.row.price_1) }} / {{ props.row.uom_1 }}
            </q-td>
            <q-td key="price_2" :props="props" class="wrap-column">
              {{ formatNumber(props.row.price_2) }} / {{ props.row.uom_2 }}
            </q-td>
            <q-td key="action" :props="props">
              <div class="flex justify-end">
                <q-btn :disabled="!check_role($CONSTANTS.USER_ROLE_ADMIN)" icon="more_vert" dense flat
                  style="height: 40px; width: 30px" @click.stop>
                  <q-menu anchor="bottom right" self="top right" transition-show="scale" transition-hide="scale">
                    <q-list style="width: 200px">
                      <q-item clickable v-ripple v-close-popup @click.stop="
                        router.get(
                          route(
                            'admin.product.duplicate',
                            props.row.id
                          )
                        )
                        ">
                        <q-item-section avatar>
                          <q-icon name="file_copy" />
                        </q-item-section>
                        <q-item-section icon="copy">Duplikat</q-item-section>
                      </q-item>
                      <q-item clickable v-ripple v-close-popup @click.stop="
                        router.get(
                          route('admin.product.edit', props.row.id)
                        )
                        ">
                        <q-item-section avatar>
                          <q-icon name="edit" />
                        </q-item-section>
                        <q-item-section icon="edit">Edit</q-item-section>
                      </q-item>
                      <q-item v-if="$page.props.auth.user.role == $CONSTANTS.USER_ROLE_ADMIN"
                        @click.stop="deleteItem(props.row)" clickable v-ripple v-close-popup>
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
