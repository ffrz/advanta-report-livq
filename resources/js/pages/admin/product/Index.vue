<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { check_role, getQueryParams, formatNumber } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { create_options } from "@/helpers/utils";
import { useProductCategoryFilter } from "@/helpers/useProductCategoryFilter";
import { useSupplierFilter } from "@/helpers/useSupplierFilter";

const page = usePage();

const showCostColumn = ref(false);

const types = [
  { value: "all", label: "Semua" },
  ...create_options(window.CONSTANTS.PRODUCT_TYPES),
];

const statuses = [
  { value: "all", label: "Semua" },
  { value: "active", label: "Aktif" },
  { value: "inactive", label: "Tidak Aktif" },
];

const stock_statuses = [
  { value: "all", label: "Semua" },
  { value: "ready", label: "Tersedia" },
  { value: "out", label: "Kosong" },
  { value: "low", label: "Stok Rendah" },
  { value: "over", label: "Stok Berlebih" },
];

const title = "Produk";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
const filter = reactive({
  type: "all",
  category_id: "all",
  supplier_id: "all",
  status: "active",
  stock_status: "all",
  search: "",
  ...getQueryParams(),
});
const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "name",
  descending: false,
});
let columns = [
  {
    name: "name",
    label: "Nama",
    field: "name",
    align: "left",
    sortable: true,
  },
  {
    name: "stock",
    label: "Stok",
    field: "stock",
    align: "right",
  },
  {
    name: "cost",
    label: "Modal",
    field: "cost",
    align: "right",
  },
  {
    name: "price",
    label: "Harga",
    field: "price",
    align: "right",
  },
  {
    name: "action",
    align: "right",
  },
];

onMounted(() => {
  fetchItems();
});

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus Produk ${row.name}?`,
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
const { filteredSuppliers, filterSuppliers } = useSupplierFilter(page.props.suppliers, true);

const computedColumns = computed(() => {
  let computedColumns = [...columns];
  if (!showCostColumn.value) {
    computedColumns.splice(2, 1)
  }

  if ($q.screen.gt.sm) return computedColumns;

  return computedColumns.filter((col) => col.name === "name" || col.name === "action");
});
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn v-if="$page.props.auth.user.role == $CONSTANTS.USER_ROLE_ADMIN" class="q-mr-sm"
        :icon="!showCostColumn ? 'visibility_off' : 'visibility'" label="" dense color="grey"
        @click="showCostColumn = !showCostColumn" />
      <q-btn icon="add" dense color="primary" @click="router.get(route('admin.product.add'))" />
      <q-btn class="q-ml-sm" :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'" color="grey" dense
        @click="showFilter = !showFilter" />
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select v-model="filter.type" class="custom-select col-xs-12 col-sm-2" :options="types" label="Jenis" dense
            map-options emit-value outlined style="min-width: 150px" @update:model-value="onFilterChange" />
          <q-select v-model="filter.status" class="custom-select col-xs-12 col-sm-2" :options="statuses" label="Status"
            dense map-options emit-value outlined style="min-width: 150px" @update:model-value="onFilterChange" />
          <q-select v-model="filter.stock_status" class="custom-select col-xs-12 col-sm-2" :options="stock_statuses"
            label="Status Stok" dense map-options emit-value outlined style="min-width: 150px"
            @update:model-value="onFilterChange" />
          <q-select v-model="filter.category_id" label="Kategori" class="custom-select col-xs-12 col-sm-2" outlined
            use-input input-debounce="300" clearable :options="filteredCategories" map-options dense emit-value
            @filter="filterCategories" style="min-width:150px" @update:model-value="onFilterChange" />
          <q-select v-model="filter.supplier_id" label="Pemasok" class="custom-select col-xs-12 col-sm-2" outlined
            use-input input-debounce="300" clearable :options="filteredSuppliers" map-options dense emit-value
            @filter="filterSuppliers" style="min-width:150px" @update:model-value="onFilterChange" />
          <q-input class="col" outlined dense debounce="300" v-model="filter.search" placeholder="Cari" clearable>
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
      </q-toolbar>
    </template>
    <div class="q-pa-sm">
      <q-table class="full-height-table" flat bordered square color="primary" row-key="id" virtual-scroll
        v-model:pagination="pagination" :filter="filter.search" :loading="loading" :columns="computedColumns"
        :rows="rows" :rows-per-page-options="[10, 25, 50]" @request="fetchItems" binary-state-sort>
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
            <q-td key="name" :props="props" class="wrap-column">
              {{ props.row.name }}
              <div v-if="props.row.category_id" class="text-grey-8"><q-icon name="category" />
                {{ props.row.category.name }}
              </div>
              <div v-if="props.row.supplier_id" class="text-grey-8"><q-icon name="local_shipping" />
                {{ props.row.supplier.name }}
              </div>
              <template v-if="!$q.screen.gt.sm">
                <div v-if="props.row.type == 'stocked'"><q-icon name="cycle" />
                  Stok: {{ formatNumber(props.row.stock) }} {{ props.row.uom }}
                </div>
                <div v-if="showCostColumn"><q-icon name="money" />
                  Modal: Rp. {{ formatNumber(props.row.cost) }}
                </div>
                <div><q-icon name="sell" />
                  Harga: Rp. {{ formatNumber(props.row.price) }}
                </div>
              </template>
            </q-td>
            <q-td key="stock" :props="props" class="wrap-column" :class="{
              'low-stock': props.row.type == 'stocked' && (Number(props.row.stock) == 0 || Number(props.row.stock) < Number(props.row.min_stock)),
              'over-stock': props.row.type == 'stocked' && (Number(props.row.max_stock) && Number(props.row.stock) > Number(props.row.max_stock))
            }">
              {{ props.row.type == 'stocked' ? formatNumber(props.row.stock) + ' ' + props.row.uom : '-' }}
            </q-td>
            <q-td key="cost" :props="props" class="wrap-column" v-if="true">
              {{ formatNumber(props.row.cost) }}
            </q-td>
            <q-td key="price" :props="props" class="wrap-column">
              {{ formatNumber(props.row.price) }}
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
