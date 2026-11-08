<?php
// Runs locally inside the container. Never log environment values or credentials.
declare(strict_types=1);
function requiredEnv(string $key): string {
    $value = getenv($key);
    if ($value === false || $value === '' || preg_match('/[\r\n\x00]/', $value)) {
        throw new RuntimeException('Missing or invalid setting: ' . $key);
    }
    return $value;
}
function iniValue(string $value): string { return '"' . addcslashes($value, "\\\"") . '"'; }
function setIni(string $source, string $section, string $key, string $value): string {
    $sectionPattern = '/(^\[' . preg_quote($section, '/') . '\][^\r\n]*\R)(.*?)(?=^\[|\z)/ms';
    if (!preg_match($sectionPattern, $source)) { throw new RuntimeException('Missing config section: '.$section); }
    return preg_replace_callback($sectionPattern, function ($match) use ($key, $value) {
        $pattern = '/^\h*' . preg_quote($key, '/') . '\h*=.*$/m';
        $replacement = $key . ' = ' . $value;
        $body = preg_match($pattern, $match[2])
            ? preg_replace_callback($pattern, fn() => $replacement, $match[2])
            : $match[2] . "\n" . $replacement . "\n";
        return $match[1] . $body;
    }, $source);
}
$url = rtrim(requiredEnv('OJS_BASE_URL'), '/');
$parts = parse_url($url);
if (($parts['scheme'] ?? '') !== 'https' || empty($parts['host']) || !empty($parts['user']) || !empty($parts['pass']) || !empty($parts['query']) || !empty($parts['fragment']) || !empty($parts['path'])) {
    throw new RuntimeException('OJS_BASE_URL must be an HTTPS origin without a path.');
}
// OJS expects the installer driver name, while PHP uses pgsql for the extension.
$driver = match (requiredEnv('OJS_DB_DRIVER')) {
    'pgsql', 'postgres9' => 'postgres9',
    'mysqli' => 'mysqli',
    default => throw new RuntimeException('Unsupported database driver.'),
};
if (getenv('RAILWAY_VOLUME_MOUNT_PATH') !== '/var/ojs') { throw new RuntimeException('Mount the Railway persistent volume at /var/ojs before starting OJS.'); }
foreach (['/var/ojs', '/var/ojs/files', '/var/ojs/public', '/var/ojs/config'] as $dir) {
    if (!is_dir($dir) && !mkdir($dir, 0750, true)) { throw new RuntimeException('Cannot create persistent directory.'); }
    if (!chown($dir, 'www-data') || !chgrp($dir, 'www-data') || !chmod($dir, 0750)) {
        throw new RuntimeException('Cannot set permissions on persistent OJS directories.');
    }
}
// Railway must mount a real persistent volume at /var/ojs before this service starts.
$configPath = '/var/ojs/config/config.inc.php';
$config = file_get_contents(is_file($configPath) ? $configPath : '/var/www/html/config.TEMPLATE.inc.php');
if ($config === false) { throw new RuntimeException('OJS configuration template is unavailable.'); }
$installed = (bool) preg_match('/^installed\h*=\h*(On|true|1)\h*$/mi', $config);
if ($installed) { touch('/var/ojs/config/installed'); } elseif (is_file('/var/ojs/config/installed')) { unlink('/var/ojs/config/installed'); }
$keysPath = '/var/ojs/config/keys.json';
if (!is_file($keysPath)) {
    $keys = ['app_key' => 'base64:'.base64_encode(random_bytes(32)), 'salt' => bin2hex(random_bytes(32)), 'api_key_secret' => bin2hex(random_bytes(32))];
    file_put_contents($keysPath, json_encode($keys, JSON_THROW_ON_ERROR), LOCK_EX);
    chmod($keysPath, 0640);
} else { $keys = json_decode(file_get_contents($keysPath), true, 512, JSON_THROW_ON_ERROR); }
$settings = [
    'general' => ['base_url' => iniValue($url), 'allowed_hosts' => iniValue('["'.$parts['host'].'"]'), 'time_zone' => '"Asia/Jakarta"', 'app_key' => iniValue($keys['app_key']), 'enable_beacon' => 'Off'],
    'database' => ['driver' => iniValue($driver), 'host' => iniValue(requiredEnv('OJS_DB_HOST')), 'port' => iniValue(requiredEnv('OJS_DB_PORT')), 'name' => iniValue(requiredEnv('OJS_DB_NAME')), 'username' => iniValue(requiredEnv('OJS_DB_USER')), 'password' => iniValue(requiredEnv('OJS_DB_PASSWORD'))],
    'files' => ['files_dir' => '"/var/ojs/files"'],
    'i18n' => ['locale' => '"en"'],
    'security' => ['force_ssl' => 'On', 'force_login_ssl' => 'On', 'salt' => iniValue($keys['salt']), 'api_key_secret' => iniValue($keys['api_key_secret'])],
    'queues' => ['job_runner' => 'Off', 'process_jobs_at_task_scheduler' => 'On'],
    'schedule' => ['task_runner' => 'Off']
];
if (getenv('OJS_SMTP_HOST')) {
    $settings['email'] = ['default'=>'"smtp"', 'smtp_server'=>iniValue(requiredEnv('OJS_SMTP_HOST')), 'smtp_port'=>iniValue(requiredEnv('OJS_SMTP_PORT')), 'smtp_auth'=>iniValue(getenv('OJS_SMTP_AUTH') ?: 'tls'), 'smtp_username'=>iniValue(requiredEnv('OJS_SMTP_USER')), 'smtp_password'=>iniValue(requiredEnv('OJS_SMTP_PASSWORD'))];
}
foreach ($settings as $section => $pairs) { foreach ($pairs as $key => $value) { $config = setIni($config, $section, $key, $value); } }
if (file_put_contents($configPath, $config, LOCK_EX) !== strlen($config)
    || !chmod($configPath, 0640) || !chown($configPath, 'www-data') || !chgrp($configPath, 'www-data')) {
    throw new RuntimeException('Cannot write persistent OJS configuration.');
}
// Point both the OJS bootstrap and installer at the persistent file directly.
$bootstrapPath = '/var/www/html/lib/pkp/classes/config/Config.php';
$bootstrap = file_get_contents($bootstrapPath);
$bundledDefinition = 'define(\'CONFIG_FILE\', \PKP\core\Core::getBaseDir() . \'/config.inc.php\');';
$persistentDefinition = 'define(\'CONFIG_FILE\', ' . var_export($configPath, true) . ');';
if ($bootstrap === false) {
    throw new RuntimeException('Cannot read the OJS configuration bootstrap.');
}
if (!str_contains($bootstrap, $persistentDefinition)) {
    $bootstrap = str_replace($bundledDefinition, $persistentDefinition, $bootstrap, $replacements);
    if ($replacements !== 1 || file_put_contents($bootstrapPath, $bootstrap, LOCK_EX) !== strlen($bootstrap)) {
        throw new RuntimeException('Cannot configure the persistent OJS configuration path.');
    }
}
clearstatcache(true);
$checkConfig = 'require "/var/www/html/lib/pkp/classes/config/ConfigParser.php"; '
    . 'exit(is_array(\\PKP\\config\\ConfigParser::readConfig($argv[1])) && is_writable($argv[1]) ? 0 : 1);';
