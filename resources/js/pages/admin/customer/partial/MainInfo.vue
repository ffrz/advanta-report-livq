<script setup>
import { usePage } from "@inertiajs/vue3";

const page = usePage();
</script>

<template>
  <div class="text-subtitle1 text-bold text-grey-8">Info Client</div>
  <table class="detail">
    <tbody>
      <tr>
        <td style="width:100px">Nama</td>
        <td style="width:1px">:</td>
        <td>{{ page.props.data.name }}</td>
      </tr>
      <tr>
        <td>Jenis</td>
        <td>:</td>
        <td>{{ page.props.data.type }}</td>
      </tr>
      <tr>
        <td>No Telepon</td>
        <td>:</td>
        <td>{{ page.props.data.phone }}</td>
      </tr>
      <tr>
        <td>Alamat</td>
        <td>:</td>
        <td>{{ page.props.data.address }}</td>
      </tr>
      <tr>
        <td>Alamat Pengiriman</td>
        <td>:</td>
        <td>{{ page.props.data.shipping_address }}</td>
      </tr>
      <tr>
        <td>Assigned to</td>
        <td>:</td>
        <td>
          <template v-if="page.props.data.assigned_user">
            <my-link :href="route('admin.user.detail', { id: page.props.data.assigned_user.id })">
              {{ page.props.data.assigned_user.name + ' - ' +
                (page.props.data.assigned_user.username) }}
            </my-link>
          </template>
          <template v-else>
            -
          </template>
        </td>
      </tr>
      <tr>
        <td>Status</td>
        <td>:</td>
        <td>{{ page.props.data.active ? 'Aktif' : 'Tidak Aktif' }}</td>
      </tr>

      <tr>
        <td>Catatan</td>
        <td>:</td>
        <td>{{ page.props.data.notes }}</td>
      </tr>
      <tr v-if="page.props.data.created_datetime">
        <td>Dibuat</td>
        <td>:</td>
        <td>
          {{ $dayjs(page.props.data.created_datetime).fromNow() }} -
          {{ $dayjs(page.props.data.created_datetime).format("DD MMMM YY HH:mm:ss") }}
          <template v-if="page.props.data.created_by_user">
            oleh
            <my-link :href="route('admin.user.detail', { id: page.props.data.created_by_user.id })">
              {{ page.props.data.created_by_user.username }}
            </my-link>
          </template>
        </td>
      </tr>
      <tr v-if="page.props.data.updated_datetime">
        <td>Diperbarui</td>
        <td>:</td>
        <td>
          {{ $dayjs(page.props.data.updated_datetime).fromNow() }} -
          {{ $dayjs(page.props.data.updated_datetime).format("DD MMMM YY HH:mm:ss") }}
          <template v-if="page.props.data.updated_by_user">
            oleh
            <my-link :href="route('admin.user.detail', { id: page.props.data.updated_by_user.id })">
              {{ page.props.data.updated_by_user.username }}
            </my-link>
          </template>
        </td>
      </tr>
    </tbody>
  </table>
</template>
