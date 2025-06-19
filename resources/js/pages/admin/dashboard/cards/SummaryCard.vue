<script setup>
import { formatNumber } from '@/helpers/utils';
import StatCard from './StatCard.vue';
</script>

<template>
  <div class="row">
    <q-card class="bg-transparent no-shadow no-border col" bordered>
      <q-card-section class="q-pa-none">
        <div class="row q-col-gutter-sm ">
          <StatCard :count="formatNumber($page.props.data.active_customer_count)" label="Client Aktif" icon="info"
            :to="route('admin.customer.index', { status: 'active' })" wrapper-class="col-md-4 col-sm-6 col-xs-12" />
          <StatCard :count="formatNumber($page.props.data.active_sales_count)" label="Sales Aktif" icon="info"
            :bg-color="'#dc3545'" :side-color="'#cc2535'" :to="route('admin.user.index', { status: 'active' })"
            wrapper-class="col-md-4 col-sm-6 col-xs-12" />
          <StatCard :count="formatNumber($page.props.data.active_service_count)" label="Layanan Aktif" icon="info"
            :bg-color="'#28a745'" :side-color="'#189735'" :to="route('admin.service.index', { status: 'active' })"
            wrapper-class="col-md-4 col-sm-6 col-xs-12" />
        </div>
        <div class="row q-col-gutter-sm q-pt-sm">
          <StatCard :count="formatNumber($page.props.data.active_interaction_plan_count)" label="Plan Interaksi"
            icon="info" :to="route('admin.interaction.index', { status: 'planned' })"
            wrapper-class="col-md-4 col-sm-6 col-xs-12" :bg-color="'#e83e8c'" :side-color="'#d82e7c'" />
          <StatCard :count="formatNumber($page.props.data.active_customer_service_count)" label="Layanan Client Aktif"
            icon="info" :bg-color="'#e83e8c'" :side-color="'#d82e7c'"
            :to="route('admin.user.index', { status: 'active' })" wrapper-class="col-md-4 col-sm-6 col-xs-12" />
          <StatCard :count="formatNumber($page.props.data.active_user_count)" label="Pengguna Aktif" icon="info"
            :bg-color="'#008cff'" :side-color="'#007bff'" :to="route('admin.user.index', { status: 'active' })"
            wrapper-class="col-md-4 col-sm-6 col-xs-12" />
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>
