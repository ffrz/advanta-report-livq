<template>
  <table class="q-table q-table--flat dense-table">
    <thead>
      <tr>
        <th>Kegiatan</th>
        <th v-if="showWeight">Bobot</th>
        <th>Target</th>
        <th>Plan</th>
        <th>Realisasi</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="type in types" :key="type.id">
        <td>{{ type.name }}</td>
        <td v-if="showWeight">{{ type.weight ?? "-" }}</td>
        <td>{{ getQuarter(targets, type.id) }}</td>
        <td>{{ getQuarter(plans, type.id) }}</td>
        <td>{{ getQuarter(activities, type.id) }}</td>
      </tr>
    </tbody>
  </table>
</template>

<script setup>
const props = defineProps({
  types: Array,
  targets: Array,
  plans: Object,
  activities: Object,
  type: {
    type: String,
    default: "quarter",
  },
  showWeight: {
    type: Boolean,
    default: false,
  },
});

/**
 * Mendapatkan nilai quarter_qty dari struktur berbeda: ini sudah terlajur data dari server 
 * untuk target dalam bentuk array, sedangkan plans dan activities dalam bentuk map by activity type ID.
 * Jika data dari server diubah, fungsi ini bisa disederhanakan atau bahkan tidak diperlukan.
 * - Array (details)
 * - Object keyed (plans, activities)
 */
function getQuarter(source, typeId) {
  if (!source) return "-";
  const key = props.type === "quarter" ? "quarter_qty" : props.type + '_qty';

  if (Array.isArray(source)) {
    const found = source.find((d) => Number(d.type_id) === Number(typeId));
    return found ? found[key] : "-";
  }

  if (typeof source === "object") {
    const item = source[typeId];
    return item ? item[key] : "-";
  }

  return "-";
}
</script>
<style scoped>
.dense-table {
  font-size: 0.8rem;
  width: 100%;
  margin: 5px 0;
  border: 1px solid #ccc;
}
.dense-table th,
.dense-table td {
  padding: 2px 8px !important;
  height: auto !important;
  border: 1px solid #ccc;
}
.dense-table td:not(:nth-child(1)) {
  text-align: center !important;
}
</style>
