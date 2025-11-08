<template>
  <AppLayout>
    <div class="container">
      <div class="p-6 bg-white rounded-lg shadow-sm">
        <h1 class="text-3xl font-bold mb-8 text-center">Manage Blog Posts</h1>

        <!-- Blog Form -->
        <div class="form-container">
          <form @submit.prevent="submitForm">
            <div class="space-y-6">
              <!-- Title -->
              <div class="form-group">
                <label class="form-label">
                  Title
                </label>
                <input
                  v-model="form.title"
                  type="text"
                  required
                  class="form-input"
                  placeholder="Enter blog title"
                >
              </div>

              <!-- Content Blocks -->
              <div class="space-y-4">
                <div class="flex justify-between items-center">
                  <label class="form-label">
                    Content Blocks
                  </label>
                  <button
                    type="button"
                    @click="addNewBlock"
                    class="btn btn-primary"
                  >
                    + Add New Block
                  </button>
                </div>

                <div
                  v-for="(block, index) in form.blocks"
                  :key="index"
                  class="block-container"
                >
                  <div class="block-header">
                    <h3 class="block-title">Block {{ index + 1 }}</h3>
                    <button
                      v-if="form.blocks.length > 1"
                      type="button"
                      @click="removeBlock(index)"
                      class="btn btn-danger btn-sm"
                    >
                      Remove
                    </button>
                  </div>

                  <!-- Content -->
                  <div class="form-group">
                    <label class="form-label">
                      Content
                    </label>
                    <div class="editor-container">
                      <textarea
                        v-model="block.content"
                        :ref="el => setEditorRef(el, index)"
                        class="form-input block-textarea"
                      ></textarea>
                    </div>
                  </div>

                  <!-- Image Upload -->
                  <div class="form-group">
                    <label class="form-label">
                      Image
                    </label>
                    <div 
                      class="image-upload-container"
                      @click="triggerFileInput(index)"
                      @dragover.prevent="handleDragOver"
                      @drop.prevent="handleDrop($event, index)"
                    >
                      <input
                        type="file"
                        :ref="el => setFileInputRef(el, index)"
                        @change="handleImageUpload($event, index)"
                        accept="image/*"
                        class="file-input"
                      >
                      <div v-if="block.image_preview" class="image-preview">
                        <img :src="block.image_preview" alt="Preview" class="preview-image">
                        <button
                          type="button"
                          @click.stop="removeImage(index)"
                          class="remove-image-btn"
                        >
                          ×
                        </button>
                      </div>
                      <div v-else class="upload-placeholder">
                        <div class="upload-icon">
                          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                          </svg>
                        </div>
                        <span class="upload-text">Click to upload an image</span>
                        <span class="upload-hint">or drag & drop</span>
                        <span class="upload-hint">PNG, JPG, GIF up to 5MB</span>
                      </div>
                    </div>

                    <!-- Image Caption -->
                    <div class="mt-3">
                      <input
                        v-model="block.image_caption"
                        type="text"
                        class="form-input"
                        placeholder="Image caption (optional)"
                      >
                    </div>
                  </div>

                  <!-- Desktop Image Settings -->
                  <div class="settings-container">
                    <h4 class="settings-title">🖥️ Desktop Image Settings</h4>
                    <div class="settings-grid">
                      <!-- Size Setting -->
                      <div class="setting-group">
                        <label class="setting-label">Size</label>
                        <select 
                          v-model="block.desktop_size"
                          class="form-input"
                        >
                          <option value="small">Small (25%)</option>
                          <option value="medium">Medium (50%)</option>
                          <option value="large">Large (75%)</option>
                          <option value="full">Full Width (100%)</option>
                        </select>
                      </div>

                      <!-- White Background Toggle -->
                      <div class="setting-group">
                        <label class="setting-label">White Background</label>
                        <div class="toggle-container">
                          <input
                            type="checkbox"
                            v-model="block.desktop_wbone_image"
                            class="toggle-checkbox"
                            :id="`desktop-wbone-toggle-${index}`"
                          >
                          <label :for="`desktop-wbone-toggle-${index}`" class="toggle-label">
                            {{ block.desktop_wbone_image ? 'On' : 'Off' }}
                          </label>
                        </div>
                      </div>

                      <!-- Display Size -->
                      <div class="setting-group">
                        <label class="setting-label">Display Size</label>
                        <select 
                          v-model="block.desktop_display_size"
                          class="form-input"
                        >
                          <option value="contain">Contain</option>
                          <option value="cover">Cover</option>
                          <option value="auto">Auto</option>
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
                        <select 
                          v-model="block.mobile_size"
                          class="form-input"
                        >
                          <option value="small">Small (25%)</option>
                          <option value="medium">Medium (50%)</option>
                          <option value="large">Large (75%)</option>
                          <option value="full">Full Width (100%)</option>
                        </select>
                      </div>

                      <!-- White Background Toggle -->
                      <div class="setting-group">
                        <label class="setting-label">White Background</label>
                        <div class="toggle-container">
                          <input
                            type="checkbox"
                            v-model="block.mobile_wbone_image"
                            class="toggle-checkbox"
                            :id="`mobile-wbone-toggle-${index}`"
                          >
                          <label :for="`mobile-wbone-toggle-${index}`" class="toggle-label">
                            {{ block.mobile_wbone_image ? 'On' : 'Off' }}
                          </label>
                        </div>
                      </div>

                      <!-- Display Size -->
                      <div class="setting-group">
                        <label class="setting-label">Display Size</label>
                        <select 
                          v-model="block.mobile_display_size"
                          class="form-input"
                        >
                          <option value="contain">Contain</option>
                          <option value="cover">Cover</option>
                          <option value="auto">Auto</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Buttons -->
              <div class="flex justify-between pt-4">
                <button
                  type="button"
                  @click="resetForm"
                  class="btn btn-warning"
                >
                  Reset
                </button>
                
                <div class="flex gap-4">
                  <button
                    v-if="editingBlog"
                    type="button"
                    @click="cancelEdit"
                    class="btn btn-danger"
                  >
                    Cancel Edit
                  </button>
                  <button
                    type="submit"
                    class="btn btn-success"
                    :disabled="form.processing"
                  >
                    {{ form.processing ? 'Processing...' : (editingBlog ? 'Update Blog' : 'Create Blog') }}
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
            <div 
              v-for="blog in blogs" 
              :key="blog.id"
              class="blog-item"
            >
              <div>
                <h3 class="blog-item-title">{{ blog.title }}</h3>
                <div class="blog-item-meta">
                  <span>Likes: {{ blog.likes }}</span>
                  <span>Created: {{ formatDate(blog.created_at) }}</span>
                </div>
              </div>
              <div class="blog-actions">
                <button
                  @click="editBlog(blog)"
                  class="btn btn-warning btn-sm"
                >
                  Edit
                </button>
                <button
                  @click="deleteBlog(blog.id)"
                  class="btn btn-danger btn-sm"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>

          <div v-if="blogs.length === 0" class="text-center py-8 text-gray-500">
            No blog posts created yet.
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  blogs: Array
});

