<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | Public Judge0 CE — no API key required.
    | Default host: ce.judge0.com (official free public API).
    | Set JUDGE0_BASE_URL if you use another Judge0-compatible endpoint.
    */
    'judge0' => [
        'enabled' => env('JUDGE0_ENABLED', true),
        'base_url' => env('JUDGE0_BASE_URL', 'https://ce.judge0.com'),
        'api_key' => env('JUDGE0_API_KEY'),
        'timeout' => (int) env('JUDGE0_TIMEOUT', 30),
    ],

    'code_runner' => [
        'fallback_enabled' => env('CODE_RUNNER_FALLBACK', true),
        'timeout' => (int) env('CODE_RUNNER_TIMEOUT', 8),
    ],

    /*
    | AI Instructor: Gemini direct is default (reliable). Set AI_PREFER_N8N=true to try n8n first.
    */
    'ai' => [
        'prefer_n8n' => filter_var(env('AI_PREFER_N8N', false), FILTER_VALIDATE_BOOLEAN),
    ],

    'n8n' => [
        'webhook_url' => env('N8N_WEBHOOK_URL'),
        'timeout' => (int) env('N8N_WEBHOOK_TIMEOUT', 90),
        'max_knowledge_context_chars' => (int) env('N8N_MAX_KNOWLEDGE_CONTEXT_CHARS', 10000),
    ],

    /*
    | Direct Gemini (primary). Same key as Google AI Studio.
    */
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'model_fallbacks' => env('GEMINI_MODEL_FALLBACKS', 'gemini-2.0-flash,gemini-1.5-flash'),
        'embedding_model' => env('GEMINI_EMBEDDING_MODEL', 'gemini-embedding-001'),
        'embedding_dimensions' => (int) env('GEMINI_EMBEDDING_DIMENSIONS', 768),
    ],

    'knowledge' => [
        'enabled' => env('KNOWLEDGE_RAG_ENABLED', true),
        'top_k' => (int) env('KNOWLEDGE_TOP_K', 5),
        'min_similarity' => (float) env('KNOWLEDGE_MIN_SIMILARITY', 0.35),
        'chunk_size' => (int) env('KNOWLEDGE_CHUNK_SIZE', 1500),
        'chunk_overlap' => (int) env('KNOWLEDGE_CHUNK_OVERLAP', 200),
        'embed_delay_ms' => (int) env('KNOWLEDGE_EMBED_DELAY_MS', 150),
    ],

];
