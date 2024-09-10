<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class JuecesVirtualesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('jueces_virtuales')->insert([
            'nombre' => 'Testing',
            'direccion' => 'https://judge0-ce.p.rapidapi.com',
            'host' => 'judge0-ce.p.rapidapi.com',
            'api_token' => env('JUDGE0_API_KEY'),
            'autenticacion' => 'rapidapi',
            'created_at' => Carbon::now()->toDateString(),
        ]);
    }
}
