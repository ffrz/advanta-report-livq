<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { formatNumber, getQueryParams } from "@/helpers/utils";
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
  sortBy: "id",
  descending: true,
});

const columns = [
  {
    name: "location",
    label: "Lokasi",
    field: "location",
    align: "left",
    sortable: true,
  },
  {
    name: "type_id",
    label: "Kegiatan",
    field: "type_id",
    align: "left",
    sortable: true,
  },
  {
    name: "product_id",
    label: "Varietas",
    field: "product_id",
    align: "left",
    sortable: true,
  },
  {
    name: "cost",
    label: "Budget (Rp)",
    field: "cost",
    align: "right",
    sortable: true,
  },
  { name: "notes", label: "Catatan", field: "notes", align: "left" },
  { name: "action", align: "right" },
];

onMounted(() => fetchItems());

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus rencana kegiatan ${row.type.name} di ${row.location}?`,
    url: route("admin.activity-plan-detail.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) =>
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.activity-plan-detail.data", {
      parent_id: page.props.data.id,
    }),
    loading,
  });

const onRowClicked = (row) =>
  router.get(route("admin.activity-plan-detail.detail", { id: row.id }));

const computedColumns = computed(() =>
  $q.screen.gt.sm
    ? columns
    : columns.filter((col) => ["location", "action"].includes(col.name))
);
</script>

<template>
  <div class="q-pa-none">
    <div class="q-pa-sm">
      <q-btn
        label="Tambah&nbsp;&nbsp;"
        color="primary"
        size="sm"
        icon="add"
        dense
        v-if="$can('admin.activity-plan-detail.add')"
        @click="
          router.get(
            route('admin.activity-plan-detail.add', {
              parent_id: page.props.data.id,
            })
          )
        "
      />
    </div>
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
          <q-td key="location" :props="props">
            <template v-if="!$q.screen.lt.md">
              {{ props.row.location }}
            </template>
            <template v-else>
              <div><q-icon name="home_pin" /> {{ props.row.location }}</div>
              <div>
                <q-icon name="event_upcoming" /> {{ props.row.type.name }}
              </div>
              <div v-if="props.row.product_id">
                <q-icon name="grass" /> {{ props.row.product.name }}
              </div>
              <div>
                <q-icon name="payments" /> Rp.
                {{ formatNumber(props.row.cost) }}
              </div>
              <div
                v-if="props.row.notes"
                style="
                  white-space: pre-wrap;
                  word-break: break-word;
                  overflow-wrap: break-word;
                "
              >
                <q-icon name="notes" />
                {{
                  props.row.notes.length > 100
                    ? props.row.notes.slice(0, 100) + "..."
                    : props.row.notes
                }}
              </div>
            </template>
          </q-td>
          <q-td key="type_id" :props="props">
            {{ props.row.type.name }}
          </q-td>
          <q-td key="product_id" :props="props">
            {{ props.row.product_id ? props.row.product.name : "" }}
          </q-td>
          <q-td key="cost" :props="props">
            {{ formatNumber(props.row.cost) }}
          </q-td>
          <q-td key="notes" :props="props">
            <div
              v-if="props.row.notes"
              style="
                white-space: pre-wrap;
                word-break: break-word;
                overflow-wrap: break-word;
              "
            >
              {{
                props.row.notes.length > 100
                  ? props.row.notes.slice(0, 100) + "..."
                  : props.row.notes
              }}
            </div>
          </q-td>
          <q-td key="action" :props="props">
            <div
              class="flex justify-end"
              v-if="
                $can('admin.activity-plan-detail.edit') ||
                $can('admin.activity-plan-detail.delete')
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
                      v-if="$can('admin.activity-plan-detail.edit')"
                      clickable
                      v-ripple
                      v-close-popup
                      @click.stop="
                        router.get(
                          route('admin.activity-plan-detail.edit', props.row.id)
                        )
                      "
                    >
                      <q-item-section avatar>
                        <q-icon name="edit" />
                      </q-item-section>
                      <q-item-section icon="edit">Edit</q-item-section>
                    </q-item>
                    <q-item
                      v-if="$can('admin.activity-plan-detail.delete')"
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
</template>
