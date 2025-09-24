<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Date;
use App\Models\User; // ✅✅✅ الخطوة 1: استيراد موديل User

class UpdateLastLoginAt
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
     *
     * @param  \Illuminate\Auth\Events\Login  $event
     * @return void
     */
    public function handle(Login $event): void
    {


        /** @var User $user */ //
        $user = $event->user;

        $user->last_login_at = Date::now();
        $user->save(); // الآن يجب أن يختفي الخط الأحمر من تحت save()
    }
}
