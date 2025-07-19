<script setup></script>

<template>
  <i-head title="DB Administration" />
  <authenticated-layout>
    <template #title>DB Administration</template>
    <div class="row">
      <q-card class="col q-pa-xs q-ma-sm" square flat bordered>
        <q-card-section>
          <div class="text-subtitle2 text-bold text-grey-8">
            Database Administration
          </div>
          <ul class="q-pa-none q-ma-none q-ml-md">
            <li>
              <div>
                <my-link :href="route('admin.db.backup')">Backup</my-link>
                <span class="text-grey-8"> (Fitur belum tersedia)</span>
              </div>
              <div class="text-grey-9 text-italic">
                Backup database secara berkala untuk mengantisipasi kehilangan
                data.
              </div>
            </li>
            <li>
              <div>
                <my-link :href="route('admin.db.restore')">Restore</my-link>
                <span class="text-grey-8"> (Fitur belum tersedia)</span>
              </div>
              <div class="text-grey-9 text-italic">
                Pulihkan database apabila terdapat kerusakan data pada database
                saat ini.
              </div>
            </li>
          </ul>
        </q-card-section>
      </q-card>
    </div>
  </authenticated-layout>
</template>
