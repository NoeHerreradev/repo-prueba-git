@extends('portal.layout')

@section('title', $property->title.' · '.App\Models\Setting::get('company_name'))
@section('description', Str::limit(strip_tags($property->description ?? $property->title), 155))

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <nav aria-label="Migas de pan" class="text-sm text-slate-500">
            <a href="{{ route('portal.home') }}" class="hover:text-[var(--brand)]">Inicio</a>
            <span class="mx-2">/</span>
            <a href="{{ route('portal.index') }}" class="hover:text-[var(--brand)]">Propiedades</a>
            <span class="mx-2">/</span>
            <span class="text-slate-700">{{ $property->code }}</span>
        </nav>

        <div class="mt-6 grid gap-8 lg:grid-cols-[1fr_360px]">
            <div>
                @php $images = $property->images; @endphp

                <div class="overflow-hidden rounded-2xl bg-slate-100">
                    <img src="{{ $property->coverUrl() }}" alt="{{ $property->title }}"
                         class="aspect-[16/10] w-full object-cover">
                </div>

                @if ($images->count() > 1)
                    <div class="mt-3 grid grid-cols-4 gap-3">
                        @foreach ($images as $image)
                            <a href="{{ Storage::disk('public')->url($image->path) }}" target="_blank" rel="noopener"
                               class="overflow-hidden rounded-lg bg-slate-100 ring-slate-300 transition hover:ring-2">
                                <img src="{{ Storage::disk('public')->url($image->path) }}"
                                     alt="{{ $image->caption ?? $property->title }}"
                                     loading="lazy"
                                     class="aspect-[4/3] w-full object-cover">
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="mt-8">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-[var(--brand-tint)] px-3 py-1 text-xs font-semibold text-[var(--brand-dark)]">
                            {{ $property->operation->getLabel() }}
                        </span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                            {{ $property->type->getLabel() }}
                        </span>
                        @if ($property->status === \App\Enums\PropertyStatus::Reservado)
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                Reservado
                            </span>
                        @endif
                        <span class="text-xs text-slate-400">Ref. {{ $property->code }}</span>
                    </div>

                    <h1 class="mt-3 text-3xl font-bold tracking-tight">{{ $property->title }}</h1>
                    <p class="mt-1 text-slate-600">{{ $property->publicAddress() }}</p>

                    <p class="mt-4 text-3xl font-bold text-[var(--brand)]">
                        {{ $property->displayPrice() ?? 'Precio a consultar' }}
                    </p>
                    @if ($property->maintenance_fee)
                        <p class="text-sm text-slate-500">
                            + ${{ number_format((float) $property->maintenance_fee, 0, ',', '.') }} de gastos comunes
                        </p>
                    @endif
                </div>

                <dl class="mt-8 grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-slate-200 bg-slate-200 sm:grid-cols-4">
                    @foreach ([
                        'Habitaciones' => $property->bedrooms,
                        'Baños' => $property->bathrooms,
                        'Estacionamientos' => $property->parking_spaces,
                        'Área construida' => $property->area_built ? (int) $property->area_built.' m²' : null,
                        'Área del terreno' => $property->area_lot ? (int) $property->area_lot.' m²' : null,
                        'Año' => $property->year_built,
                        'Piso' => $property->floor,
                    ] as $label => $value)
                        @if ($value)
                            <div class="bg-white p-4">
                                <dt class="text-xs uppercase tracking-wide text-slate-500">{{ $label }}</dt>
                                <dd class="mt-1 text-lg font-semibold">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>

                @if ($property->description)
                    <section class="mt-10">
                        <h2 class="text-xl font-bold">Descripción</h2>
                        <p class="mt-3 whitespace-pre-line leading-relaxed text-slate-700">
                            {{ $property->description }}
                        </p>
                    </section>
                @endif

                @if ($property->amenities->isNotEmpty())
                    <section class="mt-10">
                        <h2 class="text-xl font-bold">Amenidades</h2>
                        <ul class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ($property->amenities as $amenity)
                                <li class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                                    <span class="text-[var(--brand)]" aria-hidden="true">✓</span>
                                    {{ $amenity->name }}
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($property->latitude && $property->longitude)
                    <section class="mt-10">
                        <h2 class="text-xl font-bold">Ubicación</h2>
                        <p class="mt-2 text-sm text-slate-600">{{ $property->publicAddress() }}</p>
                        <a href="https://www.openstreetmap.org/?mlat={{ $property->latitude }}&mlon={{ $property->longitude }}#map=16/{{ $property->latitude }}/{{ $property->longitude }}"
                           target="_blank" rel="noopener"
                           class="mt-3 inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                            Ver en el mapa →
                        </a>
                    </section>
                @endif
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
                @if ($property->agent)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Asesor a cargo</p>
                        <p class="mt-1 text-lg font-semibold">{{ $property->agent->name }}</p>
                        @if ($property->agent->phone)
                            <a href="tel:{{ $property->agent->phone }}"
                               class="mt-3 block rounded-lg border border-slate-300 px-4 py-2 text-center text-sm font-medium transition hover:bg-slate-50">
                                {{ $property->agent->phone }}
                            </a>
                        @endif
                    </div>
                @endif

                <x-inquiry-form :property="$property" :compact="true" />
            </aside>
        </div>

        @if ($similar->isNotEmpty())
            <section class="mt-16">
                <h2 class="text-2xl font-bold tracking-tight">Propiedades similares</h2>
                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($similar as $item)
                        <x-property-card :property="$item" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