const editorRefs = ref([]);
const fileInputRefs = ref([]);

// Initialize form with enhanced block structure including mobile settings
const form = useForm({
  id: null,
  title: '',
  blocks: [
    {
      content: '',
      image: null,
      image_preview: null,
      image_caption: '',
      // Desktop settings
      desktop_size: 'medium',
      desktop_wbone_image: false,
      desktop_display_size: 'contain',
      // Mobile settings
      mobile_size: 'medium',
      mobile_wbone_image: false,
      mobile_display_size: 'contain'
    }
  ]
});

const editingBlog = ref(null);

// Set editor reference
const setEditorRef = (el, index) => {
  if (el) {
    editorRefs.value[index] = el;
  }
};

// Set file input reference
const setFileInputRef = (el, index) => {
  if (el) {
    fileInputRefs.value[index] = el;
  }
};

// Trigger file input click
const triggerFileInput = (index) => {
  if (fileInputRefs.value[index]) {
    fileInputRefs.value[index].click();
  }
};

// Handle drag over
const handleDragOver = (event) => {
  event.currentTarget.style.borderColor = '#3b82f6';
  event.currentTarget.style.backgroundColor = '#f8fafc';
};

// Handle drop event for drag and drop
const handleDrop = (event, index) => {
  event.currentTarget.style.borderColor = '#d1d5db';
  event.currentTarget.style.backgroundColor = 'white';
  
  const files = event.dataTransfer.files;
  if (files.length > 0) {
    handleImageUpload({ target: { files } }, index);
  }
};

