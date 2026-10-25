<?php

declare(strict_types=1);

namespace Divengine\Codegen\Cli;

use Divengine\Codegen\Generator\Clock;
use Divengine\Codegen\Generator\Context;
use Divengine\Codegen\Generator\GeneratedFile;
use Divengine\Codegen\Generator\ModelGenerator;
use Divengine\Codegen\Generator\ServiceGenerator;
use Divengine\Codegen\Generator\UiGenerator;
use Divengine\Codegen\Model\ModelLoader;
use Divengine\Codegen\Output\Writer;
use Divengine\Codegen\Profile\Profile;
use Divengine\Codegen\Template\Renderer;
use Throwable;

/**
 * divengine-codegen command line.
 *
 *   divengine-codegen model|ui|services|all [--output DIR] [--profile FILE]
 *   divengine-codegen check [--profile FILE]
 *
 * Paths in the profile are relative to the repository root (--root, by
 * default the root that contains dev/codegen), never to the working
 * directory. Generation only creates or overwrites files; it never deletes.
 * "check" generates into a temporary directory and compares every file with
 * the one under the root, ignoring the "@updated:" lines; nothing under the
 * root is written.
 */
final class Application
{
    private const USAGE = <<<'TXT'
        Divengine Codegen (formerly cogePHP): model-driven code generator

        Usage: divengine-codegen <command> [options]

        Commands:
          model      data access layer: SQL scripts, PartModel, views, models, functions
          ui         views, view controllers and add/set/del handlers
          services   service classes
          all        model + ui + services
          check      generate into a temporary directory and compare with the root

        Options:
          --profile FILE   project profile (default: dev/codegen/profiles/ancoragoh.php)
          --root DIR       repository root (default: the root that contains dev/codegen)
          --output DIR     output root of model/ui/services/all (default: --root)

        SOURCE_DATE_EPOCH, when set, fixes the "@updated:" timestamp.

        TXT;

    private string $root;
    private string $codegenDir;

    public function __construct()
    {
        $this->codegenDir = dirname(__DIR__, 2);
        $this->root = dirname($this->codegenDir, 2);
    }

    /**
     * @param list<string> $argv
     */
    public function run(array $argv): int
    {
        try {
            [$command, $options] = $this->parse(array_slice($argv, 1));
            if ($command === null || $command === 'help' || isset($options['help'])) {
                fwrite(STDOUT, self::USAGE);
                return $command === null ? 2 : 0;
            }
            if (isset($options['root'])) {
                $this->root = $this->realDir($options['root']);
            }
            $profile = Profile::load($this->resolve($options['profile'] ?? 'dev/codegen/profiles/ancoragoh.php'));

            return match ($command) {
                'model', 'ui', 'services', 'all' => $this->generate($command, $profile, $options['output'] ?? null),
                'check' => $this->check($profile),
                default => $this->fail("unknown command: $command"),
            };
        } catch (Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * @return list<GeneratedFile>
     */
    public function build(string $what, Profile $profile): array
    {
        $loader = new ModelLoader($profile);
        $model = $loader->load($this->resolve($profile->string('model.dir')), $profile->string('model.file'));
        $templates = $this->codegenDir . '/templates';
        $ctx = new Context($profile, $model, new Renderer($templates), Clock::updated());
        $files = [];
        if ($what === 'model' || $what === 'all') {
            array_push($files, ...(new ModelGenerator($ctx))->generate());
        }
        if ($what === 'ui' || $what === 'all') {
            array_push($files, ...(new UiGenerator($ctx))->generate());
        }
        if ($what === 'services' || $what === 'all') {
            $dir = $profile->get('model.services_dir');
            $services = is_string($dir) ? $loader->loadServices($this->resolve($dir)) : [];
            array_push($files, ...(new ServiceGenerator($ctx, $services))->generate());
        }

        return $files;
    }

    private function generate(string $what, Profile $profile, ?string $output): int
    {
        $outDir = $output === null ? $this->root : $this->realDir($output);
        $files = $this->build($what, $profile);
        $writer = new Writer($outDir);
        foreach ($files as $file) {
            $writer->write($file);
        }
        fwrite(STDOUT, sprintf(
            "%s: %d file(s) generated in %s (%d written, %d unchanged)\n",
            $what,
            count($files),
            $outDir,
            $writer->written(),
            $writer->unchanged(),
        ));

        return 0;
    }

    private function check(Profile $profile): int
    {
        $files = $this->build('all', $profile);
        $strip = static fn (string $s): string => (string) preg_replace('/^.*@updated:.*$/m', '', $s);
        $reader = new Writer($this->root);
        $differ = [];
        $missing = [];
        foreach ($files as $file) {
            $path = $reader->path($file->path);
            if (!is_file($path)) {
                $missing[] = $file->path;
            } elseif ($strip((string) file_get_contents($path)) !== $strip($file->content)) {
                $differ[] = $file->path;
            }
        }
        foreach ($missing as $p) {
            fwrite(STDERR, "[FAIL] not in the repository: $p\n");
        }
        foreach ($differ as $p) {
            fwrite(STDERR, "[FAIL] differs from the generator output (ignoring @updated): $p\n");
        }
        $ok = count($files) - count($missing) - count($differ);
        if ($missing === [] && $differ === []) {
            fwrite(STDOUT, "check: $ok generated file(s) match the committed ones (ignoring @updated lines)\n");
            return 0;
        }
        fwrite(STDOUT, sprintf(
            "check: %d of %d generated file(s) match; %d differ, %d missing\n",
            $ok,
            count($files),
            count($differ),
            count($missing),
        ));

        return 1;
    }

    /**
     * @param list<string> $args
     * @return array{0: ?string, 1: array<string, string>}
     */
    private function parse(array $args): array
    {
        $command = null;
        $options = [];
        for ($i = 0; $i < count($args); $i++) {
            $arg = $args[$i];
            if ($arg === '--help' || $arg === '-h') {
                $options['help'] = '1';
            } elseif (in_array($arg, ['--profile', '--root', '--output'], true)) {
                if (!isset($args[$i + 1])) {
                    throw new \InvalidArgumentException("$arg needs a value");
                }
                $options[substr($arg, 2)] = $args[++$i];
            } elseif (str_starts_with($arg, '-')) {
                throw new \InvalidArgumentException("unknown option: $arg");
            } elseif ($command === null) {
                $command = $arg;
            } else {
                throw new \InvalidArgumentException("unexpected argument: $arg");
            }
        }

        return [$command, $options];
    }

    private function resolve(string $path): string
    {
        return str_starts_with($path, '/') ? $path : $this->root . '/' . $path;
    }

    private function realDir(string $dir): string
    {
        $real = realpath($dir);
        if ($real === false || !is_dir($real)) {
            throw new \InvalidArgumentException("not a directory: $dir");
        }

        return $real;
    }

    private function fail(string $message): int
    {
        fwrite(STDERR, "divengine-codegen: $message\n");

        return 1;
    }
}
