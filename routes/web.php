<?php

use App\Http\Controllers\BlogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Blog Routes
Route::get('/', [BlogController::class, 'index'])->name('blog.index');
Route::get('/edit-blog', [BlogController::class, 'edit'])->name('blog.edit');
Route::post('/blogs', [BlogController::class, 'store'])->name('blog.store');
Route::put('/blogs/{blog}', [BlogController::class, 'update'])->name('blog.update');
Route::delete('/blogs/{blog}', [BlogController::class, 'destroy'])->name('blog.destroy');
Route::post('/blogs/{blog}/like', [BlogController::class, 'like'])->name('blog.like');
Route::get('/blogs/{blog}/modal', [BlogController::class, 'modal'])->name('blog.modal');

// Debug and Test Routes
Route::get('/test-debug', function() {
    \Illuminate\Support\Facades\Log::info('Test route hit');
    return response()->json(['message' => 'Test successful']);
});

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

Route::get('/test-image-upload', function() {
    return view('test-upload');
});

Route::post('/test-image-upload', function(\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Log::info('Test upload received:', $request->all());
    
    if ($request->hasFile('test_image')) {
        $image = $request->file('test_image');
        \Illuminate\Support\Facades\Log::info('File details:', [
            'name' => $image->getClientOriginalName(),
            'size' => $image->getSize(),
            'mime' => $image->getMimeType()
        ]);
        
        $path = $image->store('test-images', 'public');
        \Illuminate\Support\Facades\Log::info('Stored at: ' . $path);
        
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

// GD Test Route
Route::get('/web-gd-test', function() {
    echo "<h1>Web Server GD Test</h1>";

    // Test GD in web context
    if (extension_loaded('gd')) {
        echo "✅ Web Server: GD is LOADED<br>";
        $gd_info = gd_info();
        echo "GD Version: " . $gd_info['GD Version'] . "<br>";
    } else {
        echo "❌ Web Server: GD is NOT LOADED<br>";
    }

    echo "PHP Version: " . PHP_VERSION . "<br>";
    echo "php.ini: " . php_ini_loaded_file() . "<br>";
});

// Fallback route for undefined routes
Route::fallback(function () {
    return redirect()->route('blog.index');
});