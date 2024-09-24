<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
class JuecesVirtualesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(App::environment('local')){
            DB::table('jueces_virtuales')->insert([
                'nombre' => 'Testing',
                'direccion' => 'https://judge0-ce.p.rapidapi.com',
                'host' => 'judge0-ce.p.rapidapi.com',
                'api_token' => env('JUDGE0_API_KEY'),
                'autenticacion' => 'x-rapid-key',
                'created_at' => Carbon::now()->toDateString(),
            ]);
        }
        DB::table('jueces_virtuales')->insert([
            'nombre' => 'TutorBot Juez',
            'direccion' => '127.0.0.1:2358',
            'host' => '127.0.0.1:2358',
            'api_token' => 'tuNw38GPpkDsEJh2X5nUVgwghJmrz4NY',
            'authorize' => 'FYGRXmyS85rUWJTGwymeNYB8SBpv9bvq',
            'autenticacion' => 'X-Auth-Token',
            'created_at' => Carbon::now()->toDateString(),
        ]);
    }
}
