<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveGuestRequest;
use App\Models\Guest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GuestController extends Controller
{
    public function index(): View
    {
        $guests = Guest::query()->withCount([
            'registrations',
            'registrations as present_count' => fn ($query) => $query->where('attendance_status', 'present'),
            'registrations as no_show_count' => fn ($query) => $query->where('attendance_status', 'no_show'),
        ])->latest('id')->paginate(20);

        return view('guests.index', compact('guests'));
    }

    public function store(SaveGuestRequest $request): RedirectResponse
    {
        Guest::query()->create($request->validated());

        return redirect()->route('guests.index')->with('success', 'Tamu ditambahkan.');
    }

    public function update(SaveGuestRequest $request, Guest $guest): RedirectResponse
    {
        $guest->update($request->validated());

        return redirect()->route('guests.index')->with('success', 'Data tamu diperbarui.');
    }
}
