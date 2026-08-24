@extends('portal.layout')

@section('title', 'Propiedades · '.App\Models\Setting::get('company_name'))

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold tracking-tight">Propiedades</h1>
        <p class="mt-1 text-slate-600">
            {{ $properties->total() }} {{ Str::plural('inmueble', $properties->total()) }}
            {{ Str::plural('disponible', $properties->total()) }}
        </p>

        <div class="mt-8 grid gap-8 lg:grid-cols-[280px_1fr]">
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <form method="GET" action="{{ route('portal.index') }}"
                      class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div>
                        <label for="q" class="block text-sm font-medium text-slate-700">Buscar</label>
                        <input type="search" id="q" name="q" value="{{ request('q') }}"
                               placeholder="Barrio, ciudad, referencia…"
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]">
                    </div>

                    <div>
                        <label for="operation" class="block text-sm font-medium text-slate-700">Operación</label>
                        <select id="operation" name="operation"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]">
                            <option value="">Todas</option>
                            <option value="venta" @selected(request('operation') === 'venta')>Venta</option>
                            <option value="alquiler" @selected(request('operation') === 'alquiler')>Alquiler</option>
                        </select>
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-slate-700">Tipo</label>
                        <select id="type" name="type"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]">
                            <option value="">Todos</option>
                            @foreach (App\Enums\PropertyType::cases() as $type)
                                <option value="{{ $type->value }}" @selected(request('type') === $type->value)>
                                    {{ $type->getLabel() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-medium text-slate-700">Ciudad</label>
                        <select id="city" name="city"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]">
                            <option value="">Todas</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city }}" @selected(request('city') === $city)>{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="bedrooms" class="block text-sm font-medium text-slate-700">Habitaciones (mín.)</label>
                        <select id="bedrooms" name="bedrooms"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]">
                            <option value="">Cualquiera</option>
                            @foreach ([1, 2, 3, 4, 5] as $n)
                                <option value="{{ $n }}" @selected((string) request('bedrooms') === (string) $n)>{{ $n }}+</option>
                            @endforeach
                        </select>
                    </div>

                    <fieldset>
                        <legend class="block text-sm font-medium text-slate-700">Precio (USD)</legend>
                        <div class="mt-1 grid grid-cols-2 gap-2">
                            <input type="number" name="price_min" value="{{ request('price_min') }}" placeholder="Desde" min="0"
                                   aria-label="Precio desde"
                                   class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]">
                            <input type="number" name="price_max" value="{{ request('price_max') }}" placeholder="Hasta" min="0"
                                   aria-label="Precio hasta"
                                   class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]">
                        </div>
                    </fieldset>

                    <div>
                        <label for="sort" class="block text-sm font-medium text-slate-700">Ordenar por</label>
                        <select id="sort" name="sort"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]">
                            <option value="">Más relevantes</option>
                            <option value="price_asc" @selected(request('sort') === 'price_asc')>Precio: menor a mayor</option>
                            <option value="price_desc" @selected(request('sort') === 'price_desc')>Precio: mayor a menor</option>
                            <option value="area_desc" @selected(request('sort') === 'area_desc')>Mayor superficie</option>
                        </select>
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button type="submit"
                                class="flex-1 rounded-lg bg-[var(--brand)] px-4 py-2 text-sm font-semibold text-[var(--brand-contrast)] transition hover:bg-[var(--brand-dark)]">
                            Filtrar
                        </button>
                        <a href="{{ route('portal.index') }}"
                           class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                            Limpiar
                        </a>
                    </div>
                </form>
            </aside>

            <div>
                @if ($properties->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-300 p-12 text-center">
                        <p class="text-lg font-semibold text-slate-700">No encontramos inmuebles con esos criterios</p>
                        <p class="mt-2 text-slate-500">Prueba a ampliar el rango de precio o quitar algún filtro.</p>
                        <a href="{{ route('portal.index') }}"
                           class="mt-6 inline-block rounded-lg bg-[var(--brand)] px-4 py-2 text-sm font-semibold text-[var(--brand-contrast)] hover:bg-[var(--brand-dark)]">
                            Ver todas las propiedades
                        </a>
                    </div>
                @else
                    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($properties as $property)
                            <x-property-card :property="$property" />
                        @endforeach
                    </div>

                    <div class="mt-10">
                        {{ $properties->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
