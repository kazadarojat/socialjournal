# Fronstellar Journal

Fronstellar Journal of Integrative Multidisciplinary Social Sciences — Fronstellar Institute.

## Hosting

- GitHub Pages: `main`, `/docs`, https://kazadarojat.github.io/socialjournal/
- Vercel: import this repository; build `node scripts/build-hosting.mjs`; output `public`.
- Supabase: read-only public manuscript catalogue, project `wblinloxozmqsmfrxtbj`. The browser uses only a publishable key.

Build with Node 22+: `npm run build`. Generate Pages output: `HOSTING_OUTPUT=docs node scripts/build-hosting.mjs --pages`. Verify: `node scripts/verify-site.mjs` and `node scripts/verify-hosting.mjs` after building both outputs. Commit the generated docs when content changes.

The manuscript remains an unpublished preview. OJS accounts, submissions, reviewer assignments and production editorial workflow are not active. The private manuscript bucket denies client access until that service is implemented. Public catalogue metadata can be edited by a trusted administrator; website visitors cannot write.

English and light theme are defaults. Policies: two independent double-anonymous reviewers, no author charges, CC BY 4.0. ISSN placeholders are not valid identifiers. No indexing or acceptance is claimed.

Original article PDF and supplied cover are included under publisher/author responsibility. Do not upload credentials, private submissions or review records to this public repository. See deployment/README-ID.md for OJS installation guidance.
