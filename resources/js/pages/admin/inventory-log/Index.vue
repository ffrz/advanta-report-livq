<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams, formatNumber, check_role } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { usePageStorage } from "@/helpers/usePageStorage";
import { formatDate } from "@/helpers/datetime";
import { useProductFilter } from "@/composables/useProductFilter";
import { useCustomerFilter } from "@/composables/useCustomerFilter";

const page = usePage();
const storage = usePageStorage("inventory-log");

const title = "Log Inventori";
const $q = useQuasar();
const showFilter = ref(storage.get("show-filter", false));
const rows = ref([]);
const loading = ref(true);
const filter = reactive(
  storage.get("filter", {
    search: "",
    customer_id: null,
    product_id: "all",
    user_id:
      page.props.auth.user.role == "bs"
        ? Number(page.props.auth.user.id)
        : "all",
    ...getQueryParams(),
  })
);
const pagination = ref(
  storage.get("pagination", {
    page: 1,
    rowsPerPage: 10,
    rowsNumber: 10,
    sortBy: "id",
    descending: true,
  })
);

const columns = [
  {
    name: "check_date",
    label: $q.screen.gt.sm ? "Tanggal" : "Log Item",
    field: "check_date",
    align: "left",
  },
  { name: "area", label: "Area", field: "area", align: "left" },
  {
    name: "category",
    label: "Crops",
    field: "category",
    align: "left",
    sortable: true,
  },
  {
    name: "user",
    label: "Checker",
    field: "user",
    align: "left",
    sortable: true,
  },
  {
    name: "customer",
    label: "Kiosk / Distributor",
    field: "customer",
    align: "left",
    sortable: true,
  },
  {
    name: "product",
    label: "Hybrid",
    field: "product",
    align: "left",
    sortable: true,
  },
  {
    name: "lot_package",
    label: "LOT Package",
    field: "lot_package",
    align: "left",
    sortable: true,
  },
  {
    name: "quantity",
    label: "Quantity",
    field: "quantity",
    align: "right",
    sortable: true,
  },
  { name: "action", align: "right" },
];

const users = [
  { value: "all", label: "Semua" },
  ...page.props.users.map((user) => ({
    value: user.id,
    label: `${user.name} (${user.username})`,
  })),
];

const { products } = useProductFilter(page.props.products, true);
const { filterCustomers, filteredCustomers } = useCustomerFilter(
  page.props.customers
);
const productOptions = products;

