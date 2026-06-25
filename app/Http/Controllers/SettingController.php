<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function updateRate(Request $request)
    {
        $request->validate(['usd_rate' => 'required|numeric|min:1']);
        Setting::set('usd_rate', (int) $request->usd_rate);
        Setting::set('usd_rate_updated_at', now()->toIso8601String());
        return back()->with('success', 'تم تحديث سعر الدولار إلى ' . number_format($request->usd_rate, 0) . ' ل.س');
    }

    public function backup()
    {
        $dbPath = config('database.connections.sqlite.database');

        if (!$dbPath || !file_exists($dbPath)) {
            return back()->with('error', 'ملف قاعدة البيانات غير موجود');
        }

        $filename = 'warehouse-backup-' . now()->format('Y-m-d_H-i') . '.sqlite';

        return response()->download($dbPath, $filename, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
