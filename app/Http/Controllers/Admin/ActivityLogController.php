<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{

    public function index(Request $request)
    {

        $logs = ActivityLog::with('user')

            ->when($request->keyword,function($q) use($request){

                $q->where('activity','like','%'.$request->keyword.'%');

            })

            ->when($request->module,function($q) use($request){

                $q->where('module',$request->module);

            })

            ->when($request->user,function($q) use($request){

                $q->where('user_id',$request->user);

            })

            ->latest()

            ->paginate(20)

            ->withQueryString();


        $users = User::orderBy('name')->get();

        $modules = ActivityLog::select('module')

            ->whereNotNull('module')

            ->distinct()

            ->pluck('module');


        return view(
            'admin.activity-log.index',
            compact(
                'logs',
                'users',
                'modules'
            )
        );

    }

}