@props(['property' => null, 'compact' => false])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white p-6 shadow-sm']) }}>
    @if (session('inquiry_sent'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            <p class="font-semibold">¡Gracias por tu consulta!</p>
            <p class="mt-1">Un asesor se pondrá en contacto contigo a la brevedad.</p>
        </div>
    @else
        <h3 class="text-lg font-semibold">
            {{ $property ? 'Solicita información de este inmueble' : 'Cuéntanos qué estás buscando' }}
        </h3>
        <p class="mt-1 text-sm text-slate-500">
            Responde un asesor especializado, sin compromiso.
        </p>

        @php
            $formKey = $property ? 'property_inquiry' : 'portal_contact';
            $customForm = \App\Models\CustomForm::getByKey($formKey);
            $hasBlocks = $customForm && $customForm->is_active && !empty($customForm->blocks);
        @endphp

        <form method="POST" action="{{ route('portal.inquire') }}" class="mt-5 space-y-4">
            @csrf

            @if ($property)
                <input type="hidden" name="property_id" value="{{ $property->id }}">
            @endif

            {{-- Trampa antispam: oculta para personas, visible para bots. --}}
            <div class="hidden" aria-hidden="true">
                <label for="website">No rellenar</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            @if ($hasBlocks)
                <x-dynamic-form-blocks :blocks="$customForm->blocks" :compact="$compact" />
            @else
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Nombre *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="{{ $compact ? 'space-y-4' : 'grid gap-4 sm:grid-cols-2' }}">
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]">
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-700">Teléfono</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]">
                        @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-slate-700">Mensaje</label>
                    <textarea id="message" name="message" rows="4"
                              placeholder="{{ $property ? 'Me interesa agendar una visita…' : 'Busco un departamento de 2 dormitorios en…' }}"
                              class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]">{{ old('message') }}</textarea>
                    @error('message') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            @endif

            <button type="submit"
                    class="w-full rounded-lg bg-[var(--brand)] px-4 py-2.5 text-sm font-semibold text-[var(--brand-contrast)] shadow-sm transition hover:bg-[var(--brand-dark)] focus:outline-none focus:ring-2 focus:ring-[var(--brand)] focus:ring-offset-2">
                Enviar consulta
            </button>

            <p class="text-xs text-slate-400">Indica al menos un email o un teléfono de contacto.</p>
        </form>
    @endif
</div>
