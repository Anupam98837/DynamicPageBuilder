<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RictorAdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'uuid'                       => Str::uuid(),
            'name'                       => 'Rictor Bhowmick',
            'slug'                       => 'rictor-bhowmick',
            'email'                      => 'rictorbhowmick@hallienz.com',
            'email_verified_at'          => now(),

            'phone_number'               => null,
            'alternative_email'          => null,
            'alternative_phone_number'   => null,
            'whatsapp_number'            => null,

            'password'                   => Hash::make('admin@123'),

            'image'                      => null,
            'address'                    => null,

            'role'                       => 'admin',
            'role_short_form'            => 'ADM',

            'status'                     => 'active',
            'last_login_at'              => null,
            'last_login_ip'              => null,

            'created_by'                 => null,
            'created_at'                 => now(),
            'updated_at'                 => now(),
            'created_at_ip'              => '127.0.0.1',

            'metadata'                   => json_encode([]),
        ]);
    }
}