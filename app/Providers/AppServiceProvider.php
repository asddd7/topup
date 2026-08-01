<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Notification;

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

         View::composer(
        'admin.layouts.topbar',
        function($view){

            if(auth()->check()){


                $notifications = Notification::where(
                    'user_id',
                    auth()->id()
                )
                ->where(
                    'is_read',
                    0
                )
                ->latest()
                ->limit(5)
                ->get();



                $notificationCount = Notification::where(
                    'user_id',
                    auth()->id()
                )
                ->where(
                    'is_read',
                    0
                )
                ->count();



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
    }
}
