import { usePage } from "@inertiajs/vue3";

export const getQueryParams = (...args) => {
  const page = usePage();
  let queryString = page.url;
  if (queryString.indexOf("?") === -1) {
    return {};
  }
  queryString = queryString.substring(queryString.indexOf("?") + 1);
  return Object.assign(Object.fromEntries(new URLSearchParams(queryString)), ...args);
}

/**
 * Memeriksa apakah current user role ada di roles
 * @param {string | Array} roles
 * @returns boolean
 */
export function check_role(roles) {
  const page = usePage();
  if (!Array.isArray(roles))
    roles = [roles];
  return roles.includes(page.props.auth.user.role);
}

export function create_year_options(startYear, endYear) {
  const years = [];
  for (let i = startYear; i <= endYear; i++) {
    years.push({ value: i, label: i });
  }
  return years;
}

export function create_month_options() {
  return [
    { value: 1, label: "Januari" },
    { value: 2, label: "Februari" },
    { value: 3, label: "Maret" },
    { value: 4, label: "April" },
    { value: 5, label: "Mei" },
    { value: 6, label: "Juni" },
    { value: 7, label: "Juli" },
    { value: 8, label: "Agustus" },
    { value: 9, label: "September" },
    { value: 10, label: "Oktober" },
    { value: 11, label: "November" },
    { value: 12, label: "Desember" },
  ];
}

export function create_options(data) {
  return Object.entries(data)
    .map(([key, value]) => ({ 'value': key, 'label': value }));
}

// TODO: remove this function, move to useOperationalCostCategory
export function create_options_from_operational_cost_categories(items) {
  return items.map((item) => {
    return { 'value': item.id, 'label': `${item.name}` };
  });
}

// TODO: remove this function
export function create_options_from_users(items) {
  return items.map((user) => {
    return { 'value': user.id, 'label': `${user.username} - ${user.name}` };
  });
}

// TODO: remove this function
export function create_options_from_technicians(items) {
  return items.map((technician) => {
    return { 'value': technician.id, 'label': `#${technician.id} - ${technician.name}` };
  });
}

// TODO: remove this function
export function create_options_from_customers(items) {
  return items.map((customer) => {
    return { 'value': customer.id, 'label': `#${customer.id}: ${customer.name}` };
  });
}

// TODO: remove this function
export function create_options_from_customers_with_phone(items) {
  return items.map((customer) => {
    return { 'value': customer.id, 'label': `#${customer.id}: ${customer.name} - ${customer.phone}` };
  });
}

export async function scrollToFirstErrorField(ref) {
  const element = ref.getNativeElement();
  if (element) {
    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    element.focus();
  }
}

export const formatNumber = (value, locale = 'id-ID', maxDecimals = 0) => {
  let number = value;

  if (number === null || number === undefined || isNaN(number)) {
    number = 0;
  }

  return new Intl.NumberFormat(locale, {
    minimumFractionDigits: maxDecimals,
    maximumFractionDigits: maxDecimals,
  }).format(number);
};
