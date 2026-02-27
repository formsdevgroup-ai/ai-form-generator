<?php

namespace App\Http\Controllers;

use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;
use Gemini\Data\Content;
use Gemini\Exceptions\ErrorException;
use Illuminate\Http\Request;
use App\Models\GeneratedForm;
use Exception;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AIFormController extends Controller
{
    /**
     * Check if the error indicates a rate limit or quota exhaustion.
     */
    private function isRateLimitError(ErrorException $e): bool
    {
        $status = strtoupper($e->getErrorStatus());
        $code = $e->getErrorCode();
        $message = strtolower($e->getMessage());

        return $code === 429
            || $status === 'RESOURCE_EXHAUSTED'
            || str_contains($message, '429')
            || str_contains($message, 'resource exhausted')
            || str_contains($message, 'quota')
            || str_contains($message, 'rate limit');
    }

    public function generateCode(Request $request)
    {
        set_time_limit(180);

        $version = $request->input('version');
        $formId = $request->input('form_id');
        $sessionId = $request->input('session_id');
        $isIncremental = $request->input('incremental', false);
        $formName = $request->input('name');

        $blueprintPath = resource_path("prompts/references/v{$version}/form.php");
        $controllerBlueprint = resource_path("prompts/references/v{$version}/Controller.php");
        $formBlueprint = file_get_contents($blueprintPath);

        // Load previous form if incremental generation
        $previousForm = null;
        $previousCode = null;

        if ($isIncremental && ($formId || $sessionId)) {
            $query = GeneratedForm::query();

            if ($formId) {
                $previousForm = $query->where('id', $formId)->first();
            } elseif ($sessionId) {
                $previousForm = $query->where('session_id', $sessionId)
                    ->where('user_id', Auth::id())
                    ->latest()
                    ->first();
            }

            if ($previousForm && $previousForm->generated_code) {
                $previousCode = $previousForm->generated_code;
            }
        }

        // Determine system instruction based on mode
        if ($isIncremental && $previousCode) {
            $systemInstruction = "You are a code migration/generation tool with INCREMENTAL BUILDING capability. 
    Task: ADD NEW fields from the 'FIELD REFERENCE' to the existing 'CURRENT FORM CODE' while maintaining all existing fields and functionality.
    
    WHAT TO DO:
    - PRESERVE ALL existing fields, code, and functionality from CURRENT FORM CODE.
    - ADD ONLY the new fields that are present in FIELD REFERENCE but NOT already in CURRENT FORM CODE.
    - Use the BLUEPRINT's exact implementation patterns, structure, PHP logic, HTML structure, CSS classes, and code style for NEW fields only.
    - Maintain the existing form structure, validation patterns, and controller method patterns.
    - Keep all BLUEPRINT's header constants, includes, and overall file structure.
    - Merge new fields seamlessly into the existing form without breaking existing functionality.
    
    WHAT NOT TO DO:
    - Do NOT remove or modify any existing fields from CURRENT FORM CODE.
    - Do NOT duplicate fields that already exist in CURRENT FORM CODE.
    - Do NOT include any fields from the BLUEPRINT that are NOT in the FIELD REFERENCE.
    - Do NOT copy entire controller classes or methods - only add validation rules for NEW fields.
    - Do NOT add any code that doesn't directly relate to NEW fields from FIELD REFERENCE.
    - Do NOT include markdown, explanations, or code blocks - return RAW PHP only.
    
    PROCESS:
    1. Analyze CURRENT FORM CODE to identify all existing fields.
    2. Identify NEW fields in FIELD REFERENCE that don't exist in CURRENT FORM CODE.
    3. For each NEW field, use the BLUEPRINT's implementation pattern for that field type.
    4. Integrate NEW fields into the existing form structure without disrupting existing code.
    5. Add validation rules for NEW fields following the CONTROLLER BLUEPRINT patterns.";
        } else {
            $systemInstruction = "You are a code migration/generation tool. 
    Task: Rebuild the user's provided 'FIELD REFERENCE' using the 'BLUEPRINT' implementation style and structure.
    
    WHAT TO DO:
    - Extract ONLY the fields that are explicitly present in the FIELD REFERENCE.
    - Rebuild those fields using the BLUEPRINT's exact implementation patterns, structure, PHP logic, HTML structure, CSS classes, and code style.
    - Follow the BLUEPRINT's form structure, validation patterns, and controller method patterns exactly.
    - Keep the BLUEPRINT's header constants, includes, and overall file structure.
    
    WHAT NOT TO DO:
    - Do NOT include any fields from the BLUEPRINT that are NOT in the FIELD REFERENCE.
    - Do NOT copy entire controller classes or methods from the controllerBlueprint - only extract validation rules and parameter patterns for the fields in FIELD REFERENCE.
    - Do NOT add any code that doesn't directly relate to the fields in FIELD REFERENCE.
    - Do NOT include markdown, explanations, or code blocks - return RAW PHP only.
    
    PROCESS:
    1. Identify all fields in FIELD REFERENCE.
    2. For each field, use the BLUEPRINT's implementation pattern for that field type.
    3. Remove any fields from BLUEPRINT that aren't in FIELD REFERENCE.
    4. Keep BLUEPRINT's structure, but only include code relevant to FIELD REFERENCE fields.";
        }

        // 2. Prepare the payload array
        $payload = [];

        // Add previous form code if incremental
        if ($isIncremental && $previousCode) {
            $payload[] = "CURRENT FORM CODE (PRESERVE ALL OF THIS - DO NOT REMOVE OR MODIFY ANY EXISTING FIELDS):\n" . $previousCode . "\n\nCRITICAL: This is the existing form code. You must preserve ALL fields and functionality from this code. Only ADD new fields from FIELD REFERENCE.";
        }

        // 3. Handle the REFERENCE (Image or Text)
        if ($request->hasFile('reference')) {
            $file = $request->file('reference');
            $mime = $file->getMimeType();

            if (str_contains($mime, 'image')) {
                // It's an image: Send it as a Vision Blob
                $payload[] = new Blob(
                    mimeType: $mime === 'image/png' ? MimeType::IMAGE_PNG : MimeType::IMAGE_JPEG,
                    data: base64_encode(file_get_contents($file->path()))
                );
                if ($isIncremental && $previousCode) {
                    $payload[] = "NEW FIELD REFERENCE: Extract ONLY the NEW input fields that are visible in the attached image and are NOT already present in CURRENT FORM CODE. Add these NEW fields to the existing form while preserving all existing fields.";
                } else {
                    $payload[] = "FIELD REFERENCE: Extract ONLY the input fields that are visible in the attached image. These are the ONLY fields you should include in your output. Do NOT add any fields that are not visible in this image.";
                }
            } else {
                // It's a text/code file: Read the text and add it to the prompt
                $textContent = file_get_contents($file->path());
                if ($isIncremental && $previousCode) {
                    $payload[] = "NEW FIELD REFERENCE (Source for NEW fields to ADD - only include fields NOT already in CURRENT FORM CODE):\n" . $textContent . "\n\nIMPORTANT: Only add fields that are explicitly defined in the above FIELD REFERENCE and are NOT already present in CURRENT FORM CODE. Preserve all existing fields.";
                } else {
                    $payload[] = "FIELD REFERENCE (Source for fields - USE ONLY THESE FIELDS):\n" . $textContent . "\n\nIMPORTANT: Only include fields that are explicitly defined in the above FIELD REFERENCE. Do not add any additional fields.";
                }
            }
        }

        // 4. Add the BLUEPRINT to the payload
        $payload[] = "BLUEPRINT TO FOLLOW (MANDATORY - Follow this exact implementation style and structure):\n" . $formBlueprint . "\n\nINSTRUCTIONS: Use this blueprint's exact implementation patterns, PHP structure, HTML form structure, CSS classes, and code style. However, ONLY include fields that are in the FIELD REFERENCE. Remove any fields from this blueprint that aren't in FIELD REFERENCE. Do NOT include any <script> tags or JavaScript code.";
        $payload[] = "CONTROLLER BLUEPRINT (Reference for validation patterns and parameter structure only):\n" . file_get_contents($controllerBlueprint) . "\n\nINSTRUCTIONS: Use this ONLY as a reference for validation rule patterns and parameter structure. Extract ONLY the validation rules and patterns for fields that exist in FIELD REFERENCE. Do NOT copy entire controller classes or methods.";

        $systemInstructionContent = Content::parse($systemInstruction);
        $fallbackModels = config('gemini.fallback_models', ['gemini-2.5-flash', 'gemini-2.0-flash', 'gemini-2.5-pro']);

        // Generate session ID if new form
        $currentSessionId = $sessionId ?? ($previousForm ? $previousForm->session_id : Str::uuid()->toString());

        // Return a StreamedResponse with model fallback on rate limit
        return new StreamedResponse(function () use ($fallbackModels, $systemInstructionContent, $payload, $formName, $version, $currentSessionId, $previousForm, $isIncremental) {
            $fullResponse = '';
            $lastException = null;

            foreach ($fallbackModels as $modelId) {
                try {
                    $model = Gemini::generativeModel(model: $modelId)
                        ->withSystemInstruction($systemInstructionContent);

                    $stream = $model->streamGenerateContent($payload);

                    foreach ($stream as $response) {
                        $text = $response->text();
                        $fullResponse .= $text;
                        echo $text;

                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();
                    }

                    // Success - save and exit
                    try {
                        if ($isIncremental && $previousForm) {
                            $previousForm->update([
                                'generated_code' => $fullResponse,
                                'version' => $version,
                            ]);
                        } else {
                            GeneratedForm::create([
                                'form_name' => $formName ?? 'Untitled Form',
                                'version' => $version,
                                'generated_code' => $fullResponse,
                                'user_id' => Auth::id(),
                                'session_id' => $currentSessionId,
                            ]);
                        }
                    } catch (Exception $e) {
                        Log::error('Failed to save generated form: ' . $e->getMessage());
                    }

                    return;
                } catch (ErrorException $e) {
                    $lastException = $e;
                    if ($this->isRateLimitError($e)) {
                        Log::warning("Model {$modelId} hit rate limit, trying next: " . $e->getMessage());
                        continue;
                    }
                    throw $e;
                }
            }

            throw $lastException ?? new Exception('All models failed. Please try again later.');
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Session-ID' => $currentSessionId, // Send session ID in header for frontend
        ]);
    }
}
