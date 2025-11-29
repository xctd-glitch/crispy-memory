# Storage Directory

Persisten direktori untuk log, cache, dan sesi aplikasi SRP. Struktur default:

- `logs/` – menampung file log PHP maupun debug.
- `cache/` – file cache sementara yang aman dibersihkan.
- `sessions/` – penyimpanan sesi file-based sesuai konfigurasi `php.ini`.

Direktori ini disertakan dengan berkas placeholder agar tetap ter-versioning tanpa membawa data runtime.
