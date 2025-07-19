<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Record;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RecordController extends Controller
{
    public function create(Category $category)
    {
        $items = $category->items()->orderBy('order')->get();
        $days = range(1,31);
        $today = Carbon::today();
        return view('records.create', compact('category', 'items', 'days', 'today'));
    }

    public function store(Request $request, Category $category)
    {
        $validated = $request->validate([
            'records' => 'required|array',
        ]);
        foreach ($validated['records'] as $itemId => $dayStatus) {
            foreach ($dayStatus as $day => $status) {
                if ($status === null || $status === '') {
                    continue;
                }
                Record::updateOrCreate([
                    'item_id' => $itemId,
                    'record_date' => $this->dateFromDay($day, $request->input('month')),
                ], [
                    'user_id' => 1, // placeholder user id
                    'status' => $status,
                ]);
            }
        }
        return redirect()->route('categories.show', $category);
    }

    private function dateFromDay($day, $month)
    {
        $year = date('Y');
        return Carbon::createFromDate($year, $month, $day)->toDateString();
    }
}
