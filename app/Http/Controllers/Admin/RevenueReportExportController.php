<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\RevenueReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class RevenueReportExportController extends Controller
{
    public function csv(Request $request)
    {
        $groupBy = RevenueReport::normalizeGroup($request->string('groupBy')->toString());
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $rows = RevenueReport::rows($groupBy, $startDate, $endDate);

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                __('Name'),
                __('Details'),
                __('Bookings'),
                __('Payments'),
                __('Collected revenue'),
                __('Average payment'),
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['label'],
                    $row['secondary'],
                    $row['bookings_count'],
                    $row['payments_count'],
                    number_format($row['collected_revenue'], 2, '.', ''),
                    number_format($row['average_payment'], 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, RevenueReport::filename($groupBy, 'csv'), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function pdf(Request $request)
    {
        $groupBy = RevenueReport::normalizeGroup($request->string('groupBy')->toString());
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $pdf = Pdf::loadView('pdf.revenue_report', [
            'groupBy' => $groupBy,
            'groupLabel' => RevenueReport::groups()[$groupBy],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'rows' => RevenueReport::rows($groupBy, $startDate, $endDate),
            'summary' => RevenueReport::summary($groupBy, $startDate, $endDate),
        ]);

        return $pdf->download(RevenueReport::filename($groupBy, 'pdf'));
    }
}
