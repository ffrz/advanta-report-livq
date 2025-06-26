<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { check_role, getQueryParams, plantAge } from "@/helpers/utils";
import { useQuasar } from "quasar";
import { usePageStorage } from "@/helpers/usePageStorage";
import dayjs from "dayjs";
import { Notify, Dialog } from "quasar";

const page = usePage();
const storage = usePageStorage("activity");
const title = "Kegiatan";
const $q = useQuasar();
const showFilter = ref(storage.get("show-filter", false));
const rows = ref([]);
const loading = ref(true);

const filter = reactive(
  storage.get("filter", {
    search: "",
    user_id:
      page.props.auth.user.role == "bs" ? page.props.auth.user.id : "all",
    type_id: "all",
    status: "all",
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

const statuses = [
  { value: "all", label: "Semua" },
  { value: "approved", label: "Disetujui" },
  { value: "rejected", label: "Ditolak" },
];

const users = [
  { value: "all", label: "Semua" },
  ...page.props.users.map((user) => ({
    value: user.id,
    label: `${user.name} (${user.username})`,
  })),
];

const types = [
  { value: "all", label: "Semua" },
  ...page.props.types.map((type) => ({
    value: type.id,
    label: `${type.name}`,
  })),
];

const columns = [
  { name: "photo", label: "Foto", field: "photo", align: "left" },
  {
    name: "date",
    label: "Tanggal",
    field: "date",
    align: "left",
    sortable: true,
  },
  { name: "type", label: "Jenis Kegiatan", field: "type", align: "left" },
  { name: "bs", label: "BS", field: "bs", align: "left" },
  { name: "status", label: "Status", field: "status", align: "left" },
  { name: "notes", label: "Catatan", field: "notes", align: "left" },
  { name: "action", align: "right" },
];

onMounted(() => {
  fetchItems();
});

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus Kegiatan ${row.type.name} tanggal ${dayjs(row.date).format(
      "DD MMMM YYYY"
    )}?`,
    url: route("admin.activity.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const responActivity = (row, status) => {
  let message = "";
  if (status == "approve") {
    message += "Setujui";
  } else if (status == "reject") {
    message += "Tolak";
  } else {
    message += "Atur ulang status";
  }

  message += ` kegiatan ${row.type.name} - ${row.user.name} tanggal ${dayjs(
    row.date
  ).format("DD MMMM YYYY")}?`;

  Dialog.create({
    title: "Konfirmasi",
    icon: "question",
    message: message,
    focus: "cancel",
    cancel: true,
    persistent: true,
  }).onOk(() => {
    loading.value = true;
    axios
      .post(route("admin.activity.respond", row.id) + "?action=" + status)
      .then((response) => {
        Notify.create(response.data.message);
        fetchItems();
      })
      .finally(() => {
        loading.value = false;
      })
      .catch((error) => {
        let message = "";
        if (error.response.data && error.response.data.message) {
          message = error.response.data.message;
        } else if (error.message) {
          message = error.message;
        }

        if (message.length > 0) {
          Notify.create({ message: message, color: "red" });
        }
        console.log(error);
      });
  });
};

const fetchItems = (props = null) =>
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.activity.data"),
    loading,
  });

const onFilterChange = () => fetchItems();

const onRowClicked = (row) =>
  router.get(route("admin.activity.detail", { id: row.id }));

const computedColumns = computed(() =>
  $q.screen.gt.sm
    ? columns
    : columns.filter((col) => ["date", "action"].includes(col.name))
);

watch(filter, () => storage.set("filter", filter), { deep: true });
watch(pagination, () => storage.set("pagination", pagination.value), {
  deep: true,
});
watch(showFilter, () => storage.set("show-filter", showFilter.value), {
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
        @click="router.get(route('admin.activity.add'))"
      />
      <q-btn
        class="q-ml-sm"
        :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'"
        color="grey"
        dense
        @click="showFilter = !showFilter"
      />
      <q-btn
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
              :href="
                route('admin.activity.export', {
                  format: 'pdf',
                  filter: filter,
                })
              "
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
              :href="
                route('admin.activity.export', {
                  format: 'excel',
                  filter: filter,
                })
              "
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
            v-model="filter.status"
            :options="statuses"
            label="Status"
            dense
            map-options
            emit-value
            outlined
            @update:model-value="onFilterChange"
          />
          <q-select
            class="custom-select col-xs-12 col-sm-2"
            style="min-width: 150px"
            v-model="filter.type_id"
            :options="types"
            label="Jenis Kegiatan"
            dense
            map-options
            emit-value
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
            <q-td key="photo" :props="props">
              <q-img
                :src="`/${props.row.image_path}`"
                style="width: 64px; height: 64px; border: 1px solid #ddd"
                spinner-color="grey"
                fit="cover"
                class="rounded-borders"
              />
            </q-td>
            <q-td key="date" :props="props">
              <template v-if="!$q.screen.lt.md">
                {{ $dayjs(props.row.date).format("DD MMMM YYYY") }}
              </template>
              <template v-else>
                <div class="q-pb-xs">
                  <q-img
                    :src="`/${props.row.image_path}`"
                    style="border: 1px solid #ddd; max-height: 150px"
                    spinner-color="grey"
                    fit="scale-down"
                    class="rounded-borders bg-light-green-2"
                  />
                </div>
                <div>
                  <q-icon name="edit_calendar" />
                  {{ $dayjs(props.row.date).format("DD MMMM YYYY") }}
                </div>
                <div>
                  <q-icon name="overview" />
                  {{ props.row.type.name }}
                </div>
                <div>
                  <q-icon name="person" />
                  {{ props.row.user.name }} ({{ props.row.user.username }})
                </div>
                <div>
                  <template v-if="props.row.status == 'approved'">
                    <q-badge label="Disetujui" color="green" />
                  </template>
                  <template v-else-if="props.row.status == 'rejected'">
                    <q-badge label="Ditolak" color="red" />
                  </template>
                  <template v-else>
                    <q-badge label="Belum Direspon" color="grey" />
                  </template>
                </div>
                <div v-if="props.row.notes">
                  <q-icon name="notes" /> {{ props.row.notes }}
                </div>
              </template>
            </q-td>
            <q-td key="type" :props="props">
              {{ props.row.type.name }}
            </q-td>
            <q-td key="bs" :props="props">
              {{ props.row.user.name }} ({{ props.row.user.username }})
            </q-td>
            <q-td key="status" :props="props">
              <template v-if="props.row.status == 'approved'">
                Disetujui oleh: {{ props.row.responded_by.name }} pada
                {{
                  $dayjs(props.row.responded_datetime).format(
                    "YYYY-MM-DD HH:mm"
                  )
                }}
              </template>
              <template v-else-if="props.row.status == 'rejected'">
                Ditolak oleh: {{ props.row.responded_by.name }} pada
                {{
                  $dayjs(props.row.responded_datetime).format(
                    "YYYY-MM-DD HH:mm"
                  )
                }}
              </template>
              <template v-else> Belum Direspon </template>
            </q-td>
            <q-td key="notes" :props="props">
              {{ props.row.notes }}
            </q-td>
            <q-td key="action" :props="props">
              <div class="flex justify-end">
                <q-btn
                  :disabled="
                    !check_role([
                      $CONSTANTS.USER_ROLE_AGRONOMIST,
                      $CONSTANTS.USER_ROLE_ADMIN,
                      $CONSTANTS.USER_ROLE_BS,
                    ])
                  "
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
                        v-if="
                          props.row.status == 'not_responded' &&
                          ['agronomist', 'admin'].includes(
                            $page.props.auth.user.role
                          )
                        "
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="responActivity(props.row, 'approve')"
                      >
                        <q-item-section avatar>
                          <q-icon name="check" />
                        </q-item-section>
                        <q-item-section>Setujui</q-item-section>
                      </q-item>
                      <q-item
                        v-if="
                          props.row.status == 'not_responded' &&
                          ['agronomist', 'admin'].includes(
                            $page.props.auth.user.role
                          )
                        "
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="responActivity(props.row, 'reject')"
                      >
                        <q-item-section avatar>
                          <q-icon name="close" />
                        </q-item-section>
                        <q-item-section>Tolak</q-item-section>
                      </q-item>
                      <q-item
                        v-if="
                          props.row.status != 'not_responded' &&
                          ['agronomist', 'admin'].includes(
                            $page.props.auth.user.role
                          )
                        "
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="responActivity(props.row, 'reset')"
                      >
                        <q-item-section avatar>
                          <q-icon name="restart_alt" />
                        </q-item-section>
                        <q-item-section>Atur Ulang</q-item-section>
                      </q-item>
                      <q-separator />
                      <q-item
                        v-if="
                          ['bs', 'admin'].includes($page.props.auth.user.role)
                        "
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="
                          router.get(
                            route('admin.activity.duplicate', props.row.id)
                          )
                        "
                      >
                        <q-item-section avatar>
                          <q-icon name="content_copy" />
                        </q-item-section>
                        <q-item-section>Duplikat</q-item-section>
                      </q-item>
                      <q-item
                        v-if="
                          ['bs', 'admin'].includes($page.props.auth.user.role)
                        "
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="
                          router.get(route('admin.activity.edit', props.row.id))
                        "
                      >
                        <q-item-section avatar>
                          <q-icon name="edit" />
                        </q-item-section>
                        <q-item-section>Edit</q-item-section>
                      </q-item>
                      <q-item
                        v-if="
                          ['bs', 'admin'].includes($page.props.auth.user.role)
                        "
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
