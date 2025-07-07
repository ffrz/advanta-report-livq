<script setup>
import { formatNumber } from "@/helpers/utils";
import TargetCard from "./TargetCard.vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
</script>

<template>
  <div class="row">
    <q-card class="bg-transparent no-shadow no-border col" bordered>
      <q-card-section class="q-pa-none">
        <div class="row q-col-gutter-sm">
          <template v-if="$page.props.data.length > 0">
            <template v-for="item in $page.props.data" :key="item.id">
              <TargetCard
                icon="info"
                :plan_count="item.plan_qty"
                :target_count="item.target_qty"
                :label="item.type_name"
                :to="route('admin.activity-plan.index')"
                wrapper-class="col-lg-3 col-sm-6 col-xs-12"
              />
            </template>
          </template>
          <template v-else>
            <div class="col-12 text-center text-grey-8">
              <q-icon name="info" size="32px" />
              <p class="text-subtitle1">Target belum ditetapkan</p>
            </div>
          </template>
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>
