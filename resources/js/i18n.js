import { createI18n } from 'vue-i18n';

// Define your translations
const messages = {
  en: {
    // menu
    dashboard: 'Dashboard',

    welcome: 'Welcome to our application!',
    greeting: 'Hello, {name}!',
  },
  id: {
    // menu
    add: 'Tambah',
    filter: 'Penyaringan',
    search: 'Cari',

    dashboard: 'Dasbor',
    service_orders: 'Order Servis',
    customers: 'Client',

    users: 'Pengguna',
    settings: 'Pengaturan',
    my_profile: 'Profil Saya',
    company_profile: 'Profil Perusahaan',
    logout: 'Keluar',

    not_yet_checked: 'Belum Diperiksa',
    active_order: 'Pesanan Aktif',
    not_yet_checked: 'Belum Diperiksa',
    in_progress: 'Sedang Dikerjakan',

    add_user: 'Tambah Pengguna',
    edit_user: 'Edit Pengguna',
    delete_user: 'Hapus Pengguna',

    welcome: 'Selamat datang di aplikasi kami!',
    greeting: 'Selamat datang, {name}!',
  },
};

const i18n = createI18n({
  locale: 'id', // Default locale
  messages, // Translations for each locale
});

export default i18n;
