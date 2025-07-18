<script setup>
import { defineComponent, onMounted, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useQuasar } from "quasar";

defineComponent({
  name: "AuthenticatedLayout",
});

const LEFT_DRAWER_STORAGE_KEY = "advanta-report.layout.left-drawer-open";
const $q = useQuasar();
const page = usePage();
const leftDrawerOpen = ref(
  JSON.parse(localStorage.getItem(LEFT_DRAWER_STORAGE_KEY))
);
const isDropdownOpen = ref(false);
const toggleLeftDrawer = () => (leftDrawerOpen.value = !leftDrawerOpen.value);

watch(leftDrawerOpen, (newValue) => {
  localStorage.setItem(LEFT_DRAWER_STORAGE_KEY, newValue);
});

onMounted(() => {
  leftDrawerOpen.value = JSON.parse(
    localStorage.getItem(LEFT_DRAWER_STORAGE_KEY)
  );

  if ($q.screen.lt.md) {
    leftDrawerOpen.value = false;
  }
});
</script>

<template>
  <q-layout view="lHh LpR lFf">
    <q-header>
      <q-toolbar class="bg-grey-1 text-black toolbar-scrolled">
        <q-btn
          v-if="!leftDrawerOpen"
          flat
          dense
          aria-label="Menu"
          @click="toggleLeftDrawer"
        >
          <q-icon class="material-symbols-outlined">dock_to_right</q-icon>
        </q-btn>
        <slot name="left-button"></slot>
        <q-toolbar-title
          :class="{ 'q-ml-sm': leftDrawerOpen }"
          style="font-size: 18px"
        >
          <slot name="title">{{ $config.APP_NAME }}</slot>
        </q-toolbar-title>
        <slot name="right-button"></slot>
      </q-toolbar>
      <slot name="header"></slot>
    </q-header>
    <q-drawer
      :breakpoint="768"
      v-model="leftDrawerOpen"
      bordered
      class="bg-grey-2"
      style="color: #444"
    >
      <div
        class="absolute-top"
        style="
          height: 50px;
          border-bottom: 1px solid #ddd;
          align-items: center;
          justify-content: center;
        "
      >
        <div
          style="
            width: 100%;
            padding: 8px;
            display: flex;
            justify-content: space-between;
          "
        >
          <q-btn-dropdown
            v-model="isDropdownOpen"
            class="profile-btn text-bold"
            flat
            :label="page.props.auth.user.company_name"
            style="
              justify-content: space-between;
              flex-grow: 1;
              overflow: hidden;
            "
            :class="{ 'profile-btn-active': isDropdownOpen }"
          >
            <q-list id="profile-btn-popup" style="color: #444">
              <q-item>
                <q-avatar style="margin-left: -15px"
                  ><q-icon name="person"
                /></q-avatar>
                <q-item-section>
                  <q-item-label>
                    <div class="text-bold">{{ page.props.auth.user.name }}</div>
                    <div class="text-grey-8 text-caption">
                      {{ $CONSTANTS.USER_ROLES[page.props.auth.user.role] }} @
                      {{ page.props.auth.user.company_name }}
                    </div>
                  </q-item-label>
                </q-item-section>
              </q-item>
              <q-separator />
              <q-item
                v-close-popup
                class="subnav"
                clickable
                v-ripple
                :active="$page.url.startsWith('/admin/settings/profile')"
                @click="router.get(route('admin.profile.edit'))"
              >
                <q-item-section>
                  <q-item-label
                    ><q-icon name="manage_accounts" class="q-mr-sm" />
                    {{ $t("my_profile") }}</q-item-label
                  >
                </q-item-section>
              </q-item>
              <q-item
                v-close-popup
                v-if="$page.props.auth.user.role == $CONSTANTS.USER_ROLE_ADMIN"
                class="subnav"
                clickable
                v-ripple
                :active="
                  $page.url.startsWith('/admin/settings/company-profile')
                "
                @click="router.get(route('admin.company-profile.edit'))"
              >
                <q-item-section>
                  <q-item-label
                    ><q-icon name="home_work" class="q-mr-sm" />
                    {{ $t("company_profile") }}</q-item-label
                  >
                </q-item-section>
              </q-item>
              <q-separator />
              <q-item
                clickable
                v-close-popup
                v-ripple
                style="color: inherit"
                :href="route('admin.auth.logout')"
              >
                <q-item-section>
                  <q-item-label
                    ><q-icon
                      name="logout"
                      class="q-mr-sm"
                    />Keluar</q-item-label
                  >
                </q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
          <q-btn
            v-if="leftDrawerOpen"
            flat
            dense
            aria-label="Menu"
            @click="toggleLeftDrawer"
          >
            <q-icon name="dock_to_right" />
          </q-btn>
        </div>
      </div>
      <q-scroll-area style="height: calc(100% - 50px); margin-top: 50px">
        <q-list id="main-nav" style="margin-bottom: 50px">
          <q-item
            clickable
            v-ripple
            :active="$page.url.startsWith('/admin/dashboard')"
            @click="router.get(route('admin.dashboard'))"
          >
            <q-item-section avatar>
              <q-icon name="dashboard" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Dashboard</q-item-label>
            </q-item-section>
          </q-item>
          <q-separator />
          <q-item
            v-if="$can('admin.activity.index')"
            clickable
            v-ripple
            :active="$page.url.startsWith('/admin/activities')"
            @click="router.get(route('admin.activity.index'))"
          >
            <q-item-section avatar>
              <q-icon name="event" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Realisasi Kegiatan</q-item-label>
            </q-item-section>
          </q-item>
          <q-item
            v-if="$can('admin.activity-plan.index')"
            clickable
            v-ripple
            :active="$page.url.startsWith('/admin/activity-plans')"
            @click="router.get(route('admin.activity-plan.index'))"
          >
            <q-item-section avatar>
              <q-icon name="event_upcoming" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Rencana Kegiatan</q-item-label>
            </q-item-section>
          </q-item>
          <q-item
            v-if="$can('admin.demo-plot.index')"
            clickable
            v-ripple
            :active="$page.url.startsWith('/admin/demo-plots')"
            @click="router.get(route('admin.demo-plot.index'))"
          >
            <q-item-section avatar>
              <q-icon name="assignment" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Demplot</q-item-label>
            </q-item-section>
          </q-item>
          <q-item
            clickable
            v-if="$can('admin.activity-target.index')"
            v-ripple
            :active="$page.url.startsWith('/admin/activity-targets')"
            @click="router.get(route('admin.activity-target.index'))"
          >
            <q-item-section avatar>
              <q-icon name="target" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Target Kegiatan</q-item-label>
            </q-item-section>
          </q-item>
          <q-separator />
          <q-item
            clickable
            v-ripple
            :active="$page.url.startsWith('/admin/customers')"
            v-if="$can('admin.customer.index')"
            @click="router.get(route('admin.customer.index'))"
          >
            <q-item-section avatar>
              <q-icon name="storefront" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Client</q-item-label>
            </q-item-section>
          </q-item>
          <q-item
            clickable
            v-if="$can('admin.product.index')"
            v-ripple
            :active="$page.url.startsWith('/admin/products')"
            @click="router.get(route('admin.product.index'))"
          >
            <q-item-section avatar>
              <q-icon name="potted_plant" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Varietas Tanaman</q-item-label>
            </q-item-section>
          </q-item>
          <q-item
            v-if="$can('admin.product-category.index')"
            clickable
            v-ripple
            :active="$page.url.startsWith('/admin/product-categories')"
            @click="router.get(route('admin.product-category.index'))"
          >
            <q-item-section avatar>
              <q-icon name="category" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Kategori Varietas</q-item-label>
            </q-item-section>
          </q-item>
          <q-item
            v-if="$can('admin.activity-type.index')"
            clickable
            v-ripple
            :active="$page.url.startsWith('/admin/activity-types')"
            @click="router.get(route('admin.activity-type.index'))"
          >
            <q-item-section avatar>
              <q-icon name="activity_zone" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Jenis Kegiatan (DGA)</q-item-label>
            </q-item-section>
          </q-item>
          <q-separator />
          <q-item
            v-if="$can('admin.user.index')"
            clickable
            v-ripple
            :active="$page.url.startsWith('/admin/settings/users')"
            @click="router.get(route('admin.user.index'))"
          >
            <q-item-section avatar>
              <q-icon name="group" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Pengguna</q-item-label>
            </q-item-section>
          </q-item>
          <q-item
            clickable
            v-ripple
            :active="$page.url.startsWith('/admin/settings/profile')"
            @click="router.get(route('admin.profile.edit'))"
          >
            <q-item-section avatar>
              <q-icon name="manage_accounts" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Profil Saya</q-item-label>
            </q-item-section>
          </q-item>
          <q-item
            v-if="$can('admin.company-profile.edit')"
            clickable
            v-ripple
            :active="$page.url.startsWith('/admin/settings/company-profile')"
            @click="router.get(route('admin.company-profile.edit'))"
          >
            <q-item-section avatar>
              <q-icon name="apartment" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Profil Perusahaan</q-item-label>
            </q-item-section>
          </q-item>
          <q-item
            v-close-popup
            v-if="$can('admin.db.menu')"
            clickable
            v-ripple
            :active="$page.url.startsWith('/admin/settings/db')"
            @click="router.get(route('admin.db.index'))"
          >
            <q-item-section avatar>
              <q-icon name="database" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Database</q-item-label>
            </q-item-section>
          </q-item>

          <q-separator />

          <q-item
            clickable
            v-close-popup
            v-ripple
            style="color: inherit"
            :href="route('admin.auth.logout')"
          >
            <q-item-section>
              <q-item-label
                ><q-icon name="logout" class="q-mr-sm" />Keluar</q-item-label
              >
            </q-item-section>
          </q-item>

          <div class="absolute-bottom text-grey-6 q-pa-md">
            &copy; 2025 -
            {{ $config.APP_NAME + " v" + $config.APP_VERSION_STR }}
          </div>
        </q-list>
      </q-scroll-area>
    </q-drawer>
    <q-page-container class="bg-grey-1">
      <q-page>
        <slot></slot>
      </q-page>
    </q-page-container>
    <slot name="footer"></slot>
  </q-layout>
</template>

<style>
.profile-btn span.block {
  text-align: left !important;
  width: 100% !important;
  margin-left: 10px !important;
}
</style>
<style scoped>
.q-toolbar {
  border-bottom: 1px solid transparent;
  /* Optional border line */
}

.toolbar-scrolled {
  box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);
  /* Add shadow */
  border-bottom: 1px solid #ddd;
  /* Optional border line */
}

.profile-btn-active {
  background-color: #ddd !important;
}

#profile-btn-popup .q-item--active {
  color: inherit !important;
}
</style>
