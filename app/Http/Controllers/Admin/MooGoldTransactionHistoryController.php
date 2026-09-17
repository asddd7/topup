<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Services\MooGold\MooGoldService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class MooGoldTransactionHistoryController extends BaseAdminController
{
    /**
     * =========================================================
     * TRANSACTION HISTORY
     * =========================================================
     */
    public function index(
        Request $request,
        MooGoldService $mooGold
    ) {
        /*
        |--------------------------------------------------------------------------
        | DEFAULT DATE
        |--------------------------------------------------------------------------
        |
        | Default:
        | hari ini - 30 hari
        | sampai hari ini
        |
        */

        $defaultEndDate = now()->format('Y-m-d');

        $defaultStartDate = now()
            ->subDays(29)
            ->format('Y-m-d');


        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'start_date' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'end_date' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'status' => [
                'nullable',
                'in:processing,completed,refunded',
            ],

            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'limit' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | DATE
        |--------------------------------------------------------------------------
        */

        $startDate =
            $validated['start_date']
            ?? $defaultStartDate;

        $endDate =
            $validated['end_date']
            ?? $defaultEndDate;


        $start =
            Carbon::createFromFormat(
                'Y-m-d',
                $startDate
            );

        $end =
            Carbon::createFromFormat(
                'Y-m-d',
                $endDate
            );


        /*
        |--------------------------------------------------------------------------
        | START DATE > END DATE
        |--------------------------------------------------------------------------
        */

        if ($start->gt($end)) {

            return back()
                ->withInput()
                ->withErrors([
                    'start_date' =>
                        'Tanggal mulai tidak boleh setelah tanggal akhir.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | MAX 30 DAYS
        |--------------------------------------------------------------------------
        */

        if ($start->diffInDays($end) > 29) {

            return back()
                ->withInput()
                ->withErrors([
                    'end_date' =>
                        'Rentang tanggal maksimal 30 hari.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        $page =
            (int) (
                $validated['page']
                ?? 1
            );

        $limit =
            (int) (
                $validated['limit']
                ?? 20
            );


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        $status =
            $validated['status']
            ?? null;


        /*
        |--------------------------------------------------------------------------
        | CALL MOO GOLD
        |--------------------------------------------------------------------------
        */

        $response = [];

        $apiError = null;

        try {

            $response =
                $mooGold->transactionHistory(
                    startDate: $startDate,
                    endDate: $endDate,
                    status: $status,
                    page: $page,
                    limit: $limit
                );

        } catch (
            RuntimeException $e
        ) {

            $apiError =
                $e->getMessage();

        } catch (
            Throwable $e
        ) {

            report($e);

            $apiError =
                'Gagal mengambil history transaksi MooGold.';
        }


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'admin.transaction-history.index',
            [
                'response' =>
                    $response,

                'orders' =>
                    $response['orders'] ?? [],

                'currentPage' =>
                    $response['page'] ?? $page,

                'limit' =>
                    $response['limit'] ?? $limit,

                'startDate' =>
                    $startDate,

                'endDate' =>
                    $endDate,

                'status' =>
                    $status,

                'apiError' =>
                    $apiError,
            ]
        );
    }
}