<script setup>
import { usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import ImageViewer from "@/components/ImageViewer.vue";
import { formatNumber } from "@/helpers/utils";
const page = usePage();
const showViewer = ref(false);
</script>

<template>
  <table class="detail">
    <tbody>
      <tr>
        <td colspan="3" class="bg-white">
          <div class="text-bold text-grey-8 q-mt-md">Informasi Umum</div>
        </td>
      </tr>
      <tr>
        <td>Periode</td>
        <td>:</td>
        <td>
          {{ $dayjs(page.props.data.date).format("MMMM YYYY") }}
        </td>
      </tr>
      <tr>
        <td>BS</td>
        <td>:</td>
        <td>
          <template v-if="page.props.data.user">
            <my-link
              :href="
                route('admin.user.detail', { id: page.props.data.user.id })
              "
            >
              {{
                page.props.data.user.name +
                " - " +
                page.props.data.user.username
              }}
            </my-link>
          </template>
          <template v-else> - </template>
        </td>
      </tr>

      <tr>
        <td>Total Biaya</td>
        <td>:</td>
        <td>Rp. {{ formatNumber(page.props.data.total_cost) }}</td>
      </tr>
      <tr>
        <td>Status</td>
        <td>:</td>
        <td>
          <template v-if="page.props.data.status == 'approved'">
            Disetujui oleh {{ page.props.data.responded_by.name }} pada
            {{
              $dayjs(page.props.data.responded_datetime).format(
                "DD MMMM YYYY HH:mm"
              )
            }}
            ({{ $dayjs(page.props.data.responded_datetime).fromNow() }})
          </template>
          <template v-else-if="page.props.data.status == 'rejected'">
            Ditolak oleh {{ page.props.data.responded_by.name }} pada
            {{
              $dayjs(page.props.data.responded_datetime).format(
                "DD MMMM YYYY HH:mm"
              )
            }}
            ({{ $dayjs(page.props.data.responded_datetime).fromNow() }})
          </template>
          <template v-else> Belum Direspon </template>
        </td>
      </tr>
      <tr>
        <td>Catatan</td>
        <td>:</td>
        <td>{{ page.props.data.notes ? page.props.data.notes : "-" }}</td>
      </tr>
      <tr>
        <td colspan="3" class="bg-white">
          <div class="text-bold text-grey-8 q-mt-md">Informasi Rekaman</div>
        </td>
      </tr>
      <tr>
        <td style="width: 125px">ID</td>
        <td style="width: 1px">:</td>
        <td>#{{ page.props.data.id }}</td>
      </tr>
      <tr v-if="page.props.data.created_datetime">
        <td>Dibuat</td>
        <td>:</td>
        <td>
          {{ $dayjs(page.props.data.created_datetime).fromNow() }} -
          {{
            $dayjs(page.props.data.created_datetime).format(
              "DD MMMM YY HH:mm:ss"
            )
          }}
          <template v-if="page.props.data.created_by_user">
            oleh
            <my-link
              :href="
                route('admin.user.detail', {
                  id: page.props.data.created_by_user.id,
                })
              "
            >
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
          {{
            $dayjs(page.props.data.updated_datetime).format(
              "DD MMMM YY HH:mm:ss"
            )
          }}
          <template v-if="page.props.data.updated_by_user">
            oleh
            <my-link
              :href="
                route('admin.user.detail', {
                  id: page.props.data.updated_by_user.id,
                })
              "
            >
              {{ page.props.data.updated_by_user.username }}
            </my-link>
          </template>
        </td>
      </tr>
    </tbody>
  </table>

  <ImageViewer
    v-model="showViewer"
    :imageUrl="`/${page.props.data.image_path}`"
  />
</template>
