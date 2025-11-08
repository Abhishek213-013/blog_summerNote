@extends('layouts.app')

@section('content')
<div class="container">
    <div class="p-6 bg-white rounded-lg shadow-sm">
        <h1 class="text-3xl font-bold mb-8 text-center">Manage Blog Posts</h1>

        <!-- Blog Form -->
        <div class="form-container">
            <form action="{{ $editingBlog ? route('blog.update', $editingBlog->id) : route('blog.store') }}" method="POST" enctype="multipart/form-data" id="blogForm">
                @csrf
                @if($editingBlog)
                    @method('PUT')
                @endif

                <div class="space-y-6">
                    <!-- Title -->
                    <div class="form-group">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" value="{{ old('title', $editingBlog->title ?? '') }}" required class="form-input" placeholder="Enter blog title">
                    </div>

                    <!-- Content Blocks -->
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <label class="form-label">Content Blocks</label>
                            <button type="button" id="addBlockBtn" class="btn btn-primary">+ Add New Block</button>
                        </div>

                        <div id="blocks-container">
                            @php
                                $blocks = old('blocks', $editingBlog ? $editingBlog->content : [['content' => '', 'image_caption' => '', 'desktop_size' => 'medium', 'desktop_wbone_image' => false, 'desktop_display_size' => 'contain', 'mobile_size' => 'medium', 'mobile_wbone_image' => false, 'mobile_display_size' => 'contain']]);
                                if (empty($blocks)) {
                                    $blocks = [['content' => '', 'image_caption' => '', 'desktop_size' => 'medium', 'desktop_wbone_image' => false, 'desktop_display_size' => 'contain', 'mobile_size' => 'medium', 'mobile_wbone_image' => false, 'mobile_display_size' => 'contain']];
                                }
                                $initialBlockCount = count($blocks);
                            @endphp

                            @foreach($blocks as $index => $block)
                            <div class="block-container" id="block-{{ $index }}">
                                <div class="block-header">
                                    <h3 class="block-title">Block {{ $index + 1 }}</h3>
                                    @if($index > 0)
                                    <button type="button" class="remove-block-btn btn btn-danger btn-sm" data-index="{{ $index }}">Remove</button>
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="form-group">
                                    <label class="form-label">Content</label>
                                    <div class="editor-container">
                                        <textarea name="blocks[{{ $index }}][content]" class="form-input block-textarea summernote">{{ old("blocks.$index.content", $block['content'] ?? '') }}</textarea>
                                    </div>
                                </div>

                                <!-- Image Upload -->
                                <div class="form-group">
                                    <label class="form-label">Image</label>
                                    <div class="image-upload-container" onclick="triggerFileInput(this)">
                                        <input type="file" name="images[{{ $index }}]" class="file-input" accept="image/*">
                                        <div class="image-preview-container">
                                            @if(isset($block['original_image']) && $block['original_image'])
                                            <div class="image-preview">
                                                <img src="/storage/{{ $block['original_image'] }}" alt="Preview" class="preview-image">
                                                <button type="button" class="remove-image-btn" onclick="removeImage(this)">×</button>
                                            </div>
                                            @else
                                            <div class="upload-placeholder">
                                                <div class="upload-icon">
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                                <span class="upload-text">Click to upload an image</span>
                                                <span class="upload-hint">or drag & drop</span>
                                                <span class="upload-hint">PNG, JPG, GIF up to 5MB</span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Image Caption -->
                                    <div class="mt-3">
                                        <input type="text" name="blocks[{{ $index }}][image_caption]" value="{{ old("blocks.$index.image_caption", $block['image_caption'] ?? '') }}" class="form-input" placeholder="Image caption (optional)">
                                    </div>
                                </div>

                                <!-- Desktop Image Settings -->
                                <div class="settings-container">
                                    <h4 class="settings-title">🖥️ Desktop Image Settings</h4>
                                    <div class="settings-grid">
                                        <!-- Size Setting -->
                                        <div class="setting-group">
                                            <label class="setting-label">Size</label>
                                            <select name="blocks[{{ $index }}][desktop_size]" class="form-input">
                                                <option value="small" {{ (old("blocks.$index.desktop_size", $block['desktop_size'] ?? 'medium') == 'small') ? 'selected' : '' }}>Small (25%)</option>
                                                <option value="medium" {{ (old("blocks.$index.desktop_size", $block['desktop_size'] ?? 'medium') == 'medium') ? 'selected' : '' }}>Medium (50%)</option>
                                                <option value="large" {{ (old("blocks.$index.desktop_size", $block['desktop_size'] ?? 'medium') == 'large') ? 'selected' : '' }}>Large (75%)</option>
                                                <option value="full" {{ (old("blocks.$index.desktop_size", $block['desktop_size'] ?? 'medium') == 'full') ? 'selected' : '' }}>Full Width (100%)</option>
                                            </select>
                                        </div>

                                        <!-- White Background Toggle -->
                                        <div class="setting-group">
                                            <label class="setting-label">White Background</label>
                                            <div class="toggle-container">
                                                <input type="checkbox" name="blocks[{{ $index }}][desktop_wbone_image]" value="1" class="toggle-checkbox" id="desktop-wbone-toggle-{{ $index }}" {{ (old("blocks.$index.desktop_wbone_image", $block['desktop_wbone_image'] ?? false) ? 'checked' : '') }}>
                                                <label for="desktop-wbone-toggle-{{ $index }}" class="toggle-label">{{ (old("blocks.$index.desktop_wbone_image", $block['desktop_wbone_image'] ?? false) ? 'On' : 'Off') }}</label>
                                            </div>
                                        </div>

                                        <!-- Display Size -->
                                        <div class="setting-group">
                                            <label class="setting-label">Display Size</label>
                                            <select name="blocks[{{ $index }}][desktop_display_size]" class="form-input">
                                                <option value="contain" {{ (old("blocks.$index.desktop_display_size", $block['desktop_display_size'] ?? 'contain') == 'contain') ? 'selected' : '' }}>Contain</option>
                                                <option value="cover" {{ (old("blocks.$index.desktop_display_size", $block['desktop_display_size'] ?? 'contain') == 'cover') ? 'selected' : '' }}>Cover</option>
                                                <option value="auto" {{ (old("blocks.$index.desktop_display_size", $block['desktop_display_size'] ?? 'contain') == 'auto') ? 'selected' : '' }}>Auto</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mobile Image Settings -->
                                <div class="settings-container mobile-settings">
                                    <h4 class="settings-title">📱 Mobile Image Settings</h4>
                                    <div class="settings-grid">
                                        <!-- Size Setting -->
                                        <div class="setting-group">
                                            <label class="setting-label">Size</label>
                                            <select name="blocks[{{ $index }}][mobile_size]" class="form-input">
                                                <option value="small" {{ (old("blocks.$index.mobile_size", $block['mobile_size'] ?? 'medium') == 'small') ? 'selected' : '' }}>Small (25%)</option>
                                                <option value="medium" {{ (old("blocks.$index.mobile_size", $block['mobile_size'] ?? 'medium') == 'medium') ? 'selected' : '' }}>Medium (50%)</option>
                                                <option value="large" {{ (old("blocks.$index.mobile_size", $block['mobile_size'] ?? 'medium') == 'large') ? 'selected' : '' }}>Large (75%)</option>
                                                <option value="full" {{ (old("blocks.$index.mobile_size", $block['mobile_size'] ?? 'medium') == 'full') ? 'selected' : '' }}>Full Width (100%)</option>
                                            </select>
                                        </div>

                                        <!-- White Background Toggle -->
                                        <div class="setting-group">
                                            <label class="setting-label">White Background</label>
                                            <div class="toggle-container">
                                                <input type="checkbox" name="blocks[{{ $index }}][mobile_wbone_image]" value="1" class="toggle-checkbox" id="mobile-wbone-toggle-{{ $index }}" {{ (old("blocks.$index.mobile_wbone_image", $block['mobile_wbone_image'] ?? false) ? 'checked' : '') }}>
                                                <label for="mobile-wbone-toggle-{{ $index }}" class="toggle-label">{{ (old("blocks.$index.mobile_wbone_image", $block['mobile_wbone_image'] ?? false) ? 'On' : 'Off') }}</label>
                                            </div>
                                        </div>

                                        <!-- Display Size -->
                                        <div class="setting-group">
                                            <label class="setting-label">Display Size</label>
                                            <select name="blocks[{{ $index }}][mobile_display_size]" class="form-input">
                                                <option value="contain" {{ (old("blocks.$index.mobile_display_size", $block['mobile_display_size'] ?? 'contain') == 'contain') ? 'selected' : '' }}>Contain</option>
                                                <option value="cover" {{ (old("blocks.$index.mobile_display_size", $block['mobile_display_size'] ?? 'contain') == 'cover') ? 'selected' : '' }}>Cover</option>
                                                <option value="auto" {{ (old("blocks.$index.mobile_display_size", $block['mobile_display_size'] ?? 'contain') == 'auto') ? 'selected' : '' }}>Auto</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-between pt-4">
                        <button type="button" id="resetBtn" class="btn btn-warning">Reset</button>
                        <div class="flex gap-4">
                            @if($editingBlog)
                            <a href="{{ route('blog.edit') }}" class="btn btn-danger">Cancel Edit</a>
                            @endif
                            <button type="submit" class="btn btn-success">
                                {{ $editingBlog ? 'Update Blog' : 'Create Blog' }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Existing Blogs -->
        <div class="space-y-6">
            <h2 class="text-2xl font-bold mb-4">Existing Blog Posts</h2>
            
            <div class="blog-list">
                @foreach($blogs as $blog)
                <div class="blog-item">
                    <div>
                        <h3 class="blog-item-title">{{ $blog->title }}</h3>
                        <div class="blog-item-meta">
                            <span>Likes: {{ $blog->likes }}</span>
                            <span>Created: {{ $blog->created_at->format('M j, Y') }}</span>
                        </div>
                    </div>
                    <div class="blog-actions">
                        <a href="{{ route('blog.edit', ['edit' => $blog->id]) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('blog.destroy', $blog->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this blog?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>

            @if($blogs->count() === 0)
            <div class="text-center py-8 text-gray-500">
                No blog posts created yet.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Image upload functions
    function triggerFileInput(container) {
        const input = container.querySelector('.file-input');
        input.click();
    }

    function handleImageUpload(input, previewContainer) {
        const file = input.files[0];
        if (file) {
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file (PNG, JPG, GIF)');
                input.value = '';
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('Image size should be less than 5MB');
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                previewContainer.innerHTML = 
                    '<div class="image-preview">' +
                        '<img src="' + e.target.result + '" alt="Preview" class="preview-image">' +
                        '<button type="button" class="remove-image-btn" onclick="removeImage(this)">×</button>' +
                    '</div>';
            };
            reader.readAsDataURL(file);
        }
    }

    function removeImage(button) {
        const container = button.closest('.image-upload-container');
        const input = container.querySelector('.file-input');
        const previewContainer = container.querySelector('.image-preview-container');
        
        input.value = '';
        previewContainer.innerHTML = 
            '<div class="upload-placeholder">' +
                '<div class="upload-icon">' +
                    '<svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>' +
                    '</svg>' +
                '</div>' +
                '<span class="upload-text">Click to upload an image</span>' +
                '<span class="upload-hint">or drag & drop</span>' +
                '<span class="upload-hint">PNG, JPG, GIF up to 5MB</span>' +
            '</div>';
    }

    function handleDragOver(event) {
        event.preventDefault();
        event.currentTarget.style.borderColor = '#3b82f6';
        event.currentTarget.style.backgroundColor = '#f8fafc';
    }

    function handleDragLeave(event) {
        event.preventDefault();
        event.currentTarget.style.borderColor = '#d1d5db';
        event.currentTarget.style.backgroundColor = 'white';
    }

    function handleDrop(event, input) {
        event.preventDefault();
        event.currentTarget.style.borderColor = '#d1d5db';
        event.currentTarget.style.backgroundColor = 'white';
        
        const files = event.dataTransfer.files;
        if (files.length > 0) {
            input.files = files;
            handleImageUpload(input, event.currentTarget.querySelector('.image-preview-container'));
        }
    }

    // Summernote initialization
    function initializeSummernote(textarea) {
        $(textarea).summernote({
            height: 200,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['insert', ['link', 'picture', 'video']],
            ]
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Get initial block count from PHP variable
        let blockCount = <?php echo $initialBlockCount; ?>;
        const blocksContainer = document.getElementById('blocks-container');
        const addBlockBtn = document.getElementById('addBlockBtn');
        const resetBtn = document.getElementById('resetBtn');

        // Initialize Summernote editors
        document.querySelectorAll('.summernote').forEach(function(textarea) {
            initializeSummernote(textarea);
        });

        // Event listeners
        addBlockBtn.addEventListener('click', addNewBlock);
        resetBtn.addEventListener('click', resetForm);

        // Event delegation for remove buttons
        blocksContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-block-btn')) {
                const index = parseInt(e.target.getAttribute('data-index'));
                removeBlock(index);
            }
        });

        // Event delegation for image uploads
        blocksContainer.addEventListener('change', function(e) {
            if (e.target.classList.contains('file-input')) {
                handleImageUpload(e.target, e.target.parentNode.querySelector('.image-preview-container'));
            }
        });

        // Event delegation for remove image buttons
        blocksContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-image-btn')) {
                removeImage(e.target);
            }
        });

        // Event delegation for toggle checkboxes
        blocksContainer.addEventListener('change', function(e) {
            if (e.target.classList.contains('toggle-checkbox')) {
                const label = document.querySelector('label[for="' + e.target.id + '"]');
                if (label) {
                    label.textContent = e.target.checked ? 'On' : 'Off';
                }
            }
        });

        // Drag and drop events
        blocksContainer.addEventListener('dragover', function(e) {
            if (e.target.classList.contains('image-upload-container')) {
                handleDragOver(e);
            }
        });

        blocksContainer.addEventListener('drop', function(e) {
            if (e.target.classList.contains('image-upload-container')) {
                handleDrop(e, e.target.querySelector('.file-input'));
            }
        });

        function addNewBlock() {
            const newBlock = document.createElement('div');
            newBlock.className = 'block-container';
            newBlock.id = 'block-' + blockCount;
            
            newBlock.innerHTML = getBlockHTML(blockCount);
            blocksContainer.appendChild(newBlock);
            
            // Initialize Summernote for the new block
            const newTextarea = newBlock.querySelector('.summernote');
            initializeSummernote(newTextarea);
            
            blockCount++;
        }

        function getBlockHTML(index) {
            return '<div class="block-header">' +
                '<h3 class="block-title">Block ' + (index + 1) + '</h3>' +
                '<button type="button" class="remove-block-btn btn btn-danger btn-sm" data-index="' + index + '">Remove</button>' +
            '</div>' +
            '<div class="form-group">' +
                '<label class="form-label">Content</label>' +
                '<div class="editor-container">' +
                    '<textarea name="blocks[' + index + '][content]" class="form-input block-textarea summernote"></textarea>' +
                '</div>' +
            '</div>' +
            '<div class="form-group">' +
                '<label class="form-label">Image</label>' +
                '<div class="image-upload-container" onclick="triggerFileInput(this)">' +
                    '<input type="file" name="images[' + index + ']" class="file-input" accept="image/*">' +
                    '<div class="image-preview-container">' +
                        '<div class="upload-placeholder">' +
                            '<div class="upload-icon">' +
                                '<svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>' +
                                '</svg>' +
                            '</div>' +
                            '<span class="upload-text">Click to upload an image</span>' +
                            '<span class="upload-hint">or drag & drop</span>' +
                            '<span class="upload-hint">PNG, JPG, GIF up to 5MB</span>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="mt-3">' +
                    '<input type="text" name="blocks[' + index + '][image_caption]" class="form-input" placeholder="Image caption (optional)">' +
                '</div>' +
            '</div>' +
            '<div class="settings-container">' +
                '<h4 class="settings-title">Desktop Image Settings</h4>' +
                '<div class="settings-grid">' +
                    '<div class="setting-group">' +
                        '<label class="setting-label">Size</label>' +
                        '<select name="blocks[' + index + '][desktop_size]" class="form-input">' +
                            '<option value="small">Small (25%)</option>' +
                            '<option value="medium" selected>Medium (50%)</option>' +
                            '<option value="large">Large (75%)</option>' +
                            '<option value="full">Full Width (100%)</option>' +
                        '</select>' +
                    '</div>' +
                    '<div class="setting-group">' +
                        '<label class="setting-label">White Background</label>' +
                        '<div class="toggle-container">' +
                            '<input type="checkbox" name="blocks[' + index + '][desktop_wbone_image]" value="1" class="toggle-checkbox" id="desktop-wbone-toggle-' + index + '">' +
                            '<label for="desktop-wbone-toggle-' + index + '" class="toggle-label">Off</label>' +
                        '</div>' +
                    '</div>' +
                    '<div class="setting-group">' +
                        '<label class="setting-label">Display Size</label>' +
                        '<select name="blocks[' + index + '][desktop_display_size]" class="form-input">' +
                            '<option value="contain" selected>Contain</option>' +
                            '<option value="cover">Cover</option>' +
                            '<option value="auto">Auto</option>' +
                        '</select>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="settings-container mobile-settings">' +
                '<h4 class="settings-title">Mobile Image Settings</h4>' +
                '<div class="settings-grid">' +
                    '<div class="setting-group">' +
                        '<label class="setting-label">Size</label>' +
                        '<select name="blocks[' + index + '][mobile_size]" class="form-input">' +
                            '<option value="small">Small (25%)</option>' +
                            '<option value="medium" selected>Medium (50%)</option>' +
                            '<option value="large">Large (75%)</option>' +
                            '<option value="full">Full Width (100%)</option>' +
                        '</select>' +
                    '</div>' +
                    '<div class="setting-group">' +
                        '<label class="setting-label">White Background</label>' +
                        '<div class="toggle-container">' +
                            '<input type="checkbox" name="blocks[' + index + '][mobile_wbone_image]" value="1" class="toggle-checkbox" id="mobile-wbone-toggle-' + index + '">' +
                            '<label for="mobile-wbone-toggle-' + index + '" class="toggle-label">Off</label>' +
                        '</div>' +
                    '</div>' +
                    '<div class="setting-group">' +
                        '<label class="setting-label">Display Size</label>' +
                        '<select name="blocks[' + index + '][mobile_display_size]" class="form-input">' +
                            '<option value="contain" selected>Contain</option>' +
                            '<option value="cover">Cover</option>' +
                            '<option value="auto">Auto</option>' +
                        '</select>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }

        function removeBlock(index) {
            const block = document.getElementById('block-' + index);
            if (block) {
                const textarea = block.querySelector('.summernote');
                if (textarea && $(textarea).hasClass('note-editor')) {
                    $(textarea).summernote('destroy');
                }
                block.remove();
                renumberBlocks();
            }
        }

        function renumberBlocks() {
            const blocks = document.querySelectorAll('.block-container');
            blockCount = 0;
            
            blocks.forEach(function(block, index) {
                block.id = 'block-' + index;
                block.querySelector('.block-title').textContent = 'Block ' + (index + 1);
                
                const inputs = block.querySelectorAll('[name]');
                inputs.forEach(function(input) {
                    const name = input.getAttribute('name');
                    const newName = name.replace(/blocks\[\d+\]/, 'blocks[' + index + ']');
                    input.setAttribute('name', newName);
                });
                
                const toggles = block.querySelectorAll('.toggle-checkbox, .toggle-label');
                toggles.forEach(function(toggle) {
                    const id = toggle.getAttribute('id') || toggle.getAttribute('for');
                    if (id) {
                        const newId = id.replace(/\d+$/, index);
                        if (toggle.tagName === 'INPUT') {
                            toggle.setAttribute('id', newId);
                        } else {
                            toggle.setAttribute('for', newId);
                        }
                    }
                });
                
                const removeBtn = block.querySelector('.remove-block-btn');
                if (removeBtn) {
                    removeBtn.setAttribute('data-index', index);
                }
                
                blockCount++;
            });
        }

        function resetForm() {
            if (confirm('Are you sure you want to reset the form? All unsaved changes will be lost.')) {
                document.getElementById('blogForm').reset();
                
                document.querySelectorAll('.summernote').forEach(function(textarea) {
                    if ($(textarea).hasClass('note-editor')) {
                        $(textarea).summernote('reset');
                    }
                });
                
                document.querySelectorAll('.image-preview-container').forEach(function(container) {
                    container.innerHTML = '<div class="upload-placeholder">' +
                        '<div class="upload-icon">' +
                            '<svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>' +
                            '</svg>' +
                        '</div>' +
                        '<span class="upload-text">Click to upload an image</span>' +
                        '<span class="upload-hint">or drag & drop</span>' +
                        '<span class="upload-hint">PNG, JPG, GIF up to 5MB</span>' +
                    '</div>';
                });
            }
        }
    });
</script>
@endsection