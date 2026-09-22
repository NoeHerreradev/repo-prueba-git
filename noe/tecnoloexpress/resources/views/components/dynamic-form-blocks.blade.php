@props(['blocks' => [], 'compact' => false])

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    @foreach ($blocks as $block)
        @php
            $type = $block['type'] ?? '';
            $data = $block['data'] ?? [];
            $name = $data['name'] ?? 'field_' . $loop->index;
            $label = $data['label'] ?? '';
            $placeholder = $data['placeholder'] ?? '';
            $required = !empty($data['required']);
            $width = $data['width'] ?? 'full';
            $helperText = $data['helper_text'] ?? '';
            
            $colClass = match($width) {
                'half' => 'sm:col-span-1',
                'third' => 'sm:col-span-1 md:col-span-1',
                default => 'col-span-full',
            };
        @endphp

        @if ($type === 'header')
            <div class="col-span-full pt-2 pb-1">
                @if (!empty($data['title']))
                    <h4 class="text-base font-semibold text-slate-800">{{ $data['title'] }}</h4>
                @endif
                @if (!empty($data['subtitle']))
                    <p class="mt-0.5 text-xs text-slate-500">{{ $data['subtitle'] }}</p>
                @endif
            </div>
        @elseif ($type === 'divider')
            <div class="col-span-full py-2">
                <hr class="{{ ($data['style'] ?? 'solid') === 'dashed' ? 'border-dashed' : 'border-solid' }} border-slate-200" />
            </div>
        @elseif ($type === 'text_input')
            <div class="{{ $colClass }}">
                <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">
                    {{ $label }}
                    @if ($required) <span class="text-red-500">*</span> @endif
                </label>
                <input
                    type="{{ $data['input_type'] ?? 'text' }}"
                    id="{{ $name }}"
                    name="{{ $name }}"
                    value="{{ old($name) }}"
                    placeholder="{{ $placeholder }}"
                    @if ($required) required @endif
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]"
                />
                @if ($helperText)
                    <p class="mt-1 text-xs text-slate-400">{{ $helperText }}</p>
                @endif
                @error($name)
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @elseif ($type === 'textarea')
            <div class="{{ $colClass }}">
                <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">
                    {{ $label }}
                    @if ($required) <span class="text-red-500">*</span> @endif
                </label>
                <textarea
                    id="{{ $name }}"
                    name="{{ $name }}"
                    rows="{{ $data['rows'] ?? 3 }}"
                    placeholder="{{ $placeholder }}"
                    @if ($required) required @endif
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]"
                >{{ old($name) }}</textarea>
                @if ($helperText)
                    <p class="mt-1 text-xs text-slate-400">{{ $helperText }}</p>
                @endif
                @error($name)
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @elseif ($type === 'select')
            <div class="{{ $colClass }}">
                <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">
                    {{ $label }}
                    @if ($required) <span class="text-red-500">*</span> @endif
                </label>
                <select
                    id="{{ $name }}"
                    name="{{ $name }}"
                    @if ($required) required @endif
                    class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]"
                >
                    @if ($placeholder)
                        <option value="">{{ $placeholder }}</option>
                    @endif
                    @foreach ($data['options'] ?? [] as $option)
                        <option value="{{ $option['value'] ?? '' }}" @selected(old($name) == ($option['value'] ?? ''))>
                            {{ $option['label'] ?? ($option['value'] ?? '') }}
                        </option>
                    @endforeach
                </select>
                @if ($helperText)
                    <p class="mt-1 text-xs text-slate-400">{{ $helperText }}</p>
                @endif
                @error($name)
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @elseif ($type === 'radio')
            <div class="{{ $colClass }}">
                <label class="block text-sm font-medium text-slate-700">
                    {{ $label }}
                    @if ($required) <span class="text-red-500">*</span> @endif
                </label>
                <div class="mt-2 space-y-2">
                    @foreach ($data['options'] ?? [] as $option)
                        <div class="flex items-center gap-2">
                            <input
                                type="{{ ($data['choice_type'] ?? 'radio') === 'checkbox' ? 'checkbox' : 'radio' }}"
                                id="{{ $name }}_{{ $loop->index }}"
                                name="{{ ($data['choice_type'] ?? 'radio') === 'checkbox' ? $name . '[]' : $name }}"
                                value="{{ $option['value'] ?? '' }}"
                                class="rounded border-slate-300 text-[var(--brand)] focus:ring-[var(--brand)]"
                            />
                            <label for="{{ $name }}_{{ $loop->index }}" class="text-sm text-slate-600">
                                {{ $option['label'] ?? ($option['value'] ?? '') }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif ($type === 'date')
            <div class="{{ $colClass }}">
                <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">
                    {{ $label }}
                    @if ($required) <span class="text-red-500">*</span> @endif
                </label>
                <input
                    type="date"
                    id="{{ $name }}"
                    name="{{ $name }}"
                    value="{{ old($name) }}"
                    @if ($required) required @endif
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]"
                />
                @if ($helperText)
                    <p class="mt-1 text-xs text-slate-400">{{ $helperText }}</p>
                @endif
                @error($name)
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endif
    @endforeach
</div>
