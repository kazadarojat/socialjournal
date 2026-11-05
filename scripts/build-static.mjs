import {readFile,writeFile,mkdir} from 'node:fs/promises';
import vm from 'node:vm';
import {resolve} from 'node:path';
const root=resolve(import.meta.dirname,'..'),dist=resolve(root,'dist');
const shell=await readFile(resolve(root,'site-shell.html'),'utf8');
const context=vm.createContext({__buildOnly:true,URLSearchParams,URL,console,location:{pathname:'/',hash:'',search:''}});
for(const file of ['journal-settings.js','editorial-content.js','policy-updates.js','access.js','article-data.js','article.js','catalog.js','app.js'])vm.runInContext(await readFile(resolve(dist,file),'utf8'),context,{filename:file});
const get=code=>vm.runInContext(code,context);
const escape=value=>String(value).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const settings=get('journalSettings'),article=get('firstArticle'),routes=[...get('routes'),'article'];
const nav=get("routes.map((r,i)=>`<a href=\"${routeHref(r)}\" ${r==='login'?'class=\"nav-login\"':r==='submit'?'class=\"nav-submit\"':''}>${translations.en.nav[i]}</a>`).join('')");
const labels=get('translations.en.nav');
for(const route of routes){
 const path=get(`routeHref('${route}')`);
 context.location.pathname=path;
 const title=route==='article'?article.title.en:labels[get(`routes.indexOf('${route}')`)];
 const body=get(`language='en';page('${route}',translations.en)`);
 const description=route==='article'?article.abstract.en.slice(0,180):'Integrative social sciences research. Three issues per year, double-anonymous review by two reviewers, no publication charges, CC BY 4.0.';
 let html=shell.replace(/<title>.*?<\/title>/s,`<title>${escape(title)} — Fronstellar Journal</title>`)
 .replace(/<meta name="description" content="[^"]*">/,`<meta name="description" content="${escape(description)}">`)
 .replace(/<link rel="canonical" href="[^"]*">/,`<link rel="canonical" href="${settings.origin+path}">`)
 .replace('<nav id="nav" aria-label="Main navigation"></nav>',`<nav id="nav" aria-label="Main navigation">${nav.replace(`href="${path}"`,`href="${path}" aria-current="page"`)}</nav>`)
 .replace('<main id="content" tabindex="-1"></main>',`<main id="content" tabindex="-1">${body}</main>`)
 .replace('<p id="footer-note"></p>',`<p id="footer-note">${get('translations.en.footer')}</p>`)
 .replace('<p class="footer-small" id="footer-status"></p>','<p class="footer-small" id="footer-status">Open access · No author charges · CC BY 4.0 · Submissions not yet open</p>')
 .replace('<p id="workflow-credit-note"></p>',`<p id="workflow-credit-note">${get('editorialContent.en.credit')}</p>`)
 .replace('id="footer-link">Hubungi redaksi ↗','id="footer-link">Contact the editors ↗');
 const schema=route==='article'?{'@context':'https://schema.org','@type':'ScholarlyArticle',headline:article.title.en,alternativeHeadline:article.title.id,author:{'@type':'Person',name:article.author},abstract:article.abstract.en,inLanguage:'id',url:settings.origin+path,creativeWorkStatus:'Manuscript preview — not accepted or assigned to a published issue',license:settings.licenseUrl,encoding:{'@type':'MediaObject',contentUrl:settings.origin+'/articles/seating-preferences.pdf',encodingFormat:'application/pdf'}}:{'@context':'https://schema.org','@type':'Periodical',name:settings.name,publisher:{'@type':'Organization',name:settings.publisher},url:settings.origin};
 let metadata=`<meta property="og:title" content="${escape(title)}"><meta property="og:description" content="${escape(description)}"><meta property="og:url" content="${settings.origin+path}"><meta property="og:image" content="${settings.origin}/assets/fronstellar-cover-current.png"><script type="application/ld+json">${JSON.stringify(schema).replace(/</g,'\\u003c')}</script>`;
 if(route==='article')for(const [key,value]of Object.entries({citation_title:article.title.en,citation_author:article.author,citation_journal_title:settings.name,citation_language:'id',citation_pdf_url:settings.origin+'/articles/seating-preferences.pdf'}))metadata+=`<meta data-article-meta name="${key}" content="${escape(value)}">`;
 html=html.replace('</head>',metadata+'</head>');
 const directory=resolve(dist,'.'+path);await mkdir(directory,{recursive:true});await writeFile(resolve(directory,'index.html'),html);
}
await mkdir(resolve(dist,'downloads'),{recursive:true});
const permalink=settings.origin+'/articles/seating-preferences/';
await writeFile(resolve(dist,'downloads/seating-preferences.ris'),`TY  - UNPB\nAU  - Darojat, Fahmi Achmad\nTI  - ${article.title.en}\nPY  - ${article.manuscriptYear}\nUR  - ${permalink}\nN1  - Unpublished manuscript preview. No peer-review or acceptance status recorded.\nER  - \n`);
await writeFile(resolve(dist,'downloads/seating-preferences.bib'),`@unpublished{darojat2025seating,\n  author = {Darojat, Fahmi Achmad},\n  title = {${article.title.en}},\n  year = {${article.manuscriptYear}},\n  note = {Unpublished manuscript preview; acceptance and issue assignment pending},\n  url = {${permalink}}\n}\n`);
await writeFile(resolve(dist,'robots.txt'),'User-agent: *\nDisallow: /\n# Preparation site: enable indexing only after an approved public launch.\n');
await writeFile(resolve(dist,'sitemap.xml'),`<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">${routes.filter(r=>!['login','submit','search'].includes(r)).map(r=>`<url><loc>${settings.origin+get(`routeHref('${r}')`)}</loc></url>`).join('')}</urlset>`);
console.log(`Built ${routes.length} static pages, article metadata, RIS, BibTeX, robots and sitemap.`);
