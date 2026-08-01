<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{

protected $casts = [

    'old_data'=>'array',

    'new_data'=>'array',

];
    public function log(
        string $module,
        string $action,
        string $activity,
        $model = null,
        ?array $old = null,
        ?array $new = null
    ): void {

        ActivityLog::create([

            'user_id' => Auth::id(),

            'module' => $module,

            'activity' => $activity,

            'action' => $action,

            'target_id' => $model?->id,

            'target_name' =>

                $model?->title ??

                $model?->game_name ??

                $model?->item_name ??

                $model?->category_name ??

                $model?->payment_name ??

                $model?->invoice_number ??

                $model?->name ??

                null,

            'old_data' => $old,

            'new_data' => $new,

            'ip_address' => request()->ip(),

            'user_agent' => request()->userAgent(),

        ]);
    }
}