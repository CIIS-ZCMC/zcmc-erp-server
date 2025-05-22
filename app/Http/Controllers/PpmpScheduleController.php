<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpmpScheduleRequest;
use App\Models\PpmpSchedule;
use Illuminate\Http\Request;

class PpmpScheduleController extends Controller
{

    public function index()
    {
        //
    }

    public function store(PpmpScheduleRequest $request)
    {
        $data = new PpmpSchedule;
        $data->ppmp_item_id = $request->ppmp_item_id;
        $data->month = $request->month;
        $data->year = now()->addYear()->year;
        $data->quantity = $request->quantity;
        $data->save();
    }

    public function show(PpmpSchedule $ppmpSchedule)
    {
        //
    }

    public function update(Request $request, PpmpSchedule $ppmpSchedule)
    {
        //
    }

    public function destroy(PpmpSchedule $ppmpSchedule)
    {
        //
    }
}
