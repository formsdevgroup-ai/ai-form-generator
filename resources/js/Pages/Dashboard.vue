<template>
    <div class="max-w-4xl mx-auto p-8 space-y-8">
        <div class="bg-white shadow-lg rounded-xl p-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">AI Code Generator</h2>
            
            <div class="space-y-6">
                <!-- Incremental Mode Toggle -->
                <div class="flex items-center justify-between p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Incremental Building Mode</label>
                        <p class="text-xs text-gray-600">Add fields to existing form through successive uploads</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="incrementalMode" @change="onIncrementalModeChange" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>

                <!-- Select Previous Form (if incremental mode) -->
                <div v-if="incrementalMode && previousForms.length > 0" class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Continue Building From:</label>
                    <select v-model="selectedFormId" @change="loadPreviousForm" class="w-full border-gray-300 rounded-md shadow-sm border p-2 bg-white">
                        <option value="">-- Start New Form --</option>
                        <option v-for="prevForm in previousForms" :key="prevForm.id" :value="prevForm.id">
                            {{ prevForm.form_name }} (v{{ prevForm.version }}) - {{ formatDate(prevForm.created_at) }}
                        </option>
                    </select>
                    <p v-if="selectedFormId" class="text-xs text-green-600 mt-2">✓ Will add new fields to selected form</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Form Name</label>
                    <input v-model="form.name" type="text" placeholder="e.g. InvoiceForm" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 border p-2">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Blueprint Version</label>
                    <select v-model="form.version" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm border p-2">
                        <option value="1">Version 1</option>
                        <option value="2">Version 2</option>
                        <option value="3">Version 3</option>
                        <option value="4">Version 4</option>
                        <option value="5">Version 5 (Advanced)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">
                        Reference Attachment (Fields Source)
                        <span v-if="incrementalMode && selectedFormId" class="text-indigo-600"> - Adding to existing form</span>
                    </label>
                    <input type="file" @change="handleFileChange" accept="image/*,.txt,.php,.html" 
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="text-xs text-gray-400 mt-2 italic">
                        {{ incrementalMode && selectedFormId 
                            ? 'Upload additional fields to add to your existing form. Existing fields will be preserved.' 
                            : 'Upload an image of a form or a text file containing the fields.' }}
                    </p>
                </div>

                <div class="flex gap-3">
                    <button @click="generate" :disabled="loading || (!form.file || !form.name)" 
                            class="flex-1 bg-indigo-600 text-white py-3 rounded-md font-bold hover:bg-indigo-700 transition disabled:bg-gray-400">
                        {{ loading ? 'Analyzing & Generating...' : (incrementalMode && selectedFormId ? 'Add Fields to Form' : 'Generate Source Code') }}
                    </button>
                    <button v-if="generatedCode && !loading" @click="startNewForm" 
                            class="px-4 bg-gray-200 text-gray-700 py-3 rounded-md font-semibold hover:bg-gray-300 transition">
                        New Form
                    </button>
                </div>
            </div>
        </div>

        <!-- Current Form Preview (if incremental mode) -->
        <div v-if="incrementalMode && selectedFormId && currentFormPreview" class="bg-blue-50 shadow-lg rounded-xl p-6 border border-blue-200">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Current Form Preview</h3>
                <button @click="showPreview = !showPreview" class="text-sm text-indigo-600 hover:text-indigo-800">
                    {{ showPreview ? 'Hide' : 'Show' }} Preview
                </button>
            </div>
            <div v-if="showPreview" class="bg-slate-900 rounded-lg p-4 overflow-x-auto max-h-64 overflow-y-auto">
                <pre class="text-green-400 font-mono text-xs leading-relaxed whitespace-pre-wrap"><code>{{ currentFormPreview.substring(0, 2000) }}{{ currentFormPreview.length > 2000 ? '...' : '' }}</code></pre>
            </div>
        </div>

        <div v-if="generatedCode" class="bg-slate-900 shadow-xl rounded-xl overflow-hidden border border-slate-700 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="bg-slate-800 px-6 py-3 flex justify-between items-center border-b border-slate-700">
                <span class="text-slate-300 font-mono text-xs uppercase tracking-widest">
                    {{ incrementalMode && selectedFormId ? 'Updated Form (with new fields)' : 'Generated Output' }}
                </span>
                <div class="flex gap-2">
                    <button v-if="incrementalMode && sessionId" @click="continueBuilding" 
                            class="text-xs px-3 py-1.5 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 transition">
                        Continue Building
                    </button>
                    <button @click="copyToClipboard" 
                            class="text-xs px-3 py-1.5 rounded-md bg-slate-700 text-slate-200 hover:bg-slate-600 transition flex items-center gap-2">
                        <span v-if="!copied">Copy Code</span>
                        <span v-else class="text-green-400">Copied! ✓</span>
                    </button>
                </div>
            </div>
            
            <div class="p-6 overflow-x-auto">
                <pre class="text-green-400 font-mono text-sm leading-relaxed whitespace-pre-wrap selection:bg-indigo-500 selection:text-white"><code>{{ generatedCode }}</code></pre>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const form = ref({ name: '', version: '5', file: null });
