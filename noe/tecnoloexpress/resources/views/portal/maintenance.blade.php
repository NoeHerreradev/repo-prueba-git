@php
    use App\Models\Setting;
    use App\Support\Brand;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>En mantenimiento · {{ Setting::get('company_name') }}</title>
    @if ($favicon = Setting::url('favicon_path'))
        <link rel="icon" href="{{ $favicon }}">
    @endif
    <style>
        :root {
            --brand: {{ Brand::color() }};
            --brand-dark: {{ Brand::dark() }};
            --brand-contrast: {{ Brand::contrast() }};
        }
    </style>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 px-4 font-sans text-slate-900 antialiased">
    <div class="w-full max-w-md text-center">
        @if ($logo = Setting::url('logo_path'))
            <img src="{{ $logo }}" alt="{{ Setting::get('company_name') }}" class="mx-auto h-12 w-auto object-contain">
        @else
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-[var(--brand)] text-lg font-bold text-[var(--brand-contrast)]">
                {{ Brand::initials() }}
            </span>
        @endif

        <h1 class="mt-6 text-2xl font-bold tracking-tight">{{ Setting::get('company_name') }}</h1>

        <p class="mt-3 text-slate-600">{{ Setting::get('maintenance_message') }}</p>

        @if ($phone = Setting::get('company_phone'))
            <p class="mt-8 text-sm text-slate-500">Mientras tanto puedes llamarnos:</p>
            <a href="tel:{{ $phone }}"
               class="mt-2 inline-block rounded-lg bg-[var(--brand)] px-5 py-2.5 text-sm font-semibold text-[var(--brand-contrast)] transition hover:bg-[var(--brand-dark)]">
                {{ $phone }}
            </a>
        @endif
    </div>
</body>
</html>
