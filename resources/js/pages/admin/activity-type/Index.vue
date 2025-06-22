<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { check_role, getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";

const title = "Jenis Kegiatan";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
const statuses = [
  { value: "all", label: "Semua" },
  { value: "active", label: "Aktif" },
  { value: "inactive", label: "Tidak Aktif" },
];

const filter = reactive({
  search: "",
  status: "all",
  ...getQueryParams(),
});
const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "name",
  descending: false,
});

const columns = [
  { name: "name", label: "Kegiatan", field: "name", align: "left", sortable: true },
  { name: "description", label: "Deskripsi", field: "description", align: "left" },
  { name: "target_period", label: "Periode Target", field: "target_period", align: "center" },
  { name: "default_target", label: "Default Target", field: "default_target", align: "right" },
  { name: "weight", label: "Bobot", field: "weight", align: "right" },
  { name: "action", align: "right" },
];

onMounted(() => {
  fetchItems();
});

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus Kegiatan ${row.name}?`,
    url: route("admin.activity-type.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.activity-type.data"),
    loading,
  });
};

const onFilterChange = () => {
  fetchItems();
};
const computedColumns = computed(() =>
  $q.screen.gt.sm ? columns : columns.filter((col) => ["name", "action"].includes(col.name)));

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn icon="add" dense color="primary" @click="router.get(route('admin.activity-type.add'))" />
      <q-btn class="q-ml-sm" :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'" color="grey" dense
        @click="showFilter = !showFilter" />
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select v-model="filter.status" class="custom-select col-xs-12 col-sm-2" :options="statuses" label="Status"
            dense map-options emit-value outlined style="min-width: 150px" @update:model-value="onFilterChange" />
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
          <q-tr :props="props">
            <q-td key="name" :props="props" class="wrap-column">
              {{ props.row.name }}
              <template v-if="!$q.screen.gt.sm">
                <div v-if="props.row.description" class="text-grey-8"><q-icon name="description" /> {{
                  props.row.description }}
                </div>
                <div>Periode Target: {{ $CONSTANTS.ACTIVITY_TYPE_TARGET_PERIODS[props.row.target_period] }}</div>
                <div>Default Target: {{ props.row.default_target }}</div>
                <div>Bobot: {{ props.row.weight }}</div>
              </template>
            </q-td>
            <q-td key="description" :props="props" class="wrap-column">
              {{ props.row.description }}
            </q-td>
            <q-td key="target_period" :props="props" class="wrap-column">
              {{ $CONSTANTS.ACTIVITY_TYPE_TARGET_PERIODS[props.row.target_period] }}
            </q-td>
            <q-td key="default_target" :props="props" class="wrap-column">
              {{ props.row.default_target }}
            </q-td>
            <q-td key="weight" :props="props" class="wrap-column">
              {{ props.row.weight }}
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
                            'admin.activity-type.duplicate',
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
                          route('admin.activity-type.edit', props.row.id)
                        )
                        ">
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
