<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        return view('categories.index', ['categories' => Category::when($request->type, fn ($q, $v) => $q->where('type', $v))->orderBy('type')->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('categories')->where('type', $request->type)], 'type' => ['required', Rule::in(['income', 'expense'])]]);
        Category::create($data);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $rules = ['name' => ['required', 'max:255', Rule::unique('categories')->where('type', $request->type)->ignore($category)], 'type' => ['required', Rule::in(['income', 'expense'])]];
        $data = $request->validate($rules);
        if ($category->is_used && $data['type'] !== $category->type) {
            return back()->withErrors(['type' => 'Tipe kategori yang sudah digunakan tidak dapat diubah.']);
        }
        $category->update($data);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->is_used) {
            return back()->withErrors(['category' => 'Kategori tidak dapat dihapus karena sudah digunakan.']);
        }
        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
