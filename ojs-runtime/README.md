# Fronstellar OJS runtime — pending a real deployment test

This is OJS 3.5.0-6 from PKP's official release archive, verified against the existing downloaded archive's SHA-256. It is a separate PHP service; Vercel continues to serve the journal website.

The Docker image has not yet been built or executed in this workspace (Docker is unavailable). PHP syntax, startup shell syntax, and configuration round-trip checks using OJS's own parser passed with a workspace-local official portable PHP runtime. Its first Railway build must pass the extension checks and PHP lint included in the Dockerfile. Do not call the service installed before the OJS installer and workflow checks pass.

## Railway configuration

1. Use the already authorized free trial only. Inspect real limits before creating resources. Do not upgrade, add a payment method or accept charges without a new user instruction.
2. Connect only `kazadarojat/socialjournal`. Create an OJS service using root directory `/ojs-runtime` and its Dockerfile. Generate a Railway HTTPS domain before entering `OJS_BASE_URL`.
3. Mount a persistent volume at `/var/ojs`. Configuration, encryption keys, private manuscript files and public upload files all live under this mount. Never use an ephemeral filesystem for these files.
4. Use a dedicated PostgreSQL or MariaDB database with persistent storage and a dedicated database user. Do not grant the OJS user access to unrelated application data. Inspect trial volume allowances first; otherwise stop and choose another available database without purchasing a plan.
5. Set the private variables below in Railway. Generate passwords privately; do not commit them, put them in build arguments, print them in logs or send them in chat.

Required variables: `OJS_BASE_URL` (HTTPS origin), `OJS_DB_DRIVER` (`pgsql` or `mysqli`), `OJS_DB_HOST`, `OJS_DB_PORT`, `OJS_DB_NAME`, `OJS_DB_USER`, `OJS_DB_PASSWORD`, `OJS_SETUP_PASSWORD` (at least 24 characters). Leave `OJS_PRIVATE_PREVIEW` unset during installation/testing. Railway supplies `PORT`.

Health check: `/healthz`. This verifies that the PHP container starts; it is not proof of database initialization, login, email or editorial workflow readiness.

## Initialization and journal settings

Open the protected site through HTTPS. The temporary protection username is `setup`; its password exists only in the Railway private variable. The owner completes OJS administrator credential creation privately. Administrator and contact email authorized by the publisher: `fidarojat@gmail.com`. Enable English and Indonesian, use English as the primary locale and journal path `fronstellar`.

Use the policies in `../deployment/README-ID.md` and the installation kit. Name: Fronstellar Journal of Integrative Multidisciplinary Social Sciences. Publisher: Fronstellar Institute. Three issues per year, two double-anonymous reviewers, no author charges, CC BY 4.0. First manuscript remains unpublished and requires independent handling because its author is journal staff.

Configure SMTP via `OJS_SMTP_HOST`, `OJS_SMTP_PORT`, `OJS_SMTP_AUTH`, `OJS_SMTP_USER`, `OJS_SMTP_PASSWORD`. Do not open public submissions until email delivery and password reset work. The runtime does not invent an SMTP provider.

After installing, restart the service and test login, file upload/download, author/reviewer/editor access, revisions, scheduled tasks and backup/restore. `/var/ojs/config` and the database contain sensitive data and require protected backups. Only after these checks may `OJS_PRIVATE_PREVIEW=false` remove temporary setup protection; OJS authorization still protects editorial functions. An uninstalled instance remains setup-protected regardless of that flag.

Finally update the Vercel Login and Submit links to the actual verified OJS journal URL. This package does not claim a running service or completed peer review.
