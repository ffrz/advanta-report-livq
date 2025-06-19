<script>
import { defineComponent, h } from 'vue';
import { QBtn } from 'quasar';
import { router } from '@inertiajs/vue3';

export default defineComponent({
  name: 'BtnLink',
  props: {
    ...QBtn.props,
    method: {
      type: String,
      default: 'get',
    },
    url: String,
  },
  emits: QBtn.emits,
  setup(props, { emit, slots }) {
    return () =>
      h(QBtn, {
        ...props,
        onClick: (event) => {
          router[props.method](props.url);
          emit('click', event);
        }
      }, slots);
  }
});
</script>
