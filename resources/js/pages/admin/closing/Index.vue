<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { check_role, formatNumber, getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";

const page = usePage();
const title = "Closing";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
const filter = reactive({
  search: "",
  period: "all",
  user_id: "all",
  service_id: "all",
  ...getQueryParams(),
});

const period_options = [
  { value: "all", label: "Semua" },
  { value: "this_month", label: "Bulan Ini" },
  { value: "last_month", label: "Bulan Lalu" },
  { value: "this_year", label: "Tahun Ini" },
  { value: "last_year", label: "Tahun Lalu" },
];

const services = [
  { value: "all", label: "Semua" },
  ...page.props.services.map((service) => ({
    value: service.id,
    label: service.name,
  })),
];
const users = [
  { value: "all", label: "Semua" },
  ...page.props.users.map((user) => ({
    value: user.id,
    label: `${user.name} (${user.username})`,
  })),
];
const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "id",
  descending: true,
});

const columns = [
  { name: "id", label: "#", field: "id", align: "left", sortable: true },
  { name: "date", label: "Tanggal", field: "date", align: "left", sortable: true },
  { name: "sales", label: "Sales", field: "sales", align: "left" },
  { name: "customer", label: "Client", field: "customer", align: "left" },
  { name: "service", label: "Layanan", field: "service", align: "left" },
  { name: "description", label: "Deskripsi", field: "description", align: "left" },
  { name: "amount", label: "Jumlah (Rp)", field: "amount", align: "right" },
  { name: "action", align: "right" },
];

onMounted(() => fetchItems());

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus closing #${row.id}?`,
    url: route("admin.closing.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) =>
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.closing.data"),
    loading,
  });

const onFilterChange = () => fetchItems();
const onRowClicked = (row) => router.get(route('admin.closing.detail', { id: row.id }));
const computedColumns = computed(() =>
  $q.screen.gt.sm ? columns : columns.filter((col) => ["id", "action"].includes(col.name))
);

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn icon="add" dense color="primary" @click="router.get(route('admin.closing.add'))" />
      <q-btn class="q-ml-sm" :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'" color="grey" dense
        @click="showFilter = !showFilter" />
      <q-btn icon="file_export" dense class="q-ml-sm" color="grey" style="" @click.stop>
        <q-menu anchor="bottom right" self="top right" transition-show="scale" transition-hide="scale">
          <q-list style="width: 200px">
            <q-item clickable v-ripple v-close-popup
              :href="route('admin.closing.export', { format: 'pdf', filter: filter })">
              <q-item-section avatar>
                <q-icon name="picture_as_pdf" color="red-9" />
              </q-item-section>
              <q-item-section>Export PDF</q-item-section>
            </q-item>
            <q-item clickable v-ripple v-close-popup
              :href="route('admin.closing.export', { format: 'excel', filter: filter })">
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
          <q-select class="custom-select col-xs-12 col-sm-2" style="min-width: 150px" v-model="filter.period"
            :options="period_options" label="Periode" dense map-options emit-value outlined
            @update:model-value="onFilterChange" />
          <q-select class="custom-select col-xs-12 col-sm-2" style="min-width: 150px" v-model="filter.user_id"
            :options="users" label="Sales" dense map-options emit-value outlined @update:model-value="onFilterChange" />
          <q-select class="custom-select col-xs-12 col-sm-2" style="min-width: 150px" v-model="filter.service_id"
            :options="services" label="Layanan" dense map-options emit-value outlined
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
      <q-table class="full-height-table" ref="tableRef" flat bordered square color="primary" row-key="id" virtual-scroll
        v-model:pagination="pagination" :filter="filter.search" :loading="loading" :columns="computedColumns"
        :rows="rows" :rows-per-page-options="[10, 25, 50]" @request="fetchItems" binary-state-sort>
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
              {{ $dayjs(props.row.date).format('DD MMMM YYYY') }}
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
            <q-td key="action" :props="props">
              <div class="flex justify-end">
                <q-btn :disabled="!check_role($CONSTANTS.USER_ROLE_ADMIN)" icon="more_vert" dense flat
                  style="height: 40px; width: 30px" @click.stop>
                  <q-menu anchor="bottom right" self="top right" transition-show="scale" transition-hide="scale">
                    <q-list style="width: 200px">
                      <q-item clickable v-ripple v-close-popup
                        @click.stop="router.get(route('admin.closing.edit', props.row.id))">
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
