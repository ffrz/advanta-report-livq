import { ref } from 'vue';

export function useProductFilter(rawItems, includeAllOption = false) {
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
      console.log('updating');
      filteredItems.value = items.filter(item =>
        item.label.toLowerCase().includes(search)
      );
    });
  };

  const filteredProducts = filteredItems;
  const filterProducts = filterItems;
  const products = items;
  return {
    filteredProducts,
    filterProducts,
    products // jika butuh juga yang belum difilter
  };
}
