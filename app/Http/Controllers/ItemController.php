<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function create(Category $category)
    {
        return view('items.create', compact('category'));
    }

    public function store(Request $request, Category $category)
    {
        $data = $request->validate([
            'description' => 'required|string|max:255',
            'order' => 'nullable|integer',
        ]);
        
        $item = $category->items()->create($data);
        
        return redirect()->route('categories.show', $category)
            ->with('success', 'Item "' . $item->description . '" adicionado com sucesso!');
    }
}
