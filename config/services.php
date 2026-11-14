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
    |--------------------------------------------------------------------------
    | Audit Nota dengan AI (NotaperiksaAi)
    |--------------------------------------------------------------------------
    | Lapis AI untuk memeriksa teks bebas berkas vs fakta nota, di atas
    | rekonsiliasi deterministik (RekonsiliasiNotaService). DRIVER KOSONG =
    | FITUR MATI total, tanpa efek samping apa pun. Isi di .env (kewenangan
    | owner):
    |
    |   AI_NOTA_DRIVER=ollama
    |   AI_NOTA_URL=http://127.0.0.1:11434   (bawaan, Ollama lokal)
    |   AI_NOTA_MODEL=llama3.1               (model yang sudah di-pull)
    |
    | Dipakai lewat command `nota:audit-ai` (manual/jadwal), hasilnya
    | disimpan di tabel `nota_audit_ai` lalu tampil di halaman Berkas
    | Order - TIDAK pernah dipanggil di request halaman biasa.
    */
    'ai_nota' => [
        'driver' => env('AI_NOTA_DRIVER'),
        'url' => env('AI_NOTA_URL', 'http://127.0.0.1:11434'),
        'model' => env('AI_NOTA_MODEL', 'llama3.1'),
        'timeout' => (int) env('AI_NOTA_TIMEOUT', 120),
    ],

];
