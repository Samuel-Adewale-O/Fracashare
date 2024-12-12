<?php

namespace App\Http\Controllers;

use App\Services\TaxService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TaxController extends Controller
{
    private TaxService $taxService;

    public function __construct(TaxService $taxService)
    {
        $this->middleware(['auth:sanctum']);
        $this->taxService = $taxService;
    }

    public function annualSummary(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:' . date('Y')
        ]);

        $summary = $this->taxService->calculateAnnualTaxSummary(
            $request->user(),
            $request->year
        );

        return response()->json([
            'status' => 'success',
            'data' => $summary
        ]);
    }

    public function downloadTaxCertificate(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:' . date('Y')
        ]);

        $user = $request->user();
        $year = $request->year;
        $summary = $this->taxService->calculateAnnualTaxSummary($user, $year);

        // Generate PDF certificate
        $pdf = PDF::loadView('tax.certificate', [
            'user' => $user,
            'year' => $year,
            'summary' => $summary,
            'generated_at' => Carbon::now()->toDateTimeString()
        ]);

        return $pdf->download("tax_certificate_{$year}.pdf");
    }
}