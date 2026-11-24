<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FacilityController extends Controller
{
    public function index(): View
    {
        $facilities = Facility::query()
            ->withCount('rooms')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.facilities.index', compact('facilities'));
    }

    public function create(): View
    {
        $facility = new Facility;

        return view('admin.facilities.form', compact('facility'));
    }

    public function store(Request $request): RedirectResponse
    {
        Facility::create($this->validatedData($request));

        return redirect()->route('admin.facilities.index')->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    public function edit(Facility $facility): View
    {
        return view('admin.facilities.form', compact('facility'));
    }

    public function update(Request $request, Facility $facility): RedirectResponse
    {
        $facility->update($this->validatedData($request, $facility));

        return redirect()->route('admin.facilities.index')->with('success', 'Fasilitas berhasil diperbarui.');
    }

    public function destroy(Facility $facility): RedirectResponse
    {
        $facility->delete();

        return redirect()->route('admin.facilities.index')->with('success', 'Fasilitas berhasil dihapus.');
    }

    /**
     * @return array{name: string, icon: ?string, description: ?string}
     */
    private function validatedData(Request $request, ?Facility $facility = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('facilities', 'name')->ignore($facility)],
            'icon' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
