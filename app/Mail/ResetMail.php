<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;

class ResetMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->id = $request->user_id;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $frontendURL = "http://18001187.pupu.jp/untitled";
        return $this->subject('パスワードリセットメール')
            ->markdown('emails.activations.reset')
            ->with([
                'link' => $frontendURL . "/public/reset/".$this->id
            ]);
    }
}