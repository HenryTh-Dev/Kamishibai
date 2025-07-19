<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $category = Category::create($data);
        
        return redirect()->route('categories.index')
            ->with('success', 'Categoria "' . $category->name . '" criada com sucesso!');
    }

    public function show(Category $category)
    {
        $category->load('items');
        return view('categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $category->update($data);
        
        return redirect()->route('categories.show', $category)
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(Category $category)
    {
        $categoryName = $category->name;
        $category->delete();
        
        return redirect()->route('categories.index')
            ->with('success', 'Categoria "' . $categoryName . '" excluída com sucesso!');
    }
}
