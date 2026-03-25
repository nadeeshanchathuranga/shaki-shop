<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert a record into company_infos table
        DB::table('company_infos')->insert([
            'id' => 1,
            'name' => 'JAAN Network (Pvt) Ltd.',
            'address' => 'No 51/1/1,Mahabage road, Ragama',
            'phone' => '0756865900',
            'phone2' => '0717598064',
            'email' => 'info@jaannetwork.com',
            'website' => 'https://jaannetwork.com',
            'logo' => '/images/jaan_logo.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