onMounted(() => {
  fetchItems();
});

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus Log Inventori ${row.name}?`,
    url: route("admin.inventory-log.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.inventory-log.data"),
    loading,
  });
};

const onFilterChange = () => {
  fetchItems();
};

const computedColumns = computed(() =>
  $q.screen.gt.sm
    ? columns
    : columns.filter((col) => ["check_date", "action"].includes(col.name))
);

watch(filter, () => storage.set("filter", filter), { deep: true });
watch(showFilter, () => storage.set("show-filter", showFilter.value), {
  deep: true,
});
watch(pagination, () => storage.set("pagination", pagination.value), {
  deep: true,
});
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn
        icon="add"
        dense
        color="primary"
        @click="router.get(route('admin.inventory-log.add'))"
        v-if="$can('admin.inventory-log.add')"
      />
      <q-btn
        class="q-ml-sm"
        :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'"
        color="grey"
        dense
        @click="showFilter = !showFilter"
      />
      <q-btn
        v-if="false"
        icon="file_export"
        dense
        class="q-ml-sm"
        color="grey"
        style=""
        @click.stop
      >
        <q-menu
          anchor="bottom right"
          self="top right"
          transition-show="scale"
          transition-hide="scale"
        >
          <q-list style="width: 200px">
            <q-item
              clickable
              v-ripple
              v-close-popup
              :href="route('admin.inventory-log.export', { format: 'pdf' })"
            >
              <q-item-section avatar>
                <q-icon name="picture_as_pdf" color="red-9" />
              </q-item-section>
              <q-item-section>Export PDF</q-item-section>
            </q-item>
            <q-item
              clickable
              v-ripple
              v-close-popup
              :href="route('admin.inventory-log.export', { format: 'excel' })"
            >
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
          <q-select
            class="custom-select col-xs-12 col-sm-2"
            style="min-width: 150px"
            v-model="filter.user_id"
            v-show="check_role(['admin', 'agronomist'])"
            :options="users"
            label="Checker"
            dense
            map-options
            emit-value
            outlined
            @update:model-value="onFilterChange"
          />
          <q-select
            class="custom-select col-xs-12 col-sm-2"
            style="min-width: 150px"
            v-model="filter.product_id"
            :options="productOptions"
            label="Varietas"
            dense
            map-options
            emit-value
            outlined
            @update:model-value="onFilterChange"
          />
          <q-select
            class="custom-select col-xs-12 col-sm-2"
            v-model="filter.customer_id"
            style="min-width: 150px"
            label="Client"
            use-input
            dense
            outlined
            input-debounce="300"
            clearable
            :options="filteredCustomers"
            map-options
            emit-value
            @filter="filterCustomers"
            @update:model-value="onFilterChange"
          >
            <template v-slot:no-option>
              <q-item>
                <q-item-section>Client tidak ditemukan</q-item-section>
              </q-item>
            </template>
          </q-select>
          <q-input
            class="col"
            outlined
            dense
            debounce="300"
            v-model="filter.search"
            placeholder="Cari"
            clearable
          >
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
      </q-toolbar>
    </template>
    <div class="q-pa-sm">
      <q-table
        flat
        bordered
        square
        color="primary"
        row-key="id"
        virtual-scroll
        v-model:pagination="pagination"
        :filter="filter.search"
        :loading="loading"
        :columns="computedColumns"
        :rows="rows"
        :rows-per-page-options="[10, 25, 50]"
        @request="fetchItems"
        binary-state-sort
      >
        <template v-slot:loading>
          <q-inner-loading showing color="red" />
        </template>
        <template v-slot:no-data="{ icon, message, filter }">
          <div class="full-width row flex-center text-grey-8 q-gutter-sm">
            <span
              >{{ message }} {{ filter ? " with term " + filter : "" }}</span
            >
          </div>
        </template>
        <template v-slot:body="props">
          <q-tr
            :props="props"
            class="cursor-pointer"
            @click="
              router.get(route('admin.inventory-log.detail', props.row.id))
            "
          >
            <q-td key="check_date" :props="props" class="wrap-column">
              <template v-if="!$q.screen.gt.sm">Tgl: </template
              >{{ formatDate(props.row.check_date) }}
              <template v-if="!$q.screen.gt.sm">
                <div>Area: {{ props.row.area }}</div>
                <div>Crops: {{ props.row.product.category.name }}</div>
                <div>Checker: {{ props.row.user.name }}</div>
                <div>Client: {{ props.row.customer.name }}</div>
                <div>Hybrid: {{ props.row.product.name }}</div>
                <div>Lot Package: {{ props.row.lot_package }}</div>
                <div>
                  Qty: {{ formatNumber(props.row.base_quantity) }} pcs /
                  {{ formatNumber(props.row.quantity, "id-ID", 3) }} kg
                </div>
              </template>
            </q-td>
            <q-td key="area" :props="props">
              {{ props.row.area }}
            </q-td>
            <q-td key="category" :props="props">
              {{ props.row.product.category.name }}
            </q-td>
            <q-td key="user" :props="props">
              {{ props.row.user.name }}
            </q-td>
            <q-td key="customer" :props="props">
              {{ props.row.customer.name }}
            </q-td>
            <q-td key="product" :props="props">
              {{ props.row.product.name }}
            </q-td>
            <q-td key="lot_package" :props="props">
              {{ props.row.lot_package }}
            </q-td>
            <q-td key="quantity" :props="props">
              {{ formatNumber(props.row.base_quantity) }} pcs /
              {{ formatNumber(props.row.quantity, "id-ID", 3) }} kg
            </q-td>
            <q-td key="action" :props="props">
              <div
                class="flex justify-end"
                v-if="
                  $can('admin.inventory-log.edit') ||
                  $can('admin.inventory-log.delete')
                "
              >
                <q-btn
                  icon="more_vert"
                  dense
                  flat
                  style="height: 40px; width: 30px"
                  @click.stop
                >
                  <q-menu
                    anchor="bottom right"
                    self="top right"
                    transition-show="scale"
                    transition-hide="scale"
                  >
                    <q-list style="width: 200px">
                      <q-item
                        v-if="$can('admin.inventory-log.edit')"
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="
                          router.get(
                            route('admin.inventory-log.edit', props.row.id)
                          )
                        "
                      >
                        <q-item-section avatar>
                          <q-icon name="edit" />
                        </q-item-section>
                        <q-item-section icon="edit">Edit</q-item-section>
                      </q-item>
                      <q-item
                        v-if="$can('admin.inventory-log.delete')"
                        @click.stop="deleteItem(props.row)"
                        clickable
                        v-ripple
                        v-close-popup
                      >
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
