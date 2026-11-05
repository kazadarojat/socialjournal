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
$driver = requiredEnv('OJS_DB_DRIVER');
if (!in_array($driver, ['mysqli', 'pgsql'], true)) { throw new RuntimeException('Unsupported database driver.'); }
if (getenv('RAILWAY_VOLUME_MOUNT_PATH') !== '/var/ojs') { throw new RuntimeException('Mount the Railway persistent volume at /var/ojs before starting OJS.'); }
foreach (['/var/ojs', '/var/ojs/files', '/var/ojs/public', '/var/ojs/config'] as $dir) {
    if (!is_dir($dir) && !mkdir($dir, 0750, true)) { throw new RuntimeException('Cannot create persistent directory.'); }
    chown($dir, 'www-data'); chgrp($dir, 'www-data');
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
    'security' => ['force_ssl' => 'On', 'force_login_ssl' => 'On', 'salt' => iniValue($keys['salt']), 'api_key_secret' => iniValue($keys['api_key_secret'])]
];
if (getenv('OJS_SMTP_HOST')) {
    $settings['email'] = ['default'=>'"smtp"', 'smtp_server'=>iniValue(requiredEnv('OJS_SMTP_HOST')), 'smtp_port'=>iniValue(requiredEnv('OJS_SMTP_PORT')), 'smtp_auth'=>iniValue(getenv('OJS_SMTP_AUTH') ?: 'tls'), 'smtp_username'=>iniValue(requiredEnv('OJS_SMTP_USER')), 'smtp_password'=>iniValue(requiredEnv('OJS_SMTP_PASSWORD'))];
}
foreach ($settings as $section => $pairs) { foreach ($pairs as $key => $value) { $config = setIni($config, $section, $key, $value); } }
file_put_contents($configPath, $config, LOCK_EX); chmod($configPath, 0640); chown($configPath, 'www-data'); chgrp($configPath, 'www-data');
$webConfig = '/var/www/html/config.inc.php';
if (!is_link($webConfig)) { if (is_file($webConfig)) { unlink($webConfig); } symlink($configPath, $webConfig); }
$public = '/var/www/html/public';
if (!is_link($public)) {
    // Preserve files bundled with the distribution before moving public uploads onto the volume.
    $copy = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($public, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($copy as $file) {
        $destination = '/var/ojs/public/' . $copy->getSubPathName();
        if ($file->isDir()) { if (!is_dir($destination)) { mkdir($destination, 0750, true); } }
        elseif (!file_exists($destination)) { copy($file->getPathname(), $destination); }
    }
    rename($public, '/var/www/html/public.bundled'); symlink('/var/ojs/public', $public);
}
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('/var/ojs', FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
foreach ($iterator as $file) { chown($file->getPathname(), 'www-data'); chgrp($file->getPathname(), 'www-data'); }
$apache = "ServerName ".$parts['host']."\nServerTokens Prod\nServerSignature Off\nSetEnvIf X-Forwarded-Proto \"^https$\" HTTPS=on\n<Directory /var/www/html>\n Options -Indexes +FollowSymLinks\n AllowOverride None\n Require all granted\n</Directory>\n<FilesMatch \"^(config\\.inc\\.php|.*\\.(ini|log|sql))$\">\n Require all denied\n</FilesMatch>\n";
if (!$installed || getenv('OJS_PRIVATE_PREVIEW') !== 'false') {
    $setupPassword = requiredEnv('OJS_SETUP_PASSWORD');
    if (strlen($setupPassword) < 24) { throw new RuntimeException('Setup protection requires a password of at least 24 characters.'); }
    file_put_contents('/var/ojs/config/setup.htpasswd', 'setup:'.password_hash($setupPassword, PASSWORD_BCRYPT)."\n", LOCK_EX);
    chmod('/var/ojs/config/setup.htpasswd', 0640); chown('/var/ojs/config/setup.htpasswd', 'www-data'); chgrp('/var/ojs/config/setup.htpasswd', 'www-data');
    $apache .= "<Location />\n AuthType Basic\n AuthName \"Fronstellar OJS setup\"\n AuthUserFile /var/ojs/config/setup.htpasswd\n Require valid-user\n</Location>\n";
}
$apache .= "Alias /healthz /opt/fronstellar/healthz\n<Location /healthz>\n AuthType None\n Require all granted\n</Location>\n<Directory /opt/fronstellar>\n Require all granted\n</Directory>\n";
file_put_contents('/opt/fronstellar/healthz', "OJS runtime ready\n");
file_put_contents('/etc/apache2/conf-enabled/fronstellar.conf', $apache);
echo "Fronstellar runtime configured; installer remains protected until initialization.\n";
