<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\ReportService;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $service)
    {
        $filters = $request->only('from', 'to', 'class_id', 'subject_id', 'status');
        $items = $service->paginateRows($filters, 25);
        $allRows = $service->rows($filters);

        return view('admin.reports.index', [
            'items' => $items,
            'classes' => SchoolClass::orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'filters' => $filters,
            'summary' => [
                'total' => $allRows->count(),
                'hadir' => $allRows->where('status', 'hadir')->count(),
                'terlambat' => $allRows->where('status', 'terlambat')->count(),
                'alpa' => $allRows->where('status', 'alpa')->count(),
            ],
        ]);
    }

    public function pdf(Request $request, ReportService $service)
    {
        $filters = $request->only('from', 'to', 'class_id', 'subject_id', 'status');
        $items = $service->rows($filters);

        $html = view('admin.reports.pdf', compact('items', 'filters'))->render();

        $pdf = new Dompdf(['isRemoteEnabled' => true]);
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="rekap-absensi.pdf"',
        ]);
    }

    public function excel(Request $request, ReportService $service)
    {
        $filters = $request->only('from', 'to', 'class_id', 'subject_id', 'status');
        $items = $service->rows($filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            'No',
            'Tanggal Sesi',
            'Jam Sesi',
            'Waktu Scan',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Mata Pelajaran',
            'Guru',
            'Status',
        ], null, 'A1');

        $rowNumber = 2;

        foreach ($items as $index => $row) {
            $sheet->fromArray([
                $index + 1,
                $row->tanggal,
                $row->jam_sesi,
                $row->waktu_scan,
                $row->nis,
                $row->nama,
                $row->kelas,
                $row->mapel,
                $row->guru,
                $row->status_label,
            ], null, 'A' . $rowNumber++);
        }

        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        (new Xlsx($spreadsheet))->save($tmp);

        return response()->download($tmp, 'rekap-absensi.xlsx')->deleteFileAfterSend(true);
    }
}