// Initialize Summernote editors
onMounted(() => {
  nextTick(() => {
    if (window.jQuery && window.jQuery.fn.summernote) {
      editorRefs.value.forEach((editor, index) => {
        if (editor) {
          window.jQuery(editor).summernote({
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

          // Update form content when Summernote content changes
          window.jQuery(editor).on('summernote.change', function() {
            form.blocks[index].content = window.jQuery(editor).summernote('code');
          });
        }
      });
    }
  });
});

// Add new content block with enhanced structure including mobile settings
const addNewBlock = () => {
  form.blocks.push({
    content: '',
    image: null,
    image_preview: null,
    image_caption: '',
    // Desktop settings
    desktop_size: 'medium',
    desktop_wbone_image: false,
    desktop_display_size: 'contain',
    // Mobile settings
    mobile_size: 'medium',
    mobile_wbone_image: false,
    mobile_display_size: 'contain'
  });

  // Initialize Summernote for the new block
  nextTick(() => {
    if (window.jQuery && window.jQuery.fn.summernote) {
      const newIndex = form.blocks.length - 1;
      const newEditor = editorRefs.value[newIndex];
      if (newEditor && form.blocks[newIndex]) {
        window.jQuery(newEditor).summernote({
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

        window.jQuery(newEditor).on('summernote.change', function() {
          if (form.blocks[newIndex]) {
            form.blocks[newIndex].content = window.jQuery(newEditor).summernote('code');
          }
        });
      }
    }
  });
};

// Remove content block
const removeBlock = (index) => {
  if (form.blocks.length > 1) {
    // Destroy Summernote instance before removing
    if (window.jQuery && window.jQuery.fn.summernote) {
      const editor = editorRefs.value[index];
      if (editor) {
        window.jQuery(editor).summernote('destroy');
      }
    }
    
    form.blocks.splice(index, 1);
    editorRefs.value.splice(index, 1);
    fileInputRefs.value.splice(index, 1);
  }
};

// Handle image upload
const handleImageUpload = (event, index) => {
  const file = event.target.files[0];
  if (file) {
    // Validate file type
    if (!file.type.startsWith('image/')) {
      alert('Please select an image file (PNG, JPG, GIF)');
      event.target.value = ''; // Reset input
      return;
    }

    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
      alert('Image size should be less than 5MB');
      event.target.value = ''; // Reset input
      return;
    }

    form.blocks[index].image = file;
    
    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
      form.blocks[index].image_preview = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

// Remove image from block
const removeImage = (index) => {
  form.blocks[index].image = null;
  form.blocks[index].image_preview = null;
  form.blocks[index].image_caption = '';
  
  // Reset file input
  if (fileInputRefs.value[index]) {
    fileInputRefs.value[index].value = '';
  }
};

const submitForm = () => {
  console.log('Submitting form with blocks:', form.blocks);

  form.blocks.forEach((block, index) => {
    console.log(`Block ${index} - Desktop: ${block.desktop_size}, Mobile: ${block.mobile_size}`);
  });

  // Create FormData to handle file uploads
  const formData = new FormData();
  formData.append('title', form.title);
  
  // Send blocks as array properly
  form.blocks.forEach((block, index) => {
    formData.append(`blocks[${index}][content]`, block.content);
    formData.append(`blocks[${index}][image_caption]`, block.image_caption);
    
    // Desktop settings
    formData.append(`blocks[${index}][desktop_size]`, block.desktop_size);
    formData.append(`blocks[${index}][desktop_wbone_image]`, block.desktop_wbone_image);
    formData.append(`blocks[${index}][desktop_display_size]`, block.desktop_display_size);
    
    // Mobile settings
    formData.append(`blocks[${index}][mobile_size]`, block.mobile_size);
    formData.append(`blocks[${index}][mobile_wbone_image]`, block.mobile_wbone_image);
    formData.append(`blocks[${index}][mobile_display_size]`, block.mobile_display_size);
    
    // Append image files with proper naming
    if (block.image) {
      formData.append(`images[${index}]`, block.image);
      console.log('Appending image for block', index, ':', block.image.name);
    }
  });

  if (editingBlog.value) {
    formData.append('_method', 'PUT');
    router.post(`/blogs/${editingBlog.value.id}`, formData, {
      forceFormData: true,
      onSuccess: () => {
        console.log('Update successful');
        resetForm();
      },
      onError: (errors) => {
        console.error('Update errors:', errors);
        alert('Error updating blog: ' + JSON.stringify(errors));
      }
    });
  } else {
    router.post('/blogs', formData, {
      forceFormData: true,
      onSuccess: () => {
        console.log('Create successful');
        resetForm();
      },
      onError: (errors) => {
        console.error('Create errors:', errors);
        alert('Error creating blog: ' + JSON.stringify(errors));
      }
    });
  }
};

const editBlog = (blog) => {
  editingBlog.value = blog;
  form.id = blog.id;
  form.title = blog.title;
  
  // Parse blocks from existing blog data
  if (blog.blocks && Array.isArray(blog.blocks)) {
    form.blocks = blog.blocks.map(block => ({
      content: block.content || '',
      image: null,
      image_preview: block.image || null,
      image_caption: block.image_caption || '',
      // Desktop settings
      desktop_size: block.desktop_size || 'medium',
      desktop_wbone_image: block.desktop_wbone_image || false,
      desktop_display_size: block.desktop_display_size || 'contain',
      // Mobile settings
      mobile_size: block.mobile_size || 'medium',
      mobile_wbone_image: block.mobile_wbone_image || false,
      mobile_display_size: block.mobile_display_size || 'contain'
    }));
  } else {
    // Fallback for old blog structure
    form.blocks = [{
      content: blog.content || '',
      image: null,
      image_preview: blog.image || null,
      image_caption: '',
      // Desktop settings
      desktop_size: 'medium',
      desktop_wbone_image: false,
      desktop_display_size: 'contain',
      // Mobile settings
      mobile_size: 'medium',
      mobile_wbone_image: false,
      mobile_display_size: 'contain'
    }];
  }

  // Initialize Summernote with existing content
  nextTick(() => {
    if (window.jQuery && window.jQuery.fn.summernote) {
      editorRefs.value.forEach((editor, index) => {
        if (editor && form.blocks[index]) {
          window.jQuery(editor).summernote('code', form.blocks[index].content);
        }
      });
    }
  });
};

const deleteBlog = (id) => {
  if (confirm('Are you sure you want to delete this blog?')) {
    router.delete(`/blogs/${id}`);
  }
};

const cancelEdit = () => {
  resetForm();
};

const resetForm = () => {
  editingBlog.value = null;
  form.reset();
  form.blocks = [{
    content: '',
    image: null,
    image_preview: null,
    image_caption: '',
    // Desktop settings
    desktop_size: 'medium',
    desktop_wbone_image: false,
    desktop_display_size: 'contain',
    // Mobile settings
    mobile_size: 'medium',
    mobile_wbone_image: false,
    mobile_display_size: 'contain'
  }];
  
  // Reset all Summernote editors safely
  nextTick(() => {
    if (window.jQuery && window.jQuery.fn.summernote) {
      editorRefs.value.forEach((editor, index) => {
        if (editor && form.blocks[index]) {
          try {
            window.jQuery(editor).summernote('code', '');
          } catch (error) {
            console.warn('Error resetting Summernote editor:', error);
          }
        }
      });
    }
    
    // Reset file inputs
    fileInputRefs.value.forEach(input => {
      if (input) {
        input.value = '';
      }
    });
    
    // Clear the refs arrays
    editorRefs.value = [];
    fileInputRefs.value = [];
  });
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString();
};
</script>

<style scoped>
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

.block-textarea {
  min-height: 200px !important;
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

/* Settings Container */
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

/* Buttons */
.btn-primary {
  background: #3b82f6;
  color: white;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-warning {
  background: #f59e0b;
  color: white;
}

.btn-warning:hover {
  background: #d97706;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover {
  background: #059669;
}

.btn-sm {
  padding: 0.25rem 0.75rem;
  font-size: 0.875rem;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Form elements */
.form-input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-label {
  display: block;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
}

/* Responsive */
@media (max-width: 768px) {
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