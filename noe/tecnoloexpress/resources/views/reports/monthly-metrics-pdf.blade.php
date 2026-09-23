<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Mensual de Métricas - {{ $periodLabel }}</title>
    <style>
        @page {
            margin: 25px 30px 35px 30px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 12px;
        }
        .header-logo {
            font-size: 20px;
            font-weight: bold;
            color: #4f46e5;
        }
        .header-subtitle {
            font-size: 10px;
            color: #64748b;
        }
        .header-right {
            text-align: right;
            font-size: 10px;
            color: #475569;
        }
        .period-badge {
            display: inline-block;
            background-color: #e0e7ff;
            color: #3730a3;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            margin-top: 4px;
        }
        
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 15px;
            margin-bottom: 8px;
            border-left: 3px solid #4f46e5;
            padding-left: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* KPI Cards */
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 15px;
        }
        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
            width: 33.33%;
        }
        .kpi-val {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .kpi-lbl {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
        }
        .kpi-sub {
            font-size: 9px;
            color: #4f46e5;
            margin-top: 2px;
        }

        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            text-align: left;
            padding: 6px 8px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            text-transform: uppercase;
            font-size: 9px;
        }
        .data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-semibold {
            font-weight: 600;
        }

        .two-col-table {
            width: 100%;
            border-collapse: collapse;
        }
        .two-col-table td {
            vertical-align: top;
            width: 50%;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <div class="header-logo">{{ $companyName }}</div>
                <div class="header-subtitle">Informe Ejecutivo de Métricas y Rendimiento</div>
            </td>
            <td class="header-right" style="vertical-align: top;">
                <div class="period-badge">{{ $periodLabel }}</div>
                <div style="margin-top: 5px;">Generado el {{ $generatedAt }}</div>
                <div>Por: {{ $userName }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Resumen Ejecutivo (KPIs Clave)</div>

    <table class="kpi-table">
        <tr>
            <td class="kpi-card">
                <div class="kpi-val">${{ number_format($kpis['contractsVolume'], 0, ',', '.') }}</div>
                <div class="kpi-lbl">Volumen Cerrado en el Mes</div>
                <div class="kpi-sub">{{ $kpis['contractsTotal'] }} contratos / acuerdos</div>
            </td>
            <td class="kpi-card">
                <div class="kpi-val">${{ number_format($kpis['commissionsTotal'], 0, ',', '.') }}</div>
                <div class="kpi-lbl">Comisiones Generadas</div>
                <div class="kpi-sub">${{ number_format($kpis['commissionsPaid'], 0, ',', '.') }} pagadas</div>
            </td>
            <td class="kpi-card">
                <div class="kpi-val">{{ $kpis['conversionRate'] }}%</div>
                <div class="kpi-lbl">Tasa de Conversión</div>
                <div class="kpi-sub">{{ $kpis['contractsTotal'] }} de {{ $kpis['leadsCreatedInMonth'] }} captados</div>
            </td>
        </tr>
        <tr>
            <td class="kpi-card">
                <div class="kpi-val">{{ $kpis['leadsCreatedInMonth'] }}</div>
                <div class="kpi-lbl">Nuevos Leads Captados</div>
                <div class="kpi-sub">{{ $kpis['openLeadsCount'] }} activos (${{ number_format($kpis['openLeadsWeighted'], 0, ',', '.') }})</div>
            </td>
            <td class="kpi-card">
                <div class="kpi-val">{{ $kpis['visitsTotal'] }}</div>
                <div class="kpi-lbl">Citas / Visitas en el Mes</div>
                <div class="kpi-sub">{{ $kpis['visitsCompleted'] }} realizadas · {{ $kpis['visitsPending'] }} pendientes</div>
            </td>
            <td class="kpi-card">
                <div class="kpi-val">{{ $kpis['availableProperties'] }}</div>
                <div class="kpi-lbl">Servicios Disponibles</div>
                <div class="kpi-sub">Catálogo: ${{ number_format($kpis['availablePortfolio'], 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <table class="two-col-table">
        <tr>
            <td style="padding-right: 10px;">
                <div class="section-title">Embudo de Ventas / Oportunidades</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Etapa</th>
                            <th class="text-right">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leadsByStage as $st)
                            <tr>
                                <td class="font-semibold">{{ $st['stage'] }}</td>
                                <td class="text-right">{{ $st['count'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
            <td style="padding-left: 10px;">
                <div class="section-title">Origen de los Leads (Mes)</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Canal / Origen</th>
                            <th class="text-right">Leads</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leadsBySource as $src)
                            <tr>
                                <td>{{ $src['source'] }}</td>
                                <td class="text-right font-semibold">{{ $src['count'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    @if (!empty($agentsPerformance))
        <div class="section-title">Rendimiento por Asesor / Agente</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Asesor</th>
                    <th>Rol</th>
                    <th class="text-center">Leads Mes</th>
                    <th class="text-center">Cierres</th>
                    <th class="text-right">Volumen</th>
                    <th class="text-right">Comisión</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($agentsPerformance as $ag)
                    <tr>
                        <td class="font-semibold">{{ $ag['name'] }}</td>
                        <td>{{ $ag['role'] }}</td>
                        <td class="text-center">{{ $ag['leads_count'] }}</td>
                        <td class="text-center">{{ $ag['contracts_count'] }}</td>
                        <td class="text-right">${{ number_format((float) $ag['volume'], 0, ',', '.') }}</td>
                        <td class="text-right font-semibold">${{ number_format((float) $ag['commission'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($contractsList->isNotEmpty())
        <div class="section-title">Cierres y Contratos del Período</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cliente / Contacto</th>
                    <th>Servicio / Inmueble</th>
                    <th>Asesor</th>
                    <th>Fecha</th>
                    <th class="text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contractsList as $c)
                    <tr>
                        <td class="font-semibold">{{ $c->client?->full_name ?? 'N/D' }}</td>
                        <td>{{ $c->property?->title ?? 'Consulta general' }}</td>
                        <td>{{ $c->agent?->name ?? 'N/D' }}</td>
                        <td>{{ $c->signed_at?->format('d/m/Y') }}</td>
                        <td class="text-right font-semibold">${{ number_format((float) $c->amount, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        {{ $companyName }} · Reporte de Métricas Confidencial · Página generada automáticamente
    </div>

</body>
</html>
