<?php

namespace App\Mail;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewTaskCreated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param Task   $task        Die neu erstellte Aufgabe (inkl. project & assignedUser geladen)
     * @param bool   $isAssigned  true = direkt zugewiesen, false = nicht zugewiesen (an alle)
     */
    public function __construct(
        public readonly Task $task,
        public readonly bool $isAssigned,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->isAssigned
            ? 'Neue Aufgabe für dich: ' . $this->task->title
            : 'Neue unzugewiesene Aufgabe: ' . $this->task->title;

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-task-created',
            with: [
                'task'       => $this->task,
                'isAssigned' => $this->isAssigned,
            ],
        );
    }
}
