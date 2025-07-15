<script setup>
import { formatNumber } from "@/helpers/utils";
import TargetCard from "./TargetCard.vue";
import { usePage } from "@inertiajs/vue3";
import BsOverallProgressCard from "./BsOverallProgressCard.vue";

const page = usePage();
</script>

<template>
  <div class="row">
    <q-card class="bg-transparent no-shadow no-border col" bordered>
      <q-card-section class="q-pa-none">
        <div class="row q-col-gutter-sm">
          <BsOverallProgressCard
            icon="info"
            :total_completed="$page.props.data.total_completed"
            :total_target="$page.props.data.total_target"
            :to="route('admin.activity.index')"
            wrapper-class="col-lg-3 col-sm-6 col-xs-12"
          />
          <template v-if="$page.props.data.targets.length > 0">
            <template v-for="item in $page.props.data.targets" :key="item.id">
              <TargetCard
                icon="info"
                :plan_count="item.plan_qty"
                :real_count="item.real_qty"
                :target_count="item.target_qty"
                :label="item.type_name"
                :to="route('admin.activity.index')"
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
