<?php

namespace App\Http\Controllers;

use App\Enums\PropertyOperation;
use App\Enums\PropertyType;
use App\Models\Property;
use App\Models\Setting;
use App\Services\LeadCapture;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function home(): View
    {
        return view('portal.home', [
            'featured' => Property::published()
                ->with('images')
                ->orderByDesc('featured')
                ->orderByDesc('created_at')
                ->limit(Setting::int('featured_count', 6))
                ->get(),
            'cities' => $this->cities(),
            'stats' => [
                'total' => Property::published()->count(),
                'sale' => Property::published()->whereIn('operation', [PropertyOperation::Venta, PropertyOperation::Ambos])->count(),
                'rent' => Property::published()->whereIn('operation', [PropertyOperation::Alquiler, PropertyOperation::Ambos])->count(),
            ],
        ]);
    }

    public function index(Request $request): View
    {
        $properties = Property::published()
            ->with('images')
            ->when($request->string('q')->trim()->value(), fn (Builder $q, string $term) => $q->where(
                fn (Builder $sub) => $sub
                    ->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('neighborhood', 'like', "%{$term}%")
                    ->orWhere('city', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%"),
            ))
            ->when($request->filled('operation'), fn (Builder $q) => $q->where(
                fn (Builder $sub) => $sub
                    ->where('operation', $request->string('operation'))
                    ->orWhere('operation', PropertyOperation::Ambos),
            ))
            ->when($request->filled('type'), fn (Builder $q) => $q->where('type', $request->string('type')))
            ->when($request->filled('city'), fn (Builder $q) => $q->where('city', $request->string('city')))
            ->when($request->filled('bedrooms'), fn (Builder $q) => $q->where('bedrooms', '>=', $request->integer('bedrooms')))
            ->when($request->filled('price_min'), fn (Builder $q) => $q->where('price', '>=', $request->integer('price_min')))
            ->when($request->filled('price_max'), fn (Builder $q) => $q->where('price', '<=', $request->integer('price_max')))
            ->when($request->filled('sort'), fn (Builder $q) => match ($request->string('sort')->value()) {
                'price_asc' => $q->orderBy('price'),
                'price_desc' => $q->orderByDesc('price'),
                'area_desc' => $q->orderByDesc('area_built'),
                default => $q->orderByDesc('created_at'),
            }, fn (Builder $q) => $q->orderByDesc('featured')->orderByDesc('created_at'))
            ->paginate(9)
            ->withQueryString();

        return view('portal.index', [
            'properties' => $properties,
            'cities' => $this->cities(),
        ]);
    }

    public function show(Property $property): View
    {
        abort_unless($property->published, 404);

        $property->increment('views_count');

        return view('portal.show', [
            'property' => $property->load(['images', 'amenities', 'agent']),
            'similar' => Property::published()
                ->where('id', '!=', $property->id)
                ->where('city', $property->city)
                ->where('type', $property->type)
                ->with('images')
                ->limit(3)
                ->get(),
        ]);
    }

    public function inquire(Request $request, LeadCapture $leads): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:50', 'required_without:email'],
            'message' => ['nullable', 'string', 'max:2000'],
            'property_id' => ['nullable', 'exists:properties,id'],
            // Campo trampa: los bots lo rellenan, las personas no lo ven.
            'website' => ['nullable', 'size:0'],
        ], [
            'email.required_without' => 'Indica un email o un teléfono para poder contactarte.',
            'phone.required_without' => 'Indica un teléfono o un email para poder contactarte.',
        ]);

        $property = isset($data['property_id']) ? Property::find($data['property_id']) : null;

        $leads->capture($data, $property);

        return back()->with('inquiry_sent', true);
    }

    /** @return array<string, string> */
    protected function cities(): array
    {
        return Property::published()
            ->whereNotNull('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city', 'city')
            ->all();
    }
}
