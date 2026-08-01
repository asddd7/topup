<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends BaseAdminController
{

    public function index()
    {

        $payments = Payment::latest()->get();


        return view(
            'admin.payment.index',
            compact('payments')
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