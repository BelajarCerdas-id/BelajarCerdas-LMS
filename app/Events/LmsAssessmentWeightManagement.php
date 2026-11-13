<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LmsAssessmentWeightManagement
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    public $tipe_model; // db SchoolAssessmentWeight
    public $action; // macam" action CRUD SchoolAssessmentWeight (create, update, delete, activate)
    public $data; // isi data setap model (db)
    public function __construct($tipe_model, $action, $data)
    {
        $this->tipe_model = $tipe_model;
        $this->action = $action;
        $this->data = $data;
    }

    public function broadcastAs(): string
    {
        return 'lms.assessment.weight';
    }
}
