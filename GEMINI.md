# 🏛️ Sistem Identitas Digital — Instruksi Pengembangan & Catatan Kerja

*   **Pencatatan Perubahan:** Setiap sebelum melakukan *push*, pastikan untuk mencatat semua perubahan yang telah dilakukan di dalam berkas CHANGELOG.md sesuai dengan format Keep a Changelog.
*   **Pembaruan Dokumentasi:** Perbarui berkas README.md jika terdapat perubahan atau penambahan fitur baru yang memerlukan instruksi/konfigurasi tambahan.
*   **Kompilasi CSS:** Jika terdapat perubahan pada berkas CSS, pastikan untuk selalu melakukan proses *build* CSS (`npm run build`) sebelum melakukan *push*.
*   **Dependensi Composer:** Jika terdapat perubahan pada berkas `composer.json` atau `composer.lock`, pastikan untuk menjalankan perintah `composer install --no-dev --optimize-autoloader` sebelum melakukan *push*.
*   **Waktu Eksekusi Aturan:** Seluruh aturan di atas (kompilasi CSS jika diperlukan, pencatatan di CHANGELOG, pembaruan dokumentasi, dll.) **hanya dijalankan jika ada instruksi "push" eksplisit dari pengguna**, bukan secara otomatis di setiap perubahan kode individu.
