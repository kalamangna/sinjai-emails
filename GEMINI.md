# Git Workflow Standards

Setiap pengembang wajib mengikuti standar alur kerja Git berikut secara berurutan sebelum melakukan `push` ke repositori:

1.  **Build CSS:** Jalankan `npm run build` jika terdapat perubahan pada file view atau JavaScript untuk memastikan aset CSS terbaru telah terkompilasi ke dalam `public/css/output.css`.
2.  **Update Changelog:** Simpan riwayat perubahan teknis dan kronologi pengembangan pada berkas `CHANGELOG.md` di root direktori.
3.  **Update README:** Pastikan `README.md` diperbarui jika terdapat perubahan fitur utama, cara instalasi, atau konfigurasi sistem.
4.  **Push:** Setelah semua langkah di atas selesai, lakukan commit (termasuk file `output.css` jika berubah) dan push ke repositori.
