<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Paddle\Checkout;

class CreditsController extends Controller
{
    /**
     * Redirect to Paddle checkout.
     */
    public function checkout(Request $request)
    {
        $credits = (int) $request->input('credits', 10);
        $priceId = config('services.paddle.price_id_10_credits');

        return $request->user()->checkout($priceId)
            ->customData([
                'credits' => $credits,
                'user_id' => $request->user()->id
            ])
            ->redirectTo(route('dashboard'));
    }
}
