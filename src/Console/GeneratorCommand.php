<?php

/**
 * Laravel IDE Helper Generator.
 *
 * @author    Barry vd. Heuvel <barryvdh@gmail.com>
 * @copyright 2014 Barry vd. Heuvel / Fruitcake Studio (http://www.fruitcakestudio.nl)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @see      https://github.com/barryvdh/laravel-ide-helper
 */

namespace Barryvdh\LaravelIdeHelper\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Barryvdh\LaravelIdeHelper\Generator;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to generate autocomplete information for your IDE.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */
class GeneratorCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'ide-helper:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new IDE Helper file.';

    /** @var ConfigRepository */
    protected $config;

    /** @var Filesystem */
    protected $files;

    /** @var \Illuminate\View\Factory */
    protected $view;

    protected $onlyExtend;

    /**
     * @param ConfigRepository         $config
     * @param Filesystem               $files
     * @param \Illuminate\View\Factory $view
     */
    public function __construct(ConnfigRepository $config, Filesystem $files, $view)
    {
        $this->config = $config;
        $this->files  = $files;
        $this->view   = $view;

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (file_exists(base_path() . '/vendor/compiled.php') ||
            file_exists(base_path() . '/bootstrap/cache/compiled.php') ||
            file_exists(base_path() . '/storage/framework/compiled.php')) {
            $this->error(
                'Error generating IDE Helper: first delete your compiled file (php artisan clear-compiled)'
            );
        } else {
            $filename = $this->argument('filename');
            $format   = $this->option('format');

            // Strip the php extension
            if ('.php' === substr($filename, -4, 4)) {
                $filename = substr($filename, 0, -4);
            }

            $filename .= '.' . $format;

            if ($this->option('memory')) {
                $this->useMemoryDriver();
            }

            $helpers = '';
            if ($this->option('helpers') || ($this->config->get('ide-helper.include_helpers'))) {
                foreach ($this->config->get('ide-helper.helper_files', []) as $helper) {
                    if (file_exists($helper)) {
                        $helpers .= str_replace(['<?php', '?>'], '', $this->files->get($helper));
                    }
                }
            } else {
                $helpers = '';
            }

            $generator = new Generator($this->config, $this->view, $this->getOutput(), $helpers);
            $content   = $generator->generate($format);
            $written   = $this->files->put($filename, $content);

            if (false !== $written) {
                $this->info("A new helper file was written to $filename");

                Eloquent::writeEloquentModelHelper($this, $this->files);
            } else {
                $this->error("The helper file could not be created at $filename");
            }
        }
    }

    protected function useMemoryDriver()
    {
        // Use a SQLite database in memory, to avoid connection errors on database Facades.

        $this->config->set(
            'database.connections.sqlite',
            [
                'driver'   => 'sqlite',
                'database' => ':memory:',
            ]
        );

        $this->config->set('database.default', 'sqlite');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments() : array
    {
        return [
            [
                'filename',
                InputArgument::OPTIONAL,
                'The path to the helper file',
                $this->config->get('ide-helper.filename'),
            ],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $format = $this->config->get('ide-helper.format');

        return [
            ['format', 'F', InputOption::VALUE_OPTIONAL, 'The format for the IDE Helper', $format],
            ['helpers', 'H', InputOption::VALUE_NONE, 'Include the helper files'],
            ['memory', 'M', InputOption::VALUE_NONE, 'Use sqlite memory driver'],
            ['sublime', 'S', InputOption::VALUE_NONE, 'DEPRECATED: Use different style for SublimeText CodeIntel'],
        ];
    }
}
