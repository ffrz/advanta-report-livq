<script setup>
import { formatNumber } from '@/helpers/utils';
import StatCard from './StatCard.vue';
</script>

<template>
  <div class="row">
    <q-card class="bg-transparent no-shadow no-border col" bordered>
      <q-card-section class="q-pa-none">
        <div class="row q-col-gutter-sm ">
          <StatCard :count="formatNumber($page.props.data.active_customer_count)" label="Client Aktif"
            icon="account_circle" :bg-color="'#1976D2'" :side-color="'#3996F2'"
            :to="route('admin.customer.index', { status: 'active' })" wrapper-class="col-md-4 col-sm-6 col-xs-12" />
          <StatCard :count="formatNumber($page.props.data.active_sales_count)" label="Sales Aktif" icon="person"
            :bg-color="'#00695C'" :side-color="'#26A67A'" :to="route('admin.user.index', { status: 'active' })"
            wrapper-class="col-md-4 col-sm-6 col-xs-12" />
          <StatCard :count="formatNumber($page.props.data.active_service_count)" label="Layanan Aktif" icon="apps"
            :bg-color="'#4E342E'" :side-color="'#6E544E'" :to="route('admin.service.index', { status: 'active' })"
            wrapper-class="col-md-4 col-sm-6 col-xs-12" />
        </div>
        <div class="row q-col-gutter-sm q-pt-sm">
          <StatCard :count="formatNumber($page.props.data.active_interaction_plan_count)" label="Plan Interaksi"
            :bg-color="'#512DA8'" :side-color="'#714DC8'" icon="touch_app"
            :to="route('admin.interaction.index', { status: 'planned' })" wrapper-class="col-md-4 col-sm-6 col-xs-12" />
          <StatCard :count="formatNumber($page.props.data.active_customer_service_count)" label="Layanan Client Aktif"
            :bg-color="'#37474F'" :side-color="'#57676F'" icon="apps"
            :to="route('admin.customer-service.index', { status: 'active' })"
            wrapper-class="col-md-4 col-sm-6 col-xs-12" />
          <StatCard :count="formatNumber($page.props.data.active_user_count)" label="Pengguna Aktif" icon="person"
            :bg-color="'#1B5E20'" :side-color="'#3B7E50'" :to="route('admin.user.index', { status: 'active' })"
            wrapper-class="col-md-4 col-sm-6 col-xs-12" />
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>
