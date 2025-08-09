import { ref } from 'vue';

export function useCustomerFilter(rawItems, includeAllOption = false) {
  const baseItems = rawItems.map(item => ({
    value: item.id,
    label: item.name
  }));

  const items = includeAllOption
    ? [{ value: 'all', label: 'Semua' }, ...baseItems]
    : baseItems;

  const filteredItems = ref([...items]);

  const filterItems = (val, update) => {
    const search = val.toLowerCase();
    update(() => {
      console.log('updating...');
      filteredItems.value = items.filter(item =>
        item.label.toLowerCase().includes(search)
      );
    });
  };

  const filteredCustomers = filteredItems;
  const filterCustomers = filterItems;
  const customers = items;

  return {
    filteredCustomers,
    filterCustomers,
    customers // jika butuh juga yang belum difilter
  };
}
