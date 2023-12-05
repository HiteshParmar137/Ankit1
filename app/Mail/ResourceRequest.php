<?php

namespace App\Mail;

use App\Models\ResourceRequest as ModelsResourceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Log;

class ResourceRequest extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(protected $data)
    {
        $this->data = $data;
        Log::info('ResourceRequest -> mailstart');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
        try {
            if ($this->data['mail_type'] == ModelsResourceRequest::MAIL_TYPE) {
                return $this->from($this->data['from_email'])
                    ->subject('Resource Request')
                    ->view('emails.resource_request_mail')
                    ->with([
                        'resourceRequestDetails' => $this->data,
                    ]);
                Log::info('ResourceRequest->end');
            } else {
                return $this->from($this->data['from_email'])
                    ->subject('Resource Request FeedBack')
                    ->view('emails.resource_request_feedback_mail')
                    ->with([
                        'resourceRequestDetails' => $this->data,
                    ]);
            }
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
    }
}
