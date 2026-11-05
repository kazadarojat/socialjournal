import {readFileSync,writeFileSync,mkdirSync} from 'node:fs';
import {resolve} from 'node:path';
import {execFileSync} from 'node:child_process';
const root=resolve(import.meta.dirname,'..');
const php=process.env.OJS_PHP_BIN;
if(!php)throw new Error('Set OJS_PHP_BIN to a PHP 8.3 executable.');
const source=readFileSync(resolve(root,'ojs-runtime/configure.php'),'utf8');
execFileSync(php,['-n','-l',resolve(root,'ojs-runtime/configure.php')],{stdio:'inherit'});
const functions=source.slice(0,source.indexOf('$url ='));
const parser=resolve(root,'../ojs-3.5.0-6/ojs-3.5.0-6/lib/pkp/classes/config/ConfigParser.php').replaceAll('\\','/');
const test=`
require ${JSON.stringify(parser)};
$template = "[general]\\ninstalled = Off\\nbase_url = \\\"old\\\"\\n[database]\\nhost = localhost\\n; port = 3306\\npassword = old\\n[email]\\ndefault = sendmail\\n";
$template = stripcslashes($template);
foreach (['lettersOnly123', 'spaces and \\"quotes\\"', 'back\\\\slash', 'dollar$literal'] as $password) {
 $configured = setIni($template, 'database', 'password', iniValue($password));
 $configured = setIni($configured, 'database', 'port', '5432');
 file_put_contents(__DIR__.'/ojs-test.ini', $configured);
 $parsed = \\PKP\\config\\ConfigParser::readConfig(__DIR__.'/ojs-test.ini');
 if ($parsed['database']['password'] !== $password || $parsed['database']['port'] !== 5432 || $parsed['general']['installed'] !== false) {
  throw new RuntimeException('Configuration round-trip failed: '.json_encode([$password,$parsed]));
 }
 $again = setIni($configured, 'database', 'port', '5432');
 if ($again !== $configured) { throw new RuntimeException('Repeated startup configuration is not idempotent.'); }
}
echo "Passed INI escaping, section isolation, missing-key insertion and repeated startup configuration.\\n";
`;
mkdirSync(resolve(root,'outputs'),{recursive:true});
const file=resolve(root,'outputs/ojs-config-functions-check.php');
writeFileSync(file,functions+test);
execFileSync(php,['-n',file],{stdio:'inherit'});
