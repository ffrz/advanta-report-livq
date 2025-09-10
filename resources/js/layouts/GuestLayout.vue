<script setup>
import { defineComponent, ref, onMounted } from "vue";

defineComponent({ name: "GuestLayout" });

const headerRef = ref(null);
const paddingTop = ref("56px"); // default desktop

onMounted(() => {
  if (headerRef.value) {
    paddingTop.value = headerRef.value.$el.offsetHeight + "px";
  }
});
</script>

<template>
  <q-layout view="hHh lpR fff" style="background: #f5f5f5">
    <!-- Header -->
    <q-header ref="headerRef" elevated class="bg-primary text-white">
      <slot name="header">
        <q-toolbar style="max-width: 980px; margin: 0 auto">
          <q-toolbar-title align="center">
            <q-avatar>
              <img src="/assets/img/app-logo.png" />
            </q-avatar>
            <a class="q-pl-md text-white" :href="route('home')">
              {{ $config.APP_NAME }}
            </a>
          </q-toolbar-title>
        </q-toolbar>
      </slot>
    </q-header>

    <!-- Content -->
    <q-page-container
      :style="{ paddingTop, maxWidth: '1024px', margin: 'auto' }"
    >
      <slot />
    </q-page-container>

    <!-- Footer -->
    <q-footer class="transparent" reveal>
      <slot name="footer">
        <div class="text-center q-mb-md">
          <div class="text-grey-6">
            {{ $config.APP_NAME }} v{{ $config.APP_VERSION_STR }}
          </div>
          <div class="text-grey-8 text-center q-mb-md">
            &copy;2025 <a href="https://shiftech.my.id">Shiftech Indonesia</a>
          </div>
        </div>
      </slot>
    </q-footer>
  </q-layout>
</template>

<style scoped>
.q-toolbar a {
  color: #eee;
}
.q-toolbar a:hover {
  color: #fff;
}
</style>
