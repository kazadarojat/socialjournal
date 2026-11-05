import assert from 'node:assert/strict';
import {readFile,stat} from 'node:fs/promises';
import {resolve} from 'node:path';
import vm from 'node:vm';
const dist=resolve(import.meta.dirname,'../dist');
const c=vm.createContext({__buildOnly:true,URLSearchParams,URL,location:{pathname:'/',hash:'',search:''}});
for(const file of ['journal-settings.js','editorial-content.js','policy-updates.js','access.js','article-data.js','article.js','catalog.js','app.js'])vm.runInContext(await readFile(resolve(dist,file),'utf8'),c,{filename:file});
const get=code=>vm.runInContext(code,c);
assert.equal(get('journalSettings.name'),'Fronstellar Journal of Integrative Multidisciplinary Social Sciences');
assert.equal(get('matchingArticles({q:"Fahmi"}).length'),1);
assert.equal(get('matchingArticles({q:"posisi duduk"}).length'),1);
assert.equal(get('matchingArticles({q:"unmatched-test-term"}).length'),0);
assert.equal(get('matchingArticles({status:"published"}).length'),0);
assert.equal(get('matchingArticles({year:"2024"}).length'),0);
assert.equal(get('matchingArticles({status:"preview",year:"2025"}).length'),1);
assert.equal(get("routeHref('article')"),'/articles/seating-preferences/');
for(const lang of ['en','id']){
 get(`language='${lang}'`);
 assert.match(get("editorialContent[language].policies.sections.find(s=>s.id==='access').paragraphs.join(' ')"),/CC BY 4.0/);
 assert.match(get("editorialContent[language].policies.sections.find(s=>s.id==='peer-review').paragraphs.join(' ')"),lang==='en'?/two reviewers/:/dua reviewer/);
 assert.ok(get("articlePage()").includes('/downloads/seating-preferences.ris'));
 assert.ok(get("templatesBlock()").includes('manuscript-en.docx'));
 c.location.search='?q=%3Cscript%3Ealert(1)%3C%2Fscript%3E';
 assert.ok(!get('searchPage()').includes('<script>alert(1)</script>'));
}
const pages=[...get('routes'),'article'];
for(const route of pages){
 const path=get(`routeHref('${route}')`),html=await readFile(resolve(dist,'.'+path,'index.html'),'utf8');
 assert.match(html,/<h1/);assert.ok(html.includes('rel="canonical"'));assert.ok(!html.includes('Multidicippline'));
 for(const match of html.matchAll(/(?:href|src)="([^"]+)"/g)){
  const href=match[1];if(/^(https?:|mailto:|tel:|#|data:)/.test(href))continue;
  const url=new URL(href,'https://example.invalid/'),file=resolve(dist,'.'+url.pathname);
  const target=await stat(file);if(target.isDirectory())await stat(resolve(file,'index.html'));
 }
}
const article=await readFile(resolve(dist,'articles/seating-preferences/index.html'),'utf8');
assert.match(article,/citation_title/);assert.match(article,/ScholarlyArticle/);assert.ok(!article.includes('citation_publication_date'));
assert.ok((await stat(resolve(dist,'articles/seating-preferences.pdf'))).size>0);
console.log('Passed: catalogue filters, bilingual approved policies, safe query rendering, 11 canonical pages, all local links/assets, manuscript metadata and PDF.');
