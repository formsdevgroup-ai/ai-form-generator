<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-[#f8fafc] pt-6 sm:justify-center sm:pt-0"
    >
        <!-- Back button -->
        <Link
            href="/"
            class="mb-4 flex items-center gap-2 self-start px-4 text-sm font-medium text-[#64748b] transition-colors hover:text-[#44AAE2] sm:mb-6"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to home
        </Link>

        <!-- Logo -->
        <Link href="/" class="flex items-center">
            <img
                src="/images/MainLogo.png"
                alt="AI Forms Generator"
                class="h-10 w-auto sm:h-12"
            />
        </Link>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
