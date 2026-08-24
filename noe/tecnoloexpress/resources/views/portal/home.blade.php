@extends('portal.layout')

@php
    use App\Models\Setting;
    $heroImage = Setting::url('hero_image_path');
@endphp

@section('content')
    <section class="relative overflow-hidden bg-slate-900">
        @if ($heroImage)
            <img src="{{ $heroImage }}" alt="" aria-hidden="true"
                 class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-slate-900/70"></div>
        @else
            <div class="absolute inset-0"
                 style="background-image: linear-gradient(135deg, var(--brand) 0%, #0f172a 60%, #0f172a 100%)"></div>
        @endif

        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="max-w-2xl">
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl">
                    {{ Setting::get('hero_title') }}
                </h1>
                <p class="mt-4 text-lg text-slate-300">
                    {{ $stats['total'] }} propiedades verificadas. {{ Setting::get('hero_subtitle') }}
                </p>
            </div>

            @if (Setting::bool('hero_show_search'))
                <form method="GET" action="{{ route('portal.index') }}"
                      class="mt-10 grid gap-3 rounded-2xl bg-white p-4 shadow-xl sm:grid-cols-2 lg:grid-cols-5">
                    <div class="lg:col-span-2">
                        <label for="hero-q" class="sr-only">Buscar</label>
                        <input type="search" id="hero-q" name="q" placeholder="Barrio, ciudad o referencia…"
                               class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]">
                    </div>

                    <div>
                        <label for="hero-operation" class="sr-only">Operación</label>
                        <select id="hero-operation" name="operation"
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]">
                            <option value="">Comprar o alquilar</option>
                            <option value="venta">Comprar</option>
                            <option value="alquiler">Alquilar</option>
                        </select>
                    </div>

                    <div>
                        <label for="hero-type" class="sr-only">Tipo</label>
                        <select id="hero-type" name="type"
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]">
                            <option value="">Todo tipo</option>
                            @foreach (App\Enums\PropertyType::cases() as $type)
                                <option value="{{ $type->value }}">{{ $type->getLabel() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit"
                            class="rounded-lg bg-[var(--brand)] px-4 py-2.5 text-sm font-semibold text-[var(--brand-contrast)] transition hover:bg-[var(--brand-dark)]">
                        Buscar
                    </button>
                </form>
            @endif

            @if (Setting::bool('hero_show_stats'))
                <dl class="mt-10 flex flex-wrap gap-x-12 gap-y-4">
                    <div>
                        <dt class="text-sm text-slate-400">En venta</dt>
                        <dd class="text-2xl font-bold text-white">{{ $stats['sale'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-400">En alquiler</dt>
                        <dd class="text-2xl font-bold text-white">{{ $stats['rent'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-400">Ciudades</dt>
                        <dd class="text-2xl font-bold text-white">{{ count($cities) }}</dd>
                    </div>
                </dl>
            @endif
        </div>
    </section>

    @if (Setting::bool('show_featured'))
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">{{ Setting::get('featured_heading') }}</h2>
                    <p class="mt-1 text-slate-600">{{ Setting::get('featured_subheading') }}</p>
                </div>
                <a href="{{ route('portal.index') }}"
                   class="shrink-0 text-sm font-semibold text-[var(--brand)] hover:text-[var(--brand-dark)]">
                    Ver todas →
                </a>
            </div>

            @if ($featured->isEmpty())
                <p class="mt-8 rounded-xl border border-dashed border-slate-300 p-10 text-center text-slate-500">
                    Aún no hay propiedades publicadas.
                </p>
            @else
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featured as $property)
                        <x-property-card :property="$property" />
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    @if (Setting::bool('show_cities') && $cities)
        <section class="border-y border-slate-200 bg-white py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-lg font-semibold">{{ Setting::get('cities_heading') }}</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($cities as $city)
                        <a href="{{ route('portal.index', ['city' => $city]) }}"
                           class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-[var(--brand)] hover:text-[var(--brand)]">
                            {{ $city }}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if (Setting::bool('show_contact'))
        <section id="contacto" class="mx-auto max-w-7xl scroll-mt-20 px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-2">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">{{ Setting::get('contact_heading') }}</h2>
                    <p class="mt-3 text-slate-600">{{ Setting::get('contact_text') }}</p>

                    @if ($benefits = Setting::list('benefits'))
                        <ul class="mt-6 space-y-3 text-sm text-slate-700">
                            @foreach ($benefits as $benefit)
                                <li class="flex gap-3">
                                    <span class="text-[var(--brand)]" aria-hidden="true">✓</span>
                                    {{ $benefit }}
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($ownerTitle = Setting::get('owner_cta_title'))
                        <div class="mt-8 rounded-xl bg-slate-100 p-5 text-sm">
                            <p class="font-semibold">{{ $ownerTitle }}</p>
                            <p class="mt-1 text-slate-600">
                                {{ Setting::get('owner_cta_text') }}
                                @if ($email = Setting::get('company_email'))
                                    <a href="mailto:{{ $email }}" class="font-medium text-[var(--brand)] hover:underline">
                                        {{ $email }}
                                    </a>
                                @endif
                            </p>
                        </div>
                    @endif
                </div>

                <x-inquiry-form />
            </div>
        </section>
    @endif
@endsection
