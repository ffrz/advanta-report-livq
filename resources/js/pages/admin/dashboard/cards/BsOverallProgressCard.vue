<script setup>
import { formatNumber } from "@/helpers/utils";
import { router } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
  total_completed: {
    type: [String, Number],
    required: true,
  },
  total_target: {
    type: [String, Number],
    required: true,
  },
  icon: {
    type: String,
    default: "info",
  },
  bgColor: {
    type: String,
    default: "#888",
  },
  sideColor: {
    type: String,
    default: "#666",
  },
  to: {
    type: String,
    required: true,
  },
  wrapperClass: {
    type: String,
    default: "col-md-4 col-sm-6 col-xs-12",
  },
});

// Hitung apakah sudah tercapai
const isAchieved = computed(() => {
  return Number(props.plan_count) >= Number(props.target_count);
});

// Warna hijau jika tercapai, merah jika belum
const finalBgColor = computed(() => (isAchieved.value ? "#388E3C" : "#D32F2F")); // hijau / merah
const finalSideColor = computed(() =>
  isAchieved.value ? "#2E7D32" : "#C62828"
); // lebih gelap

function handleClick() {
  router.visit(props.to);
}
</script>

<template>
  <div :class="wrapperClass + ' hover-scale-icon hover-shake'">
    <q-item
      :style="`background-color: ${finalSideColor}`"
      class="q-pa-none"
      clickable
      @click="handleClick"
    >
      <q-item-section
        side
        :style="`background-color: ${finalBgColor}`"
        class="q-pa-lg q-mr-none text-white"
      >
        <q-icon
          class="material-filled"
          :name="icon"
          color="white"
          size="24px"
        />
      </q-item-section>
      <q-item-section class="q-pa-md q-ml-none text-white">
        <q-item-label
          class="text-white text-h6 text-weight-bolder shake-on-hover"
        >
          T:{{ total_target }} / R:{{ total_completed }} ({{
            formatNumber((total_completed / total_target) * 100, "id", 2)
          }}
          %)
        </q-item-label>
        <q-item-label class="shake-on-hover">Overall Progress</q-item-label>
      </q-item-section>
    </q-item>
  </div>
</template>

<style scoped>
.hover-scale-icon .q-icon {
  transition: transform 0.5s ease;
}

.hover-scale-icon:hover .q-icon {
  transform: scale(1.3);
}

/* Keyframe animasi shake */
@keyframes shake {
  0% {
    transform: translateX(0);
  }

  20% {
    transform: translateX(-2px);
  }

  40% {
    transform: translateX(2px);
  }

  60% {
    transform: translateX(-2px);
  }

  80% {
    transform: translateX(2px);
  }

  100% {
    transform: translateX(0);
  }
}

/* Efek teks bergetar saat hover */
.hover-shake:hover .shake-on-hover {
  animation: shake 0.4s;
}
</style>
