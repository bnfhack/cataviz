<?php

declare(strict_types=1);

/**
 * Part of Teinte https://github.com/oeuvres/teinte
 * MIT License https://opensource.org/licenses/mit-license.php
 * Copyright (c) 2020 frederic.Glorieux@fictif.org
 * Copyright (c) 2013 Frederic.Glorieux@fictif.org & LABEX OBVIL
 * Copyright (c) 2012 Frederic.Glorieux@fictif.org
 * Copyright (c) 2010 Frederic.Glorieux@fictif.org 
 *                    & École nationale des chartes
 */

namespace Oeuvres\Kit;

use Exception;

/**
 * Tools to deal with PHP Http oddities
 * code convention https://www.php-fig.org/psr/psr-12/
 */
class Http
{
    /** web parameters */
    static $pars;
    /** pathinfo, relative to base application */
    static $pathinfo;
    /** relative path to base application, calculated with pathinfo */
    static $basehref;
    /** Content-Type header */
    static $mime;
    /** Langs */
    static $langs = array(
        "en" => "English",
        "fr" => "Français",
    );
    /** lang found */
    static $lang;

    static public function session_before()
    {
        // required for the php PHPSESSID param
        session_set_cookie_params(["SameSite" => "lax"]); // none, lax, strict
        // session_set_cookie_params(["Secure" => "true"]); // false, true
        session_set_cookie_params(["HttpOnly" => "true"]); // false, true
        // needed ?
        // ini_set('session.gc_maxlifetime', strval(60*60));
    }

    /**
     * Get absolute URL
     */
    public static function url()
    {
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
        $url .= "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        return $url;
    }

    /**
     * Give pathinfo with priority order of different values.
     * The possible variables are not equally robust
     *
     * http://localhost/~user/teipot/doc/install&sons?a=1&a=2#ancre
     *
     * — $_SERVER['REQUEST_URI'] OK /~user/teipot/doc/install&sons?a=1&a=2
     * — $_SERVER['SCRIPT_NAME'] OK /~user/teipot/index.php
     * — $_SERVER['PHP_SELF'] /~user/teipot/index.php/doc/install&sons (not always given by mod_rewrite)
     * — $_SERVER['PATH_INFO'] sometimes unavailable, ex: through mod_rewrite /doc/install&sons
     * — $_SERVER['SCRIPT_URI'] sometimes, ex : http://teipot.x10.mx/install&bon
     * — $_SERVER['PATH_ORIG_INFO'] found on the web
     *
     */
    public static function pathinfo()
    {
        if (self::$pathinfo) return self::$pathinfo;
        $pathinfo = "";
        if (!isset($_SERVER['REQUEST_URI'])) return $pathinfo; // command line
        list($request) = explode('?', $_SERVER['REQUEST_URI']);
        if (strpos($request, '%') !== false) $request = urldecode($request);
        if (strpos($request, $_SERVER['SCRIPT_NAME']) === 0)
            $pathinfo = substr($request, strlen($_SERVER['SCRIPT_NAME']));
        else if (strpos($request, dirname($_SERVER['SCRIPT_NAME'])) === 0)
            $pathinfo = substr($request, strlen(dirname($_SERVER['SCRIPT_NAME'])));
        // if nothing found, try other variables
        if ($pathinfo); // something found, keep it
        else if (isset($_SERVER['PATH_ORIG_INFO'])) $pathinfo = $_SERVER['PATH_ORIG_INFO'];
        else if (isset($_SERVER['PATH_INFO'])) $pathinfo = $_SERVER['PATH_INFO'];
        else if (isset($_REQUEST['id'])) $pathinfo = $_REQUEST['id'];
        // should I trim last / ?
        $pathinfo = ltrim($pathinfo, '/');
        $pathinfo = preg_replace('@/+@', '/', $pathinfo);
        // html injection ?
        $pathinfo = strip_tags($pathinfo);
        self::$pathinfo = $pathinfo;
        return self::$pathinfo;
    }

