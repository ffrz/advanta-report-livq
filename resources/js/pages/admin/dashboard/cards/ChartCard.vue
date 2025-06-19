<template>
  <div class="row q-col-gutter-sm">
    <!-- Interaksi vs Closing vs Client Baru -->
    <div class="col-lg-6 col-md-12 card-container">
      <q-card square bordered class="no-shadow bg-white">
        <q-card-section class="q-pa-none">
          <ECharts :option="chartInteractionClosingCustomer" class="q-mt-md" :resizable="true" autoresize
            style="height: 250px" />
        </q-card-section>
      </q-card>
    </div>

    <!-- Closing -->
    <div class="col-lg-6 col-md-12 card-container">
      <q-card square bordered class="no-shadow bg-white">
        <q-card-section class="q-pa-none">
          <ECharts :option="chartRevenue" autoresize style="height: 250px;" />
        </q-card-section>
      </q-card>
    </div>

    <!-- Donut Chart Status Interaksi -->
    <div class="col-lg-4 col-md-6 col-sm-12 card-container">
      <q-card square bordered class="no-shadow bg-white">
        <q-card-section class="q-pa-none">
          <ECharts :option="interactions_chart_data" autoresize style="height: 250px" />
        </q-card-section>
      </q-card>
    </div>

    <!-- Top 5 Sales Interaksi -->
    <div class="col-lg-4 col-md-6 col-sm-12 card-container">
      <q-card square bordered class="no-shadow bg-white">
        <q-card-section class="q-pa-none">
          <ECharts :option="chartTopInteraction" autoresize style="height: 250px" />
        </q-card-section>
      </q-card>
    </div>

    <!-- Top 5 Sales Closing -->
    <div class="col-lg-4 col-md-12 col-sm-12 card-container">
      <q-card square bordered class="no-shadow bg-white">
        <q-card-section class="q-pa-none">
          <ECharts :option="chartTopClosing" autoresize style="height: 250px" />
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
import ECharts from 'vue-echarts';
import * as echarts from 'echarts';
const page = usePage();

const chartInteractionClosingCustomer = {
  title: { text: 'Interaksi, Closing, Client Baru', left: 'center', textStyle: { color: '#444' } },
  tooltip: { show: true },
  legend: { top: '10%', data: ['Interaksi', 'Closing', 'Client Baru'] },
  grid: { containLabel: true, left: '5px', bottom: '5px', right: '5px' },
  xAxis: {
    type: 'category',
    data: page.props.chart_data.labels,
    axisLine: { lineStyle: { color: '#555', type: 'dashed' } },
    axisTick: { alignWithLabel: true, lineStyle: { color: '#ccc', type: 'dashed' } },
    axisLabel: { show: true }
  },
  yAxis: {
    type: 'value',
    axisLine: { lineStyle: { color: '#555', type: 'dashed' } },
    splitLine: { lineStyle: { type: 'dashed', color: '#ccc' } }
  },
  series: [
    { name: 'Interaksi', type: 'line', smooth: true, data: page.props.chart_data.count_interactions, lineStyle: { color: '#007bff' }, itemStyle: { color: '#007bff' } },
    { name: 'Closing', type: 'line', smooth: true, data: page.props.chart_data.count_closings, lineStyle: { color: '#28a745' }, itemStyle: { color: '#28a745' } },
    { name: 'Client Baru', type: 'line', smooth: true, data: page.props.chart_data.count_new_customers, lineStyle: { color: '#dc3545' }, itemStyle: { color: '#dc3545' } },
  ]
};

const chartRevenue = {
  title: { text: 'Pendapatan', left: 'center', textStyle: { color: '#444' } },
  tooltip: { show: true },
  legend: { top: '10%', data: ['Total Closing (Rp)'] },
  grid: { containLabel: true, left: '5px', bottom: '5px', right: '5px' },
  xAxis: {
    type: 'category',
    data: page.props.chart_data.labels,
    axisLine: { lineStyle: { color: '#555', type: 'dashed' } },
    axisTick: { alignWithLabel: true, lineStyle: { color: '#ccc', type: 'dashed' } },
    axisLabel: { show: true }
  },
  yAxis: {
    type: 'value',
    axisLine: { lineStyle: { color: '#555', type: 'dashed' } },
    splitLine: { lineStyle: { type: 'dashed', color: '#ccc' } }
  },
  series: [
    {
      name: 'Total Closing (Rp)',
      type: 'bar',
      data: page.props.chart_data.total_closings,
      itemStyle: { color: '#007bff' }
    }
  ]
};

const interactions_chart_data = {
  title: { text: 'Status Interaksi', left: 'center', textStyle: { color: '#444' } },
  tooltip: { trigger: 'item' },
  legend: {
    orient: 'vertical',
    left: 'left',
    textStyle: { color: '#444' }
  },
  series: [
    {
      name: 'Status',
      type: 'pie',
      radius: '50%',
      data: page.props.chart_data.interactions,
      emphasis: {
        itemStyle: {
          shadowBlur: 10,
          shadowOffsetX: 0,
          shadowColor: 'rgba(0, 0, 0, 0.5)'
        }
      }
    }
  ]
};

const chartTopInteraction = {
  title: { text: 'Top 5 Sales Interaksi', left: 'center', textStyle: { color: '#444' } },
  tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
  xAxis: { type: 'value' },
  yAxis: {
    type: 'category',
    inverse: true,
    data: page.props.chart_data.top_interactions.labels
  },
  series: [
    {
      name: 'Interaksi',
      type: 'bar',
      data: page.props.chart_data.top_interactions.data,
      itemStyle: { color: '#17a2b8' }
    }
  ]
};

const chartTopClosing = {
  title: { text: 'Top 5 Sales Closing', left: 'center', textStyle: { color: '#444' } },
  tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
  xAxis: { type: 'value' },
  yAxis: {
    type: 'category',
    inverse: true,
    data: page.props.chart_data.top_sales_closings.labels,
  },
  series: [
    {
      name: 'Closing',
      type: 'bar',
      data: page.props.chart_data.top_sales_closings.data,
      itemStyle: { color: '#ffc107' }
    }
  ]
};
</script>

<style scoped>
.q-card {
  width: 100%;
}

@media (max-width: 992px) {
  .card-container {
    width: 100% !important;
  }
}
</style>
