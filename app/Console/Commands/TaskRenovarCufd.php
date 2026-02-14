<?php

namespace App\Console\Commands;

use App\Http\Traits\CufdTrait;
use Illuminate\Console\Command;
use Log;

class TaskRenovarCufd extends Command
{
    use CufdTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taskcufd:renovar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tarea programada para renovar los códigos de los puntos de venta. ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->forceRenovarCUFD();
        // $this->tareaRenovarCufd();
        log::info('Tarea Programada: [taskcufd:renovar] realizada');
    }
}