    /**
     * Relative path to context
     */
    public static function basehref($path = null)
    {
        if ($path) { // return a result, no store
            $path = preg_replace('@/+@', '/', ltrim($path, '/'));
            $path = str_repeat("../", substr_count($path, '/'));
            if (!$path) $path = "./"; // with /toto, go up with ./
            return $path;
        }
        if (isset(self::$basehref)) return self::$basehref;
        $pathinfo = self::pathinfo();
        self::$basehref = str_repeat("../", substr_count($pathinfo, '/'));
        if (!self::$basehref) self::$basehref = "./"; // with /toto, go up with ./
        return self::$basehref;
    }

    /**
     * Parse a query string or get it from http request (get or post)
     * Returns an array.
     * ?a=a1&b=b0&a=a2
     * array(
     *   'a' => array('a1', 'a2'),
     *   'b' => array('b0'),
     * )
     */
    public static function parse(?string $query = null): array
    {
        $pars = array(); // returned array
        if (!$query) $query = self::query();
        $a = explode('&', $query);
        foreach ($a as $p) {
            if (!$p) continue;
            if (!strpos($p, '=')) continue;
            list($k, $v) = preg_split('/=/', $p);
            $k = urldecode($k);
            $v = urldecode($v);
            /* bad with greek
            // seems ISO, translate accents
            if (preg_match('/[\xC0-\xFD]/', $k . $v)) {
                $k = utf8_encode($k);
                $v = utf8_encode($v);
            }
            */
            // sanitize value for xss
            $v = strip_tags(trim($v));
            $pars[$k][] = $v;
        }
        return $pars;
    }

    /**
     * Get a parameter as an int between min-max
     */
    public static function int(
        $name,
        ?int $default = null,
        ?int $min = null,
        ?int $max = null,
        ?string $cookie = null
    ) {
        $value = self::par($name, null, null, $cookie);
        if (!is_numeric($value)) return $default;
        $value = intval($value);
        if ($max && $value > $max) return $max;
        if ($min && $value < $min) return $min;
        return $value;
    }


    /**
     * Get a parameter as a single value 
     */
    public static function par(
        $name,
        ?string $default = null,
        ?string $pattern = null,
        ?string $cookie = null
    ) {
        // store params array extracted from query
        if (!self::$pars) {
            self::$pars = self::parse();
        }
        // no key requested, shout ?
        if (!$name) {
            Log::warning(
                "No name given for an http param\n"
                    . implode("\n", I18n::trace())
            );
            return null;
        }
        $value = null;
        // a param is requested, values found
        if (isset(self::$pars[$name]) && count(self::$pars[$name]) > 0) {
            // if more than one and first is empty, bad practice
            $value = self::$pars[$name][0];
        }
        // param maybe set programmatically, for example by Route
        else if (isset($_GET[$name]) && trim($_GET[$name])) {
            $value = $_GET[$name];
        } else if (isset($_POST[$name]) && trim($_POST[$name])) {
            $value = $_POST[$name];
        }
        // bad practice, maybe confused by cookie, but be nice
        else if (isset($_REQUEST[$name]) && trim($_REQUEST[$name])) {
            $value = $_REQUEST[$name];
        }
        // validate before set a cookie
        if ($pattern && $value && !preg_match($pattern, $value)) {
            $value = null;
        }
        // A cookie is requested
        if ($cookie) {
            $options = [
                'expires' => time() + (30 * 24 * 3600), //  30 days
                'path' => '/',
                // 'domain' => 'domain.com',
                // 'secure' => true, // https needed ?
                'httponly' => true,
                'samesite' => 'Strict',
            ];

            // empty string is used as reset cookie
            if ($value === '') {
                $options['expires'] = time() - 3600;
                setcookie($cookie, "", $options);
            }
            // no value found, find one in cookie
            else if ($value == null && isset($_COOKIE[$cookie])) {
                $value = $_COOKIE[$cookie];
                // check the value stored, maybe bad
                if ($pattern && !preg_match($pattern, $value)) {
                    $value = null;
                    $options['expires'] = time() - 3600;
                    setcookie($cookie, "", $options);
                }
            }
            // a value, cookie persistance
            else  if ($value != null) {
                setcookie($name, $value, $options);
            }
        }
        if ($value !== null) {
            return $value;
        }
        if ($default !== null) {
            return $default;
        }
        return null;
    }


