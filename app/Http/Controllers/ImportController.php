<?php

namespace App\Http\Controllers;

use App\Support\Audit;
use App\Support\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/** Bring an existing client list in from Excel (saved as CSV) */
class ImportController extends Controller
{
    public const COLUMNS = ['name', 'phone', 'whatsapp', 'email', 'nic', 'date_of_birth', 'city', 'address', 'occupation', 'stage', 'notes'];

    public function create(): View
    {
        return view('clients.import', ['columns' => self::COLUMNS]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);
        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle) ?: [];
        $header = array_map(fn ($h) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))), $header);
        $map = fn (string $col) => array_search($col, $header, true);
        if ($map('name') === false) {
            return back()->with('error', __('The first row must contain a "name" column.'));
        }

        $created = 0;
        $skipped = 0;
        $rowNo = 1;
        while (($row = fgetcsv($handle)) !== false && $rowNo <= 2000) {
            $rowNo++;
            $get = fn (string $col) => ($i = $map($col)) !== false ? trim((string) ($row[$i] ?? '')) : null;
            $dob = $get('date_of_birth');
            try {
                $dob = $dob ? Carbon::parse($dob)->toDateString() : null;
            } catch (\Throwable) {
                $dob = null;
            }
            $data = array_filter([
                'full_name' => $get('name'),
                'phone' => $get('phone'),
                'whatsapp' => $get('whatsapp'),
                'email' => $get('email'),
                'nic' => $get('nic') ? strtoupper(preg_replace('/\s+/', '', $get('nic'))) : null,
                'date_of_birth' => $dob,
                'city' => $get('city'),
                'address' => $get('address'),
                'occupation' => $get('occupation'),
                'stage' => array_key_exists((string) $get('stage'), Options::STAGES) ? $get('stage') : 'client',
                'notes' => $get('notes'),
            ], fn ($v) => $v !== null && $v !== '');

            $v = Validator::make($data, [
                'full_name' => ['required', 'string', 'max:160'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:20'],
                'whatsapp' => ['nullable', 'string', 'max:20'],
                'nic' => ['nullable', 'regex:/^(\d{9}[VX]|\d{12})$/'],
                'city' => ['nullable', 'string', 'max:80'],
                'address' => ['nullable', 'string', 'max:500'],
                'occupation' => ['nullable', 'string', 'max:120'],
                'notes' => ['nullable', 'string', 'max:5000'],
            ]);
            if ($v->fails()) {
                $skipped++;

                continue;
            }
            $request->user()->clients()->create($data);
            $created++;
        }
        fclose($handle);
        Audit::log('clients.imported', null, "$created imported, $skipped skipped");

        return redirect()->route('clients.index')->with('success', __(':created clients imported, :skipped rows skipped.', ['created' => $created, 'skipped' => $skipped]));
    }

    public function template()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, self::COLUMNS);
            fputcsv($out, ['Nimal Perera', '0771234567', '0771234567', 'nimal@example.com', '199012345678', '1990-05-14', 'Matara', 'No 12, Main Street', 'Teacher', 'client', 'Met at school event']);
            fclose($out);
        }, 'rakawarana-clients-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
