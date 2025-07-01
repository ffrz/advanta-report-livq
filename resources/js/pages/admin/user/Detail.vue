<script setup>
import { router, usePage } from "@inertiajs/vue3";

const page = usePage();
const title = "Rincian Pengguna";
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #left-button>
      <div class="q-gutter-sm">
        <q-btn
          icon="arrow_back"
          dense
          color="grey-7"
          flat
          rounded
          @click="router.get(route('admin.user.index'))"
        />
      </div>
    </template>
    <template #right-button>
      <div class="q-gutter-sm">
        <q-btn
          v-if="$can('admin.user.edit')"
          icon="edit"
          dense
          color="primary"
          :disable="$page.props.auth.user.role != $CONSTANTS.USER_ROLE_ADMIN"
          @click="
            router.get(route('admin.user.edit', { id: page.props.data.id }))
          "
        />
      </div>
    </template>
    <div class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-9">
                Profil Pengguna
              </div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width: 125px">ID Pengguna</td>
                    <td style="width: 1px">:</td>
                    <td>{{ page.props.data.username }}</td>
                  </tr>
                  <tr>
                    <td>Nama Pengguna</td>
                    <td>:</td>
                    <td>{{ page.props.data.name }}</td>
                  </tr>
                  <tr>
                    <td>Hak Akses</td>
                    <td>:</td>
                    <td>{{ $CONSTANTS.USER_ROLES[page.props.data.role] }}</td>
                  </tr>
                  <tr v-if="page.props.data.parent">
                    <td>Supervisor</td>
                    <td>:</td>
                    <td>
                      <my-link
                        :href="
                          route('admin.user.detail', {
                            id: page.props.data.parent.id,
                          })
                        "
                      >
                        {{ page.props.data.parent.name }} ({{
                          $CONSTANTS.USER_ROLES[page.props.data.parent.role]
                        }})
                      </my-link>
                    </td>
                  </tr>
                  <tr>
                    <td>Area Kerja</td>
                    <td>:</td>
                    <td>{{ page.props.data.work_area }}</td>
                  </tr>
                  <tr v-if="page.props.data.created_at">
                    <td>Dibuat</td>
                    <td>:</td>
                    <td>
                      {{ $dayjs(page.props.data.created_at).fromNow() }} -
                      {{
                        $dayjs(page.props.data.created_at).format(
                          "DD MMMM YY HH:mm:ss"
                        )
                      }}
                    </td>
                  </tr>
                  <tr v-if="page.props.data.updated_at">
                    <td>Diperbarui</td>
                    <td>:</td>
                    <td>
                      {{ $dayjs(page.props.data.updated_at).fromNow() }} -
                      {{
                        $dayjs(page.props.data.updated_at).format(
                          "DD MMMM YY HH:mm:ss"
                        )
                      }}
                    </td>
                  </tr>
                  <tr>
                    <td>Terakhir login</td>
                    <td>:</td>
                    <td>
                      <template v-if="page.props.data.last_login_datetime">
                        {{
                          $dayjs(page.props.data.last_login_datetime).fromNow()
                        }}
                        -
                        {{
                          $dayjs(page.props.data.last_login_datetime).format(
                            "DD MMMM YY HH:mm:ss"
                          )
                        }}
                      </template>
                      <template v-else> Belum pernah login </template>
                    </td>
                  </tr>
                  <tr v-if="page.props.data.last_activity_datetime">
                    <td>Aktifitas Terakhir</td>
                    <td>:</td>
                    <td>
                      {{
                        $dayjs(page.props.data.last_activity_datetime).fromNow()
                      }}
                      -
                      {{
                        $dayjs(page.props.data.last_activity_datetime).format(
                          "DD MMMM YY HH:mm:ss"
                        )
                      }}
                      <br />Jenis aktifitas:
                      {{ page.props.data.last_activity_description }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>
  </authenticated-layout>
</template>
