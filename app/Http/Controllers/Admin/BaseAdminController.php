<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\ActivityLogService;

class BaseAdminController extends Controller
{
    protected ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;

        view()->composer(
        'admin.*',
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


            }

        }
    );

    }

    
}