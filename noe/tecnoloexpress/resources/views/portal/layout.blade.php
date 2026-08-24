@php
    use App\Models\Setting;
    use App\Support\Brand;

    $company = Setting::get('company_name');
    $logo = Setting::url('logo_path');
    $favicon = Setting::url('favicon_path');
    $whatsapp = preg_replace('/\D/', '', (string) Setting::get('company_whatsapp'));

    $socials = array_filter([
        'Facebook' => Setting::get('facebook_url'),
        'Instagram' => Setting::get('instagram_url'),
        'TikTok' => Setting::get('tiktok_url'),
        'YouTube' => Setting::get('youtube_url'),
        'LinkedIn' => Setting::get('linkedin_url'),
    ]);

    $title = trim($__env->yieldContent('title')) ?: (Setting::get('seo_title') ?: $company.' · '.Setting::get('company_tagline'));
    $description = trim($__env->yieldContent('description')) ?: (Setting::get('seo_description') ?: Setting::get('company_tagline'));
    $ogImage = trim($__env->yieldContent('og_image')) ?: Setting::url('og_image_path');
@endphp
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $company }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
        <meta name="twitter:card" content="summary_large_image">
    @endif

    @if ($favicon)
        <link rel="icon" href="{{ $favicon }}">
    @endif

    {{-- El color de marca es configurable, así que viaja como variable CSS:
         Tailwind compila sus clases en build y no puede generarlo en runtime. --}}
    <style>
        :root {
            --brand: {{ Brand::color() }};
            --brand-dark: {{ Brand::dark() }};
            --brand-tint: {{ Brand::tint() }};
            --brand-contrast: {{ Brand::contrast() }};
        }
    </style>

    @vite(['resources/css/app.css'])

    @if ($analytics = Setting::get('analytics_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $analytics }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', @json($analytics));
        </script>
    @endif
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">

@if (! Setting::bool('portal_enabled'))
    <div class="bg-amber-500 px-4 py-2 text-center text-sm font-medium text-amber-950">
        El portal está en mantenimiento. Solo tú lo ves porque eres administrador.
    </div>
@endif

<header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <a href="{{ route('portal.home') }}" class="flex items-center gap-2">
            @if ($logo)
                <img src="{{ $logo }}" alt="{{ $company }}" class="h-9 w-auto max-w-[180px] object-contain">
            @else
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[var(--brand)] text-sm font-bold text-[var(--brand-contrast)]">
                    {{ Brand::initials() }}
                </span>
                <span class="text-lg font-semibold tracking-tight">{{ $company }}</span>
            @endif
        </a>

        <nav class="flex items-center gap-2 sm:gap-6">
            <a href="{{ route('portal.index') }}"
               class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                Propiedades
            </a>
            @if (Setting::bool('show_contact'))
                <a href="{{ route('portal.home') }}#contacto"
                   class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                    Contacto
                </a>
            @endif
            @if ($phone = Setting::get('company_phone'))
                <a href="tel:{{ $phone }}"
                   class="hidden rounded-lg bg-[var(--brand)] px-4 py-2 text-sm font-semibold text-[var(--brand-contrast)] transition hover:bg-[var(--brand-dark)] sm:block">
                    {{ $phone }}
                </a>
            @endif
        </nav>
    </div>
</header>

<main>
    @yield('content')
</main>

<footer class="mt-20 border-t border-slate-200 bg-white">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-3 lg:px-8">
        <div>
            <p class="text-lg font-semibold">{{ $company }}</p>
            <p class="mt-2 text-sm text-slate-600">{{ Setting::get('company_tagline') }}</p>

            @if ($socials)
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($socials as $name => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener"
                           class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-[var(--brand)] hover:text-[var(--brand)]">
                            {{ $name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="text-sm text-slate-600">
            <p class="font-medium text-slate-900">Contacto</p>
            @if ($address = Setting::get('company_address'))
                <p class="mt-2">{{ $address }}</p>
            @endif
            @if ($phone = Setting::get('company_phone'))
                <p class="mt-1">
                    <a href="tel:{{ $phone }}" class="hover:text-[var(--brand)]">{{ $phone }}</a>
                </p>
            @endif
            @if ($email = Setting::get('company_email'))
                <p class="mt-1">
                    <a href="mailto:{{ $email }}" class="hover:text-[var(--brand)]">{{ $email }}</a>
                </p>
            @endif
            @if ($hours = Setting::get('business_hours'))
                <p class="mt-3 text-slate-500">{{ $hours }}</p>
            @endif
        </div>

        <div class="text-sm text-slate-600">
            <p class="font-medium text-slate-900">Accesos</p>
            <p class="mt-2">
                <a href="{{ route('portal.index') }}" class="hover:text-[var(--brand)]">Buscar propiedades</a>
            </p>
            <p class="mt-1">
                <a href="/admin" class="hover:text-[var(--brand)]">Acceso al CRM</a>
            </p>
        </div>
    </div>

    <div class="border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <p>© {{ date('Y') }} {{ $company }}. Todos los derechos reservados.</p>
        @if ($note = Setting::get('footer_note'))
            <p class="mt-1">{{ $note }}</p>
        @endif
    </div>
</footer>

@if (Setting::bool('whatsapp_float') && $whatsapp)
    <a href="https://wa.me/{{ $whatsapp }}?text={{ urlencode((string) Setting::get('whatsapp_message')) }}"
       target="_blank" rel="noopener"
       aria-label="Escríbenos por WhatsApp"
       class="fixed bottom-5 right-5 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500 text-white shadow-lg transition hover:scale-105 hover:bg-emerald-600">
        <svg class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M17.47 14.38c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.25-.46-2.38-1.47-.88-.78-1.47-1.75-1.64-2.05-.17-.3-.02-.46.13-.6.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.6-.91-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.01-1.04 2.47s1.06 2.86 1.21 3.06c.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.75-.72 2-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35M12.04 21.5h-.01a9.4 9.4 0 0 1-4.79-1.31l-.34-.2-3.56.93.95-3.47-.22-.36a9.38 9.38 0 0 1-1.44-5.01c0-5.18 4.22-9.4 9.42-9.4a9.35 9.35 0 0 1 6.65 2.76 9.32 9.32 0 0 1 2.75 6.65c0 5.18-4.22 9.4-9.41 9.4M20.5 3.49A11.72 11.72 0 0 0 12.04 0C5.6 0 .37 5.23.36 11.66c0 2.05.54 4.06 1.56 5.83L.26 24l6.66-1.74a11.66 11.66 0 0 0 5.12 1.2h.01c6.44 0 11.67-5.23 11.68-11.66a11.6 11.6 0 0 0-3.23-8.31"/>
        </svg>
    </a>
@endif

</body>
</html>
