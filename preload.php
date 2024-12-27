<?php

require_once __DIR__ . '/vendor/autoload.php';

class Preloader
{
    private array $ignores = [];

    private static int $count = 0;

    private array $paths;

    private array $fileMap;

    public function __construct(string ...$paths)
    {
        $this->paths = $paths;

        // We'll use composer's classmap
        // to easily find which classes to autoload,
        // based on their filename
        $classMap = require __DIR__ . '/vendor/composer/autoload_classmap.php';

        $this->fileMap = array_flip($classMap);
    }

    public function paths(string ...$paths): Preloader
    {
        $this->paths = array_merge($this->paths, $paths);

        return $this;
    }

    public function ignore(string ...$names): Preloader
    {
        $this->ignores = array_merge($this->ignores, $names);

        return $this;
    }

    public function load(): void
    {
        // We'll loop over all registered paths
        // and load them one by one
        foreach ($this->paths as $path) {
            $this->loadPath(rtrim($path, '/'));
        }

        $count = self::$count;

        echo "[Preloader] Preloaded {$count} classes" . PHP_EOL;
    }

    private function loadPath(string $path): void
    {
        // If the current path is a directory,
        // we'll load all files in it
        if (is_dir($path)) {
            $this->loadDir($path);

            return;
        }

        // Otherwise we'll just load this one file
        $this->loadFile($path);
    }

    private function loadDir(string $path): void
    {
        $handle = opendir($path);

        // We'll loop over all files and directories
        // in the current path,
        // and load them one by one
        while ($file = readdir($handle)) {
            if (in_array($file, [
                '.',
                '..',
            ])) {
                continue;
            }

            $this->loadPath("{$path}/{$file}");
        }

        closedir($handle);
    }

    private function loadFile(string $path): void
    {
        // We resolve the classname from composer's autoload mapping
        $class = $this->fileMap[$path] ?? null;

        // And use it to make sure the class shouldn't be ignored
        if ($this->shouldIgnore($class, $path)) {
            return;
        }

        // Finally we require the path,
        // causing all its dependencies to be loaded as well
        if ($class) {
            require_once $path;
        } else {
            opcache_compile_file($path);
        }

        self::$count++;

        $classOrPath = $class ?: $path;

        echo "[Preloader] Preloaded `{$classOrPath}`" . PHP_EOL;
    }

    private function shouldIgnore(?string $name, string $path): bool
    {
        if ($name === null && ! str_contains($path, 'storage/framework/views')) {
            return true;
        }

        foreach ($this->ignores as $ignore) {
            if ($name && strpos($name, $ignore) === 0) {
                return true;
            }
        }

        return false;
    }
}

(new Preloader)->paths(__DIR__ . '/vendor/laravel', __DIR__ . '/storage/framework/views')
    ->ignore(
        \Illuminate\Filesystem\Cache::class,
        \Illuminate\Log\LogManager::class,
        \Illuminate\Http\Testing\File::class,
        \Illuminate\Http\UploadedFile::class,
        \Illuminate\Support\Carbon::class,
        \Illuminate\Testing\ParallelRunner::class,
    )->load();
