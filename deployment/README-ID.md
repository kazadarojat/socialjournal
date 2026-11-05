# Pemasangan OJS Fronstellar

Paket ini menyiapkan bahan pemasangan OJS asli. Situs pratinjau tetap statis; Login, registrasi, unggahan, email, dan workflow review belum aktif. Hosting PHP, basis data, domain, dan email resmi belum diberikan oleh penerbit. Jangan memasang paket ini di GitHub Pages atau hosting statis Sites.

## Keputusan penerbit 5 November 2026

- Nama: Fronstellar Journal of Integrative Multidisciplinary Social Sciences.
- Penerbit: Fronstellar Institute.
- Fokus: ilmu sosial integratif.
- Bahasa: Indonesia dan Inggris; bahasa awal situs Inggris.
- Frekuensi: setiap empat bulan, tiga edisi per tahun. Tanggal setiap edisi belum ditentukan.
- Review: anonim dua arah oleh dua reviewer. Penulis yang menjadi staf jurnal harus ditangani editor independen.
- Tidak ada biaya pengiriman, pemrosesan, maupun penerbitan.
- Artikel terbit menggunakan CC BY 4.0; penulis mempertahankan hak cipta dan memberikan hak publikasi pertama kepada jurnal.
- Editor in Chief: Yoga Hediasa, M.Hum.
- Technical Support dan Layout Editor: Fahmi Achmad Darojat.
- Kontak sementara: 081789825757.

## Kebutuhan hosting

Paket OJS yang tersedia adalah 3.5.0-6. README paket mensyaratkan PHP 8.2 atau lebih baru, ekstensi yang tercantum dalam README, dan MySQL/MariaDB atau PostgreSQL yang kompatibel. Pilih lingkungan yang masih mendapat pembaruan keamanan dan memenuhi pemeriksaan platform paket, bukan hanya angka minimum versi basis data pada README.

Hosting perlu menyediakan HTTPS, basis data, direktori berkas privat di luar web root, pengaturan tugas terjadwal sesuai OJS, pengiriman email SMTP, serta fasilitas backup dan pemulihan. PHP, Docker, dan server basis data tidak ditemukan pada PATH mesin saat persiapan. Paket ini belum diuji sebagai instalasi OJS yang berjalan.

Panduan resmi: https://docs.pkp.sfu.ca/admin-guide/ . Sumber dokumentasi jika halaman utama tidak dapat dibuka: https://github.com/pkp/pkp-docs/tree/main/admin-guide . Paket sumber yang disertakan tetap utuh dan tidak dimodifikasi.

## Urutan pemasangan

1. Siapkan hosting dan domain HTTPS. Tetapkan email redaksi resmi serta pengaturan SMTP. Jangan kirim kata sandi atau kunci SMTP melalui chat maupun menyimpannya dalam repositori.
2. Ekstrak `ojs-3.5.0-6.tar.gz`, periksa README paket, dan jalankan pemeriksaan kebutuhan PHP menggunakan fasilitas hosting.
3. Buat basis data dan akun khusus OJS. Tentukan direktori naskah privat di luar direktori situs yang dapat diakses pengunjung.
4. Jalankan installer OJS melalui HTTPS. Buat administrator dengan kredensial pribadi yang kuat; jangan gunakan akun contoh. Atur izin berkas sesuai panduan PKP.
5. Buat jurnal dengan path `fronstellar`, lalu isi identitas menggunakan `journal-settings.json`. Berkas JSON merupakan rujukan pengisian, bukan format impor otomatis OJS.
6. Aktifkan bahasa Inggris dan Indonesia sesuai locale yang tersedia. Salin isi kebijakan dari `editorial-en.json` dan `editorial-id.json` melalui pengaturan jurnal. Atur review anonim dua arah, dua reviewer, biaya nol, hak cipta penulis, dan CC BY 4.0.
7. Tambahkan anggota tim sesuai perannya. Lengkapi afiliasi, profil, alamat, dan email yang sudah diverifikasi. Jangan menggunakan afiliasi pada artikel sebagai afiliasi editorial tanpa konfirmasi.
8. Pasang sampul umum tanpa nomor edisi dan tanggal terbit. ISSN pada sampul masih placeholder; ganti hanya setelah nomor yang sah tersedia. Jangan menyalin placeholder ke kolom ISSN OJS.
9. Unggah template Word di halaman pedoman. Saat menyerahkan naskah pertama kepada editor, gunakan PDF asli dan metadata yang disertakan. Status awal bukan artikel terbit; tidak ada tanggal penerimaan atau riwayat review yang boleh direkayasa.
10. Karena penulis naskah pertama juga staf jurnal, tugaskan editor independen untuk pemeriksaan dan penilaian. Jangan membuat keputusan penerimaan oleh penulis atas naskahnya sendiri.
11. Uji akun penulis, reset kata sandi, email, unggahan, pembatasan akses reviewer, revisi, copyediting, proof, dan penjadwalan edisi menggunakan naskah uji yang diberi label jelas.
12. Uji backup dan pemulihan. Tutup akses terhadap installer dan konfigurasi sesuai dokumentasi. Verifikasi bahwa direktori berkas privat tidak dapat diunduh lewat URL.
13. Tetapkan volume, nomor, tanggal terbit, dan artikel yang sudah diterima. Terbitkan edisi hanya setelah editorial dan proof selesai.
14. Hubungkan tombol Login dan Submit pada situs publik ke URL jurnal OJS yang benar. Tetapkan pengalihan URL artikel agar tautan permanen lama tetap berguna.
15. Setelah penerbit menyetujui peluncuran publik, ubah pengaturan akses situs, tinjau penghapusan `noindex` dan `robots.txt` yang memblokir pengindeksan, lalu perbarui sitemap. Metadata yang sudah disiapkan tidak menjamin pengindeksan Google Scholar.

## Backup produksi

Cadangkan basis data, direktori naskah privat, berkas publik, plugin/tema kustom, dan konfigurasi. Kredensial harus terlindungi di cadangan. Simpan salinan di lokasi terpisah dengan akses terbatas, tetapkan masa retensi, dan uji pemulihan sebelum peluncuran serta sesudah perubahan besar. Belum ada klaim keikutsertaan dalam PKP PN atau layanan preservasi lain.

## Data yang masih diperlukan

Hosting, domain, email resmi/SMTP, alamat penerbit, afiliasi dan profil editorial, reviewer, tanggal edisi pertama, serta penyelesaian review naskah. Nomor ISSN dan DOI belum tersedia. Akses situs saat ini tetap privat untuk peninjauan.
