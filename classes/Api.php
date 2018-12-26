<?php

namespace AvosKitchen\Kitchen;

use Exception;
use Kirby\Cms\App as Kirby;
use Kirby\Cms\Page;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

class Api
{

    protected static $jobs = [
        'has-categories' => 'hasCategories',
        'load-default-categories' => 'loadDefaultCategories',
        'has-cuisines' => 'hasCuisines',
        'load-default-cuisines' => 'loadDefaultCuisines',
        'has-units' => 'hasUnits',
        'load-default-units' => 'loadDefaultUnits',
    ];

    public static function api(string $job): array
    {
        $job = Str::slug($job);
        
        $before = time();
        $data = [];
        $success = false;

        if (array_key_exists($job, static::$jobs)) {
            $c = static::$jobs[$job];
            $r = static::$c();
            if (is_array($r)) {
                $data = $r;
                $v = A::get($data, 'status', 404);
                $success = intval($v) == 200;
            } else {
                $success = boolval($r);
            }
        }

        $after = time();

        return array_merge([
            'job' => $job,
            'started' => $before,
            'finished' => $after,
            'status' => $success ? 200 : 404,
        ], $data);
    }

    protected static function getPage(): Page
    {
        $pageId = $_GET['page'] ?? null;

        if (empty($pageId)) {
            throw new Exception('page parameter was empty');
        }

        if (!$page = site()->find($pageId)) {
            throw new Exception('page not found');
        }

        return $page;
    }


    public static function hasCategories(): array
    {
        $page = static::getPage();

        return [
            'status' => 200,
            'result' => $page->categories()->toStructure()->count() > 0,
        ];
    }


    public static function hasCuisines(): array
    {
        $page = static::getPage();

        return [
            'status' => 200,
            'result' => $page->cuisines()->toStructure()->count() > 0,
        ];
    }

    public static function hasUnits(): array
    {
        $page = static::getPage();

        return [
            'status' => 200,
            'result' => $page->units()->toStructure()->count() > 0,
        ];
    }

    public static function loadDefaultCategories()
    {
        $page = static::getPage();

        if ($page->categories()->toStructure()->count() > 0) {
            return false;
        }

        $root = Kirby::plugin('avoskitchen/kitchen')->root();

        $categories = require "{$root}/defaults/categories/de.php";

        $page->update([
            'categories' => $categories,
        ]);

        return [
            'status' => 200,
            'result' => true,
            'label' => 'Standard-Kategorien wurden geladen.',
            'hideButton' => true,
        ];
    }


    public static function loadDefaultCuisines()
    {
        $page = static::getPage();

        if ($page->cuisines()->toStructure()->count() > 0) {
            return false;
        }

        $root = Kirby::plugin('avoskitchen/kitchen')->root();

        $cuisines = require "{$root}/defaults/cuisines/de.php";

        $page->update([
            'cuisines' => $cuisines,
        ]);

        return [
            'status' => 200,
            'result' => true,
            'label' => 'Standard-Länderküchen wurden geladen.',
            'hideButton' => true,
        ];
    }

    public static function loadDefaultUnits()
    {
        $page = static::getPage();

        if ($page->units()->toStructure()->count() > 0) {
            return false;
        }

        $root = Kirby::plugin('avoskitchen/kitchen')->root();

        $cuisines = require "{$root}/defaults/units/de.php";

        $page->update([
            'units' => $cuisines,
        ]);

        return [
            'status' => 200,
            'result' => true,
            'label' => 'Standard-Einheiten wurden geladen.',
            'hideButton' => true,
        ];
    }
}
