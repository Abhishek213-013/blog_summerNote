<template>
  <div class="min-h-screen bg-gray-100">
    <nav class="navbar">
      <div class="nav-container">
        <div class="nav-title">My Blog</div>
        <div class="nav-links">
          <Link href="/" class="nav-link">
            Read Blogs
          </Link>
          <!-- <Link href="/edit-blog" class="nav-link">
            Manage Blogs
          </Link> -->
        </div>
      </div>
    </nav>

    <main>
      <slot />
    </main>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>