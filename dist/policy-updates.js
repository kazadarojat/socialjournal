// Decisions approved by the publisher on 5 November 2026.
for(const lang of ['en','id']){
 const c=editorialContent[lang];
 c.status=lang==='en'?'Editorial policy':'Kebijakan editorial';
 c.note=lang==='en'?'Publication frequency, review model, author format, fee-free publishing, and CC BY licensing have been approved by the publisher. Submissions open after OJS activation.':'Frekuensi terbit, model review, format penulis, penerbitan tanpa biaya, dan lisensi CC BY telah disetujui penerbit. Penerimaan naskah dibuka setelah OJS aktif.';
 c.credit=lang==='en'?'Editorial workflow follows Open Journal Systems, developed by the Public Knowledge Project. The OJS service is not active yet.':'Alur editorial mengikuti Open Journal Systems, dikembangkan oleh Public Knowledge Project. Layanan OJS belum aktif.';
 c.authors.intro=lang==='en'?'Manuscript preparation, required files, references, and editorial stages for Fronstellar Journal authors.':'Persiapan naskah, kelengkapan berkas, referensi, dan tahapan editorial bagi penulis Fronstellar Journal.';
 c.policies.intro=lang==='en'?'Editorial principles for originality, transparent assessment, open access, and responsible scholarly publishing.':'Prinsip editorial untuk orisinalitas, penilaian transparan, akses terbuka, dan penerbitan ilmiah yang bertanggung jawab.';
 const byId=(page,id)=>c[page].sections.find(s=>s.id===id);
 const set=(page,id,paragraphs)=>{const s=byId(page,id);s.paragraphs=paragraphs;delete s.list;delete s.after;};
 const scope=byId('about','scope');
 scope.paragraphs=[lang==='en'?'The journal publishes integrative social sciences research across the following areas:':'Jurnal memuat penelitian ilmu sosial integratif dalam bidang berikut:'];
 scope.after=lang==='en'?'Scope fit is assessed through the research question, contribution, and depth of analysis.':'Kesesuaian fokus dinilai dari pertanyaan penelitian, kontribusi, dan kedalaman analisis.';
 delete byId('about','article-types').after;
 set('about','publishing',lang==='en'?[
  'Published by Fronstellar Institute three times per year, at four-month intervals. Specific issue dates will be announced by the editorial team. Editor in Chief: Yoga Hediasa, M.Hum.',
  'The journal adopts open access and Creative Commons Attribution 4.0 International (CC BY 4.0). There are no submission, processing, or publication charges. No ISSN, DOI, accreditation, or indexing status is claimed until verified.',
  'The first manuscript is available for review. No issue has been formally published; the sample manuscript is not presented as an accepted or peer-reviewed article.'
 ]:[
  'Diterbitkan oleh Fronstellar Institute tiga kali setahun dengan interval empat bulan. Tanggal setiap edisi akan diumumkan oleh redaksi. Editor in Chief: Yoga Hediasa, M.Hum.',
  'Jurnal menerapkan akses terbuka dan Creative Commons Attribution 4.0 International (CC BY 4.0). Tidak ada biaya pengiriman, pemrosesan, maupun penerbitan artikel. ISSN, DOI, akreditasi, dan status indeks hanya dicantumkan setelah terverifikasi.',
  'Naskah pertama tersedia untuk ditinjau. Belum ada edisi yang diterbitkan secara resmi; naskah pratinjau tidak dinyatakan sebagai artikel yang telah diterima atau lolos peer review.'
 ]);
 set('about','management',lang==='en'?[
  'Yoga Hediasa, M.Hum serves as Editor in Chief. Fahmi Achmad Darojat serves as Technical Support and Layout Editor. Additional reviewers, editorial affiliations, and academic profiles will be added after verification.',
  'Editorial telephone: 081789825757. An official journal email and institutional address have not yet been supplied. Submissions are not open.'
 ]:[
  'Yoga Hediasa, M.Hum berperan sebagai Editor in Chief. Fahmi Achmad Darojat berperan sebagai Technical Support dan Layout Editor. Reviewer tambahan, afiliasi editorial, dan profil akademik akan dilengkapi setelah diverifikasi.',
  'Telepon redaksi: 081789825757. Email resmi jurnal dan alamat institusi belum tersedia. Penerimaan naskah belum dibuka.'
 ]);
 set('authors','format',lang==='en'?[
  'Use Microsoft Word (.docx), A4 pages, 2.5 cm margins, Times New Roman 12 pt, 1.5 line spacing, and page numbers. Article length is 4,000–8,000 words excluding references and appendices.',
  'Download the manuscript and separate title-page templates below. Remove guidance text and identifying information from the manuscript before double-anonymous review.'
 ]:[
  'Gunakan Microsoft Word (.docx), kertas A4, margin 2,5 cm, Times New Roman 12 pt, spasi 1,5, dan nomor halaman. Panjang artikel 4.000–8.000 kata di luar daftar pustaka dan lampiran.',
  'Unduh template naskah dan halaman judul terpisah di bawah. Hapus petunjuk dan identitas penulis dari naskah sebelum review anonim dua arah.'
 ]);
 const abstract=byId('authors','title-abstract');abstract.list=abstract.list.map(x=>x.replace('usulan 150–250','150–250').replace('usulan 3–5','3–5').replace('proposed length 150–250','150–250').replace('proposed 3–5','3–5'));
 const refs=byId('authors','references');refs.paragraphs[0]=refs.paragraphs[0].replace('Gaya APA edisi ke-7 diusulkan sebagai gaya selingkung.','Gunakan gaya APA edisi ke-7.').replace('APA 7th edition is proposed as the journal style.','Use APA 7th edition as the journal style.');
 const files=byId('authors','files');files.paragraphs=[lang==='en'?'The journal uses double-anonymous review. Submit author details on a separate title page and remove identifying information from the main manuscript, file properties, acknowledgements, and supplementary material intended for reviewers.':'Jurnal menggunakan review anonim dua arah. Tempatkan identitas pada halaman judul terpisah dan hapus informasi pengenal dari naskah utama, properti dokumen, ucapan terima kasih, serta lampiran untuk reviewer.'];
 files.list[1]=lang==='en'?'An anonymised main manuscript, including document properties.':'Naskah utama yang dianonimkan, termasuk properti dokumen.';
 const checklist=byId('authors','checklist');checklist.list[checklist.list.length-1]=lang==='en'?'The current guidelines and templates have been checked.':'Panduan dan template terbaru telah diperiksa.';
 const workflow=byId('authors','workflow');workflow.paragraphs=[lang==='en'?'The journal follows the OJS editorial stages: submission and screening, double-anonymous review by two reviewers, revision, copyediting, production, and publication.':'Jurnal mengikuti tahapan editorial OJS: pengiriman dan pemeriksaan awal, review anonim dua arah oleh dua reviewer, revisi, copyediting, produksi, dan publikasi.'];workflow.after=lang==='en'?'Acceptance is based on editorial assessment, not payment. No guaranteed acceptance or decision time is offered. The online workflow becomes available after OJS activation.':'Penerimaan berdasarkan penilaian editorial, bukan pembayaran. Tidak ada jaminan penerimaan atau waktu keputusan. Alur daring tersedia setelah OJS diaktifkan.';
 set('policies','peer-review',lang==='en'?[
  'Each eligible manuscript is assessed by two reviewers through double-anonymous peer review. Author and reviewer identities are concealed during review as far as practical. The handling editor selects reviewers for relevant expertise and absence of competing interests.',
  'Initial screening checks scope, completeness, originality, and readiness for review. Reviewers assess the research question, methods, evidence, integration across perspectives, and contribution. The handling editor is responsible for the final decision.',
  'Decisions include rejection, revision, further review, and acceptance. Authors provide a point-by-point response to reviews. Submission or revision does not guarantee acceptance; no fixed decision time is promised.',
  'When an author is a journal editor or staff member, an independent handling editor must oversee screening, reviewer selection, and decisions. The author takes no part in their own editorial assessment.'
 ]:[
  'Setiap naskah yang memenuhi syarat dinilai oleh dua reviewer melalui peer review anonim dua arah. Identitas penulis dan reviewer disembunyikan selama penelaahan sejauh memungkinkan. Editor memilih reviewer berdasarkan keahlian yang relevan dan ketiadaan konflik kepentingan.',
  'Pemeriksaan awal menilai fokus, kelengkapan, orisinalitas, dan kesiapan untuk review. Reviewer menilai pertanyaan penelitian, metode, bukti, integrasi antarperspektif, dan kontribusi. Keputusan akhir berada pada editor yang menangani naskah.',
  'Keputusan meliputi penolakan, revisi, review lanjutan, dan penerimaan. Penulis memberikan tanggapan per poin atas komentar reviewer. Pengiriman atau revisi tidak menjamin penerimaan; waktu keputusan tidak dijanjikan secara tetap.',
  'Jika penulis merupakan editor atau staf jurnal, editor penanggung jawab yang independen harus mengelola pemeriksaan awal, pemilihan reviewer, dan keputusan. Penulis tidak ikut menentukan penilaian editorial atas naskahnya sendiri.'
 ]);
 set('policies','fees',lang==='en'?[
  'There are no submission fees, article processing charges, publication charges, or author fees for the journal’s editorial and publication process.',
  'Editorial decisions are independent of payment. This website does not collect payments.'
 ]:[
  'Tidak ada biaya pengiriman, pemrosesan artikel, penerbitan, atau biaya penulis untuk proses editorial dan publikasi jurnal.',
  'Keputusan editorial tidak dipengaruhi pembayaran. Situs ini tidak memungut pembayaran.'
 ]);
 set('policies','access',lang==='en'?[
  'The journal adopts open access. Published articles are made available under Creative Commons Attribution 4.0 International (CC BY 4.0). Authors retain copyright and grant the journal the right of first publication.',
  'The licence permits sharing and adaptation, including for commercial purposes, provided appropriate credit is given, a licence link is included, and changes are indicated. Material credited to third parties may carry separate terms.',
  'Read the complete licence terms at https://creativecommons.org/licenses/by/4.0/. Manuscript previews remain clearly labelled and do not imply editorial acceptance. The current site is private while it is being prepared; public access will be configured at launch.'
 ]:[
  'Jurnal menerapkan akses terbuka. Artikel terbit tersedia dengan lisensi Creative Commons Attribution 4.0 International (CC BY 4.0). Penulis mempertahankan hak cipta dan memberikan hak publikasi pertama kepada jurnal.',
  'Lisensi mengizinkan pembagian dan adaptasi, termasuk untuk tujuan komersial, dengan atribusi yang sesuai, tautan lisensi, dan penjelasan perubahan. Bahan yang dikreditkan kepada pihak ketiga dapat memiliki ketentuan tersendiri.',
  'Ketentuan lengkap: https://creativecommons.org/licenses/by/4.0/. Pratinjau naskah tetap ditandai dan tidak menyiratkan penerimaan editorial. Situs masih privat selama persiapan; akses publik akan diatur pada peluncuran.'
 ]);
 for(const page of ['policies','about'])for(const s of c[page].sections){
  if(s.paragraphs)s.paragraphs=s.paragraphs.map(x=>x.replaceAll('Usulan: ','').replaceAll('Proposal: ','').replaceAll('diusulkan ditangani','ditangani').replaceAll('are proposed to be handled','are handled'));
 }
 const ai=byId('policies','ai');ai.paragraphs[1]=lang==='en'?'AI is not listed as an author. Authors and reviewers must not upload confidential manuscripts or participant data to AI services without appropriate permission.':'AI tidak dicantumkan sebagai penulis. Penulis dan reviewer tidak boleh mengunggah naskah rahasia atau data partisipan ke layanan AI tanpa izin yang sesuai.';
 set('policies','privacy',lang==='en'?[
  'This preview does not provide account registration, manuscript uploads, or payments. Theme preferences are stored locally in the browser; the selected language applies during the visit. Fonts load from Fontshare. Private access uses the hosting provider’s authentication.',
  'The OJS service will use author and reviewer information for editorial administration and correspondence. Access must be limited to authorised editorial personnel and technical administrators. A retention schedule and data-request email must be configured before account registration opens.',
  'Backups must cover the database, private manuscript files, public assets, and configuration. A restoration check is required before launch. No participation in a long-term preservation service is claimed until that service is active.'
 ]:[
  'Pratinjau ini tidak menyediakan pendaftaran akun, unggahan naskah, atau pembayaran. Pilihan tema disimpan secara lokal di browser; pilihan bahasa berlaku selama kunjungan. Font dimuat dari Fontshare. Akses privat menggunakan autentikasi penyedia hosting.',
  'Layanan OJS akan menggunakan informasi penulis dan reviewer untuk administrasi editorial dan korespondensi. Akses dibatasi kepada personel editorial dan administrator teknis yang berwenang. Jadwal retensi dan email permintaan data harus ditetapkan sebelum pendaftaran dibuka.',
  'Pencadangan harus mencakup basis data, berkas naskah privat, aset publik, dan konfigurasi. Pemulihan cadangan perlu diuji sebelum peluncuran. Keikutsertaan dalam layanan preservasi jangka panjang tidak diklaim sebelum aktif.'
 ]);
}
