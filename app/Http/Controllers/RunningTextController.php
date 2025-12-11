<?php

namespace App\Http\Controllers;

use App\Models\RunningText;
use Illuminate\Http\Request;

class RunningTextController extends Controller
{
    // Get running text data (untuk AJAX)
    public function getData()
    {
        $runningText = RunningText::getActive();
        
        return response()->json([
            'success' => true,
            'data' => $runningText
        ]);
    }

    // Update running text
    public function update(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'is_active' => 'boolean',
            'speed' => 'in:slow,normal,fast',
            'background_color' => 'nullable|string|max:20',
            'text_color' => 'nullable|string|max:20',
        ]);

        $runningText = RunningText::first();

        if (!$runningText) {
            $runningText = RunningText::create([
                'content' => $request->content,
                'is_active' => $request->is_active ?? true,
                'speed' => $request->speed ?? 'normal',
                'background_color' => $request->background_color ?? '#1a1a1a',
                'text_color' => $request->text_color ?? '#fbbf24',
            ]);
        } else {
            $runningText->update([
                'content' => $request->content,
                'is_active' => $request->is_active ?? true,
                'speed' => $request->speed ?? 'normal',
                'background_color' => $request->background_color ?? '#1a1a1a',
                'text_color' => $request->text_color ?? '#fbbf24',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Running text berhasil diupdate!',
            'data' => $runningText
        ]);
    }
}