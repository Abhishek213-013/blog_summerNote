@extends('layouts.app')

@section('content')
<div class="container">
    <div class="p-6 bg-white rounded-lg shadow-sm">
        <h1 class="text-3xl font-bold mb-8 text-center">My Blog Posts</h1>
        
        <div class="blog-grid">
            @foreach($blogs as $blog)
            <div class="blog-card" data-blog-id="{{ $blog->id }}" data-blog-title="{{ $blog->title }}">
                @php
                    $featuredImage = null;
                    if ($blog->content && is_array($blog->content)) {
                        foreach ($blog->content as $block) {
                            if (!empty($block['desktop_image']) || !empty($block['original_image']) || !empty($block['mobile_image'])) {
                                $featuredImage = $block['desktop_image'] ?? $block['original_image'] ?? $block['mobile_image'];
                                break;
                            }
                        }
                    }
                @endphp
                
                @if($featuredImage)
                <img src="/storage/{{ $featuredImage }}" alt="{{ $blog->title }}" class="blog-image">
                @endif
                
                <div class="blog-content">
                    <h2 class="blog-title">{{ $blog->title }}</h2>
                    
                    <div class="blog-meta">
                        <button class="like-btn" data-blog-id="{{ $blog->id }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ $blog->likes }} Likes</span>
                        </button>
                        <span class="text-sm">
                            {{ $blog->created_at->format('F j, Y') }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($blogs->count() === 0)
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">No blog posts yet.</p>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Blog card click events
        document.querySelectorAll('.blog-card').forEach(function(card) {
            card.addEventListener('click', function() {
                const blogId = this.getAttribute('data-blog-id');
                openBlogModal(blogId);
            });
        });

        // Like button events
        document.querySelectorAll('.like-btn').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const blogId = this.getAttribute('data-blog-id');
                likeBlog(blogId, this);
            });
        });
    });

    function openBlogModal(blogId) {
        fetch('/blogs/' + blogId + '/modal')
            .then(response => response.json())
            .then(blog => {
                console.log('Blog data received:', blog); // Debug log
                
                const modalHtml = 
                    '<div class="modal-overlay" onclick="closeBlogModal()">' +
                        '<div class="modal-content" onclick="event.stopPropagation()">' +
                            '<button class="modal-close-btn" onclick="closeBlogModal()">' +
                                '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />' +
                                '</svg>' +
                            '</button>' +
                            '<div class="modal-body">' +
                                (blog.featured_image ? '<img src="/storage/' + blog.featured_image + '" alt="' + blog.title + '" class="modal-featured-image">' : '') +
                                '<h1 class="modal-title">' + blog.title + '</h1>' +
                                blog.blocks.map(function(block) {
                                    console.log('Processing block:', block); // Debug log
                                    
                                    // Get the correct image path - check all possible image fields
                                    const blockImage = block.desktop_image || block.original_image || block.mobile_image;
                                    console.log('Block image found:', blockImage); // Debug log
                                    
                                    return '<div class="modal-block">' +
                                        '<div class="modal-block-content">' + (block.content || '') + '</div>' +
                                        (blockImage ? 
                                            '<div class="modal-image-container">' +
                                                '<img src="/storage/' + blockImage + '" alt="' + (block.image_caption || blog.title) + '" class="modal-block-image ' + getImageClasses(block) + '">' +
                                                (block.image_caption ? '<div class="modal-image-caption">' + block.image_caption + '</div>' : '') +
                                            '</div>' : '') +
                                    '</div>';
                                }).join('') +
                                '<div class="modal-meta">' +
                                    '<div class="modal-likes">' +
                                        '<button onclick="likeBlog(' + blog.id + ', this)" class="modal-like-btn ' + (isLiked(blog.id) ? 'liked' : '') + '">' +
                                            '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">' +
                                                '<path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />' +
                                            '</svg>' +
                                            '<span>' + blog.likes + ' Likes</span>' +
                                        '</button>' +
                                    '</div>' +
                                    '<div class="modal-date">' +
                                        'Published on ' + new Date(blog.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                document.body.style.overflow = 'hidden';
            })
            .catch(error => {
                console.error('Error fetching blog data:', error);
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
        // Use desktop display settings for modal
        if (block.desktop_display_size) {
            classes.push('display-' + block.desktop_display_size);
        }
        if (block.desktop_wbone_image) {
            classes.push('white-bg');
        }
        return classes.join(' ');
    }

    function isLiked(blogId) {
        return localStorage.getItem('liked_' + blogId) === 'true';
    }

    function likeBlog(blogId, button) {
        fetch('/blogs/' + blogId + '/like', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const isCurrentlyLiked = localStorage.getItem('liked_' + blogId) === 'true';
                localStorage.setItem('liked_' + blogId, !isCurrentlyLiked);
                
                if (button) {
                    button.classList.toggle('liked', !isCurrentlyLiked);
                    const likeCount = button.querySelector('span');
                    const currentLikes = parseInt(likeCount.textContent);
                    likeCount.textContent = (isCurrentlyLiked ? currentLikes - 1 : currentLikes + 1) + ' Likes';
                }
                
                // Update like count on blog cards
                const blogCard = document.querySelector('.blog-card[data-blog-id="' + blogId + '"] .like-btn span');
                if (blogCard) {
                    const currentLikes = parseInt(blogCard.textContent);
                    blogCard.textContent = (isCurrentlyLiked ? currentLikes - 1 : currentLikes + 1) + ' Likes';
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
</script>
@endsection