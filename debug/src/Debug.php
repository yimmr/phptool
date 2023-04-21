<?php

namespace Imon;

use ReflectionObject;

class Debug
{
    public static $path;

    public static $show   = true;
    public static $boxcss = false;

    public static function run($dir)
    {
        static::$path = $dir;
    }

    public static function path($path = '')
    {
        return static::$path . ($path ? \DIRECTORY_SEPARATOR . $path : '');
    }

    public static function dataPath($path = '')
    {
        return static::$path . \DIRECTORY_SEPARATOR . 'data' . ($path ? \DIRECTORY_SEPARATOR . $path : '');
    }

    public static function show()
    {
        static::$show = true;
    }

    public static function hide()
    {
        static::$show = false;
    }

    public static function dump($var)
    {
        static::view('varbox', [
            'class' => static::$show ? 'imdebug-display' : 'imdebug-hide',
            'var'   => $var,
        ]);
    }

    public static function logHTML($var, $filename = '')
    {
        static::log($var, $filename, true);
    }

    public static function log($var, $filename = '', $html = false)
    {
        date_default_timezone_set('Asia/ShangHai');
        $ext = $html ? '.html' : '.php';
        if (!$filename) {
            $filename = date('y-m-d-his', time());
            while (@file_exists(static::dataPath($filename . $ext))) {
                $arrOfName = explode('-', $filename);
                $arrOfName[3] += 1;
                $filename = implode('-', $arrOfName);
            }
        }

        $file = static::dataPath($filename . $ext);

        if ($ext == '.php') {
            file_put_contents($file, "<?php\r\n\r\nreturn " . var_export($var, true) . ';');
        } else {
            ob_start();
            $boxcss         = static::$boxcss;
            $show           = static::$show;
            static::$boxcss = true;
            static::$show   = true;
            static::dump($var);
            static::$boxcss = $boxcss;
            static::$show   = $show;
            file_put_contents($file, ob_get_clean());
        }
    }

    public static function view($name, $data = [])
    {
        if (is_array($data)) {
            extract($data);
        }

        include static::path($name . '.view.php');
    }

    public static function varHTML($var, $before = '')
    {
        if (is_array($var)) {
            static::arrayHTML($var, $before);
        } elseif (is_object($var)) {
            static::objectHTML($var, $before);
        } else {
            echo $before . htmlspecialchars(var_export($var, true));
        }
    }

    public static function arrayHTML($array, $before = '')
    {
        echo '<div class="imdebug-ref">';
        echo $before . '<span class="imdebug-note">array:' . count($array) . ' </span>';
        echo '[';
        echo '<ul>';
        foreach ($array as $key => $value) {
            echo '<li>';
            $before = '<span class="imdebug-key">' . var_export($key, true) . '</span> =&gt; ';
            static::varHTML($value, $before);
            echo '</li>';
        }
        echo '</ul>';
        echo ']';
        echo '</div>';
    }

    public static function objectHTML($object, $before = '')
    {
        if (!($object instanceof ReflectionObject)) {
            $object = static::buildRefOBJ($object);
        }

        echo '<div class="imdebug-ref">';
        echo "{$before}<span class=\"imdebug-note\">{$object->name}</span> #{$object->id} ";
        echo '{';
        echo '<ul>';

        foreach (array_merge($object->getProperties(), $object->getMethods()) as $value) {
            $before = sprintf('<span class="imdebug-mod">%s </span>', implode(' ', \Reflection::getModifierNames($value->getModifiers())));

            echo '<li>';
            if (method_exists($value, 'getValue')) {
                $before .= '<span class="imdebug-key">' . $value->getName() . '</span> : ';
                try {
                    $val = $value->getValue($object->origin);
                    static::varHTML($val, $before);
                } catch (\Throwable $th) {
                    echo $before . '--';
                }
            } else {
                echo $before . '<span class="imdebug-method">' . $value->getName() . '()</span>';
            }
            echo '</li>';
        }

        echo '</ul>';
        echo '}';
        echo '</div>';
    }

    public static function buildRefOBJ($obj)
    {
        ob_start();
        var_dump($obj);
        $id           = preg_replace('/.*#(\d+).*/s', '$1', ob_get_clean());
        $_var         = new ReflectionObject($obj);
        $_var->id     = $id;
        $_var->origin = $obj;
        return $_var;
    }

    public static function parseVar($var, $dep = 3, $start = 1)
    {
        $type = gettype($var);

        if ($type === 'array') {
            if ($start <= $dep) {
                foreach ($var as $i => $val) {
                    $var[$i] = static::parseVar($val, $dep, $start + 1);
                }
            }
        } elseif ($type === 'object') {
            $var = static::buildRefOBJ($var);
        }

        return $start == 1 ? ['type' => $type, 'value' => $var] : $var;
    }

    public static function index()
    {
        header('content-type: text/html; charset=utf-8');

        $dir = opendir(static::dataPath());
        echo '<style>body{margin:0;background:#f8f8f8}section{background:#fff;margin:1rem 10%;padding:0 1rem 1rem}h3{margin:0 0 1rem;padding:1rem 0;border-bottom:1px solid #eee}</style>';

        static::view('varbox');

        while (false !== ($filename = readdir($dir))) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }

            $ext  = pathinfo($filename)['extension'];
            $file = static::dataPath($filename);

            echo '<section>';
            echo "<h3>$filename</h3>";
            if ($ext == 'php') {
                echo '<pre>';
                try {
                    $data = include $file;
                    echo htmlspecialchars(var_export($data, true));
                } catch (\Throwable $th) {
                    echo rtrim(ltrim(file_get_contents($file), "<?php\r\nreturn"), ";\?\>");
                }
                echo '</pre>';
            } else {
                echo file_get_contents($file);
            }
            echo '</section>';
        }
    }
}