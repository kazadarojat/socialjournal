// Public catalogue only. No authentication tokens or private manuscript files are exposed.
(async()=>{
 if(!window.journalBackendConfig)return;
 const {url,publishableKey}=window.journalBackendConfig;
 try{
  const response=await fetch(url+'/rest/v1/fronstellar_catalog?select=id,payload&visible=eq.true',{
   headers:{apikey:publishableKey},signal:AbortSignal.timeout(6000)
  });
  if(!response.ok)throw new Error('Catalogue request failed');
  const rows=await response.json();
  const item=rows.find(r=>r.id===firstArticle.id)?.payload;
  // The current site supports this single manuscript; new layouts need an editorial update.
  if(item&&item.id===firstArticle.id&&item.status==='preview'&&
   ['en','id'].every(l=>typeof item.title?.[l]==='string'&&typeof item.abstract?.[l]==='string'&&Array.isArray(item.keywords?.[l]))&&
   typeof item.author==='string'&&typeof item.affiliation==='string'){
   articleCatalogue.splice(0,articleCatalogue.length,{...firstArticle,title:item.title,abstract:item.abstract,keywords:item.keywords,author:item.author,affiliation:item.affiliation});
   document.documentElement.dataset.catalogueSource='supabase';
   if(['search','issues'].includes(currentRoute())&&!document.activeElement?.matches('input,select,textarea'))render();
  }
 }catch{document.documentElement.dataset.catalogueSource='static-fallback';}
})();
