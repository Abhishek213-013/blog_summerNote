<?php
use App\Http\Controllers\BlogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::get('/', [BlogController::class, 'index'])->name('blog.index');
Route::get('/edit-blog', [BlogController::class, 'edit'])->name('blog.edit');
Route::post('/blogs', [BlogController::class, 'store'])->name('blog.store');
Route::put('/blogs/{blog}', [BlogController::class, 'update'])->name('blog.update');
Route::delete('/blogs/{blog}', [BlogController::class, 'destroy'])->name('blog.destroy');
Route::post('/blogs/{blog}/like', [BlogController::class, 'like'])->name('blog.like');

// In routes/web.php, add this test route:
Route::get('/test-debug', function() {
    Log::info('Test route hit');
    return response()->json(['message' => 'Test successful']);
});

// In routes/web.php
Route::get('/test-image', function() {
    try {
        // Test method for Intervention Image v3
        $manager = new Intervention\Image\ImageManager(new Intervention\Image\Drivers\Gd\Driver());
        $image = $manager->read(storage_path('app/public/test.jpg'));
        return "Intervention Image v3 works with ImageManager!";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// In routes/web.php
Route::get('/test-image-upload', function() {
    return view('test-upload');
});

Route::post('/test-image-upload', function(Request $request) {
    Log::info('Test upload received:', $request->all());
    
    if ($request->hasFile('test_image')) {
        $image = $request->file('test_image');
        Log::info('File details:', [
            'name' => $image->getClientOriginalName(),
            'size' => $image->getSize(),
            'mime' => $image->getMimeType()
        ]);
        
        $path = $image->store('test-images', 'public');
        Log::info('Stored at: ' . $path);
        
        return response()->json([
            'success' => true,
            'path' => $path,
            'message' => 'Image uploaded successfully'
        ]);
    }
    
    return response()->json([
        'success' => false,
        'message' => 'No image received'
    ]);
});