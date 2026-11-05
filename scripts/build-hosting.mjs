import {cp,mkdir,readFile,writeFile,readdir} from 'node:fs/promises';
import {resolve,relative,extname} from 'node:path';
const root=resolve(import.meta.dirname,'..');
const pages=process.argv.includes('--pages');
const base=pages?'/socialjournal':'';
const origin=pages?'https://kazadarojat.github.io':(process.env.SITE_ORIGIN||'https://socialjournal.vercel.app').replace(/\/$/,'');
const out=resolve(root,process.env.HOSTING_OUTPUT||'public');
await mkdir(out,{recursive:true});
await cp(resolve(root,'dist'),out,{recursive:true});
const old='https://fronstellar-journal.wigglyvale22.chatgpt.site';
const localise=html=>base?html.replace(/(href|src|action)="\/(?!\/|socialjournal(?:\/|"))/g,`$1="${base}/`):html;
async function walk(dir){for(const e of await readdir(dir,{withFileTypes:true})){const p=resolve(dir,e.name);if(e.isDirectory()){await walk(p);continue;}if(!['.html','.js','.xml','.ris','.bib','.txt'].includes(extname(p)))continue;
 let s=await readFile(p,'utf8');
 if(e.name==='journal-settings.js')s=s.replace(old,origin);
 else s=s.replaceAll(old,origin+base);
 if(e.name==='app.js'){
  s=`const hostingBase=${JSON.stringify(base)};\nfunction hostedMarkup(html){return hostingBase?html.replace(/(href|src|action)="\\/(?!\\/|socialjournal(?:\\/|"))/g,'$1="'+hostingBase+'/'):html;}\n`+s;
  s=s.replaceAll("location.pathname.replace(/\\/+$/,'')", "location.pathname.slice(hostingBase.length).replace(/\\/+$/,'')");
  s=s.replaceAll("url.pathname.replace(/\\/+$/,'')", "url.pathname.slice(hostingBase.length).replace(/\\/+$/,'')");
  s=s.replace("return path+(section?", "return hostingBase+path+(section?");
  s=s.replace("location.pathname==='/'", "location.pathname===hostingBase+'/'");
  s=s.replace(".innerHTML=page(route,t)",".innerHTML=hostedMarkup(page(route,t))");
 }
 if(e.name==='article.js')s=s.replaceAll("journalSettings.origin+'/articles/", "journalSettings.origin+"+JSON.stringify(base)+"+'/articles/");
 if(e.name==='app.js')s=s.replaceAll("journalSettings.origin+'/articles/", "journalSettings.origin+hostingBase+'/articles/");
 if(e.name==='index.html'){
  s=localise(s).replace(/<base href="[^"]*">/,`<base href="${base}/">`);
  s=s.replace('</body>','<script src="./backend-config.js" defer></script><script src="./backend.js" defer></script></body>');
 }
 await writeFile(p,s);
}}
await walk(out);
await cp(resolve(root,'deployment/backend-config.js'),resolve(out,'backend-config.js'));
await cp(resolve(root,'deployment/backend.js'),resolve(out,'backend.js'));
await writeFile(resolve(out,'.nojekyll'),'');
await writeFile(resolve(out,'404.html'),`<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>Page not found — Fronstellar Journal</title><h1>Page not found</h1><p><a href="${base}/">Return to Fronstellar Journal</a></p></html>`);
console.log(`Prepared ${relative(root,out)} for ${origin+base}/`);
