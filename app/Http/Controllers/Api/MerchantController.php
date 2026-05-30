<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    /**
     * Bank Data
     *
     */
    public function sync(Request $request)
    {
        Merchant::updateOrCreate(
            [
                'location_id' => $request->location_id,
            ],
            [
                'username' => $request->username,
                'password' => $request->password,
            ]
        );

        return response()->json([
            'success' => true,
        ]);
    }
}
