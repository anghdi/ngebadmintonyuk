# Status Graphify

Hasil regenerasi **7 September 2026** mencakup modul komunitas dan integrasi kas–kuota: **1.606 node, 2.441 relasi, 156 komunitas**. Lihat [laporan generator](GRAPH_REPORT.md), [graph JSON](graph.json), dan [navigasi visual](graph.html).

Gunakan [konteks final proyek](../docs/11-final-context.md) untuk aturan aktif dan hasil verifikasi; [matriks fitur](../docs/10-current-features.md) menghubungkan fitur, kode, tes, dan backlog. [Pemeriksaan 6 September](../docs/10-project-status.md) merupakan arsip, bukan status graph terbaru.

Graph dibangun dengan `graphify update .` lalu `graphify cluster-only . --no-label`. Ekstraksi kode dilakukan secara lokal tanpa LLM/API, termasuk pembaruan manifest dan metadata analisis. Perintah ini tidak mengekstrak isi Markdown secara semantik; perubahan dokumentasi saja tidak memerlukan regenerasi graph kode.

Validasi cakupan class aplikasi dan konsistensi graph/analisis tersedia melalui `php artisan test --compact tests/Unit/ProjectContextTest.php`. Angka dan status tooling pada catatan 6 September telah digantikan hasil regenerasi di atas.
