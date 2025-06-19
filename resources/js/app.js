import "@/bootstrap";

import { createApp, h } from "vue";
import { createInertiaApp, Head, Link } from "@inertiajs/vue3";
import { Dialog, Loading, Notify, Quasar } from "quasar";
import AuthenticatedLayout from "./layouts/AuthenticatedLayout.vue";
import GuestLayout from "./layouts/GuestLayout.vue";
import "@quasar/extras/material-icons/material-icons.css";
import "@quasar/extras/material-icons-outlined/material-icons-outlined.css";
import "@quasar/extras/material-symbols-outlined/material-symbols-outlined.css";
import "@quasar/extras/fontawesome-v6/fontawesome-v6.css";
import "quasar/src/css/index.sass";
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { router } from "@inertiajs/vue3";
import processFlashMessage from "@/helpers/flash-message";
import MyLink from "@/components/MyLink.vue";
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime'
import 'dayjs/locale/id'; // Import the Indonesian locale
import i18n from './i18n';


dayjs.extend(relativeTime)
// Set Indonesian as the global locale
dayjs.locale('id');

createInertiaApp({
  title: (title) => window.CONFIG.APP_NAME + (title ? " - " + title : ""),
  resolve: (name) => {
    const pages = import.meta.glob("./pages/**/*.vue", { eager: true });
    return pages[`./pages/${name}.vue`];
  },
  setup({ el, App, props, plugin }) {

    const VueApp = createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .use(Quasar, {
        plugins: { Notify, Loading, Dialog },
        config: {
          iconSet: 'material-symbols-outlined',
        }
      })
      .use(i18n)
      .component('i-head', Head)
      .component('i-link', Link)
      .component('my-link', MyLink)
      .component('guest-layout', GuestLayout)
      .component('authenticated-layout', AuthenticatedLayout);

    VueApp.config.globalProperties.$dayjs = dayjs;
    VueApp.config.globalProperties.$config = window.CONFIG;
    VueApp.config.globalProperties.$CONSTANTS = window.CONSTANTS;
    VueApp.config.globalProperties.$goBack = () => {
      if (window.history.length > 1) {
        window.history.back();
      } else {
        // import('@inertiajs/inertia').then(({ Inertia }) => {
        //   Inertia.visit('/dashboard'); // Ganti fallback ini sesuai kebutuhan
        // });
      }
    };
    VueApp.mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});

router.on('success', processFlashMessage);
