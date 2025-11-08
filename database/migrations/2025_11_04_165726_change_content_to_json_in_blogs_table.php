<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up()
    {
        // First, backup the data
        $blogs = DB::table('blogs')->get();
        
        // Change the column type to JSON
        Schema::table('blogs', function (Blueprint $table) {
            $table->json('content_new')->nullable()->after('content');
        });

        // Convert existing data
        foreach ($blogs as $blog) {
            try {
                $content = $blog->content;
                
                // If content is already a JSON string, use it directly
                if (is_string($content) && json_decode($content) !== null) {
                    $newContent = $content;
                } 
                // If content is a PHP array/object, encode it
                else if (is_array($content) || is_object($content)) {
                    $newContent = json_encode($content);
                }
                // If it's a string but not JSON, wrap it in a block structure
                else {
                    $newContent = json_encode([
                        [
                            'content' => $content,
                            'image' => null,
                            'image_caption' => '',
                            'desktop_size' => 'medium',
                            'wbone_image' => false,
                            'display_size' => 'contain'
                        ]
                    ]);
                }
                
                DB::table('blogs')
                    ->where('id', $blog->id)
                    ->update(['content_new' => $newContent]);
                    
            } catch (\Exception $e) {
                Log::error("Error converting blog {$blog->id}: " . $e->getMessage());
            }
        }

        // Drop old column and rename new one
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn('content');
            $table->renameColumn('content_new', 'content');
        });
    }

    public function down()
    {
        // Reverse the process if needed
        Schema::table('blogs', function (Blueprint $table) {
            $table->text('content_old')->after('content');
        });

        $blogs = DB::table('blogs')->get();
        
        foreach ($blogs as $blog) {
            $content = $blog->content;
            if (is_string($content) && json_decode($content) !== null) {
                $decoded = json_decode($content, true);
                // Convert back to simple text if it was a single block
                if (is_array($decoded) && count($decoded) === 1 && isset($decoded[0]['content'])) {
                    $oldContent = $decoded[0]['content'];
                } else {
                    $oldContent = $content;
                }
            } else {
                $oldContent = $content;
            }
            
            DB::table('blogs')
                ->where('id', $blog->id)
                ->update(['content_old' => $oldContent]);
        }

        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn('content');
            $table->renameColumn('content_old', 'content');
            $table->text('content')->change();
        });
    }
};