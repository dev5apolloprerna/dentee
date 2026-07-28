<?php

namespace App\Exports;

use App\Models\Patient;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Http\Request;

class PatientExport implements FromView
{
    protected $patients;

    public function __construct($patients)
    {
        $this->patients = $patients;
    }

    public function view(): View
    {
        return view('exports.patients', [
            'patients' => $this->patients
        ]);
    }
}
