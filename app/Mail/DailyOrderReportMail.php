<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class DailyOrderReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reportData;

    /**
     * Create a new message instance.
     */
    public function __construct($reportData)
    {
        $this->reportData = $reportData;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Daily Orders Report')
            ->view('emails.daily_order_report')
            ->with('data', $this->reportData);
    }
}
