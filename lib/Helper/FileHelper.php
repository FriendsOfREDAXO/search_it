<?php

namespace FriendsOfRedaxo\SearchIt\Helper;

use rex_addon;

class FileHelper
{
    public static function getDirs(string $startDir = '', bool $getSubdirs = false): array
    {
        $si = rex_addon::get('search_it');

        $startDepth = mb_substr_count($startDir, '/');
        if (@is_dir($_SERVER['DOCUMENT_ROOT'] . $startDir)) {
            $dirs2 = array_diff(scandir($_SERVER['DOCUMENT_ROOT'] . $startDir), ['.', '..']);
        } else {
            return [];
        }
        $dirs = [];
        foreach ($dirs2 as $dir) {
            if (@is_dir($_SERVER['DOCUMENT_ROOT'] . $startDir . '/' . $dir)) {
                $dirs[$_SERVER['DOCUMENT_ROOT'] . $startDir . '/' . $dir] = $startDir . '/' . $dir;
            }
        }
        if (!$getSubdirs) {
            return $dirs;
        }

        $return = [];
        while (!empty($dirs)) {
            $dir = array_shift($dirs);

            $depth = mb_substr_count($dir, '/') - $startDepth;
            if (@is_dir($_SERVER['DOCUMENT_ROOT'] . $dir) and $depth <= $si->getConfig('dirdepth')) {
                $return[$_SERVER['DOCUMENT_ROOT'] . $dir] = $dir;
                $subdirs = [];
                foreach (array_diff(scandir($_SERVER['DOCUMENT_ROOT'] . $dir), ['.', '..']) as $subdir) {
                    if (@is_dir($_SERVER['DOCUMENT_ROOT'] . $dir . '/' . $subdir)) {
                        $subdirs[] = $dir . '/' . $subdir;
                    }
                }
                array_splice($dirs, 0, 0, $subdirs);
            }
        }

        return $return;
    }

    public static function getFiles(string $startDir = '', array $fileexts = [], bool $getSubdirs = false): array
    {
        $si = rex_addon::get('search_it');

        $return = [];

        if (!empty($fileexts)) {
            $fileextPattern = '~\.(' . implode('|', $fileexts) . ')$~is';
        } else {
            $fileextPattern = '~\.([^.]+)$~is';
        }

        $startDepth = mb_substr_count($startDir, '/');
        if (@is_dir($_SERVER['DOCUMENT_ROOT'] . $startDir)) {
            $dirs2 = array_diff(scandir($_SERVER['DOCUMENT_ROOT'] . $startDir), ['.', '..']);
        } else {
            return [];
        }
        $dirs = [];
        foreach ($dirs2 as $dir) {
            if (@is_dir($_SERVER['DOCUMENT_ROOT'] . $startDir . '/' . $dir)) {
                $dirs[$_SERVER['DOCUMENT_ROOT'] . $startDir . '/' . $dir] = $startDir . '/' . $dir;
            } elseif (preg_match($fileextPattern, $dir)) {
                $return[] = $startDir . '/' . $dir;
            }
        }

        if (!$getSubdirs) {
            return $return;
        }

        while (!empty($dirs)) {
            $dir = array_shift($dirs);

            $depth = mb_substr_count($dir, '/') - $startDepth;
            if (@is_dir($_SERVER['DOCUMENT_ROOT'] . $dir) and $depth <= $si->getConfig('dirdepth')) {
                $subdirs = [];
                foreach (array_diff(scandir($_SERVER['DOCUMENT_ROOT'] . $dir), ['.', '..']) as $subdir) {
                    if (@is_dir($_SERVER['DOCUMENT_ROOT'] . $dir . '/' . $subdir)) {
                        $subdirs[] = $dir . '/' . $subdir;
                    } elseif (preg_match($fileextPattern, $subdir)) {
                        $return[] = $dir . '/' . $subdir;
                    }
                }
                array_splice($dirs, 0, 0, $subdirs);
            } elseif (preg_match($fileextPattern, $subdir)) {
                $return[] = $dir;
            }
        }

        return $return;
    }
}
