<?php

namespace App\Http\Controllers;

use App\AzureApiKey;
use App\Http\Requests\AzureKeyRequest;
use Illuminate\Support\Facades\Redirect;

class AzureApiKeyController extends Controller
{
    public function update(AzureKeyRequest $request)
    {
        $keys = ['key1', 'key2'];

        foreach ($keys as $keyName)
        {
            $key = AzureApiKey::where('name', $keyName)->first();
            $key->key = $request[$keyName];
            $key->save();
        }

        return Redirect::to(url('/'));
    }
}
