import assert from 'node:assert/strict';
import {readFile,readdir,stat} from 'node:fs/promises';
import {resolve} from 'node:path';
import vm from 'node:vm';
for(const [folder,base]of [['public',''],['docs','/socialjournal']]){
 const root=resolve(import.meta.dirname,'..',folder);
 const c=vm.createContext({__buildOnly:true,URLSearchParams,URL,location:{pathname:base+'/search/',hash:'',search:'?q=Fahmi'}});
 for(const f of ['journal-settings.js','editorial-content.js','policy-updates.js','access.js','article-data.js','article.js','catalog.js','app.js'])vm.runInContext(await readFile(resolve(root,f),'utf8'),c,{filename:f});
 const get=s=>vm.runInContext(s,c);
 assert.equal(get('currentRoute()'),'search');
 assert.equal(get("routeHref('article')"),base+'/articles/seating-preferences/');
 assert.match(get('searchPage()'),/Results: 1 manuscript/);
 assert.ok(get('hostedMarkup(templatesBlock())').includes('href="'+base+'/downloads/manuscript-en.docx"'));
 let count=0;
 async function scan(dir){for(const e of await readdir(dir,{withFileTypes:true})){const p=resolve(dir,e.name);if(e.isDirectory()){await scan(p);continue;}if(e.name!=='index.html')continue;count++;const html=await readFile(p,'utf8');assert.ok(html.includes('<base href="'+base+'/">'));
  for(const m of html.matchAll(/(?:href|src|action)="([^"]+)"/g)){
   if(/^(https?:|mailto:|tel:|#|data:)/.test(m[1]))continue;
   const url=new URL(m[1],'https://test.invalid'+base+'/');assert.ok(url.pathname.startsWith(base+'/'));
   const target=resolve(root,'.'+url.pathname.slice(base.length));const info=await stat(target);if(info.isDirectory())await stat(resolve(target,'index.html'));
  }
 }}
 await scan(root);assert.equal(count,11);console.log(`Verified ${folder}: all 11 pages, prefixed routes, search, templates and local assets.`);
}
