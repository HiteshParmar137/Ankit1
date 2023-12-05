<?php

namespace App\Mail;

use App\Models\BookReadRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookMail extends Mailable
{
    use Queueable, SerializesModels;

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
