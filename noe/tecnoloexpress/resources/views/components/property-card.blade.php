@props(['property'])

<article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
    <a href="{{ route('portal.show', $property) }}" class="block">
        <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
            <img src="{{ $property->coverUrl() }}"
                 alt="{{ $property->title }}"
                 loading="lazy"
                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105">

            <div class="absolute left-3 top-3 flex gap-2">
                <span class="rounded-full bg-white/95 px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-sm">
                    {{ $property->operation->getLabel() }}
                </span>
                @if ($property->status === \App\Enums\PropertyStatus::Reservado)
                    <span class="rounded-full bg-amber-500 px-2.5 py-1 text-xs font-semibold text-white shadow-sm">
                        Reservado
                    </span>
                @endif
            </div>

            @if ($property->featured)
                <span class="absolute right-3 top-3 rounded-full bg-[var(--brand)] px-2.5 py-1 text-xs font-semibold text-[var(--brand-contrast)] shadow-sm">
                    Destacado
                </span>
            @endif
        </div>
    </a>

    <div class="p-4">
        <p class="text-xs font-medium uppercase tracking-wide text-[var(--brand)]">
            {{ $property->type->getLabel() }} · {{ $property->code }}
        </p>

        <h3 class="mt-1 line-clamp-2 text-base font-semibold leading-snug">
            <a href="{{ route('portal.show', $property) }}" class="hover:text-[var(--brand)]">
                {{ $property->title }}
            </a>
        </h3>

        <p class="mt-1 line-clamp-1 text-sm text-slate-500">{{ $property->publicAddress() }}</p>

        <p class="mt-3 text-xl font-bold text-slate-900">{{ $property->displayPrice() ?? 'Consultar' }}</p>

        <dl class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 border-t border-slate-100 pt-3 text-sm text-slate-600">
            @if ($property->bedrooms)
                <div class="flex items-center gap-1">
                    <span aria-hidden="true">🛏</span>
                    <dt class="sr-only">Habitaciones</dt>
                    <dd>{{ $property->bedrooms }}</dd>
                </div>
            @endif
            @if ($property->bathrooms)
                <div class="flex items-center gap-1">
                    <span aria-hidden="true">🛁</span>
                    <dt class="sr-only">Baños</dt>
                    <dd>{{ $property->bathrooms }}</dd>
                </div>
            @endif
            @if ($property->parking_spaces)
                <div class="flex items-center gap-1">
                    <span aria-hidden="true">🚗</span>
                    <dt class="sr-only">Estacionamientos</dt>
                    <dd>{{ $property->parking_spaces }}</dd>
                </div>
            @endif
            @if ($property->area_built)
                <div class="flex items-center gap-1">
                    <span aria-hidden="true">📐</span>
                    <dt class="sr-only">Área construida</dt>
                    <dd>{{ (int) $property->area_built }} m²</dd>
                </div>
            @endif
        </dl>
    </div>
</article>
