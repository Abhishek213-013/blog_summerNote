<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Blog</title>
    
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    
    <style>
        /* Additional styles for the blog functionality */
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .blog-card {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
            background: white;
        }

        .blog-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border-color: #3b82f6;
        }

        .blog-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .blog-content {
            padding: 1.5rem;
        }

        .blog-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1f2937;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .blog-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }

        .like-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            background-color: #f3f4f6;
            border: none;
            border-radius: 0.375rem;
            color: #6b7280;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .like-btn:hover {
            background-color: #e5e7eb;
            color: #ef4444;
        }

        .like-btn.liked {
            color: #ef4444;
            background-color: #fef2f2;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 2rem;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            position: relative;
            background: white;
            border-radius: 1rem;
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }

        .modal-close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50%;
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            transition: all 0.2s;
            backdrop-filter: blur(10px);
        }

        .modal-close-btn:hover {
            background: #ef4444;
            color: white;
            transform: scale(1.1);
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-featured-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }

        .modal-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 2rem;
            line-height: 1.2;
        }

        .modal-block {
            margin-bottom: 2rem;
        }

        .modal-block-content {
            font-size: 1.125rem;
            line-height: 1.7;
            color: #374151;
            margin-bottom: 1.5rem;
        }

        .modal-image-container {
            margin: 1rem 0;
        }

        .modal-block-image {
            max-width: 100%;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .modal-block-image.display-contain {
            object-fit: contain;
            max-height: 500px;
            width: auto;
            margin: 0 auto;
            display: block;
        }

        .modal-block-image.display-cover {
            object-fit: cover;
            width: 100%;
            height: 400px;
        }

        .modal-block-image.display-auto {
            max-width: 100%;
            height: auto;
        }

        .modal-block-image.white-bg {
            background: white;
            padding: 1rem;
            border: 1px solid #e5e7eb;
        }

        .modal-image-caption {
            text-align: center;
            font-style: italic;
            color: #6b7280;
            margin-top: 0.5rem;
            font-size: 0.95rem;
            line-height: 1.4;
            padding: 0 1rem;
        }

        .modal-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 2px solid #e5e7eb;
        }

        .modal-like-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            background-color: #f3f4f6;
            border: none;
            border-radius: 0.5rem;
            color: #6b7280;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .modal-like-btn:hover {
            background-color: #e5e7eb;
            color: #ef4444;
            transform: translateY(-1px);
        }

        .modal-like-btn.liked {
            color: #ef4444;
            background-color: #fef2f2;
        }

        .modal-date {
            font-size: 1rem;
            color: #6b7280;
            font-weight: 500;
        }

        /* Form Styles */
        .block-container {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            background: #f9fafb;
        }

        .block-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .block-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #374151;
        }

        .image-upload-container {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .image-upload-container:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }

        .file-input {
            display: none;
        }

        .upload-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .upload-icon {
            margin-bottom: 0.5rem;
        }

        .upload-text {
            font-weight: 500;
            color: #374151;
        }

        .upload-hint {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .image-preview {
            position: relative;
            display: inline-block;
        }

        .preview-image {
            max-width: 200px;
            max-height: 150px;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
        }

        .remove-image-btn {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .remove-image-btn:hover {
            background: #dc2626;
        }

        .settings-container {
            margin-top: 1.5rem;
            padding: 1rem;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }

        .mobile-settings {
            background: #f0f9ff;
            border-color: #bae6fd;
        }

        .settings-title {
            font-size: 1rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 1rem;
        }

        .mobile-settings .settings-title {
            color: #0369a1;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .setting-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .setting-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }

        .toggle-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .toggle-checkbox {
            width: 3rem;
            height: 1.5rem;
            background-color: #d1d5db;
            border-radius: 9999px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
            appearance: none;
            -webkit-appearance: none;
        }

        .toggle-checkbox:checked {
            background-color: #10b981;
        }

        .toggle-checkbox::before {
            content: '';
            position: absolute;
            top: 0.125rem;
            left: 0.125rem;
            width: 1.25rem;
            height: 1.25rem;
            background-color: white;
            border-radius: 50%;
            transition: transform 0.3s ease;
        }

        .toggle-checkbox:checked::before {
            transform: translateX(1.5rem);
        }

        .toggle-label {
            font-size: 0.875rem;
            color: #374151;
            cursor: pointer;
        }

        /* Blog List Styles */
        .blog-list {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .blog-item {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .blog-item:last-child {
            border-bottom: none;
        }

        .blog-item-title {
            font-weight: 500;
            color: #1f2937;
        }

        .blog-item-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .blog-actions {
            display: flex;
            gap: 0.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .blog-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-overlay {
                padding: 1rem;
            }
            
            .modal-body {
                padding: 1.5rem;
            }
            
            .modal-title {
                font-size: 2rem;
            }
            
            .modal-meta {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .settings-grid {
                grid-template-columns: 1fr;
            }
            
            .block-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="min-h-screen bg-gray-100">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-title">My Blog</div>
                <div class="nav-links">
                    <a href="{{ route('blog.index') }}" class="nav-link">
                        Read Blogs
                    </a>
                </div>
            </div>
        </nav>

        <main>
            @yield('content')
        </main>
    </div>

    <!-- CSRF Token for AJAX requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- JavaScript -->
    <script>
        // CSRF token setup for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').content
            }
        });

        // Blog modal functionality
        function openBlogModal(blogId) {
            fetch(`/blogs/${blogId}/modal`)
                .then(response => response.json())
                .then(blog => {
                    const modalHtml = `
                        <div class="modal-overlay" onclick="closeBlogModal()">
                            <div class="modal-content" onclick="event.stopPropagation()">
                                <button class="modal-close-btn" onclick="closeBlogModal()">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <div class="modal-body">
                                    ${blog.featured_image ? `<img src="/storage/${blog.featured_image}" alt="${blog.title}" class="modal-featured-image">` : ''}
                                    <h1 class="modal-title">${blog.title}</h1>
                                    ${blog.blocks.map(block => `
                                        <div class="modal-block">
                                            <div class="modal-block-content">${block.content}</div>
                                            ${block.image ? `
                                                <div class="modal-image-container">
                                                    <img src="/storage/${block.image}" alt="${block.image_caption || blog.title}" class="modal-block-image ${getImageClasses(block)}">
                                                    ${block.image_caption ? `<div class="modal-image-caption">${block.image_caption}</div>` : ''}
                                                </div>
                                            ` : ''}
                                        </div>
                                    `).join('')}
                                    <div class="modal-meta">
                                        <div class="modal-likes">
                                            <button onclick="likeBlog(${blog.id}, this)" class="modal-like-btn ${isLiked(blog.id) ? 'liked' : ''}">
                                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                                                </svg>
                                                <span>${blog.likes} Likes</span>
                                            </button>
                                        </div>
                                        <div class="modal-date">
                                            Published on ${new Date(blog.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.insertAdjacentHTML('beforeend', modalHtml);
                    document.body.style.overflow = 'hidden';
                });
        }

        function closeBlogModal() {
            const modal = document.querySelector('.modal-overlay');
            if (modal) {
                modal.remove();
                document.body.style.overflow = '';
            }
        }

        function getImageClasses(block) {
            const classes = [];
            if (block.display_size) {
                classes.push(`display-${block.display_size}`);
            }
            if (block.wbone_image) {
                classes.push('white-bg');
            }
            return classes.join(' ');
        }

        function isLiked(blogId) {
            return localStorage.getItem(`liked_${blogId}`) === 'true';
        }

        function likeBlog(blogId, button) {
            fetch(`/blogs/${blogId}/like`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const isCurrentlyLiked = localStorage.getItem(`liked_${blogId}`) === 'true';
                    localStorage.setItem(`liked_${blogId}`, !isCurrentlyLiked);
                    
                    if (button) {
                        button.classList.toggle('liked', !isCurrentlyLiked);
                        const likeCount = button.querySelector('span');
                        const currentLikes = parseInt(likeCount.textContent);
                        likeCount.textContent = `${isCurrentlyLiked ? currentLikes - 1 : currentLikes + 1} Likes`;
                    }
                    
                    // Update like count on blog cards
                    const blogCard = document.querySelector(`[data-blog-id="${blogId}"] .like-btn span`);
                    if (blogCard) {
                        const currentLikes = parseInt(blogCard.textContent);
                        blogCard.textContent = `${isCurrentlyLiked ? currentLikes - 1 : currentLikes + 1} Likes`;
                    }
                }
            });
        }

        // ESC key to close modal
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeBlogModal();
            }
        });

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

        // Image upload functionality
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
                    previewContainer.innerHTML = `
                        <div class="image-preview">
                            <img src="${e.target.result}" alt="Preview" class="preview-image">
                            <button type="button" onclick="removeImage(this)" class="remove-image-btn">×</button>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            }
        }

        function removeImage(button) {
            const container = button.closest('.image-upload-container');
            const input = container.querySelector('.file-input');
            const previewContainer = container.querySelector('.image-preview-container');
            
            input.value = '';
            previewContainer.innerHTML = `
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
            `;
        }

        // Drag and drop functionality
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
    </script>

    @yield('scripts')
</body>
</html>