const generatedCode = ref('');
const loading = ref(false);
const copied = ref(false);
const incrementalMode = ref(false);
const selectedFormId = ref(null);
const sessionId = ref(null);
const previousForms = ref([]);
const currentFormPreview = ref('');
const showPreview = ref(false);

onMounted(async () => {
    // Load previous forms for incremental mode
    try {
        const response = await axios.get('/api/previous-forms');
        previousForms.value = response.data;
    } catch (err) {
        console.error('Failed to load previous forms:', err);
    }
});

const handleFileChange = (e) => { 
    form.value.file = e.target.files[0]; 
};

const onIncrementalModeChange = () => {
    if (!incrementalMode.value) {
        selectedFormId.value = null;
        currentFormPreview.value = '';
        sessionId.value = null;
    }
};

const loadPreviousForm = async () => {
    if (!selectedFormId.value) {
        currentFormPreview.value = '';
        return;
    }

    try {
        const formData = previousForms.value.find(f => f.id == selectedFormId.value);
        if (formData && formData.generated_code) {
            currentFormPreview.value = formData.generated_code;
            sessionId.value = formData.session_id;
            form.value.name = formData.form_name;
            form.value.version = formData.version;
        }
    } catch (err) {
        console.error('Failed to load form:', err);
    }
};

const continueBuilding = () => {
    incrementalMode.value = true;
    if (sessionId.value) {
        const formData = previousForms.value.find(f => f.session_id === sessionId.value);
        if (formData) {
            selectedFormId.value = formData.id;
            loadPreviousForm();
        }
    }
};

const startNewForm = () => {
    generatedCode.value = '';
    form.value = { name: '', version: '5', file: null };
    selectedFormId.value = null;
    currentFormPreview.value = '';
    sessionId.value = null;
    incrementalMode.value = false;
    showPreview.value = false;
};

const copyToClipboard = async () => {
    try {
        await navigator.clipboard.writeText(generatedCode.value);
        copied.value = true;
        setTimeout(() => copied.value = false, 2000);
    } catch (err) {
        alert('Failed to copy code.');
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const generate = async () => {
    if (!form.value.file || !form.value.name) return;

    loading.value = true;
    generatedCode.value = ''; 
    
    const formData = new FormData();
    formData.append('name', form.value.name);
    formData.append('version', form.value.version);
    formData.append('reference', form.value.file);
    
    if (incrementalMode.value) {
        formData.append('incremental', 'true');
        if (selectedFormId.value) {
            formData.append('form_id', selectedFormId.value);
        } else if (sessionId.value) {
            formData.append('session_id', sessionId.value);
        }
    }

    try {
        // Using Axios with onDownloadProgress for streaming
        const response = await axios.post('/ai/generate-code', formData, {
            responseType: 'text',
            onDownloadProgress: progressEvent => {
                const response = progressEvent.event.target.response;
                generatedCode.value = response;
            }
        });

        // Extract session ID from response headers if available
        const responseSessionId = response.headers['x-session-id'];
        if (responseSessionId) {
            sessionId.value = responseSessionId;
        }

        // Reload previous forms to include the new one
        try {
            const formsResponse = await axios.get('/api/previous-forms');
            previousForms.value = formsResponse.data;
            
            // Auto-select the newly created/updated form
            if (incrementalMode.value && selectedFormId.value) {
                // Form was updated, keep it selected
            } else if (sessionId.value) {
                const newForm = formsResponse.data.find(f => f.session_id === sessionId.value);
                if (newForm) {
                    selectedFormId.value = newForm.id;
                    currentFormPreview.value = generatedCode.value;
                }
            }
        } catch (err) {
            console.error('Failed to reload forms:', err);
        }

    } catch (err) {
        console.error("Generation Error:", err);
        alert("Failed to connect to the AI service.");
    } finally {
        loading.value = false;
    }
};
</script>