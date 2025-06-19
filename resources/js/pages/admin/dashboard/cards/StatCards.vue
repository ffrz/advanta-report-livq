<script setup>
import { formatNumber } from '@/helpers/utils';
import StatCard from './StatCard.vue';
</script>

<template>
  <div class="row">
    <q-card class="bg-transparent no-shadow no-border col" bordered>
      <q-card-section class="q-pa-none">
        <div class="row q-col-gutter-sm ">
          <StatCard :count="formatNumber($page.props.data.interaction_count)" label="Total Interaksi" icon="touch_app"
            :bg-color="'#BF360C'" :side-color="'#DF562C'" :to="route('admin.interaction.index', { status: 'done' })"
            wrapper-class="col-lg-3 col-sm-6 col-xs-12" />
          <StatCard :count="formatNumber($page.props.data.new_customer_count)" label="Client Baru" icon="person_add"
            :bg-color="'#0D47A1'" :side-color="'#2D67C1'" :to="route('admin.customer.index', { status: 'active' })"
            wrapper-class="col-lg-3 col-sm-6 col-xs-12" />
          <StatCard :count="formatNumber($page.props.data.closing_count)" label="Jumlah Closing" icon="handshake"
            :bg-color="'#4A148C'" :side-color="'#6A34AC'" :to="route('admin.closing.index')"
            wrapper-class="col-lg-3 col-sm-6 col-xs-12" />
          <StatCard :count="`Rp. ${formatNumber($page.props.data.closing_amount)}`" label="Total Closing"
            :bg-color="'#BF9700'" :side-color="'#DFB700'" icon="payments" :to="route('admin.closing.index')"
            wrapper-class="col-lg-3 col-sm-6 col-xs-12" />
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>
