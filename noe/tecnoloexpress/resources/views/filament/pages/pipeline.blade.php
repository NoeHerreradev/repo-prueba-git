@php
    use App\Enums\LeadStage;
@endphp

<x-filament-panels::page>
    @if (auth()->user()?->seesAllRecords())
        <div class="flex items-center gap-3">
            <label for="pipeline-agent" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Agente
            </label>
            <select
                id="pipeline-agent"
                wire:model.live="agentFilter"
                class="fi-input block rounded-lg border border-gray-300 bg-white py-1.5 pe-8 ps-3 text-sm text-gray-950 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white"
            >
                <option value="">Todos los agentes</option>
                @foreach ($this->agents as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="-mx-4 overflow-x-auto px-4 pb-4">
        <div class="flex min-w-max gap-4">
            @foreach ($this->stages() as $stage)
                @php
                    $leads = $this->leadsByStage[$stage->value] ?? collect();
                    $total = $this->stageTotal($stage);
                @endphp

                <div class="flex w-72 flex-none flex-col rounded-xl bg-gray-100 dark:bg-white/5">
                    <div class="flex items-center justify-between gap-2 border-b border-gray-200 px-3 py-2.5 dark:border-white/10">
                        <div class="flex items-center gap-2">
                            <x-filament::icon
                                :icon="$stage->getIcon()"
                                @class([
                                    'h-4 w-4',
                                    'text-gray-500 dark:text-gray-400' => $stage->getColor() === 'gray',
                                    'text-primary-600 dark:text-primary-400' => $stage->getColor() === 'info',
                                    'text-warning-600 dark:text-warning-400' => $stage->getColor() === 'warning',
                                    'text-success-600 dark:text-success-400' => $stage->getColor() === 'success',
                                    'text-danger-600 dark:text-danger-400' => $stage->getColor() === 'danger',
                                ])
                            />
                            <span class="text-sm font-semibold text-gray-950 dark:text-white">
                                {{ $stage->getLabel() }}
                            </span>
                        </div>

                        <span class="rounded-md bg-white px-1.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/10 dark:text-gray-300">
                            {{ $leads->count() }}
                        </span>
                    </div>

                    <div class="px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400">
                        ${{ number_format($total, 0, ',', '.') }}
                    </div>

                    <div
                        x-sortable
                        x-sortable-group="leads"
                        data-stage="{{ $stage->value }}"
                        x-on:end.stop="$wire.moveLead(
                            $event.item.getAttribute('x-sortable-item'),
                            $event.to.dataset.stage,
                        )"
                        class="flex min-h-24 flex-1 flex-col gap-2 p-2"
                    >
                        @forelse ($leads as $lead)
                            <div
                                wire:key="lead-{{ $lead->id }}"
                                x-sortable-item="{{ $lead->id }}"
                                x-sortable-handle
                                class="cursor-grab rounded-lg border border-gray-200 bg-white p-3 shadow-sm transition hover:shadow-md active:cursor-grabbing dark:border-white/10 dark:bg-gray-900"
                            >
                                <a
                                    href="{{ \App\Filament\Resources\Leads\LeadResource::getUrl('view', ['record' => $lead]) }}"
                                    class="block text-sm font-semibold text-gray-950 hover:text-primary-600 dark:text-white dark:hover:text-primary-400"
                                >
                                    {{ $lead->contact?->full_name ?? 'Sin contacto' }}
                                </a>

                                @if ($lead->property)
                                    <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">
                                        {{ $lead->property->code }} · {{ $lead->property->title }}
                                    </p>
                                @else
                                    <p class="mt-0.5 text-xs italic text-gray-400 dark:text-gray-500">
                                        Consulta general
                                    </p>
                                @endif

                                <div class="mt-2 flex items-center justify-between gap-2">
                                    <span class="text-sm font-medium text-gray-950 dark:text-white">
                                        ${{ number_format((float) $lead->expected_value, 0, ',', '.') }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $lead->probability }}%
                                    </span>
                                </div>

                                <div class="mt-2 flex items-center justify-between gap-2 border-t border-gray-100 pt-2 dark:border-white/5">
                                    <span class="truncate text-xs text-gray-500 dark:text-gray-400">
                                        {{ $lead->agent?->name ?? 'Sin asignar' }}
                                    </span>

                                    @if ($stage->isOpen())
                                        <span
                                            @class([
                                                'rounded px-1.5 py-0.5 text-xs font-medium',
                                                'bg-success-50 text-success-700 dark:bg-success-400/10 dark:text-success-400' => $lead->daysInStage() <= 7,
                                                'bg-warning-50 text-warning-700 dark:bg-warning-400/10 dark:text-warning-400' => $lead->daysInStage() > 7 && $lead->daysInStage() <= 15,
                                                'bg-danger-50 text-danger-700 dark:bg-danger-400/10 dark:text-danger-400' => $lead->daysInStage() > 15,
                                            ])
                                        >
                                            {{ $lead->daysInStage() }}d
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="px-1 py-3 text-center text-xs text-gray-400 dark:text-gray-500">
                                Arrastra leads aquí
                            </p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
