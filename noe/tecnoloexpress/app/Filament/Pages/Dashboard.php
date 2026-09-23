<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_report')
                ->label('Descargar Reporte Mensual (PDF)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->modalHeading('Exportar Reporte Mensual de Métricas')
                ->modalDescription('Selecciona el mes y año para generar el informe ejecutivo en formato PDF.')
                ->form([
                    Select::make('month')
                        ->label('Mes')
                        ->options([
                            1 => 'Enero',
                            2 => 'Febrero',
                            3 => 'Marzo',
                            4 => 'Abril',
                            5 => 'Mayo',
                            6 => 'Junio',
                            7 => 'Julio',
                            8 => 'Agosto',
                            9 => 'Septiembre',
                            10 => 'Octubre',
                            11 => 'Noviembre',
                            12 => 'Diciembre',
                        ])
                        ->default(now()->month)
                        ->required(),

                    Select::make('year')
                        ->label('Año')
                        ->options([
                            2024 => '2024',
                            2025 => '2025',
                            2026 => '2026',
                            2027 => '2027',
                        ])
                        ->default(now()->year)
                        ->required(),
                ])
                ->modalSubmitActionLabel('Descargar PDF')
                ->action(function (array $data) {
                    $url = route('admin.reports.monthly-metrics', [
                        'month' => $data['month'],
                        'year' => $data['year'],
                    ]);

                    $this->redirect($url, navigate: false);
                }),
        ];
    }
}
