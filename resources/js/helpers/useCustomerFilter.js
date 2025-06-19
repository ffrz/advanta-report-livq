import { ref } from 'vue';

export function useCustomerFilter(customersRaw, includeAllOption = false) {
  const baseCustomers = customersRaw.map((c) => {
    return {
      value: c.id,
      label: `${c.name} (#${c.id}) - ${c.company} - ${c.address}`
    };
  });

  const customers = includeAllOption
    ? [{ value: 'all', label: 'Semua' }, ...baseCustomers]
    : baseCustomers;

  const filteredCustomers = ref([...customers]);

  const filterCustomerFn = (val, update) => {
    update(() => {
      filteredCustomers.value = customers.filter(item =>
        item.label.toLowerCase().includes(val.toLowerCase())
      );
    });
  };

  return {
    filteredCustomers,
    filterCustomerFn,
    customers,
  };
}
