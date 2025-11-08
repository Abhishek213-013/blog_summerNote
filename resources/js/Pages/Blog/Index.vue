<template>
  <AppLayout>
    <div class="container">
      <div class="p-6 bg-white rounded-lg shadow-sm">
        <h1 class="text-3xl font-bold mb-8 text-center">My Blog Posts</h1>
        
        <div class="blog-grid">
          <div 
            v-for="blog in blogs" 
            :key="blog.id" 
            class="blog-card"
            @click="openBlogModal(blog)"
          >
            <!-- Use desktop_image as featured image, fallback to original_image -->
            <img 
              v-if="getFeaturedImage(blog)" 
              :src="'/storage/' + getFeaturedImage(blog)" 
              :alt="blog.title"
              class="blog-image"
            >
            <div class="blog-content">
              <h2 class="blog-title">{{ blog.title }}</h2>
              
              <div class="blog-meta">
                <button 
                  @click.stop="likeBlog(blog.id)"
                  class="like-btn"
                  :class="{ 'liked': isLiked(blog.id) }"
                >
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                  </svg>
                  <span>{{ blog.likes }} Likes</span>
                </button>
                <span class="text-sm">
                  {{ formatDate(blog.created_at) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div v-if="blogs.length === 0" class="text-center py-12">
          <p class="text-gray-500 text-lg">No blog posts yet.</p>
        </div>
      </div>
    </div>

    <!-- Blog Detail Modal -->
    <div 
      v-if="selectedBlog" 
      class="modal-overlay"
      @click="closeBlogModal"
    >
      <div 
        class="modal-content"
        @click.stop
      >
        <!-- Close Button -->
        <button 
          class="modal-close-btn"
          @click="closeBlogModal"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- Blog Content -->
        <div class="modal-body">
          <!-- Featured Image -->
          <img 
            v-if="getFeaturedImage(selectedBlog)" 
            :src="'/storage/' + getFeaturedImage(selectedBlog)" 
            :alt="selectedBlog.title"
            class="modal-featured-image"
          >

          <!-- Title -->
          <h1 class="modal-title">{{ selectedBlog.title }}</h1>

          <!-- Blog Blocks -->
          <div 
            v-for="(block, index) in selectedBlog.content" 
            :key="index"
            class="modal-block"
          >
            <!-- Content -->
            <div 
              class="modal-block-content"
              v-html="block.content"
            ></div>

            <!-- Block Image with Caption - Use responsive images -->
            <div v-if="getBlockImage(block)" class="modal-image-container">
              <img 
                :src="'/storage/' + getBlockImage(block)" 
                :alt="block.image_caption || selectedBlog.title"
                class="modal-block-image"
                :class="getImageClasses(block)"
              >
              <!-- Image Caption -->
              <div 
                v-if="block.image_caption" 
                class="modal-image-caption"
              >
                {{ block.image_caption }}
              </div>
            </div>
          </div>

          <!-- Meta Information -->
          <div class="modal-meta">
            <div class="modal-likes">
              <button 
                @click="likeBlog(selectedBlog.id)"
                class="modal-like-btn"
                :class="{ 'liked': isLiked(selectedBlog.id) }"
              >
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                </svg>
                <span>{{ selectedBlog.likes }} Likes</span>
              </button>
            </div>
            <div class="modal-date">
              Published on {{ formatDate(selectedBlog.created_at) }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  blogs: Array
});

const selectedBlog = ref(null);
const likedBlogs = ref(new Set());

// Helper function to get featured image for blog card
const getFeaturedImage = (blog) => {
  if (!blog.content || !Array.isArray(blog.content) || blog.content.length === 0) {
    return blog.featured_image || null;
  }
  
  // Get the first block that has an image
  const firstBlockWithImage = blog.content.find(block => 
    block.desktop_image || block.mobile_image || block.original_image
  );
  
  if (firstBlockWithImage) {
    // Prefer desktop image for featured image
    return firstBlockWithImage.desktop_image || firstBlockWithImage.original_image || firstBlockWithImage.mobile_image;
  }
  
  return blog.featured_image || null;
};

// Helper function to get appropriate image for a block
const getBlockImage = (block) => {
  // For now, use desktop_image. You can enhance this to be responsive later
  return block.desktop_image || block.original_image || block.mobile_image;
};

// Helper function to get CSS classes based on image settings
const getImageClasses = (block) => {
  const classes = [];
  
  // Add display size class
  if (block.desktop_display_size) {
    classes.push(`display-${block.desktop_display_size}`);
  }
  
  // Add white background class if enabled
  if (block.desktop_wbone_image) {
    classes.push('white-bg');
  }
  
  return classes.join(' ');
};

// Open blog modal
const openBlogModal = (blog) => {
  selectedBlog.value = blog;
  document.body.style.overflow = 'hidden';
};

// Close blog modal
const closeBlogModal = () => {
  selectedBlog.value = null;
  document.body.style.overflow = '';
};

// Like blog function
const likeBlog = (blogId) => {
  router.post(`/blogs/${blogId}/like`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      if (likedBlogs.value.has(blogId)) {
        likedBlogs.value.delete(blogId);
      } else {
        likedBlogs.value.add(blogId);
      }
    }
  });
};

// Check if blog is liked
const isLiked = (blogId) => {
  return likedBlogs.value.has(blogId);
};

// Format date
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

// Close modal on ESC key
const handleEscapeKey = (event) => {
  if (event.key === 'Escape') {
    closeBlogModal();
  }
};

// Add event listener for ESC key
import { onMounted, onUnmounted } from 'vue';

onMounted(() => {
  document.addEventListener('keydown', handleEscapeKey);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscapeKey);
});
</script>

<style scoped>
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
  display: box;
  line-clamp: 2;
  box-orient: vertical;
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

.text-sm {
  font-size: 0.875rem;
  color: #6b7280;
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
  animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
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

.modal-block:last-child {
  margin-bottom: 0;
}

.modal-block-content {
  font-size: 1.125rem;
  line-height: 1.7;
  color: #374151;
  margin-bottom: 1.5rem;
}

.modal-block-content >>> * {
  margin-bottom: 1rem;
}

.modal-image-container {
  margin: 1rem 0;
}

.modal-block-image {
  max-width: 100%;
  border-radius: 0.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Image display size classes */
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
  /* Natural image size */
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

/* Responsive Design */
@media (max-width: 768px) {
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
  
  .blog-grid {
    grid-template-columns: 1fr;
  }
}

/* Scrollbar styling for modal */
.modal-content::-webkit-scrollbar {
  width: 6px;
}

.modal-content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 0 1rem 1rem 0;
}

.modal-content::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>