-- Applied using the Supabase migration API as fronstellar_catalog_bootstrap.
-- Only already-public manuscript metadata is exposed through this table.
create table public.fronstellar_catalog (
 id text primary key,
 payload jsonb not null check (jsonb_typeof(payload) = 'object'),
 visible boolean not null default false,
 updated_at timestamptz not null default now()
);
alter table public.fronstellar_catalog enable row level security;
revoke all on public.fronstellar_catalog from anon, authenticated;
grant select on public.fronstellar_catalog to anon, authenticated;
create policy fronstellar_catalog_public_read on public.fronstellar_catalog
 for select to anon, authenticated using (visible = true);
comment on table public.fronstellar_catalog is 'Public Fronstellar catalogue. No peer-review records, accounts or private manuscript data.';
insert into storage.buckets (id,name,public,file_size_limit,allowed_mime_types)
 values ('fronstellar-manuscripts','fronstellar-manuscripts',false,20971520,
 array['application/pdf','application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
 on conflict (id) do nothing;
-- No storage policies are granted: manuscript storage stays closed until the submission service is implemented.
