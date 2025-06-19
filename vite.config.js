import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import { quasar, transformAssetUrls } from "@quasar/vite-plugin";

export default defineConfig({
  build: {
    rollupOptions: {
      output: {
        manualChunks: {
          vendor1: ['vue', 'quasar', 'dayjs', 'material-design-icons-iconfont', 'vue-i18n'],
          vendor2: ['vue-echarts'],
          vendor3: ['echarts'],
          // components: [
          //   '/resources/js/pages/admin/auth/Login.vue',
          //   '/resources/js/pages/admin/auth/Register.vue',
          // ],
        },
      },
    },
  },
    plugins: [
        vue({
            template: { transformAssetUrls },
        }),
        // @quasar/plugin-vite options list:
        // https://github.com/quasarframework/quasar/blob/dev/vite-plugin/index.d.ts
        quasar({
            sassVariables: "/resources/css/quasar-variables.sass",
        }),
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
});
