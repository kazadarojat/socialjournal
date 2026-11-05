const journalName = journalSettings.name;
const translations = {
  id: {
    nav: ['Beranda','Tentang jurnal','Tim editorial','Terbitan','Panduan penulis','Kebijakan','Kontak'],
    notice:'Pratinjau untuk peninjauan · Jurnal belum menerima naskah.',caption:'Ilmu sosial, lintas perspektif.',skip:'Lewati ke konten',
    footer:'Jurnal ilmu sosial integratif dalam bahasa Indonesia dan Inggris. Diterbitkan oleh Fronstellar Institute.',footerLink:'Hubungi redaksi ↗',footerStatus:'Situs pratinjau · Identitas dan kebijakan sedang ditinjau · Belum ada artikel terbit.',
    subtitle:'Ruang temu penelitian ilmu sosial.',hero:'Mempertemukan perspektif untuk memahami masyarakat dan persoalan sosial secara lebih utuh.',explore:'Mengenal jurnal',cover:'Konsep sampul · Untuk peninjauan',coverStatus:'Dalam persiapan',
    facts:['Penerbit','Bahasa artikel','Frekuensi terbit'],frequency:'Setiap empat bulan',languages:'Indonesia & Inggris',
    aboutTitle:'Melampaui batas disiplin.',aboutBody:'Fronstellar Journal berfokus pada ilmu sosial integratif. Jurnal ini disiapkan sebagai ruang bagi penelitian yang mempertemukan pendekatan, konsep, dan metode untuk mengkaji kehidupan sosial.',aboutLink:'Baca fokus dan ruang lingkup ↗',
    issueTitle:'Terbitan perdana',issueBody:'Belum ada edisi atau artikel yang diterbitkan. Halaman ini akan memuat daftar isi dan artikel setelah proses editorial selesai.',issueStatus:'Sedang dipersiapkan',issueLink:'Lihat status terbitan',
    editorialTitle:'Di balik jurnal.',editorialBody:'Yoga Hediasa, M.Hum memimpin pengelolaan editorial sebagai Editor in Chief. Susunan editor dan reviewer lainnya akan dilengkapi setelah dikonfirmasi.',editorialLink:'Lihat tim editorial ↗',
    aboutPage:'Tentang jurnal',aboutIntro:'Ilmu sosial integratif menjadi titik berangkat Fronstellar Journal untuk mempertemukan berbagai perspektif dalam memahami masyarakat.',draftScope:'Draf ruang lingkup: pengelompokan bidang di bawah ini merupakan usulan untuk ditinjau oleh redaksi.',
    scope:[['Masyarakat & kebudayaan','Usulan cakupan: relasi sosial, identitas, komunitas, kebudayaan, dan perubahan sosial.'],['Institusi & kebijakan','Usulan cakupan: tata kelola, kebijakan publik, kelembagaan, dan dinamika kekuasaan.'],['Pendidikan & komunikasi','Usulan cakupan: pembelajaran, media, komunikasi, dan pembentukan pengetahuan dalam masyarakat.'],['Pendekatan integratif','Usulan cakupan: penelitian yang menghubungkan perspektif ilmu sosial dengan argumen dan metode yang jelas.']],
    publicationInfo:'Informasi penerbitan',schedule:'Direncanakan terbit setiap empat bulan, atau tiga kali setahun. Bulan dan tanggal terbit masih perlu ditetapkan bersama redaksi; frasa “awal musim” belum digunakan sebagai tanggal penerbitan.',identityNote:'Nama jurnal ditampilkan mengikuti data yang diberikan penerbit. Ejaan final, singkatan resmi, ISSN, dan alamat institusi masih menunggu konfirmasi.',
    editorialPage:'Tim editorial',editorialIntro:'Penanggung jawab editorial yang telah diinformasikan oleh Fronstellar Institute.',editorialPending:'Susunan tim berikutnya',editorialPendingBody:'Nama editor, reviewer, afiliasi, dan profil akademik belum tersedia. Informasi tersebut akan ditambahkan setelah dikonfirmasi oleh penerbit.',editorNote:'Afiliasi dan tautan profil akademik Editor in Chief belum dicantumkan.',
    issuesPage:'Terbitan & arsip',issuesIntro:'Arsip jurnal akan memuat setiap edisi beserta artikel yang telah melalui proses editorial.',emptyTitle:'Belum ada terbitan',emptyBody:'Volume, nomor, tahun, sampul final, dan daftar artikel untuk edisi pertama belum ditentukan. Tidak ada artikel contoh yang ditampilkan sebagai publikasi.',issueAction:'Hubungi redaksi',
    authorPage:'Panduan penulis',authorIntro:'Rancangan panduan untuk membantu penulis menyiapkan naskah ilmu sosial integratif.',draftGuidelines:'Draf untuk peninjauan: ketentuan berikut belum menjadi kebijakan final jurnal. Pengiriman naskah belum dibuka.',guidelines:[['Kesesuaian naskah','Jelaskan persoalan sosial yang dikaji, kontribusi penelitian, dan cara perspektif yang digunakan saling berhubungan. Naskah dapat disiapkan dalam bahasa Indonesia atau Inggris.'],['Struktur naskah','Usulan susunan: judul; identitas penulis dan afiliasi; abstrak dan kata kunci; pendahuluan; metode; hasil dan pembahasan; kesimpulan; serta daftar pustaka. Struktur artikel konseptual dapat disesuaikan setelah ditinjau redaksi.'],['Referensi dan integritas','Cantumkan sumber untuk gagasan, data, dan kutipan. Jelaskan keterbatasan penelitian, kontribusi penulis, pendanaan, serta potensi konflik kepentingan bila relevan.'],['Berkas dan format','Template, batas kata, gaya sitasi, format berkas, dan ketentuan anonimisasi belum ditetapkan. Tunggu panduan final sebelum mengirimkan naskah.']],
    workflowTitle:'Rancangan alur editorial',workflow:['Pemeriksaan awal atas kelengkapan dan kesesuaian fokus.','Penilaian ilmiah oleh reviewer yang ditugaskan editor.','Revisi dan keputusan editorial.','Penyuntingan, persetujuan versi akhir, dan penerbitan.'],workflowNote:'Model review, jumlah reviewer, dan tenggat setiap tahap masih perlu diputuskan. Alur ini belum aktif pada situs pratinjau.',
    policyPage:'Kebijakan jurnal',policyIntro:'Halaman ini memisahkan rancangan kebijakan dari keputusan editorial yang masih perlu ditetapkan.',policyDraft:'Seluruh isi kebijakan di bawah ini adalah bahan peninjauan, bukan pernyataan kebijakan final.',policies:[['Etika publikasi','Usulan: naskah harus orisinal, sumber dikutip dengan benar, dan semua penulis menyetujui pengiriman. Prosedur pengaduan, koreksi, dan pencabutan perlu ditetapkan redaksi.'],['Proses review','Model review dan mekanisme penanganan konflik kepentingan belum ditentukan. Tidak ada jaminan penerimaan naskah atau waktu keputusan yang dinyatakan pada tahap ini.'],['Biaya publikasi','Biaya pengiriman, pemrosesan, maupun penerbitan belum ditentukan. Tidak ada transaksi atau pembayaran melalui situs ini.'],['Hak cipta & lisensi','Pemegang hak cipta, lisensi penggunaan, dan model akses artikel belum ditentukan oleh penerbit.'],['Privasi & pengarsipan','Situs pratinjau tidak menyediakan akun atau unggahan naskah. Kebijakan penyimpanan data penulis dan strategi preservasi artikel perlu disiapkan sebelum OJS dioperasikan.'],['Identitas & pengindeksan','Belum ada ISSN, DOI, akreditasi, atau status indeks yang diinformasikan. Situs ini tidak menyatakan jurnal telah terdaftar atau terindeks.']],
    contactPage:'Hubungi redaksi',contactIntro:'Untuk peninjauan profil jurnal, kesiapan editorial, dan informasi rencana penerbitan.',phone:'Telepon',call:'Telepon redaksi ↗',contactNote:'Kontak ini digunakan untuk informasi. Pengiriman naskah belum dibuka.',publisher:'Penerbit',pendingContact:'Informasi pelengkap',pendingContactBody:'Email resmi, alamat institusi, dan jam layanan belum tersedia. Informasi akan dilengkapi setelah dikonfirmasi.',
    statusTitle:'Sebelum penerbitan',statuses:[['Identitas jurnal','Konfirmasi ejaan final, singkatan, logo, dan alamat penerbit.'],['Kebijakan editorial','Tetapkan pedoman penulis, model review, biaya, dan lisensi.'],['Platform OJS','Aktifkan instalasi OJS pada hosting PHP dan basis data sebelum menerima naskah.'],['Edisi pertama','Siapkan volume, nomor, tanggal, dan artikel yang telah selesai diproses.']]
  },
  en: {
    nav:['Home','About the journal','Editorial team','Issues','Author guidelines','Policies','Contact'],notice:'Review preview · Manuscript submissions are not open.',caption:'Social sciences, across perspectives.',skip:'Skip to content',
    footer:'An integrative social sciences journal in Indonesian and English. Published by Fronstellar Institute.',footerLink:'Contact the editors ↗',footerStatus:'Review preview · Identity and policies under review · No published articles yet.',
    subtitle:'A meeting place for social research.',hero:'Bringing perspectives together to understand society and social questions more fully.',explore:'Explore the journal',cover:'Cover concept · For review',coverStatus:'In preparation',facts:['Publisher','Article languages','Publication frequency'],frequency:'Every four months',languages:'Indonesian & English',
    aboutTitle:'Across disciplines.',aboutBody:'Fronstellar Journal focuses on integrative social sciences. It is being prepared as a space for research connecting approaches, concepts, and methods to examine social life.',aboutLink:'Read the aims and scope ↗',issueTitle:'The first issue',issueBody:'No issues or articles have been published yet. This space will present the contents and articles once the editorial process is complete.',issueStatus:'In preparation',issueLink:'View issue status',editorialTitle:'Behind the journal.',editorialBody:'Yoga Hediasa, M.Hum leads the editorial process as Editor in Chief. Additional editors and reviewers will be listed after confirmation.',editorialLink:'Meet the editorial team ↗',
    aboutPage:'About the journal',aboutIntro:'Integrative social sciences are the starting point for Fronstellar Journal to connect perspectives on society.',draftScope:'Draft scope: the subject groupings below are proposals for editorial review.',scope:[['Society & culture','Proposed scope: social relations, identity, communities, culture, and social change.'],['Institutions & policy','Proposed scope: governance, public policy, institutions, and the dynamics of power.'],['Education & communication','Proposed scope: learning, media, communication, and the social production of knowledge.'],['Integrative approaches','Proposed scope: research connecting social science perspectives through clear arguments and methods.']],publicationInfo:'Publication information',schedule:'Publication is planned every four months, or three times a year. Publication months and dates remain to be agreed with the editors; “the start of each season” has not been treated as a publication date.',identityNote:'The journal name follows the publisher’s supplied wording. Final spelling, an official abbreviation, ISSN, and institutional address await confirmation.',
    editorialPage:'Editorial team',editorialIntro:'Editorial leadership supplied by Fronstellar Institute.',editorialPending:'Additional team members',editorialPendingBody:'Names of additional editors and reviewers, affiliations, and academic profiles are not yet available. They will be added once confirmed by the publisher.',editorNote:'The Editor in Chief’s affiliation and academic profile links have not yet been supplied.',issuesPage:'Issues & archive',issuesIntro:'The archive will hold each issue and the articles that have completed the editorial process.',emptyTitle:'No issues published yet',emptyBody:'The first issue’s volume, number, year, final cover, and article list have not been determined. No sample articles are presented as published research.',issueAction:'Contact the editors',
    authorPage:'Author guidelines',authorIntro:'Proposed guidance to help authors prepare integrative social sciences manuscripts.',draftGuidelines:'Draft for review: these provisions are not final journal policies. Manuscript submissions are not open.',guidelines:[['Scope and contribution','Explain the social question, research contribution, and how the perspectives used connect. Manuscripts may be prepared in Indonesian or English.'],['Manuscript structure','Proposed structure: title; author details and affiliations; abstract and keywords; introduction; methods; findings and discussion; conclusion; and references. Conceptual articles may use an alternative structure after editorial consideration.'],['References and integrity','Cite sources for ideas, data, and quotations. Explain research limitations, author contributions, funding, and potential competing interests where relevant.'],['Files and formatting','The template, word limits, citation style, file format, and anonymisation requirements have not been set. Please wait for the final guidelines before submitting.']],workflowTitle:'Proposed editorial workflow',workflow:['Initial checks for completeness and scope.','Scholarly assessment by editor-assigned reviewers.','Revisions and editorial decision.','Copyediting, final approval, and publication.'],workflowNote:'The review model, number of reviewers, and stage deadlines remain to be decided. This workflow is not active on the preview site.',
    policyPage:'Journal policies',policyIntro:'This page distinguishes policy proposals from editorial decisions that remain to be made.',policyDraft:'All policy content below is for review and does not constitute final journal policy.',policies:[['Publication ethics','Proposal: manuscripts must be original, sources correctly cited, and all authors must agree to submission. Procedures for complaints, corrections, and retractions must be established by the editors.'],['Peer review','The review model and conflict-of-interest procedures have not been determined. No acceptance or decision-time guarantees are made.'],['Publication fees','Submission, processing, and publication fees have not been determined. This site does not accept payments or transactions.'],['Copyright & licensing','Copyright ownership, reuse licences, and the article access model have not been determined by the publisher.'],['Privacy & preservation','The preview does not provide accounts or manuscript uploads. Author-data policies and article-preservation arrangements must be prepared before operating OJS.'],['Identifiers & indexing','No ISSN, DOI, accreditation, or indexing status has been supplied. This site does not claim that the journal has been registered or indexed.']],
    contactPage:'Contact the editors',contactIntro:'For reviewing the journal profile, editorial preparations, and publication plans.',phone:'Telephone',call:'Call the editors ↗',contactNote:'This contact is for information. Manuscript submissions are not open.',publisher:'Publisher',pendingContact:'Further contact details',pendingContactBody:'An official email address, institutional address, and service hours have not been supplied. These will be added after confirmation.',statusTitle:'Before publication',statuses:[['Journal identity','Confirm final spelling, abbreviation, logo, and publisher address.'],['Editorial policies','Set author guidelines, review model, fees, and licensing.'],['OJS platform','Activate an OJS installation on hosting with PHP and a database before receiving submissions.'],['First issue','Prepare the volume, number, date, and articles that have completed editorial processing.']]
  }
};
translations.en.editorialIntro='Editorial and production team supplied by Fronstellar Institute.';
translations.id.editorialIntro='Tim editorial dan produksi yang telah diinformasikan oleh Fronstellar Institute.';
translations.en.editorialPendingBody='Additional editors and reviewers will be listed after publisher confirmation. Further affiliations and academic profiles have not yet been supplied.';
translations.id.editorialPendingBody='Editor dan reviewer tambahan akan dicantumkan setelah dikonfirmasi penerbit. Afiliasi dan profil akademik pelengkap belum tersedia.';
translations.en.editorialBody='Yoga Hediasa, M.Hum serves as Editor in Chief. Fahmi Achmad Darojat supports the journal as Technical Support and Layout Editor.';
translations.id.editorialBody='Yoga Hediasa, M.Hum berperan sebagai Editor in Chief. Fahmi Achmad Darojat mendukung pengelolaan jurnal sebagai Technical Support dan Layout Editor.';
translations.en.nav.push('Search','Login','Submit Article');
translations.id.nav.push('Cari','Login','Kirim Artikel');
translations.en.issueTitle='The first article';
translations.id.issueTitle='Artikel pertama';
translations.en.issueBody='A manuscript by Fahmi Achmad Darojat on medical students’ seating preferences is now available for review, with its bilingual abstracts and original PDF.';
translations.id.issueBody='Naskah oleh Fahmi Achmad Darojat mengenai preferensi posisi duduk mahasiswa kedokteran kini tersedia untuk ditinjau, lengkap dengan abstrak dwibahasa dan PDF asli.';
translations.en.issueStatus='Manuscript preview · Not yet assigned to an issue';
translations.id.issueStatus='Pratinjau naskah · Belum ditempatkan pada edisi';
translations.en.issueLink='View first article';
translations.id.issueLink='Lihat artikel pertama';
const routes=['home','about','editorial','issues','authors','policies','contact','search','login','submit'];
// Every new page load starts in English; language switching applies to this visit.
let language=new URLSearchParams(location.search).get('lang')==='id'?'id':'en';
const cover = t => `<figure class="supplied-cover"><a href="./assets/fronstellar-cover-current.png" target="_blank" rel="noopener" aria-label="${language==='en'?'View journal cover at full size':'Lihat sampul jurnal ukuran penuh'}"><img src="./assets/fronstellar-cover-current.png" width="1052" height="1495" fetchpriority="high" alt="${language==='en'?'Fronstellar journal cover. Published every four months, three issues per year. ISSN values are placeholders.':'Sampul jurnal Fronstellar. Terbit setiap empat bulan, tiga edisi per tahun. Nomor ISSN masih placeholder.'}"></a><figcaption class="cover-caption">${language==='en'?'Journal cover · ISSN placeholders':'Sampul jurnal · ISSN masih placeholder'}</figcaption></figure>`;
const link=(route,label,button=false)=>`<a class="${button?'button':'text-link'}" href="${routeHref(route)}">${label}${button?' <span aria-hidden="true">↗</span>':''}</a>`;
const sheet=(title,intro,body)=>`<article class="sheet reveal"><h1 class="page-title">${title}</h1><p class="intro">${intro}</p>${body}</article>`;
function editorialPage(route){
 const c=editorialContent[language];const p=c[route];
 return `<article class="journal-page reveal"><header class="journal-page-heading"><h1>${p.title}</h1><p class="intro">${p.intro}</p></header><div class="journal-layout"><aside class="contents"><p>${c.contents}</p><nav aria-label="${c.contents}">${p.sections.map(s=>`<a href="${routeHref(route,s.id)}">${s.title}</a>`).join('')}</nav><div class="publisher-note"><p>Fronstellar Institute</p><span>${language==='id'?'Indonesia & Inggris · Terbit setiap empat bulan':'Indonesian & English · Every four months'}</span></div></aside><div class="journal-reading"><div class="editorial-note"><strong>${c.status}</strong><p>${c.note}</p></div>${route==='authors'?templatesBlock():''}${p.sections.map(s=>`<section id="${s.id}" class="reading-section"><h2>${s.title}</h2>${(s.paragraphs||[]).map(p=>`<p>${p}</p>`).join('')}${s.list?`<ul>${s.list.map(item=>`<li>${item}</li>`).join('')}</ul>`:''}${s.after?`<p>${s.after}</p>`:''}</section>`).join('')}<div class="reading-end">${link('contact',translations[language].issueAction,true)}</div></div></div></article>`;
}
function page(route,t){
  if(route==='article') return articlePage();
  if(route==='issues') return archivePage();
  if(route==='search') return searchPage();
  if(['login','submit'].includes(route)) return accessPage(route);
  if(['about','authors','policies'].includes(route)) return editorialPage(route);
  switch(route){
    case 'home':return `<section class="hero reveal"><div><h1 class="hero-title">Fronstellar<br>Journal</h1><p class="full-title">${journalName}</p><p class="hero-copy">${t.hero}</p>${link('issues',language==='en'?'Browse articles':'Telusuri artikel',true)}</div>${cover(t)}</section><dl class="fact-strip"><div><dt>${t.facts[0]}</dt><dd>Fronstellar Institute</dd></div><div><dt>${t.facts[1]}</dt><dd>${t.languages}</dd></div><div><dt>${t.facts[2]}</dt><dd>${t.frequency}</dd></div></dl><section class="section"><h2>${t.aboutTitle}</h2><div class="section-copy"><p>${t.aboutBody}</p>${link('about',t.aboutLink)}</div></section><section class="edition"><div><h2>${t.issueTitle}</h2><p class="issue-mark">${t.issueStatus}</p></div><div><p>${t.issueBody}</p>${link('issues',t.issueLink,true)}</div></section><section class="section"><h2>${t.editorialTitle}</h2><div><p>${t.editorialBody}</p>${link('editorial',t.editorialLink)}</div></section>`;
    case 'about':return sheet(t.aboutPage,t.aboutIntro,`<div class="draft">${t.draftScope}</div><div class="scope-list">${t.scope.map(([a,b])=>`<article><h2>${a}</h2><p>${b}</p></article>`).join('')}</div><div class="reading"><h2>${t.publicationInfo}</h2><p>${t.schedule}</p><p>${t.identityNote}</p></div>`);
    case 'editorial':return sheet(t.editorialPage,t.editorialIntro,`<div class="editor"><p>Editor in Chief</p><div><p class="name">Yoga Hediasa, M.Hum</p><small>${t.editorNote}</small></div></div><div class="editor"><p>Technical Support</p><div><p class="name">Fahmi Achmad Darojat</p></div></div><div class="editor"><p>Layout Editor</p><div><p class="name">Fahmi Achmad Darojat</p></div></div><div class="reading"><h2>${t.editorialPending}</h2><p>${t.editorialPendingBody}</p>${link('contact',t.issueAction,true)}</div>`);
    case 'issues':return sheet(t.issuesPage,t.issuesIntro,`<div class="reading"><h2>${t.emptyTitle}</h2><p>${t.emptyBody}</p><div class="draft">${t.schedule}</div>${link('contact',t.issueAction,true)}</div>`);
    case 'authors':return sheet(t.authorPage,t.authorIntro,`<div class="draft">${t.draftGuidelines}</div><div class="reading">${t.guidelines.map(([a,b])=>`<h2>${a}</h2><p>${b}</p>`).join('')}<h2>${t.workflowTitle}</h2><ol>${t.workflow.map(s=>`<li>${s}</li>`).join('')}</ol><p>${t.workflowNote}</p>${link('contact',t.issueAction,true)}</div>`);
    case 'policies':return sheet(t.policyPage,t.policyIntro,`<div class="draft">${t.policyDraft}</div>${t.policies.map(([a,b])=>`<details><summary>${a}</summary><p>${b}</p></details>`).join('')}`);
    case 'contact':return sheet(t.contactPage,t.contactIntro,`<div class="contact-grid"><div><h2>${t.phone}</h2><a class="phone" href="tel:+6281789825757">081789825757</a><p>${t.contactNote}</p><p>${t.publisher}: Fronstellar Institute</p></div><div><h2>${t.pendingContact}</h2><p>${t.pendingContactBody}</p></div></div><div class="reading"><h2>${t.statusTitle}</h2><ul class="status-list">${t.statuses.map(([a,b])=>`<li><strong>${a}</strong>${b}</li>`).join('')}</ul></div>`);
  }
}
function currentRoute(){
 const path=location.pathname.replace(/\/+$/,'')||'/';
 if(path==='/articles/seating-preferences')return 'article';
 const name=path.slice(1);return routes.includes(name)?name:'home';
}
function routeHref(route,section=''){
 const path=route==='home'?'/':route==='article'?'/articles/seating-preferences/':`/${route}/`;
 return path+(section?`#${section}`:'');
}
function normaliseLegacyHash(){
 const [name,section]=location.hash.slice(1).split('/');
 if(location.pathname==='/'&&(routes.includes(name)||name==='article'))history.replaceState({},'',routeHref(name,section));
}
function updateNavigationState(open=false){
 document.body.dataset.navOpen=String(open);
 const toggle=document.querySelector('#nav-toggle');
 toggle.setAttribute('aria-expanded',String(open));
 toggle.textContent=language==='en'?(open?'Close menu':'Menu'):(open?'Tutup menu':'Menu');
 document.querySelector('#mobile-submit').textContent=language==='en'?'Submit Article':'Kirim Artikel';
}
function updatePageMetadata(route){
 const a=firstArticle;
 const descriptions={en:'Integrative social sciences research. Three issues per year, double-anonymous review, no publication charges, CC BY 4.0.',id:'Penelitian ilmu sosial integratif. Tiga edisi per tahun, review anonim dua arah, tanpa biaya publikasi, CC BY 4.0.'};
 const description=route==='article'?a.abstract[language].slice(0,180):descriptions[language];
 document.querySelector('meta[name="description"]').content=description;
 document.querySelector('link[rel="canonical"]').href=journalSettings.origin+routeHref(route);
 document.querySelectorAll('meta[data-article-meta]').forEach(x=>x.remove());
 if(route==='article'){
  const values={citation_title:a.title[language],citation_author:a.author,citation_journal_title:journalSettings.name,citation_language:'id',citation_pdf_url:journalSettings.origin+'/articles/seating-preferences.pdf'};
  for(const [name,content] of Object.entries(values)){const meta=document.createElement('meta');meta.name=name;meta.content=content;meta.dataset.articleMeta='';document.head.append(meta);}
 }
}
function render(focus=false){
 normaliseLegacyHash();
 const t=translations[language],route=currentRoute();let sectionId='';
 try{sectionId=decodeURIComponent(location.hash.slice(1));}catch{sectionId='';}
 document.documentElement.lang=language;
 document.title=`${route==='article'?firstArticle.title[language]:t.nav[routes.indexOf(route)]} — Fronstellar Journal`;
 document.querySelector('#notice').textContent=t.notice;
 document.querySelector('#header-caption').textContent=t.caption;
 document.querySelector('#skip').textContent=t.skip;
 document.querySelector('#nav').setAttribute('aria-label',language==='id'?'Navigasi utama':'Main navigation');
 document.querySelector('#nav').innerHTML=routes.map((r,i)=>`<a href="${routeHref(r)}" ${r==='login'?'class="nav-login"':r==='submit'?'class="nav-submit"':''} ${route===r||(route==='article'&&r==='issues')?'aria-current="page"':''}>${t.nav[i]}</a>`).join('');
 document.querySelector('#content').innerHTML=page(route,t);
 document.querySelector('#footer-note').textContent=t.footer;
 document.querySelector('#footer-link').textContent=t.footerLink;
 document.querySelector('#footer-status').textContent=language==='en'?'Open access · No author charges · CC BY 4.0 · Submissions not yet open':'Akses terbuka · Tanpa biaya penulis · CC BY 4.0 · Penerimaan belum dibuka';
 document.querySelector('#workflow-credit-note').textContent=editorialContent[language].credit;
 document.querySelectorAll('[data-lang]').forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.lang===language)));
 window.updateThemeControl(language);updateNavigationState(false);updatePageMetadata(route);
 if(sectionId){const section=document.getElementById(sectionId);if(section){section.setAttribute('tabindex','-1');section.focus({preventScroll:true});section.scrollIntoView({behavior:'instant',block:'start'});return;}}
 if(focus){document.querySelector('#content').focus({preventScroll:true});window.scrollTo({top:0,behavior:'instant'});}
}
function navigate(url){history.pushState({},'',url.pathname+url.search+url.hash);render(true);}
if(!globalThis.__buildOnly){
 document.querySelectorAll('[data-lang]').forEach(b=>b.addEventListener('click',()=>{language=b.dataset.lang;render();}));
 document.querySelector('#nav-toggle').addEventListener('click',()=>updateNavigationState(document.body.dataset.navOpen!=='true'));
 document.addEventListener('keydown',event=>{if(event.key==='Escape'&&document.body.dataset.navOpen==='true'){updateNavigationState(false);document.querySelector('#nav-toggle').focus();}});
 document.addEventListener('click',event=>{
  const anchor=event.target.closest('a');if(!anchor||anchor.target||anchor.hasAttribute('download')||event.button!==0||event.metaKey||event.ctrlKey||event.shiftKey||event.altKey)return;
  const url=new URL(anchor.href,location.href);if(url.origin!==location.origin)return;
  if(anchor.id==='skip'){event.preventDefault();document.querySelector('#content').focus();return;}
  const path=url.pathname.replace(/\/+$/,'')||'/';
  if(path==='/'||path==='/articles/seating-preferences'||routes.includes(path.slice(1))){event.preventDefault();navigate(url);}
 });
 document.addEventListener('click',async event=>{const button=event.target.closest('[data-copy]');if(!button)return;const text=button.dataset.copy==='url'?journalSettings.origin+routeHref('article'):manuscriptCitation();const status=document.querySelector('#copy-status');try{await navigator.clipboard.writeText(text);status.textContent=language==='en'?'Copied to clipboard.':'Disalin ke clipboard.';}catch{status.textContent=language==='en'?'Copy manually from the citation or permanent link below.':'Salin secara manual dari sitasi atau tautan permanen di bawah.';}});
 window.addEventListener('popstate',()=>render(true));
 window.addEventListener('hashchange',()=>render(true));
 render();
}
