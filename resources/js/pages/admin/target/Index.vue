<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { check_role, formatNumber, getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { usePageStorage } from '@/helpers/usePageStorage'

const storage = usePageStorage('targets')
const page = usePage();
const title = "Target";
const $q = useQuasar();
const showFilter = ref(true);
const rows = ref([]);
const loading = ref(true);

const filter = reactive(storage.get('filter', {
  period: "all",
  user_id: "all",
  ...getQueryParams(),
}));

const pagination = ref(storage.get('pagination', {
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "id",
  descending: true,
}));

const period_options = [
  { value: "all", label: "Semua" },
  { value: "this_month", label: "Bulan Ini" },
  { value: "last_month", label: "Bulan Lalu" },
  { value: "this_year", label: "Tahun Ini" },
  { value: "last_year", label: "Tahun Lalu" },
];

const users = [
  { value: "all", label: "Semua" },
  ...page.props.users.map((user) => ({
    value: user.id,
    label: `${user.name} (${user.username})`,
  })),
];

const columns = [
  { name: "id", label: "#", field: "id", align: "left", sortable: true },
  { name: "date", label: "Bulan", field: "date", align: "left", sortable: true },
  { name: "user", label: "BS", field: "user", align: "left" },
  { name: "fm", label: "FM", field: "fm", align: "center" },
  { name: "odp", label: "ODP", field: "odp", align: "center" },
  { name: "ft", label: "FT", field: "ft", align: "center" },
  { name: "fdd", label: "FDD", field: "fdd", align: "center" },
  { name: "progress", label: "Progres", field: "progress", align: "center" },
  { name: "action", align: "right" },
];

onMounted(() => fetchItems());

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus target #${row.id}?`,
    url: route("admin.target.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) =>
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.target.data"),
    loading,
  });

const onFilterChange = () => fetchItems();
const onRowClicked = (row) => router.get(route('admin.target.detail', { id: row.id }));
const computedColumns = computed(() =>
  $q.screen.gt.sm ? columns : columns.filter((col) => ["id", "action"].includes(col.name))
);

watch(filter, () => storage.set('filter', filter), { deep: true })
watch(pagination, () => storage.set('pagination', pagination.value), { deep: true })

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn icon="add" dense color="primary" @click="router.get(route('admin.target.add'))" />
      <q-btn class="q-ml-sm" :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'" color="grey" dense
        @click="showFilter = !showFilter" />
      <q-btn icon="file_export" dense class="q-ml-sm" color="grey" style="" @click.stop>
        <q-menu anchor="bottom right" self="top right" transition-show="scale" transition-hide="scale">
          <q-list style="width: 200px">
            <q-item clickable v-ripple v-close-popup
              :href="route('admin.target.export', { format: 'pdf', filter: filter })">
              <q-item-section avatar>
                <q-icon name="picture_as_pdf" color="red-9" />
              </q-item-section>
              <q-item-section>Export PDF</q-item-section>
            </q-item>
            <q-item clickable v-ripple v-close-popup
              :href="route('admin.target.export', { format: 'excel', filter: filter })">
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
          <q-select class="custom-select col" style="min-width: 150px" v-model="filter.period" :options="period_options"
            label="Periode" dense map-options emit-value outlined @update:model-value="onFilterChange" />
          <q-select v-if="check_role($CONSTANTS.USER_ROLE_ADMIN) || check_role($CONSTANTS.USER_ROLE_AGRONOMIST)"
            class="custom-select col" style="min-width: 150px" v-model="filter.user_id" :options="users" label="BS"
            dense map-options emit-value outlined @update:model-value="onFilterChange" />
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
                #{{ props.row.id }}
                <template v-if="$q.screen.lt.md">
                  - <span><q-icon name="history" /> {{ $dayjs(props.row.date).format('MMMM YYYY') }}</span>
                </template>
              </div>
              <template v-if="$q.screen.lt.md">
                <div>
                  <q-icon name="person" /> {{ props.row.user.name }} ({{ props.row.user.username }})
                </div>
                <div>
                  <q-icon name="target" />
                  FM: {{ formatNumber(props.row.fm) }},
                  ODP: {{ formatNumber(props.row.odp) }},
                  FT: {{ formatNumber(props.row.ft) }},
                  FDD: {{ formatNumber(props.row.fdd) }}<br />
                </div>
                <div>
                  <q-icon name="timeline" />
                  <span>{{ formatNumber(props.row.progress) }} / {{
                    formatNumber(props.row.total_target) }}
                    ({{ formatNumber(props.row.total_target > 0 ? (props.row.progress / props.row.total_target) * 100 :
                      0)
                    }}%)</span>
                  <q-linear-progress :value="props.row.total_target > 0
                    ? props.row.progress / props.row.total_target
                    : 0" color="primary" track-color="grey-3" size="10px" rounded stripe animated />
                </div>
                <div v-if="props.row.notes"><q-icon name="notes" /> {{ props.row.notes }}</div>
              </template>
            </q-td>
            <q-td key="date" :props="props" class="wrap-column">
              {{ $dayjs(props.row.date).format('MMMM YYYY') }}
            </q-td>
            <q-td key="user" :props="props">
              {{ props.row.user.name }} ({{ props.row.user.username }})
            </q-td>
            <q-td key="fm" :props="props">
              {{ formatNumber(props.row.fm) }}
            </q-td>
            <q-td key="odp" :props="props">
              {{ formatNumber(props.row.odp) }}
            </q-td>
            <q-td key="ft" :props="props">
              {{ formatNumber(props.row.ft) }}
            </q-td>
            <q-td key="fdd" :props="props">
              {{ formatNumber(props.row.fdd) }}
            </q-td>
            <q-td key="progress" :props="props">
              {{ formatNumber(props.row.progress) }} / {{ formatNumber(props.row.total_target) }}
              ({{ formatNumber(props.row.total_target > 0 ? (props.row.progress / props.row.total_target) * 100 : 0)
              }}%)
              <q-linear-progress :value="props.row.total_target > 0
                ? props.row.progress / props.row.total_target
                : 0" color="primary" track-color="grey-3" size="10px" rounded stripe animated />
            </q-td>
            <q-td key="action" :props="props">
              <div class="flex justify-end">
                <q-btn :disabled="!check_role($CONSTANTS.USER_ROLE_ADMIN)" icon="more_vert" dense flat
                  style="height: 40px; width: 30px" @click.stop>
                  <q-menu anchor="bottom right" self="top right" transition-show="scale" transition-hide="scale">
                    <q-list style="width: 200px">
                      <q-item clickable v-ripple v-close-popup
                        @click.stop="router.get(route('admin.target.edit', props.row.id))">
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
