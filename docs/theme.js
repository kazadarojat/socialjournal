// Apply the saved appearance before styles paint. Storage is optional.
(function(){
 // New preference key resets the previous system-derived appearance to Light.
 const key='fronstellar-theme-v2';
 let saved=null;
 try { const value=localStorage.getItem(key);if(value==='light'||value==='dark')saved=value; } catch {}
 function apply(value){
  document.documentElement.dataset.theme=value;
  document.documentElement.style.colorScheme=value;
  const meta=document.querySelector('meta[name="theme-color"]');
  if(meta)meta.content=value==='dark'?'#171518':'#7d0c14';
 }
 apply(saved||'light');
 window.updateThemeControl=function(lang){
  const button=document.getElementById('theme-toggle');if(!button)return;
  const dark=document.documentElement.dataset.theme==='dark';
  button.setAttribute('aria-pressed',String(dark));
  const label=lang==='id'?(dark?'Gunakan tema terang':'Gunakan tema gelap'):(dark?'Switch to light theme':'Switch to dark theme');
  button.setAttribute('aria-label',label);button.title=label;
  button.querySelector('[data-theme-label]').textContent=lang==='id'?(dark?'Gelap':'Terang'):(dark?'Dark':'Light');
 };
 document.addEventListener('DOMContentLoaded',()=>{
  const button=document.getElementById('theme-toggle');
  if(!button)return;
  window.updateThemeControl(document.documentElement.lang);
  button.addEventListener('click',()=>{
   saved=document.documentElement.dataset.theme==='dark'?'light':'dark';
   apply(saved);try{localStorage.setItem(key,saved);}catch{}
   window.updateThemeControl(document.documentElement.lang);
  });
 });
 window.addEventListener('storage',event=>{
  if(event.key!==key&&event.key!==null)return;
  saved=event.newValue==='dark'||event.newValue==='light'?event.newValue:null;
  apply(saved||'light');window.updateThemeControl(document.documentElement.lang);
 });
})();
