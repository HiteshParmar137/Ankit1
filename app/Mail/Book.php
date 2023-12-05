<?php

namespace App\Mail;

use App\Models\BookReadRequest;
use App\Models\Leaves;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Log;

class Book extends Mailable
{
    use Queueable, SerializesModels;
    // protected $data;
    // protected $type;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(protected $data, protected $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
        if ($this->type == BookReadRequest::TYPE) {
            return $this->from($this->data->user->email)
                ->subject('Book Read Request')
                ->view('emails.book_read_request_mail')
                ->with([
                    'bookReadRequest' => $this->data,
                ]);
        } else {
            return $this->from($this->data->user->email)
                ->subject('New Book Request')
                ->view('emails.new_book_request_mail')
                ->with([
                    'book' => $this->data,
                ]);
        }
    }
}
