<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MetricsReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MetricsReportController extends Controller
{
    public function download(Request $request, MetricsReportService $service): Response
    {
        $year = $request->integer('year', Carbon::now()->year);
        $month = $request->integer('month', Carbon::now()->month);

        if ($month < 1 || $month > 12) {
            $month = Carbon::now()->month;
        }

        if ($year < 2020 || $year > 2050) {
            $year = Carbon::now()->year;
        }

        $data = $service->getMonthlyMetrics($year, $month, auth()->user());

        $pdf = Pdf::loadView('reports.monthly-metrics-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        $fileName = sprintf('reporte-metricas-%s-%02d.pdf', $year, $month);

        return $pdf->download($fileName);
    }
}