    /**
     * Handle repeated parameters values, especially in multiple select.
     * $_REQUEST propose a strange PHP centric interpretation of http protocol, with the bracket keys
     * &lt;select name="var[]">
     *
     * $query : optional, a "query string" ?cl%C3%A9=%C3%A9%C3%A9&param=valeur1&param=&param=valeur2
     * return : Array (
     *   "clé" => array("éé"),
     *   "param" => array("valeur1", "", "valeur2")
     *)
     */
    public static function pars(
        ?string $name = null,
        $default = null,
        ?string $pattern = null
        // ?string $cookie = null
    ) {
        // store params array extracted from query
        if (!self::$pars) {
            self::$pars = self::parse();
        }
        // no key requested, return all params, do not store cookies
        if (!$name) return self::$pars;
        // a param is requested, values found
        else if (isset(self::$pars[$name])) $pars = self::$pars[$name];
        // no param for this name
        else $pars = array();

        // TODO, cookie for multiple values
        /*
        // no cookie store requested
        if (!$expire);
        // if empty ?, delete cookie
        else if (count($pars) == 1 && !$pars[0]) {
            setcookie($name);
        }
        // if a value, set cookie, do not $_COOKIE[$name] = $value
        else if (count($pars)) {
            // if a number
            if ($expire > 60) setcookie($name, serialize($pars), time() + $expire);
            // session time
            else setcookie($name, serialize($pars));
        }
        // if cookie stored, load it
        else if (isset($_COOKIE[$name])) $pars = unserialize($_COOKIE[$name]);
        */
        // validate
        if ($pattern) {
            $newPars = array();
            foreach ($pars as $value) if (preg_match($pattern, $value)) $newPars[] = $value;
            $pars = $newPars;
        }
        // default
        if (count($pars));
        else if (!$default);
        else if (is_array($default)) $pars = $default;
        else $pars = array($default);
        return $pars;
    }

    /**
     * Search for a lang in an accepted list
     */
    public static function lang($langs = null)
    {
        if (!$langs || !is_array($langs) || !count($langs)) $langs = self::$langs;
        // check browser request
        $lang = false;
        // http param, set a lang
        if (isset($_GET['lang'])) {
            // empty value, reset cookie
            if (!$_GET['lang']) setcookie("lang", "", time() - 3600);
            // lang not available, do nothing
            else if (!isset($langs[$_GET['lang']]));
            // language requested should be available
            else {
                $lang = $_GET['lang'];
                setcookie("lang", $lang);
            }
        }
        // coookie persistancy
        if (!$lang && isset($_COOKIE['lang'])) {
            // language in cookie is not available, maybe setted from elsewhere in the site, do nothing
            if (!isset($langs[$_COOKIE['lang']]));
            else $lang = $_COOKIE['lang'];
        }
        // browser request
        if (!$lang) {
            $http_accept_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
            preg_match_all("/(\w\w)(-\w+)*/", $http_accept_language, $matches);
            // array_values() reindex the keys starting at 0
            $accepted = array_values(array_intersect(array_keys(array_flip($matches[1])), array_keys($langs)));
            if (isset($accepted[0])) $lang = $accepted[0];
        }
        // no lang found, take the first lang available
        if (!$lang) {
            reset($langs);
            $lang = key($langs);
        }
        self::$lang = $lang;
        return self::$lang;
    }

