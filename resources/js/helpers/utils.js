import { usePage } from "@inertiajs/vue3";
import dayjs from 'dayjs';

export const getQueryParams = (...args) => {
  const page = usePage();
  let queryString = page.url;
  if (queryString.indexOf("?") === -1) {
    return {};
  }
  queryString = queryString.substring(queryString.indexOf("?") + 1);
  return Object.assign(Object.fromEntries(new URLSearchParams(queryString)), ...args);
}

export function getMonthIndexInQuarter(month) {
  // month = 1–12
  const fiscalIndex = (month - 4 + 12) % 12; // Geser agar April = 0
  return (fiscalIndex % 3) + 1;              // Ambil posisi dalam kuartal
}

export function wa_send_url(phone, country_code = '62') {
  if (!phone) return '';

  // Hapus semua karakter non-digit (kecuali + di awal)
  const cleaned = phone.replace(/[^0-9]/g, '');

  // Validasi dasar: minimal 10 digit, diawali dengan 08 atau 62
  const isValid = /^(\+?62|08)[0-9]{8,}$/g.test(phone);

  if (!isValid) return '';

  // Normalisasi nomor ke format internasional (62)
  let waNumber = cleaned;
  if (waNumber.startsWith('08')) {
    waNumber = country_code + waNumber.slice(1);
  }

  return `https://wa.me/${waNumber}`;
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

export function create_quarter_options(year) {
  // const nextYear = Number(year) + 1;
  return [
    { value: `${year}-q1`, label: `${year}-Q1 (April-Juni)` },
    { value: `${year}-q2`, label: `${year}-Q2 (Juli-September)` },
    { value: `${year}-q3`, label: `${year}-Q3 (Oktober-Desember)` },
    { value: `${year}-q4`, label: `${year}-Q4 (Januari-Maret)` },
  ]
}

export function current_year() {
  return new Date().getFullYear();
}

export function current_month() {
  return new Date().getMonth() + 1; // getMonth() returns 0-11
}

export function current_quarter() {
  const month = new Date().getMonth() + 1; // getMonth() returns 0-11
  if (month >= 1 && month <= 3) return 4; // Jan-Mar
  if (month >= 4 && month <= 6) return 1; // Apr-Jun
  if (month >= 7 && month <= 9) return 2; // Jul-Sep
  return 3; // Oct-Dec
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

export function plantAge(plant_date, toDate) {
  if (!plant_date) return 0;
  return `${(toDate ? dayjs(toDate) : dayjs()).diff(dayjs(plant_date), 'day')}`;
};

export function create_options(data) {
  return Object.entries(data)
    .map(([key, value]) => ({ 'value': key, 'label': value }));
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
