# Git Workflow Standards

Setiap pengembang wajib mengikuti standar alur kerja Git berikut sebelum melakukan `push` ke repositori:

1.  **Preservasi History:** Simpan riwayat perubahan teknis dan kronologi pengembangan pada berkas `CHANGELOG.md` di root direktori.
2.  **Update README:** Pastikan `README.md` diperbarui jika terdapat perubahan fitur utama, cara instalasi, atau konfigurasi sistem.
3.  **Dokumentasi:**
    - `CHANGELOG.md`: Riwayat perubahan fitur, bugfix, dan kronologi sistem.
    - `README.md`: Rangkuman proyek, fitur utama, dan spesifikasi teknis.

# Build & Asset Management

Proyek ini menggunakan Tailwind CSS untuk pengelolaan gaya (styling). Pastikan aset dikelola dengan benar:

1.  **Development:** Gunakan `npm run dev` saat pengembangan untuk memantau perubahan class CSS secara otomatis.
2.  **Production/Deploy:** Selalu jalankan `npm run build` sebelum melakukan `push` atau deploy jika terdapat perubahan pada file view (`.php`) atau JavaScript (`.js`). Hal ini memastikan file `public/css/output.css` telah diperbarui dengan class CSS terbaru.
3.  **Source Control:** File `public/css/output.css` harus ikut di-commit setelah menjalankan `npm run build` agar tampilan di server tetap sinkron dengan pengembangan lokal.
