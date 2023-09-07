<?php

namespace App\Http\Controllers;

use App\Http\Requests\UuidRequest;
use App\Interfaces\PaymentServiceInterface;
use App\Models\Order;
use App\Models\Price;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\TinkoffPaymentService;
use Carbon\Carbon;
use Log;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        public PaymentServiceInterface $paymentService = new TinkoffPaymentService(),
    ) {}

    public function redirect(UuidRequest $request)
    {
        $user = User::findOrFail($request->id);

        if (!in_array($request->order_type, [Order::NORMAL, Order::VIP, Order::PREMIUM])) {
            abort(422, "Must be in: " . print_r([Order::NORMAL, Order::VIP, Order::PREMIUM], true));
        }

        //TODO Transaction
        $order = Order::create([
                                   'order_status' => Order::CREATED,
                                   'order_type'   => $request->order_type,
                                   'price'        => Price::where(['locale' => 'ru'])->first()?->{'price_' . $request->order_type} ?? 9999,
                                   'duration'     => Price::where(['locale' => 'ru'])->first()?->{'duration_' . $request->order_type} ?? 1
                               ]);

        list($paymentUrl, $paymentId) = $this->paymentService->getPaymentUrl($order);

        if (empty($paymentId)) {
            Log::alert('OrderId:' . $order->id . ' No payment url. Check payment provider');
            return redirect($paymentUrl);
        }


        $order->user()->associate($user);
        $order->payment_id = $paymentId;
        $order->save();

        return redirect($paymentUrl);
    }

    public function success(UuidRequest $request)
    {
        $order = Order::where(['order_status' => Order::CREATED])->findOrFail($request->id);

        list($paymentSuccess, $paymentAmount) = $this->paymentService->getPaymentState($order);

        $cents      = 100;
        $priceTotal = $order->price * $cents;

        if ($paymentSuccess !== true || $priceTotal < $paymentAmount) {
            $message = 'OrderId: ' . $order->id . ' Payment status failed or small payed amount. Payment/Amount: ' . $paymentSuccess . ' / ' . $paymentAmount;
            Log::alert($message);
            NotificationService::notifyAdmin($message);

            return redirect(
                config('front-end.payment_failed')
                . $order->id
                . config('front-end.payment_status_failed')
                . __('order.payment_failed')
            );
        }

        $duration = Price::where(['locale' => 'ru'])->first()?->{'duration_' . $order->order_type} ?? 1;


        $user                          = $order->user;
        $user->subscription_created_at = now();
        $user->subscription_expires_at = Carbon::parse($user->subscription_expires_at ?? now())->addDays($duration)->format('Y-m-d H:i:s');
        $user->save();

        $order->order_status = Order::PAYED;
        $order->save();

        return redirect(config('front-end.payment_success') . $order->id);
    }

    public function failed(UuidRequest $request)
    {
        $order               = Order::where(['order_status' => Order::CREATED])->findOrFail($request->id);
        $order->order_status = Order::CANCELED;
        $order->save();

        $message = 'Payment failed! ' . 'OrderId:' . $order->id;
        Log::info($message);
        NotificationService::notifyAdmin($message);

        return redirect(
            config('front-end.payment_failed')
            . $order->id
            . config('front-end.payment_status_failed')
            . __('order.payment_error')
        );
    }
}
