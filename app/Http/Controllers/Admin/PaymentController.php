<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Integrations\Midtrans\MidtransService;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends BaseAdminController
{
    public function __construct(
        \App\Services\ActivityLogService $activity,
        protected MidtransService $midtrans
    ) {
        parent::__construct($activity);
    }

    public function index()
    {
        $payments = Payment::latest()->get();

        try {
            $midtransChannels = $this->midtrans->getSnapPaymentChannels();
            $midtransError = null;
        } catch (\Throwable $e) {
            report($e);
            $midtransChannels = [];
            $midtransError = 'Channel Midtrans tidak dapat diambil saat ini.';
        }


        return view(
            'admin.payment.index',
            compact('payments', 'midtransChannels', 'midtransError')
        );

    }

    public function syncMidtrans()
    {
        try {
            $channels = $this->midtrans->getSnapPaymentChannels();
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal mengambil channel pembayaran dari Midtrans.');
        }

        foreach ($channels as $channel) {
            $type = strtolower(trim((string) ($channel['name'] ?? '')));

            if ($type === '') {
                continue;
            }

            Payment::updateOrCreate(
                ['payment_type' => $type],
                [
                    'payment_name' => $this->midtransPaymentLabel($type),
                    'payment_number' => 'Midtrans',
                    'account_name' => 'Midtrans',
                    'is_active' => true,
                ]
            );
        }

        return back()->with('success', count($channels) . ' channel Midtrans berhasil disinkronkan.');
    }

    private function midtransPaymentLabel(string $type): string
    {
        return [
            'credit_card' => 'Kartu Kredit',
            'bca_va' => 'BCA Virtual Account',
            'bni_va' => 'BNI Virtual Account',
            'bri_va' => 'BRI Virtual Account',
            'cimb_va' => 'CIMB Niaga Virtual Account',
            'gopay' => 'GoPay',
            'ovo' => 'OVO',
            'dana' => 'DANA',
            'shopeepay' => 'ShopeePay',
            'other_qris' => 'QRIS',
            'alfamart' => 'Alfamart',
            'indomaret' => 'Indomaret',
            'akulaku' => 'Akulaku',
            'kredivo' => 'Kredivo',
        ][$type] ?? ucwords(str_replace('_', ' ', $type));
    }

    public function toggleActive(Request $request, Payment $payment)
    {
        $old = $payment->toArray();
        $payment->update(['is_active' => !$payment->is_active]);

        $this->activity->log(
            'Payment',
            'Toggle Status',
            'Mengubah status payment ' . $payment->payment_name . ' menjadi ' .
                ($payment->is_active ? 'aktif' : 'nonaktif'),
            $payment,
            $old,
            $payment->fresh()->toArray()
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => (bool) $payment->is_active,
                'message' => 'Status payment berhasil diubah.',
            ]);
        }

        return back()->with(
            'success',
            'Status ' . $payment->payment_name . ' berhasil diubah.'
        );
    }



    public function create()
    {
        return view(
            'admin.payment.create'
        );
    }




    public function store(Request $request)
    {

        $request->validate([

            'payment_name'=>'required',

            'payment_number'=>'required',

            'account_name'=>'required',

            'payment_type'=>'required',

            'image'=>'nullable|image|max:2048'

        ]);



        $image=null;



        if($request->hasFile('image')){


            $image=$request->file('image')
                ->store('payments','public');


        }




        $payment = Payment::create([


            'payment_name'=>$request->payment_name,

            'payment_number'=>$request->payment_number,

            'account_name'=>$request->account_name,

            'payment_type'=>$request->payment_type,

            'image'=>$image,

            'is_active'=>$request->has('is_active')


        ]);
$this->activity->log(
    'Payment',
    'Create',
    'Create payment : '.$payment->payment_name,
    $payment,
    null,
    $payment->toArray()
);


        return redirect()
        ->route('admin.payment.index')
        ->with(
            'success',
            'Metode pembayaran berhasil ditambahkan'
        );


    }





    public function edit(Payment $payment)
    {

        return view(
            'admin.payment.edit',
            compact('payment')
        );

    }





    public function update(Request $request, Payment $payment)
    {


        $request->validate([

            'payment_name'=>'required',

            'payment_number'=>'required',

            'account_name'=>'required',

            'payment_type'=>'required',

            'image'=>'nullable|image|max:2048'


        ]);
$old = $payment->toArray();



        $image=$payment->image;



        if($request->hasFile('image')){


            $image=$request->file('image')
                ->store('payments','public');


        }





        $payment->update([


            'payment_name'=>$request->payment_name,

            'payment_number'=>$request->payment_number,

            'account_name'=>$request->account_name,

            'payment_type'=>$request->payment_type,

            'image'=>$image,

            'is_active'=>$request->has('is_active')


        ]);

$this->activity->log(
    'Payment',
    'Update',
    'Update payment : '.$payment->payment_name,
    $payment,
    $old,
    $payment->fresh()->toArray()
);

        return redirect()
        ->route('admin.payment.index')
        ->with(
            'success',
            'Metode pembayaran berhasil diperbarui'
        );


    }





    public function destroy(Payment $payment)
    {

$old = $payment->toArray();
$this->activity->log(
    'Payment',
    'Delete',
    'Delete payment : '.$payment->payment_name,
    $payment,
    $payment->toArray(),
    null
);
        $payment->delete();


        return back()
        ->with(
            'success',
            'Metode pembayaran berhasil dihapus'
        );

    }


}