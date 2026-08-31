<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Services\TransactionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $service) {}

    private function type(Request $request): string
    {
        return $request->routeIs('incomes.*') ? 'income' : 'expense';
    }

    private function model(string $type): string
    {
        return $type === 'income' ? Income::class : Expense::class;
    }

    public function index(Request $request)
    {
        $type = $this->type($request);
        $class = $this->model($type);
        $items = $class::with('category')->withSum('details', 'amount')
            ->when($request->start_date, fn ($q, $v) => $q->whereDate('date', '>=', $v))
            ->when($request->end_date, fn ($q, $v) => $q->whereDate('date', '<=', $v))
            ->when($request->category_id, fn ($q, $v) => $q->where('category_id', $v))->latest('date')->latest('id')->paginate(12)->withQueryString();

        return view('transactions.index', compact('type', 'items') + ['categories' => Category::whereType($type)->orderBy('name')->get()]);
    }

    public function create(Request $request)
    {
        return $this->form($this->type($request));
    }

    public function edit(Request $request, Model $transaction)
    {
        return $this->form($this->type($request), $transaction->load('details'));
    }

    private function form(string $type, ?Model $transaction = null)
    {
        return view('transactions.form', compact('type', 'transaction') + ['categories' => Category::whereType($type)->orderBy('name')->get()]);
    }

    private function validated(Request $request, string $type): array
    {
        return $request->validate([
            'date' => ['required', 'date'],
            'category_id' => ['required', Rule::exists('categories', 'id')->where('type', $type)],
            'description' => ['nullable', 'string', 'max:1000'], 'details' => ['required', 'array', 'min:1'],
            'details.*.name' => ['required', 'string', 'max:255'], 'details.*.amount' => ['required', 'integer', 'min:1'],
            'details.*.note' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    public function store(Request $request)
    {
        $type = $this->type($request);
        $item = $this->service->save($type, $this->validated($request, $type));

        return redirect()->route($type.'s.show', $item)->with('success', 'Transaksi berhasil disimpan.');
    }

    public function update(Request $request, Model $transaction)
    {
        $type = $this->type($request);
        $this->service->save($type, $this->validated($request, $type), $transaction);

        return redirect()->route($type.'s.show', $transaction)->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function show(Request $request, Model $transaction)
    {
        return view('transactions.show', ['type' => $this->type($request), 'transaction' => $transaction->load('category', 'details')]);
    }

    public function destroy(Request $request, Model $transaction)
    {
        $type = $this->type($request);
        $this->service->delete($transaction);

        return redirect()->route($type.'s.index')->with('success', 'Transaksi berhasil dihapus.');
    }
}
