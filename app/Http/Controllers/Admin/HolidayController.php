<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Traits\LogsUserActivity;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    use LogsUserActivity;

    public function index()
    {
        $holidays = Holiday::orderBy('date')->get();
        
        return view('admin.holidays.index', compact('holidays'));
    }

    public function create()
    {
        return view('admin.holidays.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'is_recurring' => 'boolean'
        ]);
        
        Holiday::create([
            'name' => $request->name,
            'date' => $request->date,
            'is_recurring' => $request->is_recurring ?? false
        ]);
        
        $this->logUserActivity('Created holiday: ' . $request->name);
        
        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday created successfully.');
    }

    public function edit(Holiday $holiday)
    {
        return view('admin.holidays.edit', compact('holiday'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'is_recurring' => 'boolean'
        ]);
        
        $holiday->update([
            'name' => $request->name,
            'date' => $request->date,
            'is_recurring' => $request->is_recurring ?? false
        ]);
        
        $this->logUserActivity('Updated holiday: ' . $request->name);
        
        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday)
    {
        $this->logUserActivity('Deleted holiday: ' . $holiday->name);
        
        $holiday->delete();
        
        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday deleted successfully.');
    }
}