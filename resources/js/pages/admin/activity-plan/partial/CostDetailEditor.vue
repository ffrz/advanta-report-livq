<script setup>
// FIXME: FITUR BREAKDOWN INI BARU RUMUSAN

import { ref, computed, watch } from "vue";

const columns = [
  { name: "no", label: "No", align: "left", field: "no" },
  { name: "item", label: "Item", align: "left", field: "item" },
  { name: "qty", label: "Qty", align: "right", field: "qty" },
  { name: "harga", label: "Harga", align: "right", field: "harga" },
  { name: "subtotal", label: "Subtotal", align: "right", field: "subtotal" },
];

const props = defineProps({
  modelValue: Boolean,
});

const emit = defineEmits(["update:modelValue"]);

// Local copy untuk binding
const localDialog = ref(false);

// Sinkronisasi 2 arah
watch(
  () => props.modelValue,
  (val) => {
    localDialog.value = val;
  },
  { immediate: true }
);

watch(localDialog, (val) => {
  emit("update:modelValue", val);
});

const items = ref([{ id: 1, item: "", qty: 0, harga: 0, subtotal: 0 }]);

const tambahItem = () => {
  items.value.push({
    id: Date.now(),
    item: "",
    qty: 0,
    harga: 0,
    subtotal: 0,
  });
};

const hitungSubtotal = (row) => {
  row.subtotal = (row.qty || 0) * (row.harga || 0);
};

const totalSubtotal = computed(() =>
  items.value.reduce((sum, item) => sum + (item.subtotal || 0), 0)
);

const formatCurrency = (val) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(val);
};

const emitClose = () => {
  localDialog.value = false;
};
</script>

<template>
  <q-dialog v-model="localDialog" persistent>
    <q-card style="min-width: 600px">
      <q-card-section class="row items-center justify-between">
        <div class="text-h6">Breakdown Anggaran</div>
        <q-btn icon="close" flat round dense @click="emitClose" />
      </q-card-section>

      <q-separator />

      <q-card-section>
        <q-btn
          label="Tambah Item"
          icon="add"
          color="primary"
          @click="tambahItem"
          class="q-mb-md"
          flat
        />

        <q-table
          :rows="items"
          :columns="columns"
          hide-pagination
          row-key="id"
          class="no-border"
          separator="horizontal"
        >
          <template v-slot:body-cell-no="props">
            <q-td>{{ props.pageIndex + 1 }}</q-td>
          </template>

          <template v-slot:body-cell-item="props">
            <q-td><q-input v-model="props.row.item" dense borderless /></q-td>
          </template>

          <template v-slot:body-cell.qty="props">
            <q-td>
              <q-input
                v-model.number="props.row.qty"
                type="number"
                dense
                borderless
                @update:model-value="hitungSubtotal(props.row)"
              />
            </q-td>
          </template>

          <template v-slot:body-cell.harga="props">
            <q-td>
              <q-input
                v-model.number="props.row.harga"
                type="number"
                dense
                borderless
                @update:model-value="hitungSubtotal(props.row)"
              />
            </q-td>
          </template>

          <template v-slot:body-cell.subtotal="props">
            <q-td>
              {{ formatCurrency(props.row.subtotal) }}
            </q-td>
          </template>
        </q-table>

        <div class="text-right q-mt-md">
          <strong>Total: {{ formatCurrency(totalSubtotal) }}</strong>
        </div>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn label="Tutup" flat @click="emitClose" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