    /**
     * build an optimized query string from requested params
     */
    public static function qstring(
        array $names
    ): string {
        if (!self::$pars) self::$pars = self::parse();
        $query = array();
        foreach ($names as $name) {
            if (!isset(self::$pars[$name])) continue;
            foreach (self::$pars[$name] as $value) {
                $query[] =  rawurlencode($name) . '=' . rawurlencode($value);
            }
        }
        if (count($query) < 1) return '';
        return '?' . implode('&amp;', $query);
    }
    /**
     * build a clean query string from get or post, especially
     * to get multiple params from select
     *
     * query: ?A=1&A=2&A=&B=3
     * return: ?A=1&A=2&B=3
     * $keep=true : keep empty params -> ?A=1&A=2&A=&B=3
     * $exclude=array() : exclude some parameters
     */
    public static function query(
        $keep = false,
        $exclude = array(),
        $query = null
    ): string {
        // query given as param
        if ($query) {
            $query = preg_replace('/&amp;/', '&', $query);
        }
        // POST
        else if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (isset($HTTP_RAW_POST_DATA)) {
                $query = $HTTP_RAW_POST_DATA;
            } else {
                $query = file_get_contents("php://input");
            }
        }
        // GET
        else {
            $query = $_SERVER['QUERY_STRING'];
        }
        // exclude some params
        if (count($exclude)) {
            $query = preg_replace('/&(' . implode('|', $exclude) . ')=[^&]*/', '', '&' . $query);
        }
        // delete empty params
        if (!$keep) {
            $query = preg_replace(array('/[^&=]+=&/', '/&$/'), array('', ''), $query . '&');
        }
        return $query;
    }

    /**
     * Read file on request, or send nice headers fo nor modified
     */
    public static function readfile(string $file): bool
    {
        if (!isset(self::$mime)) {
            self::$mime = include(__DIR__ . "/mime.php");
        }
        if (!Filesys::readable($file)) {
            Log::warning(I18n::_("Web.readfile.404"));
            return false;
        }
        self::notModified($file);
        $ext = ltrim(pathinfo($file, PATHINFO_EXTENSION), '.');

        header("Cache-control: public"); // for FireFox over https
        header("Last-Modified: " . 
            gmdate('D, d M Y H:i:s', filemtime($file)) . ' GMT');


        if (isset(self::$mime[$ext])) {
            $mime = self::$mime[$ext];
            header('Content-Type: ' . $mime["type"]);
            $type = $mime["type"];
            // some infos for caching persistant resources
            if (isset($mime['max-age'])) {
                header('Cache-Control: max-age='. $mime['max-age']);
                header('Expires: ' . gmdate('D, d M Y H:i:s', time() +  $mime['max-age']) . ' GMT');
            }
        }
        if (!$mime) {
            header('Content-Type: ' . mime_content_type($file));
        }
        $length = filesize($file);
        header("Content-Length: $length");
        readfile($file);
        exit();
    }

    /**
     * Send the best headers for cache, according to the request and a timestamp
     */
    public static function notModified($file, $expires = null, $force = false)
    {
        if (!$file) return false;
        $filemtime = false;
        // seems already a filemtime
        if (is_int($file)) $filemtime = $file;
        // if array of file, get the newest
        else if (is_array($file)) foreach ($file as $f) {
            // if not file exists, no error
            if (!file_exists($f)) continue;
            $i = filemtime($f);
            if ($i && $i > $filemtime) $filemtime = $i;
        }
        else $filemtime = filemtime($file);
        if (!$filemtime) return $filemtime;
        // Default expires
        if (filemtime($_SERVER['SCRIPT_FILENAME']) > $filemtime) {
            $filemtime = filemtime($_SERVER['SCRIPT_FILENAME']);
        }
        $if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']) : false;
        // $if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) : false; // etag
        $modification = gmdate('D, d M Y H:i:s', $filemtime) . ' GMT';
        // tests for 304
        if ($force);
        else if (self::noCache());
        // ($if_none_match && $if_none_match == $etag) ||
        else if ($if_modified_since == $modification) {
            header('HTTP/1.x 304 Not Modified');
            exit;
        }
        // header("X-Date: ". substr(gmdate('r'), 0, -5).'GMT');
        /*
        // According to google, https://developers.google.com/speed/docs/best-practices/caching
        // exclude etag if last-Modified, and last-Modified is better
        $etag = '"'.md5($modification).'"';
        header("ETag: $etag");
        */
    }

    /**
     * If client ask a forced reload.
     */
    public static function noCache()
    {
        // pas de cache en POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') return 'POST';
        if (isset($_SERVER['HTTP_PRAGMA']) && stripos($_SERVER['HTTP_PRAGMA'], "no-cache") !== false) return "Pragma: no-cache";
        if (isset($_SERVER['HTTP_CACHE_CONTROL']) && stripos($_SERVER['HTTP_CACHE_CONTROL'], "no-cache") !== false) return "Cache-Control: no-cache";
        if (isset($_REQUEST['no-cache'])) return '?no-cache=';
        if (isset($_REQUEST['force'])) return '?force=';
        return false;
    }

    /**
     * Get link to un upload file, by key or first one if no key
     * return a file record like in $_FILES
     * http://php.net/manual/features.file-upload.post-method.php
     */
    public static function upload($key = null): ?array
    {
        // no post, return nothing
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return null;
        $lang = self::lang(array('en' => '', 'fr' => ''));
        $mess = array(
            UPLOAD_ERR_INI_SIZE => array(
                'en' => 'The uploaded file exceeds a directive in php.ini; upload_max_filesize=' . ini_get('upload_max_filesize') . ', post_max_size=' . ini_get('post_max_size'),
                "fr" => 'Le fichier téléchargé dépasse la limite acceptée par la configuration du serveur (php.ini) ; upload_max_filesize=' . ini_get('upload_max_filesize') . ', post_max_size=' . ini_get('post_max_size'),
            ),
            UPLOAD_ERR_FORM_SIZE => array(
                'en' => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
                'fr' => 'Le fichier téléchargé dépasse la directive MAX_FILE_SIZE spécifiée dans le formulaire.',
            ),
            UPLOAD_ERR_PARTIAL => array(
                'en' => 'The uploaded file was only partially uploaded. ',
                'fr' => 'Le fichier téléchargé est incomplet',
            ),
            UPLOAD_ERR_NO_FILE => array(
                'en' => 'No file was uploaded.',
                'fr' => 'Pas de fichier téléchargé.',
            ),
            UPLOAD_ERR_NO_TMP_DIR => array(
                'en' => 'Server configuration error, missing a temporary folder.',
                'fr' => 'Erreur de configuration serveur, pas de dossier temporaire.',
            ),
            UPLOAD_ERR_CANT_WRITE => array(
                'en' => 'Server system error, failed to write file to disk.',
                'fr' => 'Erreur système sur le serveur, impossible d’écrire le fichier sur le disque.',
            ),
            UPLOAD_ERR_EXTENSION => array(
                'en' => 'PHP server problem, a PHP extension stopped the file upload.',
                'fr' => 'Erreur de configuration PHP, une extension a arrêté le téléchargement du fichier.',
            ),
            'nokey' => array(
                'en' => "Web::upload(), no field $key in submitted form.",
                'fr' => "Web::upload(), pas de champ $key dans le formulaire soumis.",
            ),
            'nofile' => array(
                'en' => 'Web::upload(), no file found. Too big ? Directives in php.ini: upload_max_filesize=' . ini_get('upload_max_filesize') . ', post_max_size=' . ini_get('post_max_size'),
                'fr' => 'Web::upload(), pas de fichier trouvé. Trop gros ? Directives php.ini: upload_max_filesize=' . ini_get('upload_max_filesize') . ', post_max_size=' . ini_get('post_max_size'),
            ),
        );
        if ($key && !isset($_FILES[$key])) throw new Exception($mess['nokey'][$lang]);
        if ($key) $file = $_FILES[$key];
        else $file = reset($_FILES);
        if (!$file || !is_array($file) || !isset($file['error'])) throw new Exception($mess['nofile'][$lang]);
        // validation, no matter for an exception
        if ($file['error'] == UPLOAD_ERR_NO_FILE) return null;
        if ($file['error']) throw new Exception($mess[$file['error']][$lang]);
        // return the array to have the tmp link, and the original name of the file, and some more useful fields
        $file["filename"] = pathinfo($file['name'], PATHINFO_FILENAME);
        $file["extension"] = pathinfo($file['name'], PATHINFO_EXTENSION);
        return $file;
    }

    /**
     * Get length in byte from a string content, for Content-Length http header
     */
    static function length(string &$str)
    {
        if (!$str) return 0;
        return ini_get('mbstring.func_overload') ? mb_strlen($str, '8bit') : strlen($str);
    }
}
