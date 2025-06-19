# Shiftech CRM – Sistem Manajemen CRM dan Pelacakan Kunjungan Tim Marketing

Shiftech CRM adalah aplikasi berbasis web untuk membantu tim salesman dalam mengelola client, menjadwalkan dan merekam interaksi, serta memonitor progress pemasaran secara terstruktur dan terukur.

Sistem ini memudahkan manajemen dalam mengawasi aktivitas salesman, melihat performa tiap salesman, dan menganalisis status prospek hingga closing.

---

## 🚀 Fitur Utama

- Manajemen Data Client
- Penugasan Client kepada Salesman (Users)  
- Penjadwalan dan Pencatatan Interaksi Sales (Interactions)  
- Tracking Status Progress Prospek (pipeline status)  (belum diimplementasikan)
- Dashboard performa tim salesman (kunjungan, konversi, follow-up)  (belum diimplementasikan)
- Laporan detail interaksi per salesman (belum diimplementasikan)
- Analisis status pelanggan dan sumber prospek (butuh review)
- Notifikasi reminder follow-up pelanggan (butuh review)

---

## 💡 Tech Stack

- **Backend**: Laravel 11 (PHP)  
- **Frontend**: Vue.js 3 + Quasar 2
- **Database**: MySQL  
- **Charting & Dashboard**: ECharts / Chart.js  

---

## 📈 Dashboard dan Laporan

- **Dashboard Marketer**  
  - Jumlah kunjungan hari ini / minggu / bulan  
  - Status interaksi pelanggan
  - Grafik performa per salesman  

- **Laporan Kunjungan**  
  - Daftar kunjungan per salesman dengan status hasil kunjungan (belum diimplementasikan) 
  - Rangkuman aktivitas follow-up dan jadwal berikutnya (rencana)  

- **Laporan Pipeline**  
  - Visualisasi funnel penjualan dari prospek ke closing  
  - Statistik sumber pelanggan dan konversi  

---

## 🚀 Cara Instalasi

1. Clone repository  
2. Jalankan `composer install`  
3. Setup `.env` dan konfigurasi database  
4. Jalankan migrasi dan seeder:  
   ```bash
   php artisan migrate --seed
5. Jalankan `npm install` untuk instalasi dependensi client side
6. Jalankan `npm run dev` untuk memulai vite
