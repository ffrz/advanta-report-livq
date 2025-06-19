import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import { Notify, Dialog } from "quasar";

const _scrollToFirstError = () => {
  const page = usePage();
  const firstErrorKey = Object.keys(page.props.errors)[0];
  if (firstErrorKey) {
    setTimeout(() => {
      const errorElement = document.querySelector('.q-field--error input');
      if (errorElement) {
        errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        errorElement.focus();
      }
    }, 0);
  }
};

export function handleSubmit(data) {
  const { form, url, onSuccess, onError, method, forceFormData } = data;

  form.clearErrors();
  form[method ?? 'post' ](url,
    {
      preserveScroll: true,
      forceFormData: forceFormData ?? false,
      onSuccess: (response) => {
        if (typeof onSuccess === 'function') {
          onSuccess(response);
        }

        // Notify.create({
        //   message: response.message || 'Berhasil disimpan',
        //   icon: "info",
        //   color: "positive",
        //   actions: [
        //     { icon: "close", color: "white", round: true, dense: true },
        //   ],
        // });
      },
      onError: (error) => {
        if (typeof onError === 'function') {
          onError(error);
        }

        _scrollToFirstError();
        if (!error || typeof (error.response?.data) === 'object' || error.message.length === 0)
          return;

        Notify.create({
          message: error.message,
          icon: "info",
          color: "negative",
          actions: [
            { icon: "close", color: "white", round: true, dense: true },
          ],
        });
      },
    }
  );
}

export function handleDelete(data) {
  const { message, url, fetchItemsCallback, loading } = data;
  Dialog.create({
    title: "Konfirmasi",
    icon: "question",
    message: message,
    focus: "cancel",
    cancel: true,
    persistent: true,
  }).onOk(() => {
    loading.value = true;
    axios
      .post(url)
      .then((response) => {
        Notify.create(response.data.message);
        fetchItemsCallback();
      })
      .finally(() => {
        loading.value = false;
      })
      .catch((error) => {
        let message = "";
        if (error.response.data && error.response.data.message) {
          message = error.response.data.message;
        } else if (error.message) {
          message = error.message;
        }

        if (message.length > 0) {
          Notify.create({ message: message, color: "red" });
        }
        console.log(error);
      });
  });
}

export function handleFetchItems(options) {
  const { pagination, props, rows, url, loading, filter } = options;

  let source = props ? props.pagination : pagination.value;

  let params = {
    page: source.page,
    per_page: source.rowsPerPage,
    order_by: source.sortBy,
    order_type: source.descending ? "desc" : "asc",
    filter: filter,
  };

  loading.value = true;

  axios
    .get(url, { params: params })
    .then((response) => {
      rows.value = response.data.data;
      pagination.value.page = response.data.current_page;
      pagination.value.rowsPerPage = response.data.per_page;
      pagination.value.rowsNumber = response.data.total;
      if (props) {
        pagination.value.sortBy = props.pagination.sortBy;
        pagination.value.descending = props.pagination.descending;
      }
    })
    .finally(() => {
      loading.value = false;
    });
}
