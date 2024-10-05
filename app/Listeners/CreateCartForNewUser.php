<?php

namespace App\Listeners;

use App\Models\Cart;
use App\Events\CartEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateCartForNewUser
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CartEvent $event): void
    {
        Cart::create([
            'user_id' => $event->user->id
        ]);
    }
}
