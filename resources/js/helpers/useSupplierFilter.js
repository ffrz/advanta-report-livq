import { ref } from 'vue';

export function useSupplierFilter(suppliersRaw, includeAllOption = false) {
  const baseSuppliers = suppliersRaw.map((item) => {
    return { value: item.id, label: '#' + item.id + ' - ' + item.name };
  });

  const suppliers = includeAllOption
    ? [{ value: 'all', label: 'Semua' }, ...baseSuppliers]
    : baseSuppliers;

  const filteredSuppliers = ref([...suppliers]);

  const filterSupplierFn = (val, update) => {
    update(() => {
      filteredSuppliers.value = suppliers.filter(item =>
        item.label.toLowerCase().includes(val.toLowerCase())
      );
    });
  };

  return {
    filteredSuppliers,
    filterSupplierFn,
    suppliers,
  };
}
