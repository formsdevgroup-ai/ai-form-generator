<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="AI Forms Generator" />
    <div class="min-h-screen bg-[#f8fafc]">
        <!-- Subtle gradient background -->
        <div class="absolute inset-0 bg-gradient-to-br from-[#99C0E0]/10 via-transparent to-[#44AAE2]/5 pointer-events-none" aria-hidden="true" />

        <div class="relative flex min-h-screen flex-col">
            <!-- Header -->
            <header class="flex items-center justify-between px-6 py-6 sm:px-10">
                <Link href="/" class="flex items-center gap-3">
                    <img
                        src="/images/MainLogo.png"
                        alt="AI Forms Generator"
                        class="h-10 w-auto sm:h-12"
                    />
                </Link>

                <nav v-if="canLogin" class="flex items-center gap-2">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-lg px-4 py-2.5 text-sm font-medium text-[#1e293b] transition-colors hover:bg-white/80 hover:text-[#44AAE2] focus:outline-none focus:ring-2 focus:ring-[#44AAE2]/50"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-lg px-4 py-2.5 text-sm font-medium text-[#64748b] transition-colors hover:text-[#44AAE2] focus:outline-none focus:ring-2 focus:ring-[#44AAE2]/50"
                        >
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-lg bg-[#44AAE2] px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-[#3a96c9] focus:outline-none focus:ring-2 focus:ring-[#44AAE2]/50 focus:ring-offset-2"
                        >
                            Get started
                        </Link>
                    </template>
                </nav>
            </header>

            <!-- Hero -->
            <main class="flex flex-1 flex-col items-center justify-center px-6 py-16 sm:px-10">
                <div class="mx-auto max-w-2xl text-center">
                    <h1 class="text-4xl font-bold tracking-tight text-[#0f172a] sm:text-5xl">
                        Generate forms with
                        <span class="text-[#44AAE2]">AI</span>
                    </h1>
                    <p class="mt-6 text-lg leading-relaxed text-[#64748b]">
                        Upload an image or document, and let AI create production-ready form code for you. 
                        Fast, accurate, and built for developers.
                    </p>

                    <div v-if="canLogin" class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="w-full rounded-xl bg-[#44AAE2] px-8 py-4 text-base font-semibold text-white shadow-lg shadow-[#44AAE2]/25 transition-all hover:bg-[#3a96c9] hover:shadow-[#44AAE2]/30 focus:outline-none focus:ring-2 focus:ring-[#44AAE2] focus:ring-offset-2 sm:w-auto"
                        >
                            Go to Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('register')"
                                class="w-full rounded-xl bg-[#44AAE2] px-8 py-4 text-base font-semibold text-white shadow-lg shadow-[#44AAE2]/25 transition-all hover:bg-[#3a96c9] hover:shadow-[#44AAE2]/30 focus:outline-none focus:ring-2 focus:ring-[#44AAE2] focus:ring-offset-2 sm:w-auto"
                            >
                                Start for free
                            </Link>
                            <Link
                                :href="route('login')"
                                class="w-full rounded-xl border-2 border-[#99C0E0] bg-white px-8 py-4 text-base font-semibold text-[#44AAE2] transition-colors hover:border-[#44AAE2] hover:bg-[#99C0E0]/5 focus:outline-none focus:ring-2 focus:ring-[#44AAE2] focus:ring-offset-2 sm:w-auto"
                            >
                                Sign in
                            </Link>
                        </template>
                    </div>
                </div>

                <!-- Feature highlights -->
                <div class="mx-auto mt-20 grid max-w-4xl gap-6 sm:grid-cols-3">
                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-[#e2e8f0] transition-shadow hover:shadow-md">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#99C0E0]/20">
                            <svg class="h-5 w-5 text-[#44AAE2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 font-semibold text-[#0f172a]">Image to code</h3>
                        <p class="mt-2 text-sm text-[#64748b]">Upload a screenshot or sketch and get clean, structured form code.</p>
                    </div>
                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-[#e2e8f0] transition-shadow hover:shadow-md">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#99C0E0]/20">
                            <svg class="h-5 w-5 text-[#44AAE2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </div>
                        <h3 class="mt-4 font-semibold text-[#0f172a]">Multiple blueprints</h3>
                        <p class="mt-2 text-sm text-[#64748b]">Choose from versioned templates. Incremental mode for iterative builds.</p>
                    </div>
                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-[#e2e8f0] transition-shadow hover:shadow-md">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#99C0E0]/20">
                            <svg class="h-5 w-5 text-[#44AAE2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 font-semibold text-[#0f172a]">Powered by Google Gemini</h3>
                        <p class="mt-2 text-sm text-[#64748b]">State-of-the-art AI for accurate field detection and code generation.</p>
                    </div>
                </div>
            </main>

            <footer class="py-8 text-center text-sm text-[#94a3b8]">
                AI Forms Generator · Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
            </footer>
        </div>
    </div>
</template>
