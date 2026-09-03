<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Notification;
use App\Models\Order;
use App\Observers\OrderObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */

    public function boot()
    {
        Paginator::useBootstrapFive();

        Order::observe(OrderObserver::class);

         View::composer(
        'admin.layouts.topbar',
        function($view){

            if(auth()->check()){


$notifications = Notification::where(
        'user_id',
        auth()->id()
    )
    ->where('is_read',0)
    ->latest()
    ->take(5)
    ->get();

$notificationCount = $notifications->count();



                $view->with([

                    'notifications'=>$notifications,

                    'notificationCount'=>$notificationCount

                ]);

            }else{


                $view->with([

                    'notifications'=>collect(),

                    'notificationCount'=>0

                ]);


            }

        }
    );

            if (config('app.env') === 'production') {

                    URL::forceScheme('https');

                }
    }

    
}
