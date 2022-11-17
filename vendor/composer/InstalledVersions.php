<?php

namespace Composer;

use Composer\Semver\VersionParser;






class InstalledVersions
{
private static $installed = array (
  'root' => 
  array (
    'pretty_version' => 'dev-main',
    'version' => 'dev-main',
    'aliases' => 
    array (
    ),
    'reference' => 'b33046af3abe5c12316dd37e05a3fe0e161b7b20',
    'name' => '__root__',
  ),
  'versions' => 
  array (
    '__root__' => 
    array (
      'pretty_version' => 'dev-main',
      'version' => 'dev-main',
      'aliases' => 
      array (
      ),
      'reference' => 'b33046af3abe5c12316dd37e05a3fe0e161b7b20',
    ),
    'guzzlehttp/guzzle' => 
    array (
      'pretty_version' => '6.5.8',
      'version' => '6.5.8.0',
      'aliases' => 
      array (
      ),
      'reference' => 'a52f0440530b54fa079ce76e8c5d196a42cad981',
    ),
    'guzzlehttp/promises' => 
    array (
      'pretty_version' => '1.5.2',
      'version' => '1.5.2.0',
      'aliases' => 
      array (
      ),
      'reference' => 'b94b2807d85443f9719887892882d0329d1e2598',
    ),
    'guzzlehttp/psr7' => 
    array (
      'pretty_version' => '1.9.0',
      'version' => '1.9.0.0',
      'aliases' => 
      array (
      ),
      'reference' => 'e98e3e6d4f86621a9b75f623996e6bbdeb4b9318',
    ),
    'maknz/slack' => 
    array (
      'pretty_version' => '1.7.0',
      'version' => '1.7.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '7f21fefc70c76b304adc1b3a780c8740dfcfb595',
    ),
    'mexitek/phpcolors' => 
    array (
      'pretty_version' => 'v1.0.4',
      'version' => '1.0.4.0',
      'aliases' => 
      array (
      ),
      'reference' => '4043974240ca7dc3c2bec3c158588148b605b206',
    ),
    'nubs/random-name-generator' => 
    array (
      'pretty_version' => 'v2.2.0',
      'version' => '2.2.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '50abf24519badc5795a43c831f1d2a1faa45cb77',
    ),
    'psr/http-message' => 
    array (
      'pretty_version' => '1.0.1',
      'version' => '1.0.1.0',
      'aliases' => 
      array (
      ),
      'reference' => 'f6561bf28d520154e4b0ec72be95418abe6d9363',
    ),
    'psr/http-message-implementation' => 
    array (
      'provided' => 
      array (
        0 => '1.0',
      ),
    ),
    'ralouphie/getallheaders' => 
    array (
      'pretty_version' => '3.0.3',
      'version' => '3.0.3.0',
      'aliases' => 
      array (
      ),
      'reference' => '120b605dfeb996808c31b6477290a714d356e822',
    ),
    'roave/security-advisories' => 
    array (
      'pretty_version' => 'dev-latest',
      'version' => 'dev-latest',
      'aliases' => 
      array (
        0 => '9999999-dev',
      ),
      'reference' => 'ed33734832e5a87eaa4341be7ce580de54157460',
    ),
    'symfony/polyfill-intl-idn' => 
    array (
      'pretty_version' => 'v1.27.0',
      'version' => '1.27.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '639084e360537a19f9ee352433b84ce831f3d2da',
    ),
    'symfony/polyfill-intl-normalizer' => 
    array (
      'pretty_version' => 'v1.27.0',
      'version' => '1.27.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '19bd1e4fcd5b91116f14d8533c57831ed00571b6',
    ),
    'symfony/polyfill-php72' => 
    array (
      'pretty_version' => 'v1.27.0',
      'version' => '1.27.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '869329b1e9894268a8a61dabb69153029b7a8c97',
    ),
  ),
);







public static function getInstalledPackages()
{
return array_keys(self::$installed['versions']);
}









public static function isInstalled($packageName)
{
return isset(self::$installed['versions'][$packageName]);
}














public static function satisfies(VersionParser $parser, $packageName, $constraint)
{
$constraint = $parser->parseConstraints($constraint);
$provided = $parser->parseConstraints(self::getVersionRanges($packageName));

return $provided->matches($constraint);
}










public static function getVersionRanges($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

$ranges = array();
if (isset(self::$installed['versions'][$packageName]['pretty_version'])) {
$ranges[] = self::$installed['versions'][$packageName]['pretty_version'];
}
if (array_key_exists('aliases', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['aliases']);
}
if (array_key_exists('replaced', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['replaced']);
}
if (array_key_exists('provided', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['provided']);
}

return implode(' || ', $ranges);
}





public static function getVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['version'])) {
return null;
}

return self::$installed['versions'][$packageName]['version'];
}





public static function getPrettyVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['pretty_version'])) {
return null;
}

return self::$installed['versions'][$packageName]['pretty_version'];
}





public static function getReference($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['reference'])) {
return null;
}

return self::$installed['versions'][$packageName]['reference'];
}





public static function getRootPackage()
{
return self::$installed['root'];
}







public static function getRawData()
{
return self::$installed;
}



















public static function reload($data)
{
self::$installed = $data;
}
}
