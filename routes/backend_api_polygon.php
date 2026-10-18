<?php

use App\Http\Controllers\Backend\PolygonExerciseController;
use Illuminate\Support\Facades\Route;

// Issue #401: Polygon Problem Authoring and Calibration REST API
// Restrito a administradores autenticados ('auth', 'admin').
Route::prefix('api/backend/polygon')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [PolygonExerciseController::class, 'index'])->name('api.backend.polygon.index');
    Route::post('/', [PolygonExerciseController::class, 'store'])->name('api.backend.polygon.store');
    Route::get('/{id}', [PolygonExerciseController::class, 'show'])->name('api.backend.polygon.show');
    Route::put('/{id}', [PolygonExerciseController::class, 'update'])->name('api.backend.polygon.update');
    Route::delete('/{id}', [PolygonExerciseController::class, 'destroy'])->name('api.backend.polygon.destroy');
    Route::post('/{id}/compile-generator', [PolygonExerciseController::class, 'compileGenerator'])->name('api.backend.polygon.compile-generator');
    Route::post('/{id}/generate-tests', [PolygonExerciseController::class, 'generateTests'])->name('api.backend.polygon.generate-tests');
    Route::post('/{id}/calibrate', [PolygonExerciseController::class, 'calibrate'])->name('api.backend.polygon.calibrate');
    Route::post('/{id}/publish', [PolygonExerciseController::class, 'publish'])->name('api.backend.polygon.publish');
    Route::get('/{id}/cases/{caseNumber}/download', [PolygonExerciseController::class, 'downloadTestCase'])->name('api.backend.polygon.case-download');
});
