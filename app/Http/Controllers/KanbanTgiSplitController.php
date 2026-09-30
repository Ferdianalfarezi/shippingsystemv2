<?php

namespace App\Http\Controllers;

use App\Models\KanbanTgi;
use App\Services\KanbanTgiComposer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KanbanTgiSplitController extends Controller
{
    public function index()
    {
        return view('kanbantgisplit.index');
    }

    public function process(Request $request)
    {
        $sourceFullPath = null;

        try {
            $request->validate([
                'delivery_note' => 'required|file|mimes:pdf|max:20480',
            ]);

            $file = $request->file('delivery_note');
            $originalUploadName = $file->getClientOriginalName();

            $originalName = pathinfo($originalUploadName, PATHINFO_FILENAME);
            $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $originalName);

            $sourceDir = storage_path('app/kanban/tgi/source');
            if (!is_dir($sourceDir) && !mkdir($sourceDir, 0777, true) && !is_dir($sourceDir)) {
                throw new \RuntimeException("Gagal bikin folder source: {$sourceDir}");
            }

            $sourceFileName = $safeName . '_' . time() . '.pdf';
            $sourceFullPath = $sourceDir . '/' . $sourceFileName;
            $file->move($sourceDir, $sourceFileName);

            $outputName = $safeName . '_kanban_' . now()->format('Ymd_His') . '.pdf';
            $outputDir = storage_path('app/kanban/tgi/output');
            if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
                throw new \RuntimeException("Gagal bikin folder output: {$outputDir}");
            }
            $outputFullPath = $outputDir . '/' . $outputName;

            $composer = new KanbanTgiComposer();
            $result = $composer->compose($sourceFullPath, $outputFullPath);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('KANBAN TGI PROCESS ERROR', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            if ($sourceFullPath && is_file($sourceFullPath)) {
                @unlink($sourceFullPath);
            }

            return response()->json([
                'message' => 'Gagal memproses Delivery Note: ' . $e->getMessage()
                    . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')',
            ], 500);
        }

        if (is_file($sourceFullPath)) {
            @unlink($sourceFullPath);
        }

        if (!$result['output']) {
            return response()->json([
                'message' => 'Gak ada satupun part no di Delivery Note ini yang ketemu di bank kanban. Cek daftar part no-nya, atau upload dulu PDF kanbannya ke Bank Kanban TGI.',
                'items' => $result['items'],
            ], 422);
        }

        $metaToken = Str::random(24);

        $historyDir = storage_path('app/kanban/tgi/history');
        if (!is_dir($historyDir) && !mkdir($historyDir, 0777, true) && !is_dir($historyDir)) {
            throw new \RuntimeException("Gagal bikin folder history: {$historyDir}");
        }
        $historyRelativePath = 'kanban/tgi/history/' . $metaToken . '.pdf';
        copy($result['output'], storage_path('app/' . $historyRelativePath));

        KanbanTgi::create([
            'token' => $metaToken,
            'original_filename' => $originalUploadName,
            'file_path' => $historyRelativePath,
            'dn_no' => $result['dn_no'],
            'po_no' => $result['po_no'],
            'total_parts' => $result['total_parts'],
            'matched' => $result['matched'],
            'unmatched' => $result['unmatched'],
            'items_meta' => $result['items'],
        ]);

        return response()->file($result['output'], [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $outputName . '"',
            'X-Dn-No' => $result['dn_no'] ?? '',
            'X-Po-No' => $result['po_no'] ?? '',
            'X-Total-Parts' => $result['total_parts'],
            'X-Matched' => $result['matched'],
            'X-Unmatched' => $result['unmatched'],
            'X-Meta-Token' => $metaToken,
        ])->deleteFileAfterSend(true);
    }

    public function itemsMeta(string $token)
    {
        $split = KanbanTgi::where('token', $token)->first();

        if (!$split) {
            return response()->json([
                'message' => 'Data gak ketemu, coba proses ulang Delivery Note-nya.',
            ], 404);
        }

        return response()->json(['items' => $split->items_meta ?? []]);
    }

    public function recent()
    {
        $items = KanbanTgi::latest()
            ->take(3)
            ->get(['token', 'original_filename', 'dn_no', 'po_no', 'total_parts', 'matched', 'unmatched', 'created_at'])
            ->map(fn ($item) => [
                'token' => $item->token,
                'filename' => $item->original_filename,
                'dn_no' => $item->dn_no,
                'po_no' => $item->po_no,
                'total_parts' => $item->total_parts,
                'matched' => $item->matched,
                'unmatched' => $item->unmatched,
                'created_at' => $item->created_at->diffForHumans(),
            ]);

        return response()->json(['items' => $items]);
    }

    public function download(string $token)
    {
        $split = KanbanTgi::where('token', $token)->first();

        if (!$split) {
            abort(404, 'Riwayat tidak ditemukan.');
        }

        $fullPath = storage_path('app/' . $split->file_path);

        if (!is_file($fullPath)) {
            abort(404, 'File hasil proses sudah tidak ada di server.');
        }

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $split->original_filename . '"',
            'X-Dn-No' => $split->dn_no ?? '',
            'X-Po-No' => $split->po_no ?? '',
            'X-Total-Parts' => $split->total_parts,
            'X-Matched' => $split->matched,
            'X-Unmatched' => $split->unmatched,
            'X-Meta-Token' => $split->token,
        ]);
    }
}