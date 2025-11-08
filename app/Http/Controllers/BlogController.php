<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::latest()->get();
        
        $formattedBlogs = array();
        foreach ($blogs as $blog) {
            $formattedBlogs[] = array(
                'id' => $blog->id,
                'title' => $blog->title,
                'content' => $blog->content,
                'likes' => $blog->likes,
                'created_at' => $blog->created_at,
                'updated_at' => $blog->updated_at,
                'featured_image' => $blog->featured_image,
            );
        }

        return Inertia::render('Blog/Index', array(
            'blogs' => $formattedBlogs
        ));
    }

    public function edit()
    {
        $blogs = Blog::latest()->get();
        
        $formattedBlogs = array();
        foreach ($blogs as $blog) {
            $formattedBlogs[] = array(
                'id' => $blog->id,
                'title' => $blog->title,
                'content' => $blog->content,
                'likes' => $blog->likes,
                'created_at' => $blog->created_at,
                'updated_at' => $blog->updated_at,
            );
        }

        return Inertia::render('Blog/Edit', array(
            'blogs' => $formattedBlogs
        ));
    }

    public function store(Request $request)
    {
        Log::info('=== BLOG STORE START ===');
        
        // Critical: Check GD in web context
        if (extension_loaded('gd')) {
            Log::info('SUCCESS: GD extension is LOADED in web server!');
            $gd_info = gd_info();
            Log::info('GD Version: ' . $gd_info['GD Version']);
        } else {
            Log::error('PROBLEM: GD extension not loaded in web server!');
            Log::error('Please restart Apache after enabling GD in php.ini');
        }

        $request->validate(array(
            'title' => 'required|string|max:255',
            'blocks' => 'required|array|min:1'
        ));

        $processedBlocks = array();

        foreach ($request->blocks as $index => $blockData) {
            $content = isset($blockData['content']) ? $blockData['content'] : '';
            $image_caption = isset($blockData['image_caption']) ? $blockData['image_caption'] : '';
            
            // Desktop settings
            $desktop_size = isset($blockData['desktop_size']) ? $blockData['desktop_size'] : 'medium';
            $desktop_wbone_image = isset($blockData['desktop_wbone_image']) ? $blockData['desktop_wbone_image'] : false;
            $desktop_display_size = isset($blockData['desktop_display_size']) ? $blockData['desktop_display_size'] : 'contain';
            
            // Mobile settings
            $mobile_size = isset($blockData['mobile_size']) ? $blockData['mobile_size'] : 'medium';
            $mobile_wbone_image = isset($blockData['mobile_wbone_image']) ? $blockData['mobile_wbone_image'] : false;
            $mobile_display_size = isset($blockData['mobile_display_size']) ? $blockData['mobile_display_size'] : 'contain';
            
            $block = array(
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
            );

            if ($request->hasFile("images." . $index)) {
                $image = $request->file("images." . $index);

                $imageValidator = Validator::make(array('image' => $image), array(
                    'image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120'
                ));

                if ($imageValidator->fails()) {
                    continue;
                }

                // Store original image
                $originalImagePath = $image->store('blog-images', 'public');
                Log::info("Original image uploaded: " . $originalImagePath);
                
                // Create desktop version
                $desktopImagePath = $this->createResizedVersion($originalImagePath, $desktop_size, 'desktop');
                if ($desktopImagePath) {
                    $block['desktop_image'] = $desktopImagePath;
                    Log::info("Desktop image created: " . $desktopImagePath . " with size: " . $desktop_size);
                }
                
                // Create mobile version
                $mobileImagePath = $this->createResizedVersion($originalImagePath, $mobile_size, 'mobile');
                if ($mobileImagePath) {
                    $block['mobile_image'] = $mobileImagePath;
                    Log::info("Mobile image created: " . $mobileImagePath . " with size: " . $mobile_size);
                }
                
                // Store original image path as well (optional)
                $block['original_image'] = $originalImagePath;
            }

            $processedBlocks[] = $block;
        }

        $blog = Blog::create(array(
            'title' => $request->title,
            'content' => $processedBlocks,
            'likes' => 0
        ));

        Log::info('Blog created with ID: ' . $blog->id);
        Log::info('=== BLOG STORE END ===');

        return response()->json(array(
            'success' => true,
            'message' => 'Blog created successfully!',
            'blog' => $blog
        ));
    }

    private function createResizedVersion($originalImagePath, $size, $type = 'desktop')
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
            return $originalImagePath; // Return original path for full size
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

        // Generate new filename for resized version
        $pathInfo = pathinfo($originalImagePath);
        $resizedFilename = $pathInfo['filename'] . '_' . $type . '_' . $size . '.' . $pathInfo['extension'];
        $resizedPath = $pathInfo['dirname'] . '/' . $resizedFilename;
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
            // Verify the new size
            $newInfo = getimagesize($fullResizedPath);
            if ($newInfo) {
                Log::info($type . " RESIZE SUCCESS: " . $width . "x" . $height . " -> " . $newInfo[0] . "x" . $newInfo[1]);
            } else {
                Log::info($type . " RESIZE SUCCESS: " . $width . "x" . $height . " -> " . $newWidth . "x" . $newHeight);
            }
            return $resizedPath;
        } else {
            Log::error("Failed to save " . $type . " resized image");
            return false;
        }
    }

    public function update(Request $request, Blog $blog)
    {
        $request->validate(array(
            'title' => 'required|string|max:255',
            'blocks' => 'required|array|min:1'
        ));

        $processedBlocks = array();

        foreach ($request->blocks as $index => $blockData) {
            $content = isset($blockData['content']) ? $blockData['content'] : '';
            $image_caption = isset($blockData['image_caption']) ? $blockData['image_caption'] : '';
            
            // Desktop settings
            $desktop_size = isset($blockData['desktop_size']) ? $blockData['desktop_size'] : 'medium';
            $desktop_wbone_image = isset($blockData['desktop_wbone_image']) ? $blockData['desktop_wbone_image'] : false;
            $desktop_display_size = isset($blockData['desktop_display_size']) ? $blockData['desktop_display_size'] : 'contain';
            
            // Mobile settings
            $mobile_size = isset($blockData['mobile_size']) ? $blockData['mobile_size'] : 'medium';
            $mobile_wbone_image = isset($blockData['mobile_wbone_image']) ? $blockData['mobile_wbone_image'] : false;
            $mobile_display_size = isset($blockData['mobile_display_size']) ? $blockData['mobile_display_size'] : 'contain';
            
            $processedBlock = array(
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
            );

            if ($request->hasFile("images." . $index)) {
                // Delete old images if they exist
                $oldBlock = isset($blog->content[$index]) ? $blog->content[$index] : null;
                if ($oldBlock) {
                    $imagesToDelete = array(
                        isset($oldBlock['original_image']) ? $oldBlock['original_image'] : null,
                        isset($oldBlock['desktop_image']) ? $oldBlock['desktop_image'] : null,
                        isset($oldBlock['mobile_image']) ? $oldBlock['mobile_image'] : null
                    );
                    
                    foreach ($imagesToDelete as $oldImage) {
                        if (!empty($oldImage)) {
                            Storage::disk('public')->delete($oldImage);
                            Log::info("Deleted old image: " . $oldImage);
                        }
                    }
                }

                $image = $request->file("images." . $index);
                
                // Store original image
                $originalImagePath = $image->store('blog-images', 'public');
                Log::info("New original image uploaded: " . $originalImagePath);
                
                // Create desktop version
                $desktopImagePath = $this->createResizedVersion($originalImagePath, $desktop_size, 'desktop');
                if ($desktopImagePath) {
                    $processedBlock['desktop_image'] = $desktopImagePath;
                    Log::info("New desktop image created: " . $desktopImagePath);
                }
                
                // Create mobile version
                $mobileImagePath = $this->createResizedVersion($originalImagePath, $mobile_size, 'mobile');
                if ($mobileImagePath) {
                    $processedBlock['mobile_image'] = $mobileImagePath;
                    Log::info("New mobile image created: " . $mobileImagePath);
                }
                
                // Store original image path as well
                $processedBlock['original_image'] = $originalImagePath;
            } else {
                // Keep existing images if no new image uploaded
                $oldBlock = isset($blog->content[$index]) ? $blog->content[$index] : null;
                if ($oldBlock) {
                    $processedBlock['original_image'] = isset($oldBlock['original_image']) ? $oldBlock['original_image'] : null;
                    $processedBlock['desktop_image'] = isset($oldBlock['desktop_image']) ? $oldBlock['desktop_image'] : null;
                    $processedBlock['mobile_image'] = isset($oldBlock['mobile_image']) ? $oldBlock['mobile_image'] : null;
                }
            }

            $processedBlocks[] = $processedBlock;
        }

        $blog->update(array(
            'title' => $request->title,
            'content' => $processedBlocks
        ));

        Log::info('Blog updated successfully: ' . $blog->id);
        return redirect()->back()->with('success', 'Blog updated successfully!');
    }

    public function destroy(Blog $blog)
    {
        if ($blog->content && is_array($blog->content)) {
            foreach ($blog->content as $block) {
                $imagesToDelete = array(
                    isset($block['original_image']) ? $block['original_image'] : null,
                    isset($block['desktop_image']) ? $block['desktop_image'] : null,
                    isset($block['mobile_image']) ? $block['mobile_image'] : null
                );
                
                foreach ($imagesToDelete as $image) {
                    if (isset($image) && !empty($image)) {
                        Storage::disk('public')->delete($image);
                        Log::info("Deleted image: " . $image);
                    }
                }
            }
        }

        $blog->delete();

        return redirect()->back()->with('success', 'Blog deleted successfully!');
    }

    public function like(Blog $blog)
    {
        $blog->increment('likes');
        return redirect()->back();
    }
}