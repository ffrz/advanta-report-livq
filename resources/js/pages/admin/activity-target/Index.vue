<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { getQueryParams } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { usePageStorage } from "@/helpers/usePageStorage";
import ActivityTable from "@/components/ActivityTable.vue";
import { calculateQuarterActivityProgress } from "@/composables/useCalculateActivityProgress";
import useTableHeight from "@/composables/useTableHeight";

const page = usePage();
const storage = usePageStorage("activity-target");
const title = "Target Kegiatan";
const $q = useQuasar();
const showFilter = ref(storage.get("show-filter", false));
const rows = ref([]);
const loading = ref(true);
const currentYear = new Date().getFullYear();
const tableRef = ref(null);
const filterToolbarRef = ref(null);
const tableHeight = useTableHeight(filterToolbarRef);

const filter = reactive(
  storage.get("filter", {
    search: "",
    user_id: "all",
    year: currentYear,
    quarter: "all",
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

const types = page.props.types;

const years = [
  { value: "all", label: "Semua" },
  ...Array.from({ length: 3 }, (_, i) => {
    const year = currentYear - 1 + i;
    return { value: year, label: String(year) + " / " + String(year + 1) };
  }),
];

const quarters = [
  { value: "all", label: "Semua" },
  { value: 1, label: "Q1 (April-Juni)" },
  { value: 2, label: "Q2 (Juli-September)" },
  { value: 3, label: "Q3 (Oktober-Desember)" },
  { value: 4, label: "Q4 (Januari-Maret)" },
];

const users = [
  { value: "all", label: "Semua" },
  ...page.props.users.map((user) => ({
    value: user.id,
    label: `${user.name} (${user.username})`,
  })),
];

const columns = [
  { name: "period", label: "Periode", field: "period", align: "left" },
  { name: "bs", label: "BS", field: "bs", align: "left" },
  ...types.map((type) => ({
    name: `target-${type.id}`,
    label: type.name,
    align: "center",
  })),
  { name: "total_progress", label: "Total Progress", align: "center" },
  { name: "notes", field: "notes", align: "left", label: "Catatan" },
  { name: "action", align: "right" },
];

onMounted(() => {
  fetchItems();
});

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus Target Kegiatan ${row.user.name} periode ${row.year}-Q${row.quarter}?`,
    url: route("admin.activity-target.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) =>
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.activity-target.data"),
    loading,
    tableRef,
  });

const onFilterChange = () => fetchItems();

const onRowClicked = (row) =>
  router.get(route("admin.activity-target.detail", { id: row.id }));

const computedColumns = computed(() =>
  $q.screen.gt.sm
    ? columns
    : columns.filter((col) => ["period", "action"].includes(col.name))
);

watch(filter, () => storage.set("filter", filter), { deep: true });
watch(pagination, () => storage.set("pagination", pagination.value), {
  deep: true,
});
watch(showFilter, () => storage.set("show-filter", showFilter.value), {
  deep: true,
});

const calculated_progress = {};
function calculateQuarterProgress(types, row) {
  if (calculated_progress[row.id]) {
    return calculated_progress[row.id];
  }
  const percent = calculateQuarterActivityProgress(
    types,
    row.details,
    row.activities
  );
  calculated_progress[row.id] = percent;
  return percent;
}

const exportPdf = () => {
  const url = route("admin.activity-target.export", {
    format: "pdf",
    filter: filter,
  });
  window.open(url, "_blank");
};
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn
        v-if="$can('admin.activity-target.add')"
        icon="add"
        dense
        color="primary"
        @click="router.get(route('admin.activity-target.add'))"
      />
      <q-btn
        class="q-ml-sm"
        :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'"
        color="grey"
        dense
        @click="showFilter = !showFilter"
      />
      <q-btn
        v-if="$can('admin.activity-target.export')"
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
            <q-item clickable v-ripple v-close-popup @click.stop="exportPdf()">
              <q-item-section avatar>
                <q-icon name="picture_as_pdf" color="red-9" />
              </q-item-section>
              <q-item-section>Export PDF</q-item-section>
            </q-item>
            <!-- <q-item
              clickable
              v-ripple
              v-close-popup
              :href="
                route('admin.activity-target.export', {
                  format: 'excel',
                  filter: filter,
                })
              "
            >
              <q-item-section avatar>
                <q-icon name="csv" color="green-9" />
              </q-item-section>
              <q-item-section>Export Excel</q-item-section>
            </q-item> -->
          </q-list>
        </q-menu>
      </q-btn>
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar" ref="filterToolbarRef">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select
            class="custom-select col-xs-12 col-sm-2"
            style="min-width: 120px"
            v-model="filter.year"
            :options="years"
            label="Tahun"
            dense
            emit-value
            map-options
            outlined
            @update:model-value="onFilterChange"
          />

          <q-select
            class="custom-select col-xs-12 col-sm-2"
            style="min-width: 120px"
            v-model="filter.quarter"
            :options="quarters"
            label="Kuartal"
            dense
            emit-value
            map-options
            outlined
            @update:model-value="onFilterChange"
          />
          <q-select
            class="custom-select col-xs-12 col-sm-2"
            style="min-width: 150px"
            v-model="filter.user_id"
            v-show="$page.props.auth.user.role != 'bs'"
            :options="users"
            label="BS"
            dense
            map-options
            emit-value
            outlined
            @update:model-value="onFilterChange"
          />
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
        ref="tableRef"
        :style="{ height: tableHeight }"
        class="full-height-table"
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
            <span>
              {{ message }}
              {{ filter ? " with term " + filter : "" }}
            </span>
          </div>
        </template>

        <template v-slot:body="props">
          <q-tr
            :props="props"
            :class="props.row.active == 'inactive' ? 'bg-red-1' : ''"
            class="cursor-pointer"
            @click="onRowClicked(props.row)"
          >
            <q-td key="period" :props="props">
              {{ props.row.year }}-Q{{ props.row.quarter }}

              <template v-if="$q.screen.lt.md">
                <!-- Informasi User -->
                <div>
                  <q-icon name="person" />
                  {{ props.row.user.name }} ({{ props.row.user.username }})
                </div>

                <!-- Progress Keseluruhan -->
                <div class="q-mt-sm">
                  Progress:
                  {{ calculateQuarterProgress(types, props.row).toFixed(2) }}%
                  <q-linear-progress
                    :value="calculateQuarterProgress(types, props.row) / 100"
                    size="10px"
                    color="primary"
                    class="q-mt-xs"
                    rounded
                    stripe
                    animated
                  />
                </div>

                <!-- Detail Target dan Realisasi -->
                <ActivityTable
                  :types="types"
                  :targets="props.row.details"
                  :plans="props.row.plans"
                  :activities="props.row.activities"
                />

                <!-- Notes -->
                <div v-if="props.row.notes" class="text-grey-8">
                  <q-icon name="notes" />
                  {{ props.row.notes }}
                </div>
              </template>
            </q-td>

            <q-td key="bs" :props="props">
              {{ props.row.user.name }} ({{ props.row.user.username }})
            </q-td>
            <q-td
              v-for="type in types"
              :key="`target-${type.id}`"
              :props="props"
              class="text-right"
            >
              <!-- Target -->
              <div>
                T:
                {{
                  (() => {
                    const detail = props.row.details.find(
                      (d) => Number(d.type_id) === Number(type.id)
                    );
                    return detail
                      ? `${detail.quarter_qty} (${detail.month1_qty}/${detail.month2_qty}/${detail.month3_qty})`
                      : "-";
                  })()
                }}
              </div>

              <!-- (Plan) -->
              <div v-if="props.row.plans && props.row.plans[type.id]">
                P:
                {{
                  `${props.row.plans[type.id].quarter_qty} (${
                    props.row.plans[type.id].month1_qty
                  }/${props.row.plans[type.id].month2_qty}/${
                    props.row.plans[type.id].month3_qty
                  })`
                }}
              </div>

              <!-- Realisasi -->
              <div v-if="props.row.activities && props.row.activities[type.id]">
                R:
                {{
                  `${props.row.activities[type.id].quarter_qty} (${
                    props.row.activities[type.id].month1_qty
                  }/${props.row.activities[type.id].month2_qty}/${
                    props.row.activities[type.id].month3_qty
                  })`
                }}
              </div>
            </q-td>
            <q-td key="total_progress" :props="props">
              {{ calculateQuarterProgress(types, props.row).toFixed(2) }}%

              <q-linear-progress
                :value="calculateQuarterProgress(types, props.row) / 100"
                size="10px"
                color="primary"
                class="q-mt-xs"
                rounded
                stripe
                animated
              />
            </q-td>
            <q-td key="notes" :props="props">
              {{ props.row.notes }}
            </q-td>
            <q-td key="action" :props="props">
              <div
                class="flex justify-end"
                v-if="
                  $can('admin.activity-target.edit') ||
                  $can('admin.activity-target.delete')
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
                        v-if="$can('admin.activity-target.edit')"
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="
                          router.get(
                            route('admin.activity-target.edit', props.row.id)
                          )
                        "
                      >
                        <q-item-section avatar>
                          <q-icon name="edit" />
                        </q-item-section>
                        <q-item-section>Edit</q-item-section>
                      </q-item>
                      <q-item
                        v-if="$can('admin.activity-target.delete')"
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