passthru('runuser -u www-data -- /usr/local/bin/php -r ' . escapeshellarg($checkConfig) . ' ' . escapeshellarg($configPath), $checkStatus);
if ($checkStatus !== 0) {
    throw new RuntimeException('The web server user cannot read or update the OJS configuration.');
}
// The image establishes the symlink at build time; never rename an image-layer directory at startup.
$copy = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('/opt/fronstellar/public.bundled', FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
foreach ($copy as $file) {
    $destination = '/var/ojs/public/' . $copy->getSubPathName();
    if ($file->isDir()) { if (!is_dir($destination)) { mkdir($destination, 0750, true); } }
    elseif (!file_exists($destination) && !copy($file->getPathname(), $destination)) { throw new RuntimeException('Cannot initialize persistent public files.'); }
}
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('/var/ojs', FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
foreach ($iterator as $file) { chown($file->getPathname(), 'www-data'); chgrp($file->getPathname(), 'www-data'); }
// Resolve the client address only for requests arriving from the Railway ingress subnet.
exec('a2enmod remoteip', $remoteIpOutput, $remoteIpStatus);
if ($remoteIpStatus !== 0) {
    throw new RuntimeException('Cannot enable Apache client IP handling.');
}
$apache = "RemoteIPHeader X-Real-IP\nRemoteIPTrustedProxy 100.64.0.0/24\nServerName ".$parts['host']."\nServerTokens Prod\nServerSignature Off\nSetEnvIf X-Forwarded-Proto \"^https$\" HTTPS=on\n<Directory /var/www/html>\n Options -Indexes +FollowSymLinks\n AllowOverride None\n Require all granted\n</Directory>\n<FilesMatch \"^(config\\.inc\\.php|.*\\.(ini|log|sql))$\">\n Require all denied\n</FilesMatch>\n";
$publicJournal = $installed && getenv('OJS_PRIVATE_PREVIEW') === 'false';
if (!$publicJournal) {
    $setupPassword = requiredEnv('OJS_SETUP_PASSWORD');
    if (strlen($setupPassword) < 24) { throw new RuntimeException('Setup protection requires a password of at least 24 characters.'); }
    file_put_contents('/var/ojs/config/setup.htpasswd', 'setup:'.password_hash($setupPassword, PASSWORD_BCRYPT)."\n", LOCK_EX);
    chmod('/var/ojs/config/setup.htpasswd', 0640); chown('/var/ojs/config/setup.htpasswd', 'www-data'); chgrp('/var/ojs/config/setup.htpasswd', 'www-data');
    $apache .= "<Location />\n AuthType Basic\n AuthName \"Fronstellar OJS setup\"\n AuthUserFile /var/ojs/config/setup.htpasswd\n Require valid-user\n</Location>\n";
}
$apache .= "<LocationMatch \"(?i)(?:^|/)(?:config(?:\\.template)?\\.inc\\.php|[^/]+\\.(?:ini|log|sql))(?:/|$)\">\n Require all denied\n</LocationMatch>\n";
$apache .= "Alias /healthz /opt/fronstellar/healthz\n<Location /healthz>\n AuthType None\n Require all granted\n</Location>\n<Directory /opt/fronstellar>\n Require all granted\n</Directory>\n";
file_put_contents('/opt/fronstellar/healthz', "OJS runtime ready\n");
chmod('/opt/fronstellar/healthz', 0644);
file_put_contents('/etc/apache2/conf-enabled/fronstellar.conf', $apache);

// Use the Fronstellar GitHub front page for this journal's OJS home route.
$journalHomePath = '/var/www/html/templates/frontend/pages/indexJournal.tpl';
$journalHome = file_get_contents($journalHomePath);
if ($journalHome === false) {
    throw new RuntimeException('Cannot read the OJS journal homepage template.');
}
if (!str_starts_with($journalHome, '{* Fronstellar branded homepage *}')) {
    $fronstellarHome = <<<'FRONSTELLAR_HOME'
{if $currentLocale == "id"}{literal}<!doctype html>
<html lang="id">
{/literal}{else}{literal}<!doctype html>
<html lang="en">
{/literal}{/if}
{literal}<head><script>document.documentElement.classList.add('js');</script>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="Integrative social sciences research. Three issues per year, double-anonymous review by two reviewers, no publication charges, CC BY 4.0.">
  <meta name="robots" content="noindex,nofollow">
  <meta name="theme-color" content="#7d0c14">
  <title>Home — Fronstellar Journal</title>
  <link rel="preconnect" href="https://api.fontshare.com"><link rel="preconnect" href="https://cdn.fontshare.com" crossorigin>
  <link href="https://api.fontshare.com/v2/css?f[]=erode@400,500,600&display=swap" rel="stylesheet">
<style>
:root{--bg:#e2e2e2;--paper:#f1f0ee;--surface:#d6d6d6;--ink:#7d0c14;--muted:#74494d;--accent:#7d0c14;--reverse:#f1f0ee;--body:clamp(1.0625rem,1rem + .25vw,1.1875rem);--small:.875rem;--lead:clamp(1.25rem,1.1rem + .7vw,1.625rem);--head:clamp(2rem,1.3rem + 3.2vw,4rem);--display:clamp(3.1rem,7.9vw,7.5rem);--gutter:clamp(20px,4vw,64px)}
*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:var(--bg);color:var(--ink);font-family:Erode,Georgia,serif;font-size:var(--body);line-height:1.5}a{color:inherit;text-underline-offset:5px}button{font:inherit;cursor:pointer}button,a{-webkit-tap-highlight-color:transparent}a:hover{opacity:.75}a:focus-visible,button:focus-visible,summary:focus-visible{outline:3px solid var(--ink);outline-offset:5px}::selection{background:var(--ink);color:var(--reverse)}.skip{position:fixed;top:-100px;left:20px;padding:12px;background:var(--paper);z-index:10}.skip:focus{top:12px}.notice{padding:10px var(--gutter);background:var(--ink);color:var(--reverse);display:flex;justify-content:space-between;gap:20px;font-size:var(--small)}.masthead{display:flex;align-items:center;justify-content:space-between;padding:28px var(--gutter) 25px;gap:30px}.brand{font-size:34px;line-height:.85;letter-spacing:-1.4px;text-decoration:none;font-weight:600}.brand span{display:block;font-size:20px;line-height:1.25;font-weight:400;letter-spacing:0}.header-right{display:flex;align-items:center;gap:38px}.header-right>span{font-style:italic}.language{display:flex;gap:4px}.language button{border:0;background:transparent;color:var(--ink);padding:9px 12px;min-height:44px}.language button[aria-pressed=true]{background:var(--ink);color:var(--reverse)}nav{display:flex;gap:24px;padding:0 var(--gutter) 22px;flex-wrap:wrap}nav a{font-size:var(--small);text-decoration:none;min-height:32px;padding:4px 0}nav a[aria-current=page]{text-decoration:underline;text-decoration-thickness:2px}main{min-height:50vh}main:focus{outline:none}h1,h2,h3,p{margin:0}h1,h2,h3{font-weight:500;letter-spacing:-.025em;text-wrap:balance;line-height:1.03}h1{font-size:var(--display)}h2{font-size:var(--head)}h3{font-size:var(--lead);line-height:1.15}p+p{margin-top:20px}.hero{margin:0 var(--gutter);background:var(--paper);padding:clamp(28px,4vw,60px);display:grid;grid-template-columns:1.35fr .75fr;gap:7%;align-items:center}.hero-title{font-size:var(--display);line-height:.92;max-width:10ch}.full-title{font-size:var(--lead);max-width:36ch;margin-top:28px;line-height:1.25}.hero-copy{max-width:43ch;margin-top:24px}.button{display:inline-flex;justify-content:space-between;align-items:center;gap:35px;padding:15px 24px;background:var(--ink);color:var(--reverse);text-decoration:none;min-height:52px;border:0;font-size:var(--body);transition:transform .2s}.button:hover{opacity:1;transform:translateY(-3px)}.button:active{transform:translateY(1px)}.hero .button{margin-top:30px}.book{background:var(--ink);color:var(--reverse);aspect-ratio:3/4;max-width:350px;width:100%;justify-self:end;padding:32px;display:flex;flex-direction:column;position:relative;isolation:isolate}.book .cover-title{font-size:clamp(30px,3.7vw,53px);letter-spacing:-.04em;line-height:.96}.cover-subtitle{font-size:var(--small);margin-top:18px;max-width:22ch}.cover-concept{flex:1;display:flex;align-items:center;justify-content:center;padding:26px 0}.cover-concept svg{width:100%;max-height:160px;overflow:visible}.cover-bottom{display:flex;justify-content:space-between;gap:15px;font-size:var(--small)}.cover-caption{font-size:var(--small);margin-top:12px;text-align:right}.fact-strip{margin:0 var(--gutter);padding:28px clamp(28px,4vw,60px);display:grid;grid-template-columns:1fr 1fr 1fr;background:var(--surface);gap:24px}.fact-strip dt{font-size:var(--small);color:var(--muted)}.fact-strip dd{margin:6px 0 0}.section{margin:100px var(--gutter);display:grid;grid-template-columns:1fr 1fr;gap:10%;align-items:start}.section p{max-width:53ch}.section h2{max-width:15ch}.section .section-copy{padding-top:4px}.text-link{display:inline-block;margin-top:26px}.sheet{margin:65px var(--gutter) 90px;background:var(--paper);padding:clamp(26px,5vw,72px)}.sheet .page-title{font-size:var(--head);margin-bottom:28px}.intro{font-size:var(--lead);max-width:65ch;line-height:1.4}.reading{max-width:760px;margin-top:48px}.reading h2{font-size:var(--lead);margin:36px 0 16px}.reading ul,.reading ol{padding-left:24px}.reading li{padding:5px 0}.draft{background:var(--surface);padding:18px 22px;margin:28px 0;font-size:var(--body)}.edition{background:var(--ink);color:var(--reverse);margin:0 var(--gutter) 90px;padding:clamp(28px,5vw,72px);display:grid;grid-template-columns:1fr 1fr;gap:9%}.edition .button{background:var(--paper);color:var(--ink);margin-top:26px}.edition p{max-width:48ch}.edition h2{margin-bottom:25px}.edition .issue-mark{font-size:var(--lead);margin-bottom:18px}.editor{display:grid;grid-template-columns:.55fr 1fr;gap:36px;margin:45px 0;background:var(--surface);padding:35px}.editor .name{font-size:var(--lead)}.editor small{display:block;font-size:var(--small);margin-top:10px}.scope-list{display:grid;grid-template-columns:1fr 1fr;gap:28px;margin-top:35px}.scope-list article{background:var(--surface);padding:26px}.scope-list h2{font-size:var(--lead);margin-bottom:15px}details{background:var(--surface);padding:22px 26px;margin:12px 0}summary{cursor:pointer;font-size:var(--lead);min-height:36px}details p{margin-top:20px;max-width:66ch}.contact-grid{display:grid;grid-template-columns:1fr 1fr;gap:60px;margin-top:45px}.phone{font-size:var(--lead);display:inline-block;margin:12px 0 25px}.contact-grid h2{font-size:var(--lead);margin:0 0 14px}.small{font-size:var(--small);color:var(--muted)}.status-list{padding:0;list-style:none}.status-list li{padding:12px 0}.status-list strong{display:block}footer{background:var(--ink);color:var(--reverse);padding:48px var(--gutter) 25px;display:grid;grid-template-columns:1fr 1fr auto;gap:30px;align-items:start}.footer-name{font-size:34px;line-height:1}.footer-name span{display:block;font-size:var(--body);margin-top:8px}footer>p{font-size:var(--small);max-width:43ch}footer>a{font-size:var(--small)}.footer-small{grid-column:1/-1;margin-top:20px;opacity:.8}.noscript{padding:32px}.reveal{animation:enter .45s ease both}@keyframes enter{from{opacity:.65;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
@media(min-width:1600px){body{max-width:1800px;margin:auto}}@media(max-width:900px){.header-right>span{display:none}.hero{grid-template-columns:1fr .7fr;gap:30px}.book{padding:24px}.cover-title{font-size:34px!important}.section{gap:6%;margin-top:70px;margin-bottom:70px}nav{gap:10px 22px}.hero-copy{font-size:17px}.editor{grid-template-columns:1fr}footer{grid-template-columns:1fr 1fr}footer>a{grid-column:2}}
@media(max-width:600px){.notice>span+span{display:none}.notice{font-size:13px}.masthead{padding-top:25px;padding-bottom:25px}.hero{grid-template-columns:1fr;padding:28px 24px;gap:36px}.hero-title{font-size:clamp(44px,12vw,68px);max-width:none}.full-title{font-size:21px}.hero-copy{font-size:17px}.hero .button{width:100%}.book{max-width:250px;justify-self:center;padding:25px}.cover-title{font-size:36px!important}.cover-caption{text-align:center}.fact-strip{grid-template-columns:1fr;padding:24px;gap:18px}.fact-strip dt{display:inline}.fact-strip dd{display:inline;margin-left:10px}.section{grid-template-columns:1fr;gap:25px;margin:58px var(--gutter)}.section h2{max-width:none}.edition{grid-template-columns:1fr;gap:30px;margin-bottom:58px}.sheet{margin-top:24px;margin-bottom:58px;padding:28px 24px}.page-title{font-size:36px!important}.scope-list,.contact-grid{grid-template-columns:1fr;gap:20px}.scope-list{margin-top:26px}.editor{padding:24px;gap:18px}.contact-grid{margin-top:30px}.reading{margin-top:30px}footer{grid-template-columns:1fr;gap:25px}footer>a{grid-column:auto}.footer-small{margin-top:5px}nav{column-gap:20px;row-gap:6px}nav a{font-size:14px;min-height:38px;padding:7px 0}.intro{font-size:21px}h2{font-size:36px}summary{font-size:21px}.cover-concept{padding:20px 0}}
@media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}*,*:before,*:after{animation:none!important;transition:none!important}}

</style>
<style>
/* Journal masthead and long-form editorial reading pages. */
.supplied-cover{margin:0;width:100%;max-width:350px;justify-self:end}
.supplied-cover>a{display:block;transition:transform .2s ease}
.supplied-cover>a:hover{opacity:1;transform:translateY(-3px)}
.supplied-cover img{display:block;width:100%;height:auto;object-fit:contain}
@media(max-width:600px){.supplied-cover{max-width:280px;justify-self:center}}
.article-list{padding:clamp(28px,4vw,58px)}
.article-entry{max-width:950px;margin-top:30px}
.article-entry h2{font-size:clamp(27px,3vw,42px);line-height:1.15;text-wrap:pretty}
.article-entry h2 a{text-decoration:none}
.article-byline{font-size:var(--lead);margin:24px 0 10px}
.article-entry .editorial-note{margin:30px 0 20px}
.article-actions{display:flex;align-items:center;flex-wrap:wrap;gap:18px 28px}
.article-actions .text-link{margin-top:0}
.article-heading{background:var(--ink);color:var(--reverse);padding:clamp(28px,4vw,58px)}
.article-back{display:inline-block;margin-bottom:30px;font-size:var(--small)}
.article-heading h1{font-size:clamp(28px,3.3vw,46px);line-height:1.15;max-width:36ch;text-wrap:pretty}
.article-other-title{font-size:var(--lead);max-width:65ch;margin-top:26px}
.article-body{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:clamp(32px,5vw,72px);padding:clamp(28px,4vw,58px)}
.article-main{min-width:0}.article-main>.small{max-width:65ch}
.article-sidebar{background:var(--bg);padding:26px;align-self:start}
.article-sidebar h2{font-size:var(--lead);margin-bottom:22px}
.article-sidebar .article-actions{display:flex;flex-direction:column;align-items:stretch}
.article-sidebar .small{margin-top:18px}.article-sidebar dl{margin:28px 0 0}
.article-sidebar dt{font-weight:600;margin-top:24px}.article-sidebar dd{margin:7px 0 0;font-size:16px;overflow-wrap:anywhere}
@media(max-width:850px){.article-body{grid-template-columns:1fr}.article-sidebar{max-width:none}.article-sidebar .article-actions{flex-direction:row;align-items:center}.article-entry h2{font-size:29px}}
#nav .nav-login{margin-left:auto;font-weight:600;padding-left:18px;padding-right:18px}
#nav .nav-submit{background:var(--ink);color:var(--reverse);padding-left:18px;padding-right:18px;text-decoration:none}
#nav .nav-submit:focus-visible{outline-offset:4px}
.access-layout{display:grid;grid-template-columns:minmax(0,1fr) minmax(240px,.65fr);gap:clamp(32px,6vw,90px);padding:clamp(28px,4vw,58px)}
.access-layout .editorial-note{margin-bottom:30px}
.access-form{display:flex;flex-direction:column;gap:10px}
.access-form label{margin-top:12px;font-weight:600}
.access-form input,.access-form textarea,.access-form select{font:inherit;width:100%;max-width:100%;background:var(--bg);color:var(--muted);padding:13px 16px;border:1px solid var(--surface);border-radius:0;min-height:50px}
.access-form textarea{resize:vertical}
.access-form input:disabled,.access-form textarea:disabled,.access-form select:disabled{opacity:1;cursor:not-allowed}
.access-form .button:disabled{margin-top:20px;justify-content:center;background:var(--surface);color:var(--muted);cursor:not-allowed;transform:none;opacity:1}
.access-followup{margin-top:35px}.access-followup h2,.access-aside h2{font-size:var(--lead);margin-bottom:18px}
.access-aside{background:var(--bg);padding:28px;align-self:start}
.access-aside ul{padding-left:22px;margin:0 0 24px}.access-aside li{margin:15px 0}
.access-aside .text-link{display:block}
@media(max-width:1050px){#nav{gap:8px 20px}#nav .nav-login{margin-left:0}}
@media(max-width:700px){.access-layout{grid-template-columns:1fr;padding:26px 24px}.access-aside{padding:24px}#nav .nav-login{padding-left:0;padding-right:8px}#nav .nav-submit{padding-left:14px;padding-right:14px}}
.masthead{align-items:center;padding-top:32px;padding-bottom:30px;gap:40px;background:var(--paper)}
.brand{font-size:clamp(23px,2.35vw,36px);line-height:1.12;font-weight:500;letter-spacing:-.025em;max-width:900px}
.brand span{font-size:var(--small);margin-top:15px;letter-spacing:0;line-height:1.4}
.header-right{flex-shrink:0;flex-direction:column;align-items:flex-end;gap:12px}
.header-right>span{font-size:var(--small)}
#nav{background:var(--paper);padding-top:14px;padding-bottom:14px;margin-bottom:30px;gap:12px 28px}
#nav a{font-size:16px;min-height:40px;padding:8px 0}
.journal-page{margin:0 var(--gutter) 80px;background:var(--paper)}
.journal-page-heading{background:var(--ink);color:var(--reverse);padding:clamp(28px,4vw,58px)}
.journal-page-heading h1{font-size:var(--head);margin-bottom:24px}
.journal-page-heading .intro{max-width:58ch;font-size:var(--lead)}
.journal-layout{display:grid;grid-template-columns:240px minmax(0,1fr);gap:clamp(40px,6vw,90px);padding:clamp(28px,4vw,58px)}
.contents{position:sticky;top:28px;align-self:start}
.contents>p{font-size:var(--lead);margin-bottom:20px}
.contents nav{padding:0;margin:0;display:flex;flex-direction:column;gap:0}
.contents nav a{padding:9px 0;font-size:16px;min-height:42px;text-decoration:none;line-height:1.4}
.contents nav a:hover{text-decoration:underline;opacity:1}
.publisher-note{margin-top:32px;padding:20px;background:var(--bg)}
.publisher-note p{font-weight:600;font-size:16px}
.publisher-note span{display:block;margin-top:10px;font-size:14px;line-height:1.5}
.journal-reading{min-width:0;max-width:800px}
.editorial-note{background:var(--bg);padding:24px 28px;margin-bottom:48px;font-size:16px}
.editorial-note strong{font-weight:600;display:block;margin-bottom:10px}
.reading-section{margin-bottom:46px;scroll-margin-top:28px}
.reading-section:focus{outline:none}
.reading-section h2{font-size:clamp(25px,2.2vw,32px);line-height:1.15;margin-bottom:22px;text-wrap:pretty}
.reading-section p{max-width:70ch}
.reading-section ul{padding-left:23px;margin:20px 0}
.reading-section li{padding-left:5px;margin:12px 0;max-width:68ch}
.reading-end{padding:22px 0 8px}
.pkp-credit{grid-column:1/-1;display:flex;align-items:flex-start;gap:60px;padding-top:24px}
.pkp-credit>a{font-size:22px;line-height:1.3;white-space:nowrap;text-decoration:none}
.pkp-credit>a:hover{text-decoration:underline}
.pkp-credit p{font-size:14px;max-width:72ch;margin:0;line-height:1.5}
@media(max-width:1050px){.header-right>span{display:none}.header-right{display:block}.journal-layout{grid-template-columns:190px minmax(0,1fr);gap:38px}.brand{font-size:28px}.desktop-break{display:none}}
@media(max-width:700px){.masthead{align-items:flex-start;gap:16px}.brand{font-size:25px;max-width:none}.brand span{font-size:14px}.language{flex-direction:column}.language button{padding:6px 10px;min-height:40px}.header-right{padding-top:0}#nav{gap:0 22px;padding-bottom:14px;margin-bottom:20px}#nav a{font-size:15px}.journal-layout{grid-template-columns:1fr;gap:28px;padding:26px 24px}.journal-page{margin-bottom:48px}.journal-page-heading{padding:30px 24px}.journal-page-heading h1{font-size:36px}.journal-page-heading .intro{font-size:21px}.contents{position:static}.contents>p{font-size:22px;margin-bottom:12px}.contents nav{display:block;columns:2;column-gap:22px}.contents nav a{display:block;font-size:15px;padding:8px 0;break-inside:avoid}.publisher-note{display:none}.editorial-note{padding:20px;margin-bottom:34px;font-size:16px}.reading-section{margin-bottom:36px}.reading-section h2{font-size:27px;margin-bottom:18px}.pkp-credit{display:block;padding-top:5px}.pkp-credit p{margin-top:16px}.brand{overflow-wrap:break-word}.reading-end .button{width:100%}}
@media(max-width:360px){.brand{font-size:22px}.contents nav{columns:1}.journal-page-heading h1{font-size:32px}}

</style>
<style>
:root{color-scheme:light}
:root[data-theme="dark"]{--bg:#171518;--paper:#221f22;--surface:#342c30;--ink:#f0d8d9;--muted:#c8aeb2;--accent:#f0d8d9;--reverse:#20191b;color-scheme:dark}
:root[data-theme="dark"] :is(.notice,.edition,.journal-page-heading,.article-heading,footer){background:#461b24;color:#f7e9e9}
.header-controls{display:flex;align-items:center;gap:18px}
.theme-toggle{display:inline-flex;align-items:center;justify-content:center;gap:9px;min-height:44px;padding:9px 12px;background:var(--surface);color:var(--ink);border:0;font:inherit;font-size:16px;cursor:pointer;white-space:nowrap}
.theme-toggle svg{width:18px;height:18px;flex-shrink:0;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.theme-toggle:hover{background:var(--ink);color:var(--reverse)}
.theme-toggle:active{transform:translateY(1px)}
.theme-toggle .moon{display:none}
:root[data-theme="dark"] .theme-toggle .sun{display:none}
:root[data-theme="dark"] .theme-toggle .moon{display:block}
.header-controls .language{flex-direction:row}
@media(max-width:700px){.header-controls{flex-direction:column;gap:10px;align-items:flex-end}.theme-toggle{font-size:14px;gap:7px;padding:8px 10px}.header-controls .language button{padding:6px 9px;min-height:40px}.masthead{gap:18px}}

</style>
<style>
/* Compact journal navigation and task-focused reading tools. */
.mobile-toolbar{display:none}
:root:not(.js) .mobile-toolbar{display:none}
.masthead{padding-top:26px;padding-bottom:24px}
.brand{font-size:clamp(22px,2.1vw,32px)}
.brand span{margin-top:10px}
.header-right>span{max-width:26ch;text-align:right}
#nav{gap:6px 24px;align-items:center}
#nav a{white-space:nowrap}
.catalog-body{padding:clamp(26px,4vw,58px)}
.search-form{display:grid;grid-template-columns:minmax(180px,1.8fr) 1fr 1fr auto;gap:20px;align-items:end}
.search-form label{display:block;margin-bottom:9px;font-size:16px}
.search-form input,.search-form select{font:inherit;background:var(--bg);color:var(--ink);border:1px solid var(--muted);padding:12px;min-height:52px;width:100%;border-radius:0}
.search-form .button{min-height:52px}
.search-form :focus-visible{outline:3px solid var(--ink);outline-offset:3px}
.search-count{margin-top:36px;font-weight:600}
.catalog-entry{margin-top:28px;padding:32px;background:var(--bg)}
.catalog-entry h2{font-size:clamp(26px,2.4vw,36px);line-height:1.2;max-width:48ch;text-wrap:pretty}
.catalog-entry h2 a{text-decoration:none}
.catalog-entry .article-byline{font-size:var(--body);font-weight:600;margin:22px 0 8px}
.catalog-entry .catalog-status{font-size:14px;margin-bottom:16px;color:var(--muted)}
.catalog-keywords{font-size:16px;margin:20px 0}
.catalog-entry .article-actions{margin-top:24px}
.archive-status{padding-bottom:40px;max-width:70ch}
.archive-status h2,.catalog-section-title h2{font-size:var(--lead);margin-bottom:18px}
.catalog-section-title{display:flex;align-items:center;justify-content:space-between;gap:25px}
.catalog-section-title .text-link{margin:0 0 18px}
.search-empty{padding:45px 0}
.templates-section{margin:0 0 48px;scroll-margin-top:24px}
.templates-section h2{font-size:var(--lead);margin-bottom:20px}
.templates-section h3{font-size:21px;margin:24px 0 14px}
.template-downloads{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin:26px 0}
.template-downloads>div{background:var(--bg);padding:22px}
.template-downloads h3{margin:0 0 16px}
.template-downloads a{display:block;font-size:16px;margin-top:15px}
.service-unavailable{padding:22px;background:var(--surface);font-weight:600}
.citation-section{margin:40px 0}
.citation-section h2{font-size:var(--lead);margin-bottom:20px}
.citation-section p{overflow-wrap:anywhere}
.citation-actions{display:flex;align-items:center;gap:16px 22px;flex-wrap:wrap;margin:26px 0 12px}
.citation-actions button{background:var(--surface);color:var(--ink);border:0;padding:12px 15px;min-height:44px;cursor:pointer}
.citation-actions a{font-size:16px}
.permanent-link{margin-top:20px}.permanent-link a{overflow-wrap:anywhere}
.footer-license{grid-column:1/-1}
.article-heading h1{max-width:42ch}
@media(max-width:1100px){#nav{gap:6px 18px}#nav a{font-size:15px}.search-form{grid-template-columns:1fr 1fr}.search-query{grid-column:1/-1}.search-form .button{justify-content:center}}
@media(max-width:800px){
 .notice{font-size:13px;padding-top:8px;padding-bottom:8px}
 .masthead{display:grid;grid-template-columns:1fr;gap:18px;padding:22px var(--gutter) 18px}
 .brand{font-size:22px;line-height:1.14;max-width:34ch}
 .brand span{font-size:14px;margin-top:8px}
 .desktop-break{display:none}
 .header-right{display:block;width:100%}
 .header-right>span{display:none}
 .header-controls{display:flex;flex-direction:row;justify-content:space-between;width:100%;align-items:center;gap:16px}
 .header-controls .language{display:flex;flex-direction:row;gap:4px}
 .header-controls .language button{padding:7px 12px;min-height:40px}
 .theme-toggle{min-height:40px;padding:8px 12px}
 .mobile-toolbar{display:flex;align-items:stretch;justify-content:space-between;padding:0 var(--gutter) 18px;background:var(--paper);margin-bottom:20px;gap:18px}
 .mobile-toolbar button,.mobile-toolbar a{font:inherit;font-size:16px;padding:11px 18px;min-height:46px;background:var(--surface);color:var(--ink);border:0;text-decoration:none}
 .mobile-toolbar a{background:var(--ink);color:var(--reverse)}
 #nav{display:grid;grid-template-columns:1fr 1fr;padding:0 var(--gutter) 22px;margin-bottom:20px}
 .js #nav{display:none}
 body[data-nav-open="true"] #nav{display:grid;grid-template-columns:1fr 1fr;gap:0 20px}
 body[data-nav-open="true"] .mobile-toolbar{margin-bottom:0}
 #nav a{min-height:46px;padding:12px 0;white-space:normal}
 #nav .nav-login{margin-left:0;padding-left:0}
 #nav .nav-submit{display:none}
 .hero{margin-top:0}
 .search-form{grid-template-columns:1fr;gap:18px}.search-query{grid-column:auto}
 .catalog-body{padding:26px 24px}.catalog-entry{padding:24px;margin-top:24px}
 .catalog-entry h2{font-size:26px}
 .catalog-section-title{align-items:flex-start;flex-direction:column;gap:0}
 .template-downloads{grid-template-columns:1fr}
 .article-heading h1{font-size:28px}
}

</style>
<script type="application/ld+json">{"@context":"https://schema.org","@type":"Periodical","name":"Fronstellar Journal of Integrative Multidisciplinary Social Sciences","publisher":{"@type":"Organization","name":"Fronstellar Institute"},"url":"https://socialjournal-production.up.railway.app/index.php/fs/en/index"}</script><script>
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

</script></head>
{/literal}
{if $currentLocale == "id"}{literal}<body>
  <a href="#content" class="skip" id="skip">Lewati ke konten</a>
  <div class="notice"><span id="notice">Akses terbuka · Tanpa biaya publikasi · CC BY 4.0</span><span>Fronstellar Institute</span></div>
  <header class="masthead"><a href="/index.php/fs/id/index" class="brand" aria-label="Fronstellar Journal of Integrative Multidisciplinary Social Sciences">Fronstellar Journal of Integrative<br class="desktop-break"> Multidisciplinary Social Sciences<span>Fronstellar Institute</span></a><div class="header-right"><span id="header-caption">Ilmu sosial, lintas perspektif.</span><div class="header-controls"><div class="language" role="group" aria-label="Language"><button type="button" data-lang="id" aria-pressed="true">ID</button><button type="button" data-lang="en" aria-pressed="false" onclick="location.href='/index.php/fs/en/index'">EN</button></div><button id="theme-toggle" class="theme-toggle" type="button" aria-label="Switch to dark theme" aria-pressed="false"><svg class="sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/></svg><svg class="moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 14A8.5 8.5 0 0 1 10 3.5 8.5 8.5 0 1 0 20.5 14Z"/></svg><span data-theme-label>Light</span></button></div></div></header>
  <div class="mobile-toolbar"><button id="nav-toggle" type="button" aria-expanded="false" aria-controls="nav">Menu</button><a id="mobile-submit" href="/index.php/fs/id/about/submissions">Kirim Naskah</a></div>
  <nav id="nav" aria-label="Main navigation"><a href="/index.php/fs/id/index" aria-current="page" >Beranda</a><a href="/index.php/fs/id/about" >Tentang jurnal</a><a href="/index.php/fs/id/about/editorialMasthead" >Tim editorial</a><a href="/index.php/fs/id/issue/archive" >Terbitan</a><a href="/index.php/fs/id/about/submissions" >Panduan penulis</a><a href="/index.php/fs/id/editorial-policies" >Kebijakan</a><a href="/index.php/fs/id/about/contact" >Kontak</a><a href="/index.php/fs/id/search" >Cari</a><a href="/index.php/fs/id/login" class="nav-login">Masuk</a><a href="/index.php/fs/id/about/submissions" class="nav-submit">Kirim Naskah</a></nav>
  <main id="content" tabindex="-1"><section class="hero reveal"><div><h1 class="hero-title">Fronstellar<br>Journal</h1><p class="full-title">Fronstellar Journal of Integrative Multidisciplinary Social Sciences</p><p class="hero-copy">Mempertemukan perspektif untuk memahami masyarakat dan persoalan sosial secara lebih utuh.</p><a class="button" href="/index.php/fs/id/issue/archive">Telusuri artikel <span aria-hidden="true">↗</span></a></div><figure class="supplied-cover"><a href="/public/journals/1/homepageImage_en.png" target="_blank" rel="noopener" aria-label="View journal cover at full size"><img src="/public/journals/1/homepageImage_en.png" width="1052" height="1495" fetchpriority="high" alt="Fronstellar journal cover. Published every four months, three issues per year. ISSN values are placeholders."></a><figcaption class="cover-caption">Journal cover · ISSN placeholders</figcaption></figure></section><dl class="fact-strip"><div><dt>Penerbit</dt><dd>Fronstellar Institute</dd></div><div><dt>Bahasa artikel</dt><dd>Indonesia & Inggris</dd></div><div><dt>Frekuensi terbit</dt><dd>Setiap empat bulan</dd></div></dl><section class="section"><h2>Melampaui batas disiplin.</h2><div class="section-copy"><p>Fronstellar Journal berfokus pada ilmu sosial integratif dan mempertemukan pendekatan, konsep, serta metode untuk mengkaji kehidupan sosial.</p><a class="text-link" href="/index.php/fs/id/about">Baca fokus dan ruang lingkup ↗</a></div></section><section class="edition"><div><h2>Terbitan perdana</h2><p class="issue-mark">Dalam persiapan</p></div><div><p>Terbitan perdana akan muncul di arsip setelah proses editorial selesai.</p><a class="button" href="/index.php/fs/id/issue/archive">Lihat arsip terbitan <span aria-hidden="true">↗</span></a></div></section><section class="section"><h2>Di balik jurnal.</h2><div><p>Yoga Hediasa, M.Si bertugas sebagai Editor in Chief. Fahmi Achmad Darojat bertugas sebagai Technical Support dan Layout Editor.</p><a class="text-link" href="/index.php/fs/id/about/editorialMasthead">Lihat tim editorial ↗</a></div></section></main>
  <footer><div class="footer-name">fronstellar<span>Institute</span></div><p id="footer-note">Jurnal ilmu sosial integratif dalam bahasa Indonesia dan Inggris. Diterbitkan oleh Fronstellar Institute.</p><a href="/index.php/fs/id/about/contact" id="footer-link">Hubungi redaksi ↗</a><p class="footer-small" id="footer-status">Akses terbuka · Tanpa biaya publikasi · CC BY 4.0</p><a class="footer-license" href="https://creativecommons.org/licenses/by/4.0/" rel="license" target="_blank">CC BY 4.0 ↗</a><div class="pkp-credit"><a href="https://pkp.sfu.ca/software/ojs/" target="_blank" rel="noopener noreferrer">Workflow By OJS PKP <span aria-hidden="true">↗</span></a><p id="workflow-credit-note">Editorial workflow follows Open Journal Systems, developed by the Public Knowledge Project. </p></div></footer>
  <noscript><div class="noscript">Enable JavaScript for theme controls.</div></noscript><script>
const menu=document.getElementById("nav-toggle");menu.addEventListener("click",()=>{const open=menu.getAttribute("aria-expanded")!=="true";menu.setAttribute("aria-expanded",String(open));document.body.dataset.navOpen=String(open);});
</script></body>
</html>
{/literal}{else}{literal}<body>
  <a href="#content" class="skip" id="skip">Skip to content</a>
  <div class="notice"><span id="notice">Open access · No author charges · CC BY 4.0</span><span>Fronstellar Institute</span></div>
  <header class="masthead"><a href="/index.php/fs/en/index" class="brand" aria-label="Fronstellar Journal of Integrative Multidisciplinary Social Sciences">Fronstellar Journal of Integrative<br class="desktop-break"> Multidisciplinary Social Sciences<span>Fronstellar Institute</span></a><div class="header-right"><span id="header-caption">Social sciences, across perspectives.</span><div class="header-controls"><div class="language" role="group" aria-label="Language"><button type="button" data-lang="id" aria-pressed="false" onclick="location.href='/index.php/fs/id/index'">ID</button><button type="button" data-lang="en" aria-pressed="true">EN</button></div><button id="theme-toggle" class="theme-toggle" type="button" aria-label="Switch to dark theme" aria-pressed="false"><svg class="sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/></svg><svg class="moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 14A8.5 8.5 0 0 1 10 3.5 8.5 8.5 0 1 0 20.5 14Z"/></svg><span data-theme-label>Light</span></button></div></div></header>
  <div class="mobile-toolbar"><button id="nav-toggle" type="button" aria-expanded="false" aria-controls="nav">Menu</button><a id="mobile-submit" href="/index.php/fs/en/about/submissions">Submit Article</a></div>
  <nav id="nav" aria-label="Main navigation"><a href="/index.php/fs/en/index" aria-current="page" >Home</a><a href="/index.php/fs/en/about" >About the journal</a><a href="/index.php/fs/en/about/editorialMasthead" >Editorial team</a><a href="/index.php/fs/en/issue/archive" >Issues</a><a href="/index.php/fs/en/about/submissions" >Author guidelines</a><a href="/index.php/fs/en/editorial-policies" >Policies</a><a href="/index.php/fs/en/about/contact" >Contact</a><a href="/index.php/fs/en/search" >Search</a><a href="/index.php/fs/en/login" class="nav-login">Login</a><a href="/index.php/fs/en/about/submissions" class="nav-submit">Submit Article</a></nav>
  <main id="content" tabindex="-1"><section class="hero reveal"><div><h1 class="hero-title">Fronstellar<br>Journal</h1><p class="full-title">Fronstellar Journal of Integrative Multidisciplinary Social Sciences</p><p class="hero-copy">Bringing perspectives together to understand society and social questions more fully.</p><a class="button" href="/index.php/fs/en/issue/archive">Browse articles <span aria-hidden="true">↗</span></a></div><figure class="supplied-cover"><a href="/public/journals/1/homepageImage_en.png" target="_blank" rel="noopener" aria-label="View journal cover at full size"><img src="/public/journals/1/homepageImage_en.png" width="1052" height="1495" fetchpriority="high" alt="Fronstellar journal cover. Published every four months, three issues per year. ISSN values are placeholders."></a><figcaption class="cover-caption">Journal cover · ISSN placeholders</figcaption></figure></section><dl class="fact-strip"><div><dt>Publisher</dt><dd>Fronstellar Institute</dd></div><div><dt>Article languages</dt><dd>Indonesian & English</dd></div><div><dt>Publication frequency</dt><dd>Every four months</dd></div></dl><section class="section"><h2>Across disciplines.</h2><div class="section-copy"><p>Fronstellar Journal focuses on integrative social sciences. It is being prepared as a space for research connecting approaches, concepts, and methods to examine social life.</p><a class="text-link" href="/index.php/fs/en/about">Read the aims and scope ↗</a></div></section><section class="edition"><div><h2>The first issue</h2><p class="issue-mark">In preparation</p></div><div><p>The first issue will appear in the archive after the editorial process is complete.</p><a class="button" href="/index.php/fs/en/issue/archive">View issue archive <span aria-hidden="true">↗</span></a></div></section><section class="section"><h2>Behind the journal.</h2><div><p>Yoga Hediasa, M.Si serves as Editor in Chief. Fahmi Achmad Darojat supports the journal as Technical Support and Layout Editor.</p><a class="text-link" href="/index.php/fs/en/about/editorialMasthead">Meet the editorial team ↗</a></div></section></main>
  <footer><div class="footer-name">fronstellar<span>Institute</span></div><p id="footer-note">An integrative social sciences journal in Indonesian and English. Published by Fronstellar Institute.</p><a href="/index.php/fs/en/about/contact" id="footer-link">Contact the editors ↗</a><p class="footer-small" id="footer-status">Open access · No author charges · CC BY 4.0</p><a class="footer-license" href="https://creativecommons.org/licenses/by/4.0/" rel="license" target="_blank">CC BY 4.0 ↗</a><div class="pkp-credit"><a href="https://pkp.sfu.ca/software/ojs/" target="_blank" rel="noopener noreferrer">Workflow By OJS PKP <span aria-hidden="true">↗</span></a><p id="workflow-credit-note">Editorial workflow follows Open Journal Systems, developed by the Public Knowledge Project. </p></div></footer>
  <noscript><div class="noscript">Enable JavaScript for theme controls.</div></noscript><script>
const menu=document.getElementById("nav-toggle");menu.addEventListener("click",()=>{const open=menu.getAttribute("aria-expanded")!=="true";menu.setAttribute("aria-expanded",String(open));document.body.dataset.navOpen=String(open);});
</script></body>
</html>
{/literal}{/if}
FRONSTELLAR_HOME;
    if ($publicJournal) {
        $fronstellarHome = str_replace('<meta name="robots" content="noindex,nofollow">', '', $fronstellarHome);
    }
    $journalHome = '{* Fronstellar branded homepage *}' . "\n"
        . '{if $currentJournal->getPath() == "fs"}' . "\n" . $fronstellarHome . "\n"
        . '{else}' . "\n" . $journalHome . "\n" . '{/if}' . "\n";
    if (file_put_contents($journalHomePath, $journalHome, LOCK_EX) !== strlen($journalHome)) {
        throw new RuntimeException('Cannot save the Fronstellar homepage template.');
    }
}


if ($publicJournal) {
    $verificationFile = getenv('OJS_GOOGLE_VERIFICATION_FILE');
    if ($verificationFile !== false && $verificationFile !== '') {
        if (!preg_match('/\Agoogle[a-f0-9]{16}\.html\z/', $verificationFile)) {
            throw new RuntimeException('Invalid Google verification filename.');
        }
        $verificationPath = '/var/www/html/' . $verificationFile;
        $verificationContents = 'google-site-verification: ' . $verificationFile;
        if (file_put_contents($verificationPath, $verificationContents, LOCK_EX) !== strlen($verificationContents)
            || !chmod($verificationPath, 0644)) {
            throw new RuntimeException('Cannot write Google Search Console verification file.');
        }
    }
    $sitemap = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
        . "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach (['en', 'id'] as $locale) {
        foreach (['index', 'about', 'about/submissions', 'editorial-policies', 'issue/archive'] as $page) {
            $location = htmlspecialchars($url . '/index.php/fs/' . $locale . '/' . $page, ENT_XML1, 'UTF-8');
            $sitemap .= "  <url><loc>" . $location . "</loc></url>\n";
        }
    }
    $sitemap .= "</urlset>\n";
    foreach ([
        '/var/www/html/sitemap.xml' => $sitemap,
        '/var/www/html/robots.txt' => "User-agent: *\nAllow: /\nSitemap: " . $url . "/sitemap.xml\n",
    ] as $path => $contents) {
        if (file_put_contents($path, $contents, LOCK_EX) !== strlen($contents) || !chmod($path, 0644)) {
            throw new RuntimeException('Cannot write journal search discovery files.');
        }
    }
}

echo "Fronstellar runtime configured; installer remains protected until initialization.\n";

