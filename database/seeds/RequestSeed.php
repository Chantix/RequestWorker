<?php

use App\Request;
use Illuminate\Database\Seeder;

class RequestSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Request::class, 10000)->create();
    }
}
