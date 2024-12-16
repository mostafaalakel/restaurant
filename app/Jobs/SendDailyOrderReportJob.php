<?php

namespace App\Jobs;

use App\Mail\DailyOrderReportMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDailyOrderReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $today = now()->toDateString();

        $orders = Order::whereDate('created_at', $today)->get();

        $reportData = [
            'date' => $today,
            'totalOrders' => $orders->count(),
            'totalPrice' => $orders->sum('price_after_discounts'),
            'deliveredOrders' => $orders->where('order_status', 'delivered')->count(),
            'paidOrders' => $orders->where('payment_status', 'paid')->count(),
        ];

        Mail::to('manager@gmail.com')->send(new DailyOrderReportMail($reportData));
    }
}
