import dayjs from 'dayjs';
import { can } from './auth';
import { goBack } from './navigation';

export default {
  install(app) {
    app.config.globalProperties.$can = function (permissionName) {
      return can(permissionName, this.$page);
    };

    app.config.globalProperties.$goBack = goBack();
    app.config.globalProperties.$dayjs = dayjs;
    app.config.globalProperties.$config = window.CONFIG;
    app.config.globalProperties.$CONSTANTS = window.CONSTANTS;
  }
};
