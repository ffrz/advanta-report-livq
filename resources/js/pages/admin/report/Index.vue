<script setup>
import { router } from "@inertiajs/vue3";

const title = 'Laporan';

</script>
<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <div class="row">
      <q-card class="col q-pa-none q-ma-sm" square flat bordered>
        <q-card-section>
          <div class="text-subtitle2 text-bold">Laporan Interaksi</div>
          <ul class="menu q-mt-xs">
            <li><my-link :href="route('admin.report.interaction')">Laporan Interaksi</my-link></li>
            <li><my-link :href="route('admin.report.sales-activity')">Laporan Aktivitas Sales</my-link></li>
          </ul>

          <div class="text-subtitle2 text-bold">Laporan Closing</div>
          <ul class="menu q-mt-xs">
            <li><my-link :href="route('admin.report.closing-detail')">Laporan Closing Penjualan</my-link></li>
            <li><my-link :href="route('admin.report.closing-by-sales')">Laporan Rekap Closing Sales</my-link></li>
            <li><my-link :href="route('admin.report.closing-by-services')">Laporan Rekap Closing Layanan</my-link></li>
          </ul>

          <div class="text-subtitle2 text-bold">Laporan Layanan Pelanggan</div>
          <ul class="menu q-mt-xs">
            <li><my-link href="route('admin.report.customer-services-active')">Laporan Layanan Aktif Aktual</my-link></li>
            <li><my-link href="route('admin.report.customer-services-new')">Laporan Layanan Baru</my-link></li>
            <li><my-link href="route('admin.report.customer-services-ended')">Laporan Layanan Berakhir</my-link></li>
          </ul>

          <div class="text-subtitle2 text-bold">Laporan Klien</div>
          <ul class="menu q-mt-xs">
            <li><my-link href="route('admin.report.client-new')">Laporan Klien Baru</my-link></li>
            <li><my-link href="route('admin.report.client-statuses')">Laporan Klien Aktif / Tidak Aktif</my-link></li>
            <li><my-link href="route('admin.report.client-history')">Laporan Riwayat Klien</my-link></li>
          </ul>


          <div class="text-subtitle2 text-bold">Laporan Sales</div>
          <ul class="menu q-mt-xs">
            <li><my-link href="route('admin.report.sales-performance')">Laporan Kinerja Sales</my-link></li>
            <li><my-link href="route('admin.report.sales-target-vs-achievement')">Laporan Target Vs Capaian</my-link></li>
            <li><my-link href="route('admin.report.sales-all-activity')">Laporan Aktivitas per Sales</my-link></li>
          </ul>

          <div class="text-subtitle2 text-bold">Ringkasan Dashboard</div>
          <ul class="menu q-mt-xs">
            <li><my-link href="route('admin.report.dashboard-summary')">Laporan Ringkasan Dashboard</my-link></li>
          </ul>
        </q-card-section>
      </q-card>
    </div>
  </authenticated-layout>
</template>
