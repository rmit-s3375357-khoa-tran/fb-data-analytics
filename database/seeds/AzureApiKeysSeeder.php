<?php

use Illuminate\Database\Seeder;

class AzureApiKeysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\AzureApiKey::create([
            'name' => 'key1',
            'key' => '6fa67a9d8f1645a7991fbe698742272a'
        ]);

        \App\AzureApiKey::create([
            'name' => 'key2',
            'key' => '4e540d98fb47455ea0f2bd19a3fa1f70'
        ]);
    }
}
