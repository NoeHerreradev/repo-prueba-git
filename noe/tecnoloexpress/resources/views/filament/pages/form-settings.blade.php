<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center justify-between border-t border-gray-200 pt-6 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Los cambios en los bloques de construcción se aplicarán automáticamente a los formularios activos.
            </p>

            <x-filament::button type="submit" icon="heroicon-m-check">
                Guardar bloques de formularios
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
