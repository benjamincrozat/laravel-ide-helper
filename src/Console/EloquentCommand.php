<?php

/**
 * Laravel IDE Helper Generator - Eloquent Model Mixin.
 *
 * @author    Charles A. Peterson <artistan@gmail.com>
 * @copyright 2017 Charles A. Peterson / Fruitcake Studio (http://www.fruitcakestudio.nl)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @see      https://github.com/barryvdh/laravel-ide-helper
 */

namespace Barryvdh\LaravelIdeHelper\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Barryvdh\LaravelIdeHelper\Eloquent;

/**
 * A command to add \Eloquent mixin to Eloquent\Model.
 *
 * @author Charles A. Peterson <artistan@gmail.com>
 */
class EloquentCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'ide-helper:eloquent';

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add \Eloquent helper to \Eloquent\Model';

    /**
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Eloquent::writeEloquentModelHelper($this, $this->files);
    }
}
