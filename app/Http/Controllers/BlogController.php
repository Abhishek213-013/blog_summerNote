<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BlogController extends Controller
{
    /**
     * Display all blog posts
     */
    public function index()
    {
        $blogs = Blog::latest()->get();
        return view('blog.index', compact('blogs'));
    }

    /**
     * Show the blog management page
     */
    public function edit(Request $request)
    {
        $blogs = Blog::latest()->get();
        $editingBlog = null;

        if ($request->has('edit')) {
            $editingBlog = Blog::find($request->edit);
        }

        return view('blog.edit', compact('blogs', 'editingBlog'));
    }

    /**
     * Store a new blog post
     */
    public function store(Request $request)
    {
        Log::info('=== BLOG STORE START ===');
        
        // Check GD in web context
        if (extension_loaded('gd')) {
            Log::info('SUCCESS: GD extension is LOADED in web server!');
            $gd_info = gd_info();
            Log::info('GD Version: ' . $gd_info['GD Version']);
        } else {
            Log::error('PROBLEM: GD extension not loaded in web server!');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'blocks' => 'required|array|min:1'
        ]);

        $processedBlocks = [];

        foreach ($request->blocks as $index => $blockData) {
            $content = $blockData['content'] ?? '';
            $image_caption = $blockData['image_caption'] ?? '';
            
            // Desktop settings
            $desktop_size = $blockData['desktop_size'] ?? 'medium';
            $desktop_wbone_image = isset($blockData['desktop_wbone_image']);
            $desktop_display_size = $blockData['desktop_display_size'] ?? 'contain';
            
            // Mobile settings
            $mobile_size = $blockData['mobile_size'] ?? 'medium';
            $mobile_wbone_image = isset($blockData['mobile_wbone_image']);
            $mobile_display_size = $blockData['mobile_display_size'] ?? 'contain';
            
            $block = [
                'content' => $content,
                'image_caption' => $image_caption,
                // Desktop settings
                'desktop_size' => $desktop_size,
                'desktop_wbone_image' => $desktop_wbone_image,
                'desktop_display_size' => $desktop_display_size,
                // Mobile settings
                'mobile_size' => $mobile_size,
                'mobile_wbone_image' => $mobile_wbone_image,
                'mobile_display_size' => $mobile_display_size
            ];

            if ($request->hasFile("images." . $index)) {
                $image = $request->file("images." . $index);

                $imageValidator = Validator::make(['image' => $image], [
                    'image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120'
                ]);

                if ($imageValidator->fails()) {
                    continue;
                }

                // Generate folder structure based on current date
                $currentDate = Carbon::now();
                $year = $currentDate->year;
                $month = $currentDate->month;
                $day = $currentDate->day;
                
                $folderPath = "blog-images/{$year}/{$month}/{$day}";
                
                // Store original image with organized path
                $originalImagePath = $image->store($folderPath, 'public');
                Log::info("Original image uploaded: " . $originalImagePath);
                
                // Create desktop version with organized path
                $desktopImagePath = $this->createResizedVersion($originalImagePath, $desktop_size, 'desktop', $folderPath);
                if ($desktopImagePath) {
                    $block['desktop_image'] = $desktopImagePath;
                    Log::info("Desktop image created: " . $desktopImagePath . " with size: " . $desktop_size);
                }
                
                // Create mobile version with organized path
                $mobileImagePath = $this->createResizedVersion($originalImagePath, $mobile_size, 'mobile', $folderPath);
                if ($mobileImagePath) {
                    $block['mobile_image'] = $mobileImagePath;
                    Log::info("Mobile image created: " . $mobileImagePath . " with size: " . $mobile_size);
                }
                
                // Store original image path as well
                $block['original_image'] = $originalImagePath;
            }

            $processedBlocks[] = $block;
        }

        $blog = Blog::create([
            'title' => $request->title,
            'content' => $processedBlocks,
            'likes' => 0
        ]);

        Log::info('Blog created with ID: ' . $blog->id);
        Log::info('=== BLOG STORE END ===');

        return redirect()->route('blog.edit')->with('success', 'Blog created successfully!');
    }

    /**
     * Create resized version of an image
     */
    private function createResizedVersion($originalImagePath, $size, $type = 'desktop', $folderPath = '')
    {
        $fullPath = storage_path('app/public/' . $originalImagePath);
        
        Log::info("Creating " . $type . " version: " . $originalImagePath . " -> " . $size);
        
        if (!file_exists($fullPath)) {
            Log::error("Original file not found: " . $fullPath);
            return false;
        }

        // Get original image info
        $info = getimagesize($fullPath);
        if (!$info) {
            Log::error("Cannot read image info");
            return false;
        }

        $width = $info[0];
        $height = $info[1];
        $mime = $info['mime'];
        
        Log::info("Original: " . $width . "x" . $height . ", Type: " . $mime);

        // Calculate target size
        $percentage = 1.0;
        if ($size === 'small') {
            $percentage = 0.25;
        } elseif ($size === 'medium') {
            $percentage = 0.50;
        } elseif ($size === 'large') {
            $percentage = 0.75;
        } elseif ($size === 'full') {
            Log::info("Full size - using original for " . $type);
            return $originalImagePath;
        }

        $newWidth = (int)($width * $percentage);
        $newHeight = (int)($height * $percentage);
        
        Log::info($type . " target size: " . $newWidth . "x" . $newHeight . " (" . ($percentage * 100) . "%)");

        // Load the image
        $sourceImage = null;
        if ($mime === 'image/jpeg') {
            $sourceImage = imagecreatefromjpeg($fullPath);
        } elseif ($mime === 'image/png') {
            $sourceImage = imagecreatefrompng($fullPath);
        } elseif ($mime === 'image/gif') {
            $sourceImage = imagecreatefromgif($fullPath);
        } else {
            Log::error("Unsupported image type: " . $mime);
            return false;
        }

        if (!$sourceImage) {
            Log::error("Failed to create image resource");
            return false;
        }

        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Handle transparency for PNG
        if ($mime === 'image/png') {
            $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
            imagecolortransparent($newImage, $transparent);
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }

        // Perform the resize
        $resizeSuccess = imagecopyresampled(
            $newImage, $sourceImage, 
            0, 0, 0, 0, 
            $newWidth, $newHeight, $width, $height
        );

        if (!$resizeSuccess) {
            Log::error("Image resize operation failed for " . $type);
            imagedestroy($sourceImage);
            imagedestroy($newImage);
            return false;
        }

        // Generate new filename for resized version with organized path
        $pathInfo = pathinfo($originalImagePath);
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'];
        
        // Create the organized folder if it doesn't exist
        $resizedFolderPath = 'public/' . $folderPath;
        if (!Storage::exists($resizedFolderPath)) {
            Storage::makeDirectory($resizedFolderPath);
        }
        
        $resizedFilename = $filename . '_' . $type . '_' . $size . '.' . $extension;
        $resizedPath = $folderPath . '/' . $resizedFilename;
        $fullResizedPath = storage_path('app/public/' . $resizedPath);

        // Save the resized image
        $saveSuccess = false;
        if ($mime === 'image/jpeg') {
            $saveSuccess = imagejpeg($newImage, $fullResizedPath, 90);
        } elseif ($mime === 'image/png') {
            $saveSuccess = imagepng($newImage, $fullResizedPath, 9);
        } elseif ($mime === 'image/gif') {
            $saveSuccess = imagegif($newImage, $fullResizedPath);
        }

        // Clean up memory
        imagedestroy($sourceImage);
        imagedestroy($newImage);

        if ($saveSuccess) {
            Log::info($type . " RESIZE SUCCESS: " . $width . "x" . $height . " -> " . $newWidth . "x" . $newHeight);
            Log::info("Resized image saved at: " . $resizedPath);
            return $resizedPath;
        } else {
            Log::error("Failed to save " . $type . " resized image");
            return false;
        }
    }

    /**
     * Update an existing blog post
     */
    public function update(Request $request, Blog $blog)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'blocks' => 'required|array|min:1'
        ]);

        $processedBlocks = [];

        foreach ($request->blocks as $index => $blockData) {
            $content = $blockData['content'] ?? '';
            $image_caption = $blockData['image_caption'] ?? '';
            
            // Desktop settings
            $desktop_size = $blockData['desktop_size'] ?? 'medium';
            $desktop_wbone_image = isset($blockData['desktop_wbone_image']);
            $desktop_display_size = $blockData['desktop_display_size'] ?? 'contain';
            
            // Mobile settings
            $mobile_size = $blockData['mobile_size'] ?? 'medium';
            $mobile_wbone_image = isset($blockData['mobile_wbone_image']);
            $mobile_display_size = $blockData['mobile_display_size'] ?? 'contain';
            
            $processedBlock = [
                'content' => $content,
                'image_caption' => $image_caption,
                // Desktop settings
                'desktop_size' => $desktop_size,
                'desktop_wbone_image' => $desktop_wbone_image,
                'desktop_display_size' => $desktop_display_size,
                // Mobile settings
                'mobile_size' => $mobile_size,
                'mobile_wbone_image' => $mobile_wbone_image,
                'mobile_display_size' => $mobile_display_size
            ];

            if ($request->hasFile("images." . $index)) {
                // Delete old images if they exist
                $oldBlock = $blog->content[$index] ?? null;
                if ($oldBlock) {
                    $imagesToDelete = [
                        $oldBlock['original_image'] ?? null,
                        $oldBlock['desktop_image'] ?? null,
                        $oldBlock['mobile_image'] ?? null
                    ];
                    
                    foreach ($imagesToDelete as $oldImage) {
                        if (!empty($oldImage)) {
                            Storage::disk('public')->delete($oldImage);
                            Log::info("Deleted old image: " . $oldImage);
                        }
                    }
                }

                $image = $request->file("images." . $index);
                
                // Generate folder structure based on current date
                $currentDate = Carbon::now();
                $year = $currentDate->year;
                $month = $currentDate->month;
                $day = $currentDate->day;
                
                $folderPath = "blog-images/{$year}/{$month}/{$day}";
                
                // Store original image with organized path
                $originalImagePath = $image->store($folderPath, 'public');
                Log::info("New original image uploaded: " . $originalImagePath);
                
                // Create desktop version with organized path
                $desktopImagePath = $this->createResizedVersion($originalImagePath, $desktop_size, 'desktop', $folderPath);
                if ($desktopImagePath) {
                    $processedBlock['desktop_image'] = $desktopImagePath;
                    Log::info("New desktop image created: " . $desktopImagePath);
                }
                
                // Create mobile version with organized path
                $mobileImagePath = $this->createResizedVersion($originalImagePath, $mobile_size, 'mobile', $folderPath);
                if ($mobileImagePath) {
                    $processedBlock['mobile_image'] = $mobileImagePath;
                    Log::info("New mobile image created: " . $mobileImagePath);
                }
                
                // Store original image path as well
                $processedBlock['original_image'] = $originalImagePath;
            } else {
                // Keep existing images if no new image uploaded
                $oldBlock = $blog->content[$index] ?? null;
                if ($oldBlock) {
                    $processedBlock['original_image'] = $oldBlock['original_image'] ?? null;
                    $processedBlock['desktop_image'] = $oldBlock['desktop_image'] ?? null;
                    $processedBlock['mobile_image'] = $oldBlock['mobile_image'] ?? null;
                }
            }

            $processedBlocks[] = $processedBlock;
        }

        $blog->update([
            'title' => $request->title,
            'content' => $processedBlocks
        ]);

        Log::info('Blog updated successfully: ' . $blog->id);
        return redirect()->route('blog.edit')->with('success', 'Blog updated successfully!');
    }

    /**
     * Delete a blog post
     */
    public function destroy(Blog $blog)
    {
        if ($blog->content && is_array($blog->content)) {
            foreach ($blog->content as $block) {
                $imagesToDelete = [
                    $block['original_image'] ?? null,
                    $block['desktop_image'] ?? null,
                    $block['mobile_image'] ?? null
                ];
                
                foreach ($imagesToDelete as $image) {
                    if (!empty($image)) {
                        Storage::disk('public')->delete($image);
                        Log::info("Deleted image: " . $image);
                    }
                }
            }
        }

        $blog->delete();

        return redirect()->route('blog.edit')->with('success', 'Blog deleted successfully!');
    }

    /**
     * Like a blog post
     */
    public function like(Blog $blog)
    {
        $blog->increment('likes');
        return response()->json(['success' => true, 'likes' => $blog->likes]);
    }

    /**
     * Get blog data for modal display
     */
    public function modal(Blog $blog)
    {
        return response()->json([
            'id' => $blog->id,
            'title' => $blog->title,
            'content' => $blog->content,
            'likes' => $blog->likes,
            'created_at' => $blog->created_at->toISOString(),
            'featured_image' => $blog->featured_image,
            'blocks' => $blog->content // This should contain desktop_image, original_image, mobile_image fields
        ]);
    }
}